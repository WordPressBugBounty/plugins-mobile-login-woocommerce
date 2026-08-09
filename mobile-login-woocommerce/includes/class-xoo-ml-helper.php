<?php

use XooML\Framework\Xoo_Helper;

//Exit if accessed directly
if(!defined('ABSPATH')){
	return;
}


class Xoo_Ml_Helper extends Xoo_Helper{

	protected static $_instance = null;

	public $whatsapp_enabled;

	public $sms_enabled;

	public $mergeCC;

	public $allowed_country_codes;

	public function __construct(...$args){
		parent::__construct(...$args);
		$this->whatsapp_enabled = $this->get_phone_option('m-sms-channels') !== 'sms';
		$this->sms_enabled 		= $this->get_phone_option('m-sms-channels') !== 'whatsapp';
	}


	public static function get_instance( $slug, $path, $helperArgs = array() ){
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $slug, $path, $helperArgs );
		}
		return self::$_instance;
	}

	public function get_phone_option( $subkey = '' ){
		return $this->get_option( 'xoo-ml-phone-options', $subkey );
	}

	public function get_service_option( $subkey = '' ){
		return $this->get_option( 'xoo-ml-services-options', $subkey );
	}

	public function mergeCC(){
		if( !$this->mergeCC ){
			$this->mergeCC = $this->get_phone_option('m-cc-merge') === "yes" && $this->canMergeCC(); 
		}
		return $this->mergeCC;
	}

	public function canMergeCC(){
		return $this->get_phone_option('r-enable-cc-field') === "yes" && $this->get_phone_option('m-show-country-code-as') === 'selectbox';
	}

	public function get_allowed_country_codes(){

		if( !isset( $this->allowed_country_codes ) ){

			$allowed 	= xoo_ml_helper()->get_phone_option('r-countries');
			$allowed 	= !is_array( $allowed ) ? array() : $allowed;

		 	$all 		= $this->get_country_codes_list();


		 	$return = array();

		 	if( $allowed && !empty( $allowed ) ){
		 		foreach ($all as $cc => $phone_code ) {
		 			if( in_array( $phone_code , $allowed ) ){
		 				$return[ $cc ] = $phone_code;
		 			}
		 		}
		 	}
		 	else{
		 		$return = $all;
		 	}

		 	$this->allowed_country_codes = apply_filters( 'xoo_ml_country_codes', $return );

		}

		return $this->allowed_country_codes;

	}

	public function get_country_codes_list(){
		return apply_filters( 'xoo_ml_country_codes_list', include XOO_ML_PATH . '/includes/xoo-framework/countries/phone.php' );
	}


	public function get_allowed_phone_codes_list(){
		return array_values( $this->get_allowed_country_codes() );
	}



}

function xoo_ml_helper(){
	return Xoo_Ml_Helper::get_instance( 'mobile-login-woocommerce', XOO_ML_PATH, array(
		'pluginFile' 	=> XOO_ML_PLUGIN_FILE,
		'pluginName' 	=> 'OTP Login Woocommerce',
		'sidebar' 		=> true
	) );
}
xoo_ml_helper();