<?php

// disable direct access
defined('ABSPATH') || die();

class Cue
{

    private static $_instance;
    private static $_options;
    private static $_env;

    private static $_version = "1.0.3";

    public static function getInstance()
    {
        if (!isset(self::$_instance) || empty(self::$_instance)) {
            self::$_instance = new Cue;
        }
        return self::$_instance;
    }

    private function __construct()
    {

        $this->versionCheck();

        self::$_env = CueEnv::$env;
        self::$_options = CueOptions::get();

        CueSync::checkSchedule(self::$_options['last_sync']);

        if (is_admin()) {
            CueOptions::init();
        }

        // add /apps/mylist endpoint if CP is enabled
        if (('2' == self::$_options['version']) && self::$_options['place_id']) {

            add_action('init', array($this, 'mylistRewrite'));
            add_filter('query_vars', array($this, 'mylistQueryVars'));
            add_action('template_redirect', array($this, 'mylistChangeTemplate'));
            add_action('init', array($this, 'mylistTitleFilter'));

            // Add My List registration hook
            $api = CueApi::get_instance();
            add_action('wp_enqueue_scripts', array($this, 'cueFrontendScripts'));
            add_action('wp_login', array($api, 'loginCustomer'), 10, 2);
            add_action('woocommerce_login_redirect', array($this, 'mylistLoginRedirect'));

        }

        if (get_option('_cue_version_switched')) {
            add_action('init', 'flush_rewrite_rules');
            update_option('_cue_version_switched', null);
        }

        if (self::$_options['place_id'] && self::$_options['api_key']) {

            // Add Cue frontend script
            add_action('wp_footer', array($this,'cueFrontEnd'));

            // Add OB to product page
            add_action(
                'woocommerce_single_product_summary',
                array($this, 'productAddOB'),
                20
            );

            // Add OB to cart page
            add_action(
                'woocommerce_proceed_to_checkout',
                array($this, 'cartAddOB'),
                0
            );

            // Add converstion tracking code
            add_action(
                'woocommerce_thankyou',
                array($this, 'checkoutTrackingPixel')
            );

            // Create the hooks for product synchronization
            if (is_admin() && get_option('cue_activated_plugin') == 'true') {
                delete_option('cue_activated_plugin');
            };
        }        
    }

    private function versionCheck()
    {
        $version = get_option('cue_connect_version', false);
        if (!$version || $version != self::$_version) {
            $options = CueOptions::get();

            $settings = get_option(
                'woocommerce_cue_settings',
                array(
                    'username' => null,
                    'password' => null
                )
            );

            $options['username'] = !empty($settings['username'])?$settings['username']:$options['username'];
            $options['password'] = !empty($settings['password'])?$settings['password']:$options['password'];

            if ($api_key = get_option('woocommerce_cue_api_key')) {
                $options['api_key'] = $api_key;
            }
            if ($place_id = get_option('woocommerce_cue_place_id')) {
                $options['place_id'] = $place_id;
            }
            if ($last_sync = get_option('woocommerce_cue_last_sync')) {
                $option['last_sync'] = $last_sync;
            }

            // Save updated options
            $env = get_option('cue_env', 'prod');
            $options_key = $env == 'prod' ? 'cue_options':'cue_'.$env.'_options';
            update_option($options_key, $options);

            // Remove unused options from database
            delete_option('woocommerce_cue_settings');
            delete_option('woocommerce_cue_api_key');
            delete_option('woocommerce_cue_place_id');

            // Update plugin version value to current
            update_option('cue_version', self::$_version);
        }
    }

    public function cueFrontendScripts()
    {
        wp_enqueue_style('cue-frontend', CUE_PLUGIN_URL . "assets/frontend.css");
    }

    public function mylistRewrite()
    {
        add_rewrite_rule('^apps/mylist$', 'index.php?apps_mylist=1', 'top');
        add_rewrite_endpoint('apps/mylist', EP_PERMALINK | EP_PAGES);
    }

    public function mylistQueryVars($vars)
    {
        $vars[] = 'apps_mylist';
        return $vars;
    }

    public function mylistTitleFilter()
    {
        add_filter('pre_get_document_title', array($this, 'mylistTitle'), 10, 3);
        add_filter('wp_title', array($this, 'mylistTitle'), 10, 3);
    }

    public function mylistTitle($title) {
        global $wp_query;
            if (isset($wp_query->query_vars['apps_mylist'])) {
            $title = "My List &mdash; " . get_bloginfo('name');
        }
        return $title;
    }

    public function mylistChangeTemplate($template)
    {
        global $wp_query;

        if (!isset($wp_query->query_vars['apps_mylist'])) {
            return $template;
        }

        $errors = array();

        $register = array(
            'cue_register_email' => isset($_POST['cue_register_email'])?trim($_POST['cue_register_email']):"",
            'cue_register_password' => isset($_POST['cue_register_password'])?$_POST['cue_register_password']:"",
            'cue_register_fname' => isset($_POST['cue_register_fname'])?trim($_POST['cue_register_fname']):"",
            'cue_register_lname' => isset($_POST['cue_register_lname'])?trim($_POST['cue_register_lname']):"",
        );

        $login = array(
            'cue_login_email' => isset($_POST['cue_login_email'])?trim($_POST['cue_login_email']):"",
            'cue_login_password' => isset($_POST['cue_login_password'])?$_POST['cue_login_password']:""
        );

        if (isset($_POST['cue_action']) && !empty($_POST['cue_action'])) {
            switch ($_POST['cue_action']) {
                case 'register' :
                    $errors = $this->mylistRegister($register);
                break;
                case 'login' :
                    $errors = $this->mylistLogin($login);
                break;
                default :
                break;
            }
        }

        get_header();
        
        echo "<div class='cue-mylist-wrapper'>";


        if ('2' == self::$_options['version'] && !empty(self::$_options['api_key'])) {
            if (is_user_logged_in() && !is_admin()) {
                $this->mylistIframe();
            } else {
                require_once CUE_PLUGIN_DIR . 'templates/cue-login-form.php';
            }
        }

        echo "</div>";

        get_footer();

        exit;
    }

    public function mylistIframe()
    {
        global $current_user;
        $user = CueApi::get_wc_user($current_user->ID);
        $params = array(
            'version' => 'embed',
            'from' => 'stream',
            'origin' => get_bloginfo('url'),
            'email' => $user['email'],
            'fname' => $user['firstName'],
            'lname' => $user['lastName']
        );
        $place_id = self::$_options['place_id'];
        $src_url = "https://" . CueEnv::$env['consumer'] . "/poweredby/{$place_id}/?";
        $src_url .= http_build_query($params);
        ?>

        <iframe 
            id="streamIFrame" 
            name="streamIFrame"
            src="<?php echo esc_html($src_url) ?>"
            height="600px" 
            width="100%"
            frameborder=0
            style="border:none;display:block;width:100%;margin: 0 auto;"
            scrolling="no"></iframe>

        <?php
    }

    public function mylistLogin($data)
    {
        $errors = array();

        if (empty($data['cue_login_email'])) {
            $errors[] = 'Please enter your email address';
            return $errors;
        }

        if (empty($data['cue_login_password'])) {
            $errors[] = 'Please enter password';
            return $errors;
        }

        $login_success = wp_signon(
            array(
                'user_login' => $data['cue_login_email'],
                'user_password' => $data['cue_login_password'],
                'remember' => $data['cue_login_remember']                
            )
        );

        if (isset($login_success->errors)) {
            foreach ($login_success->errors as $error) {
                $errors[] = $error[0];
            }
            return $errors;
        }

        if ($login_success) {
            wp_redirect('/apps/mylist/');
            exit;
        }

        return $errors;
    }

    public function mylistRegister($data)
    {
        $user_id = username_exists($data['cue_register_email']);
        $errors = array();

        if ($user_id || email_exists($data['cue_register_email']) != false) {
            $errors[] = "User with this email is already registered, please use 'Forgot password' form";
            return $errors;
        }

        if (empty($data['cue_register_email'])) {
            $errors[] = "Please enter your email address";
            return $errors;
        } else {
            if (!filter_var($data['cue_register_email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Please enter valid email address";
                return $errors;
            }
        }

        if (empty($data['cue_register_password'])) {
            $errors[] = "Please enter password";
            return $errors;
        }

        if (strlen($data['cue_register_password']) < 6) {
            $errors[] = "Password must be at least 6 charachters long";
            return $errors;
        }

        if (empty($errors)) {
            $password = $data['cue_register_password'];
            $user_id = wp_create_user($data['cue_register_email'], $password, $data['cue_register_email']);
        }

        if ($user_id) {

            if (!empty($data['cue_register_fname'])) {
                update_user_meta($user_id, 'first_name', trim($data['cue_register_fname']));
            }

            if (!empty($data['cue_register_lname'])) {
                update_user_meta($user_id, 'last_name', trim($data['cue_register_lname']));
            }

            $login_success = wp_signon(
                array(
                    'user_login' => $data['cue_register_email'],
                    'user_password' => $data['cue_register_password'],
                    'remember' => 1
                )
            );

            if (isset($login_success->errors)) {
                foreach ($login_success->$errors as $error) {
                    $errors[] = $error[0];
                }
                return $errors;
            }

            if ($login_success) {
                wp_redirect('/apps/mylist/');
                exit;
            }
        } else {
            $errors[] = 'Registration failed, please try again with different email or password';
        }

        return $errors; 
    }

    public function mylistLoginRedirect($redirect_to)
    {
        $redirect_to = get_bloginfo('url') . '/apps/mylist';
        return $redirect_to;
    }

    public function productAddOB()
    {
        $imiSku = get_the_ID();
        $cid = is_user_logged_in();
        ?>

        <div class="cue-wrapper" style="margin: 1em 0">
        <div style="display: inline-block; margin-right: 1em;" class="cue-onebutton" data-imisku="<?php echo $imiSku;?>" data-cid="<?php echo $cid; ?>"></div> 
        <div style="display: inline-block; margin-right: 1em;" class="cue-cueit" data-imisku="<?php echo $imiSku;?>" data-cid="<?php echo $cid; ?>"></div>
        <div style="display: inline-block;" class="cue-learnmore" data-imisku="<?php echo $imiSku;?>" data-cid="<?php echo $cid; ?>"></div>
        </div>

        <?php
    }

    public function cartAddOB()
    {
        $items = array();

        global $woocommerce;
        $items = $woocommerce->cart->get_cart();

        // bail if no items in cart
        if (!count($items)) {
            return;
        }

        $item = reset($items);
        $imiSku = $item['data']->post->ID;
        $cid = is_user_logged_in();

        ?>
        
        <style>.cue-wrapper iframe{margin:0;padding:0;}.cue-wrapper>* {display:inline-block}</style>

        <div class="cue-wrapper" style="margin:1em 0; clear:both;">
            <div class="cue-onebutton" data-imisku="<?php echo $imiSku;?>" data-cid="<?php echo $cid; ?>"></div> 
        </div>

        <?php
    }

    public function cueFrontend()
    {
        global $current_user;
        $poweredby_url ="https://" . self::$_env['poweredby'] . "/js/cue-seed.js";
?>

<script src="<?php echo $poweredby_url ?>"></script> 
<script>
CUE({
    'retailId': <?php echo self::$_options['place_id']; ?>,
    'apiKey': "<?php echo self::$_options['api_key']; ?>",
    'cid' : <?php echo is_user_logged_in()?$current_user->ID:'null'; ?>,
    'path' : "<?php echo get_bloginfo('url'); ?>/apps/mylist"
});
</script>

<?php
    }

    /**
     * Renders tracking pixel gif image.
     * Url format:
     * //api.cueconnect.com/imi/cart_track/json?
     *      api_key=XXX
     *      &place_id=XXX
     *      &email=user@email.com
     *      &cart=sku:quantity,444:3,123:1
     *      &order_id=order_id
     *
     * @since 1.0.3
     * @param int order_id
     * @return tracking pixel url
     */
    public function checkoutTrackingPixel($order_id)
    {
        $order = new WC_Order($order_id);

        $user_email = get_user_meta(
            $order->user_id,
            'billing_email',
            true
        );

        $cart = array();
        foreach ($order->get_items() as $item) {
            $cart[] = "{$item['product_id']}:{$item['qty']}";
        }

        $params = array(
            'api_key' => self::$_options['api_key'],
            'place_id' => self::$_options['place_id'],
            'email' => $user_email,
            'cart' => implode(',', $cart),
            'order_id' => $order_id
        );

        $url = "https://" . self::$_env['api'] . "/imi/cart_track?" . http_build_query($params);
        
        ?>

<!-- Cue Conversion Tracking START -->        
<img src="<?php echo $url ?>" width=1 height=1 style="display:block;position:absolute;width:1px;height:1px;background:none;">
<!-- Cue Conversion Tracking END -->

        <?php

        return $url;
    }

    public static function pluginActivate()
    {
        // Workaround to be able to trigger actions
        // https://codex.wordpress.org/Function_Reference/register_activation_hook#Process_Flow
        add_option('cue_activated_plugin', 'true');
    }

    /**
     * Executed when the plugin is deactivated
     */
    public static function pluginDeactivate()
    {
        // Disable products synchronization cron
        wp_clear_scheduled_hook('cue_sync_hook');
        delete_option('woocommerce_cue_place_id');
        delete_option('woocommerce_cue_api_key');
        delete_option('woocommerce_cue_settings');
        delete_option('woocommerce_cue_last_sync');
        delete_option('woocommerce_cue_sync_queue');
        delete_option('cue_options');
        delete_option('cue_version');
    }

    /**
     * Executed when the plugin is uninstalled
     */
    public static function pluginUninstall()
    {
        // Erase Cue settings
        wp_clear_scheduled_hook('cue_sync_hook');
        delete_option('woocommerce_cue_settings');
        delete_option('woocommerce_cue_place_id');
        delete_option('woocommerce_cue_api_key');
        delete_option('woocommerce_cue_last_sync');
        delete_option('woocommerce_cue_sync_queue');
        delete_option('cue_options');
        delete_option('cue_version');
    }

}
