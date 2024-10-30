<?php

// Disable direct access
if (!defined('ABSPATH')) {
    die();
}
/**
 * Cue Api
 *
 * Connects to cue proxy to make requests
 *
 * @since 1.0.3
 */
class CueApi
{

    private static $_instance;

    protected $api_url = "http://proxy.cueconnect.net/v1/";

    /**
     * Request template, used in sanitize, add your values to validate them
     *
     * @since 1.0.3
     */
    private $request = array(
        'env' => '',
        'username' => '',
        'password' => '',
        'service' => '',
        'action' => '',
        'data' => ''
    );

    private function __construct()
    {

    }

    public static function get_instance()
    {
        if (!isset(self::$_instance) || empty(self::$_instance)) {
            self::$_instance = new CueApi;
        }
        return self::$_instance;
    }

    // TODO: CP webhooks
    private $webhook_actions = array(
        'save_customer' => array(
            'action' => 'saveCustomer',
            'key' => '026132282952ccf731a765413bf11ca32ccbc683ab92c7ad3171b2a0a71ef21d69d1334efcf79da5ecc2d496221e0e28ed025e71d210ebf16e70bfa338c4f2d3',
        ),

        'select_version' => array(
            'action' => 'selectVersion',
            'key' => 'a2645def81375a4f88475acc6b4b0639fd87bfaef715b828e1704f79bac6262ef0e85876dd7047765360678b3372812b5f1741b3abfe9159a3d50fe01e05d757'
        )
    );

    /**
     * Validates request array
     *
     * @since 1.0.3
     */
    private function sanitize_request($data)
    {
        $data['env'] = CueEnv::$env['imi_loc'];
        $request = array();
        foreach ($this->request as $key=>$value) {
            $request[$key] = isset($data[$key])?$data[$key]:null;
        }
        return $request;
    }

    private function get_webhook_url()
    {
        $url = "https://" . CueEnv::$env['merchant'] . '/magento/';
        return $url;
    }

    /**
     * Makes request to que proxy
     *
     * @since 1.0.3
     */
    public function request($payload)
    {
        $payload = $this->sanitize_request($payload);
        $response = wp_remote_post( $this->api_url, array(
            'method' => 'POST',
            'timeout' => 45,
            'redirection' => 5,
            'httpversion' => '1.0',
            'blocking' => true,
            'headers' => array(),
            'body' => $payload,
            'cookies' => array()
            )
        );
        if (is_array($response)) {
            $data = isset($response['body'])?json_decode($response['body'], ARRAY_A):false;
        } else {
            $data = false;
        }
        return $data;
    }

    /**
     * Request place_id and api_key from Cue app
     *
     * @since 1.0.3
     */
    public function get_place($username, $password)
    {
        $request = array(
            "service" => "place",
            "action" => "get",
            "username" => $username,
            "password" => $password
        );
        $response = $this->request($request);
        if (!isset($response['data']['data'])) {
            return false;
        }
        return $response['data']['data'];
    }

    // TODO: CP webhooks

    /**
     * Sync customer to Cue database
     * 
     * @since 1.0.8
     * @param int $user_id
     * @return bool success
     */

    private function syncCustomer($user_id)
    {
        // Check if we need to sync
        $already_synced = get_user_meta($user_id, 'cue_sync', true);
        // if ('1' == $already_synced) {
        //     return true;
        // }

        // Gather data
        $options = CueOptions::get();
        $api_key = $options['api_key'];
        $place_id = $options['place_id'];
        $webhook_url = $this->get_webhook_url() . 'saveCustomer';
        $customer = $this->get_wc_user($user_id);
        
        $auth_str = $this->webhook_actions['save_customer']['key']
            . $webhook_url
            . $customer['id']
            . $customer['email'];

        $auth_key = sha1($auth_str) . "$" . $api_key;
        $response = $this->do_webhook($webhook_url, $auth_key, $customer);

        // Save results
        if ($response) {
            update_user_meta($user_id, 'cue_sync', '1');
        } else {
            update_user_meta($user_id, 'cue_sync', null);
        }
        return $response;
    }

    public function loginCustomer($user_login, $user)
    {
        $user_id = $user->ID;
        return $this->syncCustomer($user_id); 
    }

    public function get_wc_user($user_id) {
        global $wpdb;

        $options = CueOptions::get();

        $user_sql = "

            SELECT      user_email, user_registered
            FROM        {$wpdb->users} u
            WHERE       ID='$user_id'

        ";

        $user = $wpdb->get_row($user_sql, ARRAY_A);

        $user_meta_sql = "

            SELECT *
            FROM    {$wpdb->usermeta} um
            WHERE   user_id='$user_id'
            AND (
                    meta_key='first_name'
                OR  meta_key='last_name'
            )

        ";

        $user_meta = $wpdb->get_results($user_meta_sql, ARRAY_A);

        if (count($user_meta)) {
            foreach ($user_meta as $meta) {
                $user[$meta['meta_key']] = $meta['meta_value'];
            }
        }

        $response = array(
            'storeId'    => $options['place_id'],
            'id'         => $user_id,
            'email'      => $user['user_email'],
            'fullName'   => trim("{$user['first_name']} {$user['last_name']}"),
            'firstName'  => $user['first_name'],
            'lastName'   => $user['last_name'],
            'created_at' => $user['user_registered'],
            'dob'        => null,
            'gender'     => null,
        );
        return $response;
    }

    public function select_version($value) {

        $options = CueOptions::get();

        $url = $this->get_webhook_url() . 'selectVersion';
        $version = $value;

        $str = 
            "v$version" 
            . $this->webhook_actions['select_version']['key'] 
            . $this->get_webhook_url() . "selectVersion"
            . $options['api_key'];

        $key = sha1($str) . "$" . $options['api_key'];

        $params = array(
            'version' => $version
        );

        
        $response = (int)$this->do_webhook($url, $key, $params);
        if ($response == $options['place_id']) {
            return true;
        } else {
            return false;
        }
        return false;
    }

    public function do_webhook($url, $key, $params)
    {
        $response = null;

        // do POST - use curl
        if (function_exists('curl_version')) {
            try {
                
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER,array('X-Cue-Mage-Auth: ' . $key));

                $response = curl_exec($ch);
                $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);  
                curl_close($ch); 

            } catch (Exception $e) {
                // TODO: Log errors
            }
        }
        // do GET
        else {
            $params['key'] = $key;
            $queryString  = '?' . http_build_query($params);
            try {
                $response_get = wp_remote_get($url . $queryString);
                if (is_array($response_get)) {
                    $response = $response_get['body'];
                }
            } catch (Exception $e) {
                // TODO: Log errors
            }
        }
        return $response;
    }
}
