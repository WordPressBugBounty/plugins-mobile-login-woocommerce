<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Xoo_Ml_Otp_Handler{

	protected static $_instance = null;

	public $operator;

	public static $form_id = 'phone';

	public static function get_instance(){
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function __construct(){
		
	}


	/**
	 * This will only send OTP SMS.
	 * @return OTP
	*/
	public static function onlySendOTPSMS( $phone_code, $phone_no ){

		if( !in_array( $phone_code, xoo_ml_helper()->get_allowed_phone_codes_list() ) ){
			return new Wp_Error( 'no-phonecode', sprintf( __( 'Sorry, %s phone code is not allowed.', 'mobile-login-woocommerce' ), $phone_code ) );
		}

		//Remove 0
		if( strrpos( $phone_no , 0 ) === 0 ){
			$phone_no = substr( $phone_no, 1); 
		}

		$otp = self::generate_otp_digits();

		$smsParams = apply_filters( 'xoo_ml_sms_send_params', array( $phone_code, $phone_no, self::getOTPSMSText( $otp ), $otp ) );

		$smsSent = $whatsappSent = false;


		if( xoo_ml_helper()->sms_enabled && xoo_ml_helper()->get_phone_option('m-operator') !== 'firebase' ){

			$sms_operator = xoo_ml_services()->operator( $phone_code );

			if( $sms_operator ){
				$smsSent = $sms_operator->sendSMS( $smsParams[0].$smsParams[1], $smsParams[2], $smsParams[0], $smsParams[1] );
			}
			else{
				$smsSent = new Wp_Error( 'no-operator', 'Operator not found. Please download operator SDK from the plugin settings. Check documentation for how to setup.' );
			}

			if( is_wp_error( $smsSent ) ){
				return $smsSent; // do not proceed to whatsapp if SMS failed.
			}

		}


		if( xoo_ml_helper()->whatsapp_enabled ){
			$whatsappOperator 	= xoo_ml_services()->get_whatsapp_service();
			$whatsappSent 		= $whatsappOperator->sendSMS( $smsParams[0].$smsParams[1], $smsParams[3], $smsParams[0], $smsParams[1] );

			if( $smsSent === false && is_wp_error($whatsappSent) ){
				return $whatsappSent;
			}
		}

		
		 if( xoo_ml_helper()->get_phone_option('m-en-debug') === 'yes' ){
		 	if( xoo_ml_helper()->get_phone_option('m-operator') === 'firebase'  && !$whatsappSent ) return;

		 	wp_send_json( array(
		 		'error' => 1,
		 		'notice' => $smsSent.$whatsappSent
		 	) );
		 }


		
		return array(
			'otp' 			=> $otp,
			'smsSent' 		=> $smsSent !== false,
			'whatsappSent' 	=> $whatsappSent !== false
		);
	}

	/**
	 * This will send OTP SMS & set data to current user ip address as well.
	 * @return OTP
	*/
	public static function sendOTPSMS( $phone_code, $phone_no ){

		$ok_to_send_otp = self::ok_to_send_otp( $phone_code.$phone_no );
		
		if( is_wp_error( $ok_to_send_otp ) ){
			return $ok_to_send_otp;
		}

		$phone_otp_data = self::get_otp_data();

		$otp_sent = self::onlySendOTPSMS( $phone_code, $phone_no );
		//$otp_sent = true;

		if( is_wp_error( $otp_sent ) ){
			return $otp_sent;
		}

		$otp = $otp_sent['otp'];

		$data = array(
			'phone_no' 		=> $phone_no,
			'phone_code' 	=> $phone_code,
			'sendTo' 		=> $phone_code.$phone_no,
			'otp' 			=> $otp,
			'expiry' 		=> strtotime( xoo_ml_helper()->get_phone_option('otp-expiry'). ' seconds' ),
			'verified' 		=> false,
			'form_token' 	=> false,
			'whatsapp_sent' => $otp_sent['whatsappSent'],
			'sms_sent' 		=> $otp_sent['smsSent']
		);

		self::set_otp_data( $data );

		$limit_data = self::get_destination_limit_data( $data['sendTo'] );

        self::set_destination_limit_data( $data['sendTo'], array(
            'sent_times' => isset( $limit_data['sent_times'] ) ? (int) $limit_data['sent_times'] + 1 : 1,
            'incorrect'  => isset( $limit_data['incorrect'] ) ? (int) $limit_data['incorrect'] : 0,
        ) );


        $limit_data = self::get_destination_limit_data( $data['sendTo'] );

		return $otp;
	}

	/**
	 * Sends OTP to already assigned phone number in user's IP address
	*/
	public static function resendOTPSMS(){
		$phone_otp_data = self::get_otp_data();

		if( !$phone_otp_data  || !isset( $phone_otp_data[ 'phone_no' ] ) || !$phone_otp_data[ 'phone_no' ] || !isset( $phone_otp_data['phone_code'] ) ){
			return new Wp_Error( 'no-phone', __( "Phone Number not found", 'mobile-login-woocommerce' ) );
		}

		$otp = self::sendOTPSMS( $phone_otp_data['phone_code'], $phone_otp_data['phone_no'] );

		return $otp;
	}


	public static function generate_otp_digits(){
		$digits = xoo_ml_helper()->get_phone_option('otp-digits') ? xoo_ml_helper()->get_phone_option('otp-digits') : 4;

		return random_int( pow( 10, $digits - 1 ) , pow( 10, $digits ) - 1 );
	}


	public static function set_otp_data( $key, $value = '' ){

		self::ensure_verification_token();

        $data = self::get_otp_data();

        if( is_array( $key ) ){
        	$data   = wp_parse_args( $key, $data );
        }
        else{
        	$data[ $key ] = $value;
        }

        $data['last_updated']   = time();

        set_transient( self::get_transient_name(), $data, DAY_IN_SECONDS );

	}


	public static function get_otp_users(){
		return (array) get_option( 'xoo_ml_otp_users' );
	}


	public static function get_otp_data( $subkey = '' ){


		$transient_name = self::get_transient_name();
        
        $user_data = $transient_name ? get_transient( $transient_name ) : array();

        if( $subkey ){
            return isset( $user_data[$subkey] ) ? $user_data[$subkey] : null;
        }

        return $user_data;


	}


	public static function incorrect_tries_limit_reached( $destination = '' ){

        $limit_data = $destination ? self::get_destination_limit_data( $destination ) : self::get_otp_data();

        if( isset( $limit_data['incorrect'] ) && $limit_data['incorrect'] >= xoo_ml_helper()->get_phone_option('otp-incorrect-limit') ){
            return new \WP_Error( 'tries-exceeded', __( 'Number of tries exceeded, Please try again in few minutes', 'mobile-login-woocommerce' ) );
        }

        return false;

    }


	public static function ok_to_send_otp( $sendTo = '' ){

        $limit_data = self::get_destination_limit_data( $sendTo );

        if( !is_array( $limit_data ) || empty( $limit_data ) ) return;

        $limits = array(
			'resend_limit'     => xoo_ml_helper()->get_phone_option('otp-resend-limit'),
			'incorrect_limit'  => xoo_ml_helper()->get_phone_option('otp-incorrect-limit'),
			'resend_wait_time' => xoo_ml_helper()->get_phone_option('otp-resend-wait'),
			'ban_time'         => xoo_ml_helper()->get_phone_option('otp-ban-time'),
		);

        $time_passed = time() - (int) $limit_data['created'];


        if( isset( $limit_data['sent_times'] ) && $limit_data['sent_times'] >= $limits['resend_limit'] ){

            $unban_time_left = $limits['ban_time'] - $time_passed;

            if(  $unban_time_left < 0  ){
                self::set_destination_limit_data( $sendTo, array( 'sent_times' => 0, 'created' => time() ) );
            }
            else{
                return new \WP_Error( 'limit-reached', sprintf( __( 'OTP Limit reached. Please try again in %s.', 'mobile-login-woocommerce' ), self::getTimeDuration( $unban_time_left) ) );
            }
        }


        $incorrect_tries_limit_reached = self::incorrect_tries_limit_reached( $sendTo );

        if( is_wp_error(  $incorrect_tries_limit_reached ) ){

            $unban_time_left = $limits['ban_time'] - $time_passed;


            if( $unban_time_left < 0 ){
                self::set_destination_limit_data( $sendTo, array( 'incorrect' => 0, 'created' => time() ) );
            }
            else{
                return $incorrect_tries_limit_reached;
            }
        }


        if( $limit_data['sent_times'] >= 1 &&  $limits['resend_wait_time'] > $time_passed ){
			$unban_time_left = $limits['resend_wait_time'] - $time_passed;

			return new WP_Error( 'resend-wait', sprintf( __( 'Please wait %s for a new OTP.', 'mobile-login-woocommerce' ), self::getTimeDuration( $unban_time_left) ) );
		}


    }



	public static function getTimeDuration( $time ){
		return $time > 60 ? round($time/60). ' minutes' : $time. ' seconds';
	}


	public static function getOTPSMSText( $otp ){
		
		$sms_text = xoo_ml_helper()->get_phone_option('r-sms-txt');

		$placeholders = array(
			'[otp]'		=> $otp,
		);

		foreach ( $placeholders as $placeholder => $placeholder_value ) {
			$sms_text = str_replace( $placeholder , $placeholder_value , $sms_text );
		}

		$sms_text = apply_filters( 'xoo_ml_phone_sms_text',$sms_text );

		return $sms_text;
	}




    public static function get_transient_name(){

        $token = self::get_verification_token();

        if ( ! $token ) {
            return '';
        }

        // The browser receives only the random token. Store and look up the
        // verification state by its keyed hash so a leaked transient name is
        // not itself a usable bearer credential.
        return 'xoo_ml_verification_' . sanitize_key( self::$form_id ) . '_' . hash_hmac( 'sha256', $token, wp_salt( 'auth' ) );
    }


    public static function get_verification_cookie_name(){
        return 'xoo_ml_verification_' . sanitize_key( self::$form_id );
    }


    public static function get_verification_token(){
        $cookie_name = self::get_verification_cookie_name();

        if ( empty( $_COOKIE[ $cookie_name ] ) ) {
            return '';
        }

        // Tokens created below are URL-safe base64 strings. Reject anything
        // else rather than using arbitrary client input in a transient key.
        $token = sanitize_text_field( wp_unslash( $_COOKIE[ $cookie_name ] ) );

        return preg_match( '/^[A-Za-z0-9_-]{43}$/', $token ) ? $token : '';
    }


    public static function create_verification_token(){
        $token = rtrim( strtr( base64_encode( random_bytes( 32 ) ), '+/', '-_' ), '=' );
        $cookie_name = self::get_verification_cookie_name();
        $expires = time() + DAY_IN_SECONDS;
        $path = COOKIEPATH ? COOKIEPATH : '/';

        if ( PHP_VERSION_ID >= 70300 ) {
            setcookie( $cookie_name, $token, array(
                'expires'  => $expires,
                'path'     => $path,
                'domain'   => COOKIE_DOMAIN,
                'secure'   => is_ssl(),
                'httponly' => true,
                'samesite' => 'Lax',
            ) );
        } else {
            setcookie( $cookie_name, $token, $expires, $path, COOKIE_DOMAIN, is_ssl(), true );
        }

        // Make it available during this request too; PHP does not populate
        // $_COOKIE until the browser makes its next request.
        $_COOKIE[ $cookie_name ] = $token;

        return $token;
    }


    public static function ensure_verification_token(){
        return self::get_verification_token() ?: self::create_verification_token();
    }


    public static function get_destination_limit_transient_name( $destination ){
        $destination = strtolower( trim( (string) $destination ) );

        return 'xoo_ml_verification_limit_' . sanitize_key( self::$form_id ) . '_' . hash_hmac( 'sha256', $destination, wp_salt( 'auth' ) );
    }


    public static function get_destination_limit_data( $destination ){
        return get_transient( self::get_destination_limit_transient_name( $destination ) );
    }


    public static function set_destination_limit_data( $destination, $data = array() ){

        $existing = self::get_destination_limit_data( $destination );

        $data = wp_parse_args( $data, is_array( $existing ) ? $existing : array() );

        if ( empty( $data['created'] ) ) {
            $data['created'] = time();
        }

        set_transient( self::get_destination_limit_transient_name( $destination ), $data, DAY_IN_SECONDS );
    }


    public static function consume_verification(){
        $transient_name = self::get_transient_name();

        if ( $transient_name ) {
            delete_transient( $transient_name );
        }

        $cookie_name = self::get_verification_cookie_name();
        $path = COOKIEPATH ? COOKIEPATH : '/';

        setcookie( $cookie_name, ' ', time() - YEAR_IN_SECONDS, $path, COOKIE_DOMAIN, is_ssl(), true );

        unset( $_COOKIE[ $cookie_name ] );
    }


    public static function hash_code( $code ){
        return hash_hmac( 'sha256', (string) $code, wp_salt( 'nonce' ) );
    }


    public function is_user_verified(){
        $user_data = self::get_otp_data();

        return ! empty( $user_data['verified'] )
            && ! empty( $user_data['verified_until'] )
            && time() <= (int) $user_data['verified_until'];
    }

}


function xoo_ml_otp_handler(){
	return Xoo_Ml_Otp_Handler::get_instance();
}

xoo_ml_otp_handler();