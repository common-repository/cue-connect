<?php

/**
 * Disable direct access
 */
if (!defined('ABSPATH')) {
    die();
}
/**
 * Cue Sync
 *
 * @since 1.0.3
 */
class CueSync
{

    /**
     * Stores cue options
     * @since 1.0.3
     */
    private static $_options;

    /**
     * Sync products every $_syncInterval hours
     * @var int
     * @since 1.0.6
     */
    protected static $_sync_interval=6;

    /**
     * Checks if we need to sync products
     *
     * @since  1.0.6
     * @return array['started' => true, 'next_sync_in' => int hours];
     */
    public static function checkSchedule($last_sync = false)
    {
        // start sync and bail if never synced before
        if (!$last_sync || empty($last_sync)) {
            CueSync::startSync();
            return true;
        }

        $old_time = new DateTime($last_sync);
        $now_time = new DateTime(date('r'));
        $diff = $now_time->diff($old_time);

        if ($diff->h > self::$_sync_interval) {
            CueSync::startSync();
            return true;
        }

        $next_sync_in = self::$_sync_interval - $diff->h;

        return $next_sync_in;
    }

    /**
     * Starts sync process
     *
     * @return boolean
     */
    public static function startSync() {
        // Check if sync not running and user is authenticated with Cue
        self::$_options = CueOptions::get();

        if (!self::$_options['api_key'] || !self::$_options['place_id']) {
            return false;
        }

        // lock to prevent parallel sync of same data
        update_option('woocommerce_cue_sync_queue', 1);

        // Get ids of products that need to be exported
        $products_ids = self::get_wc_products();

        if (!count($products_ids)) {
            // bail if nothing to sync          
            self::unlock();
            self::$_options['last_sync'] = date('r');
            CueOptions::set(self::$_options);
            return true;
        }

        // Convert products to Cue format
        $products = self::prepare_products($products_ids);

        // Make request
        $api = CueApi::get_instance();

        $slices = self::get_slices_from_array($products, 100);

        foreach ($slices as $slice) {
            $request = array(
                "service" => "product",
                "action" => "set",
                "data" => $slice,
                "username" => self::$_options['username'],
                "password" => self::$_options['password']
            );
            $response = $api->request($request);            
        }

        if (is_array($response) && isset($response['success'])) {
            // Update last sync value
            self::$_options['last_sync'] = date('r');
            CueOptions::set(self::$_options);
        } else {
            // log failed sync
        }

        self::unlock();
        return true;
    }

    /**
     * Get all woocommerce products from wordpress database
     * If last_sync option is set - get only updated & recently created 
     * products
     *
     * @since 1.0.3
     * @return array products IDs
     */
    private static function get_wc_products() {
        global $wpdb;
        $current_sync = date('r');
        $wc_product_ids = array();
        $last_sync_sql = "";
        $last_sync = self::$_options['last_sync'];
        if ($last_sync) {
            $last_sync_str = date('Y-m-d H:i:s', strtotime($last_sync));
            $last_sync_sql = " AND post_modified_gmt > '{$last_sync_str}'";
        }
        $sql = "
            SELECT  ID 
            FROM    {$wpdb->posts} 
            WHERE   post_type='product' 
            AND     post_status='publish'
            {$last_sync_sql}
            ";
        $wc_product_ids = $wpdb->get_results($sql);
        $result = array();
        if (count($wc_product_ids)) {
            foreach($wc_product_ids as $wc_product_id) {
                $result[] = $wc_product_id->ID;
            }
        }
        return $result;
    }

    /**
     * Convert Woocommerce product to cue product
     *
     * @since 1.0.3
     * @param  array $ids array of woocommerce products IDs
     * @return array products ready to be sent to cue app
     */
    public static function prepare_products($ids) {
        global $wpdb;

        $products_id_sql = implode(',', array_map('intval', $ids));

        $sql = "

            SELECT      p.ID as 'sku', 
                        p.post_title as 'name',
                        p.post_content as 'description',
                        m1.meta_value as 'price',
                        m2.meta_value as 'icon'
            FROM        {$wpdb->posts} p
            LEFT JOIN   {$wpdb->postmeta} m1
                        ON m1.post_id=p.ID AND m1.meta_key = '_price'
            LEFT JOIN   {$wpdb->postmeta} m2
                        ON m2.post_id=p.ID AND m2.meta_key = '_thumbnail_id'
            WHERE       p.ID IN ({$products_id_sql})
            ORDER BY    p.ID

            ";


        $products = $wpdb->get_results($sql, ARRAY_A);
        $updated = array();
        foreach ($products as $product) {
            
            $product['description'] = self::strip_tags_shortcodes($product['description']);
            $product['url'] = get_permalink($product['sku']);
            $product['icon'] = wp_get_attachment_url($product['icon']);
            $product['price'] = @number_format((float)$product['price'], 2, '.', '');
            $product['live'] = 1;

            $updated[] = $product;
        }
        return $updated;
    }

    public static function get_slices_from_array($data, $slice_size)
    {
        $slices = array();
        if (count($data) > $slice_size) {
            $i = 0;
            while ($i < count($data)) {
                if ($i % $slice_size == 0) {
                    $slices[] = array_slice($data, $i,  $slice_size);
                }
                $i++;
            }
        } else {
            $slices[] = $data;
        }
        return $slices;
    }

    /**
     * Strip html and shortcode tags from string
     *
     * @since 1.0.3
     * @param  string $content
     * @return string cleaned up
     */
    public static function strip_tags_shortcodes($content) {
        $content = wp_strip_all_tags($content, $remove_breaks = true);
        $content = preg_replace("~\[.*?\]~", '', $content);
        $content = esc_html($content);
        return $content;
    }

    /**
     * Removes sync lock
     *
     * @since 1.0.3
     * @return null
     */
    private static function unlock() {
        update_option('woocommerce_cue_sync_queue', null);
        return;
    }
}