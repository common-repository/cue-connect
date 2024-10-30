<?php
// disable direct access
if (!defined('ABSPATH')) {
    exit();
}

/**
 * Cue Options
 *
 * @since 1.0.3
 * @package Cue
 */
class CueOptions
{
    /**
     * Options template, also used for sanitize, add your new option key here
     * or it will not be saved
     *
     * @since 1.0.3
     */
    public static $options = array(
        'version' => 1,
        'username' => '',
        'password' => '',
        'api_key' => '',
        'place_id' => '',
        'last_sync' => ''
    );

    private static $_notices = array();

    /**
     * Default value for cue options database key
     *
     * @since 1.0.3
     */
    private static $options_key = 'cue_options';

    /**
     * Adds hooks to render wp admin page;
     *
     * @since 1.0.3
     */
    public static function init()
    {
        if (is_admin()) {
            add_action('admin_menu', array('CueOptions','admin_menu'));
            add_action(
                'admin_enqueue_scripts', 
                array('CueOptions','enqueue_scripts')
            );
        }
    }
    /**
     * Retrieves value of cue options key in database
     *
     * @since 1.0.3
     */
    private static function get_options_key()
    {
        $env = get_option('cue_env', 'prod');
        self::$options_key = $env == 'prod'?'cue_options':'cue_'.$env.'_options';
        return self::$options_key;
    }

    /**
     * Returns cue options
     *
     * @since 1.0.3
     */
    public static function get()
    {
        $options = get_option(self::get_options_key(), self::$options);
        $sanitized_options = array();
        foreach (self::$options as $key=>$value) {
            $sanitized_options[$key] = isset($options[$key])?$options[$key]:$value;
        }
        return $sanitized_options;
    }

    /**
     * Saves cue options, connects to cue, starts sync
     *
     * @since 1.0.3
     */
    public static function set($options)
    {
        $sanitized_options = array();
        self::$options = self::get();

        /** 
         * Check if credentials were changed and try to connect to Cue 
         * if they did 
         */
        $connected = false;

        $new = $options;
        $old = self::get();

        if (
            $options['username'] !== self::$options['username']
            || $options['password'] !== self::$options['password']
        ) {
            $connected = self::connect_cue_account(
                $options['username'],
                $options['password']
            );
            if ($connected) {
                self::$_notices['credentials'] = array(
                    'type' => 'success',
                    'message' => "Your Cue account successfully connected to Woocommerce"
                );
            } else {
                if (!empty(self::$options['username']) && !empty(self::$options['password'])) {                
                    self::$_notices['credentials'] = array(
                        'type' => 'error',
                        'message' => "Error. Please check credentials and try again."
                    );
                }
            }
        }

        /**
         * Check if PB/CP version has changed and send webhook if true
         */
        
        if (!empty($new['version']) && $new['version'] !== $old['version']) {
            $version_changed = self::cue_select_version($new['version']);
            if ($version_changed) {
                self::$_notices['credentials'] = array(
                    'type' => 'success',
                    'message' => "My-List version changed successfully"
                );
                update_option('_cue_version_switched', 1);
            }
        }

        // Update cue options in database
        foreach (self::$options as $key=>$value) {
            $sanitized_options[$key] = isset($options[$key])?$options[$key]:$value;
        }

        update_option(self::get_options_key(), $sanitized_options);

        // Schedule export if connection with Cue was established
        if ($connected) {
            update_option('cue_activated_plugin', true);
        }

        return 1; 
    }

    /**
     * Retrieves place_id and api_key from Cue
     *
     * @since 1.0.3
     */
    public static function connect_cue_account($username, $password)
    {
        // Flush previous values
        self::$options['last_sync'] = null;
        self::$options['place_id'] = null;
        self::$options['api_key'] = null;

        // Make request
        $api = CueApi::get_instance();
        $data = $api->get_place($username, $password);

        // Bail if request failed
        if (!$data) {
            return false;
        }

        // Update options
        self::$options['place_id'] = $data['id'];
        self::$options['api_key'] = $data['api_key'];

        return true;
    }

    /**
     * Call to magento webhook, asking to change CP/PB
     * 
     * @param  int      $value 1:PB, 2:CP
     * @return bool     true on success
     */
    private static function cue_select_version($value)
    {
        $api = CueApi::get_instance();
        $response = $api->select_version($value);
        return $response;
    }

    /**
     * Hook wordpress admin page
     *
     * @since 1.0.3
     */
    public static function admin_menu()
    {
        add_menu_page(
            'Cue Connect',
            'Cue Connect',
            'administrator',
            'cue-options-page',
            array('CueOptions','options_page')
        );
    }

    /**
     * Add admin page js and css
     * @since 1.0.3
     */
    public static function enqueue_scripts()
    {
        wp_enqueue_script( 
            'cue-script', 
            CUE_PLUGIN_URL . 'assets/script.js', 
            array('jquery'), 
            $ver = null, 
            $in_footer = true
        );
        wp_enqueue_style( 
            'cue-style', 
            CUE_PLUGIN_URL . 'assets/style.css', 
            $deps = null, 
            $ver = null, 
            $media = null
        );
    }

    /**
     * Updates cue options with post data from admin page
     *
     * @since 1.0.3
     */
    public static function process_post_data()
    {
        if (isset($_POST['cue_options']) 
            && (isset($_POST['action']) 
            && $_POST['action'] == 'update')) 
        {
            self::set($_POST['cue_options']);
        }
    }

    /**
     * Renders wp admin page
     *
     * @since 1.0.3
     */
    public static function options_page()
    {

        self::process_post_data();

?>

<div id="cue-container" class="wrap">

    <?php self::render_admin_notices(); ?>
    <?php $options = self::get(); ?>

    <h2>Cue Connect</h2>

    <form action="" class="cue-form" method="post">
    <div class="cue-section">
    <h4>Merchant Hub Credentials</h4>
    <?php if (empty($options['username']) && empty($options['password'])) : ?>
    <p>Enter your Merchant Cue Connect Username and Password</p>
    <?php endif; ?>
    <?php
        cue_options_fields::textfield(
            array(
                'name' => 'username',
                'label' => 'username'
            )
        );
        cue_options_fields::textfield(
            array(
                'name' => 'password',
                'label' => 'password',
                'type' => 'password'
            )
        );
    ?>
    </div>

    <?php if ($options['place_id'] && $options['api_key']) : ?>
    <div class="cue-section">
        <h4>My-List Version</h4>
        <p>Available in two versions: a customizable widget or enterprise fully 
        integrated that works seamlessly with your existing Customer Account and 
        Profile.</p>
        <p><strong>Standalone Widget:</strong> My-List technology is deployed on 
        a standalone widget. Shoppers will opt into this service by signing up 
        through the My-list login popup modal.</p>
        <p><strong>Fully Integrated:</strong> works seamlessly with your 
        existing Customer Account and Profile. Shoppers simply need to create 
        an account with your store to have access to My-List capabilities.
        </p>

        <?php
        cue_options_fields::select(
            'version',
            $options['version'],
            array(
                1 => 'Standalone',
                2 => 'Fully Integrated'
            )
        );
        ?>
    </div>
    <?php endif; ?>

    <?php settings_fields( 'cue-options-page' ); ?>
    <?php submit_button(); ?>

    </form>

</div>

<?php
    }

    /**
     * Shows notices to user
     *
     * @since 1.0.3
     */
    public static function render_admin_notices()
    {
        if (is_array(self::$_notices) && !empty(self::$_notices)) {
            foreach (self::$_notices as $notice) {
?>

    <div class="notice notice-<?php echo $notice['type'] ?> is_dismissible">
        <p><?php _e($notice['message'], 'cue_connect') ?></p>
    </div>

<?php
            }
        }
    }
}