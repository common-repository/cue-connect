<?php
/**
 * Plugin Name: Cue Connect
 * Plugin URI: https://wordpress.org/plugins/cue-connect
 * Description: Meet Cue, your customers’ personal shopping assistant that feeds you actionable data.
 * Version: 1.0.8
 * Author: Cue Connect
 * Author URI: https://business.cueconnect.com/
 * Developer: Cue Connect
 * Developer URI: https://business.cueconnect.com/
 * Text Domain: cue-connect
 *
 * Copyright: © 2009-2016 WooThemes.
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

// Disable direct access
if (!defined('ABSPATH')) {
    die();
}

/**
 * Check if WooCommerce is active
 */
if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {

    define('CUE_PLUGIN_DIR', plugin_dir_path(__FILE__));
    define('CUE_PLUGIN_URL', plugin_dir_url(__FILE__));

    register_activation_hook(__FILE__, array('Cue', 'pluginActivate'));
    register_deactivation_hook(__FILE__, array('Cue', 'pluginDeactivate'));
    register_uninstall_hook(__FILE__, array('Cue', 'pluginUninstall'));

    require_once CUE_PLUGIN_DIR . 'inc/class.cue-env.php';
    require_once CUE_PLUGIN_DIR . 'inc/class.cue-options.php';
    require_once CUE_PLUGIN_DIR . 'inc/class.cue-options-fields.php';
    require_once CUE_PLUGIN_DIR . 'inc/class.cue-api.php';
    require_once CUE_PLUGIN_DIR . 'inc/class.cue-sync.php';
    require_once CUE_PLUGIN_DIR . 'inc/class.cue.php';

    add_action('plugins_loaded', array('Cue','getInstance'));
    
}
