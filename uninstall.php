<?php

// If uninstall is not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
	exit();
}

// Erase Cue settings
wp_clear_scheduled_hook('cue_sync_hook');
delete_option('woocommerce_cue_settings');
delete_option('woocommerce_cue_place_id');
delete_option('woocommerce_cue_api_key');
delete_option('woocommerce_cue_last_sync');
delete_option('woocommerce_cue_sync_queue');
delete_option('cue_options');
delete_option('cue_version');