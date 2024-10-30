<?php
// disable direct access
defined('ABSPATH') || die();

class CueEnv {
	
	private static $_instance;
	
	public static $env;

	public static function get_instance()
	{
		if (!isset(self::$_instance) || empty(self::$_instance)) {
			self::$_instance = new CueEnv;
		}
		return self::$_instance;
	}

	private function __construct()
	{
		switch (get_option('cue_env', 'prod')) {
			case 'qa' :
				self::$env = array(
					"env" => "qa",
					"imi_loc" => "qa",
					"rapi"=>"qa-rapi.cueconnect.net",
	                "api" => "qa-api.cueconnect.net",
	                "oauth"=>"qa-oauth.cueconnect.net",
	                "consumer"=>"qa-www.cueconnect.net",
	                "poweredby"=>"qa-stream.cueconnect.net",
	                "onebutton"=>"qa-onebutton.cueconnect.net",
	                "shop"=>"qa-shop.cueconnect.net",
	                "merchant"=>"qa-business.cueconnect.net",
	                "proxy"=>"proxy.cueconnect.net"
				);
				if (!isset($_COOKIE['imi-location'])) {
					setcookie('imi-location', self::$env['imi_loc'], time() + 3600, '/');
					setcookie('imi-loc', self::$env['imi_loc'], time() + 3600, '/');
					setcookie('imi-domain-api', self::$env['api'], time() + 3600, '/');
					setcookie('imi-domain-cdns-nonsecure', self::$env['poweredby'], time() + 3600, '/');
					setcookie('imi-domain-consumer', self::$env['consumer'], time() + 3600, '/');
					setcookie('imi-domain-merchant', self::$env['merchant'], time() + 3600, '/');
					setcookie('imi-domain-onebutton', self::$env['onebutton'], time() + 3600, '/');
					setcookie('imi-domain-poweredby', self::$env['poweredby'], time() + 3600, '/');
					setcookie('imi-domain-shop', self::$env['shop'], time() + 3600, '/');
				}
			break;
			case 'prod':
			default :
				self::$env = array(
					"env" => "",
					"imi_loc" => "prod",
					"rapi"=>"rapi.cueconnect.com",
	                "api" => "api.cueconnect.com",
	                "oauth"=>"oauth.cueconnect.com",
	                "consumer"=>"www.cueconnect.com",
	                "poweredby"=>"stream.cueconnect.com",
	                "onebutton"=>"onebutton.cueconnect.com",
	                "shop"=>"shop.cueconnect.com",
	                "merchant"=>"business.cueconnect.com",
	                "proxy"=>"proxy.cueconnect.net"
				);
				if (isset($_COOKIE['imi-location'])) {
					setcookie('imi-location', null, -1, '/');
					setcookie('imi-loc', null, -1, '/');
					setcookie('imi-domain-api', null, -1, '/');
					setcookie('imi-domain-cdns-nonsecure', null, -1, '/');
					setcookie('imi-domain-consumer', null, -1, '/');
					setcookie('imi-domain-merchant', null, -1, '/');
					setcookie('imi-domain-onebutton', null, -1, '/');
					setcookie('imi-domain-poweredby', null, -1, '/');
					setcookie('imi-domain-shop', null, -1, '/');
				}
			break;


		}

		return self::$env;
	}
}

CueEnv::get_instance();