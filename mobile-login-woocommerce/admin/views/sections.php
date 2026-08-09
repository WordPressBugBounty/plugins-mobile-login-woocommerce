<?php

//Exit if accessed directly
if(!defined('ABSPATH')){
	return;
}


$sections = array(

	/* General TAB Sections */
	array(
		'title' => 'Main',
		'id' 	=> 'ph_main',
		'tab' 	=> 'phone',
		'icon' 	=> 'xoo-icon-home',
	),

	array(
		'title' => 'Country code',
		'id' 	=> 'ph_cc',
		'tab' 	=> 'phone',
		'icon' 	=> 'xoo-icon-globe',
	),

	array(
		'title' => 'OTP',
		'id' 	=> 'ph_otp',
		'tab' 	=> 'phone',
		'icon' 	=> 'xoo-icon-sms',
	),

	array(
		'title' => 'Register',
		'id' 	=> 'ph_reg',
		'tab' 	=> 'phone',
		'icon' 	=> 'xoo-icon-signup',
	),

	array(
		'title' => 'Login',
		'id' 	=> 'ph_login',
		'tab' 	=> 'phone',
		'icon' 	=> 'xoo-icon-login2',
	),


	array(
		'id' 	=> 'woocommerce',
		'title' => 'WooCommerce Checkout',
		'tab' 	=> 'phone',
		'pro' 	=> 'yes',
		'icon' 	=> 'xoo-icon-woo',
	),


	array(
		'title' => 'Login with Email OTP',
		'id' 	=> 'ph_emlogin',
		'tab' 	=> 'phone',
		'pro' 	=> 'yes',
		'icon' 	=> 'xoo-icon-mail',
	),

	array(
		'title' => 'Two Factor Authentication (2FA)',
		'id' 	=> 'ph_2fa',
		'tab' 	=> 'phone',
		'desc' 	=> '2FA field is also added under "Fields" page.',
		'icon' 	=> 'xoo-icon-shield',
		'pro' 	=> 'yes',
	),

	array(
		'id' 	=> 'ph_popup',
		'title' => 'Popup',
		'tab' 	=> 'phone',
		'desc' 	=> !function_exists('xoo_el') ? '<b>PRO:</b> Integrate OTP Login with our free Login/Signup Popup plugin. While the popup plugin is free, OTP login integration is available only with the PRO version of the OTP plugin.' : "<b>**NOTE**</b> You will need the pro version to integrate it with our login/signup popup plugin. The free version works well with the woocommerce's login/register form",
		'icon' 	=> 'xoo-icon-popup',
	),


	array(
		'title' => 'Whatsapp',
		'id' 	=> 'sv_whatsapp',
		'tab' 	=> 'services',
		'desc' 	=> '<a href="https://docs.xootix.com/mobile-login-for-woocommerce/whatsapp/" target="_blank">Documentation</a>'
	),


	array(
		'title' => 'Firebase',
		'id' 	=> 'sv_firebase',
		'tab' 	=> 'services',
		'desc' 	=> '<a href="https://docs.xootix.com/mobile-login-for-woocommerce/google-firebase/" target="_blank">Documentation</a>'
	),


	array(
		'title' => 'Amazon SNS',
		'id' 	=> 'sv_aws',
		'tab' 	=> 'services',
		'desc' 	=> '<a href="https://docs.xootix.com/mobile-login-for-woocommerce/amazon-sns/" target="_blank">Documentation</a>'
	),

	array(
		'title' => 'Twilio',
		'id' 	=> 'sv_twilio',
		'tab' 	=> 'services',
		'desc' 	=> '<a href="https://docs.xootix.com/mobile-login-for-woocommerce/twilio/" target="_blank">Documentation</a>'
	),

	array(
		'title' => 'Bulk SMS',
		'id' 	=> 'sv_bulksms',
		'tab' 	=> 'services',
		'desc' 	=> '<a href="https://docs.xootix.com/mobile-login-for-woocommerce/bulksms/" target="_blank">Documentation</a>'
	),

	array(
		'title' => 'Bulk(s) SMS',
		'id' 	=> 'sv_bulkssms',
		'tab' 	=> 'services',
		'desc' 	=> '<a href="https://docs.xootix.com/mobile-login-for-woocommerce/bulkssms/" target="_blank">Documentation</a>'
	),

	array(
		'title' => 'Net GSM',
		'id' 	=> 'sv_netgsm',
		'tab' 	=> 'services',
		'desc' 	=> '<a href="https://docs.xootix.com/mobile-login-for-woocommerce/netgsm/" target="_blank">Documentation</a>'
	),


	array(
		'title' => 'OurSMS',
		'id' 	=> 'sv_oursms',
		'tab' 	=> 'services',
		'desc' 	=> '<a href="https://docs.xootix.com/mobile-login-for-woocommerce/oursms/" target="_blank">Documentation</a>'
	),

	array(
		'title' => 'SMS Alert',
		'id' 	=> 'sv_smsalert',
		'tab' 	=> 'services',
		'desc' 	=> '<a href="https://docs.xootix.com/mobile-login-for-woocommerce/smsalert/" target="_blank">Documentation</a>'
	),

	array(
		'title' => 'Unifonic',
		'id' 	=> 'sv_unifonic',
		'tab' 	=> 'services',
		'desc' 	=> '<a href="https://docs.xootix.com/mobile-login-for-woocommerce/unifonic/" target="_blank">Documentation</a>'
	),

	array(
		'title' => 'Msg91',
		'id' 	=> 'sv_msg91',
		'tab' 	=> 'services',
		'desc' 	=> '<a href="https://docs.xootix.com/mobile-login-for-woocommerce/unifonic/" target="_blank">Documentation</a>'
	),

	array(
		'title' => 'TextLocal',
		'id' 	=> 'sv_txlocal',
		'tab' 	=> 'services',
		'desc' 	=> '<a href="https://docs.xootix.com/mobile-login-for-woocommerce/textlocal/" target="_blank">Documentation</a>'
	),

	array(
		'title' => 'SMSLane',
		'id' 	=> 'sv_smslane',
		'tab' 	=> 'services',
		'desc' 	=> '<a href="https://docs.xootix.com/mobile-login-for-woocommerce/smslane/" target="_blank">Documentation</a>'
	),

	array(
		'title' => 'Semaphore',
		'id' 	=> 'sv_semaphore',
		'tab' 	=> 'services',
		'desc' 	=> '<a href="https://docs.xootix.com/mobile-login-for-woocommerce/semaphore/" target="_blank">Documentation</a>'
	),

	array(
		'title' => 'Msegat',
		'id' 	=> 'sv_msegat',
		'tab' 	=> 'services',
		'desc' 	=> '<a href="https://docs.xootix.com/mobile-login-for-woocommerce/msegat/" target="_blank">Documentation</a>'
	),

	array(
		'title' => 'Custom',
		'id' 	=> 'sv_custom',
		'tab' 	=> 'services',
		'desc' 	=> "If your service operator isn't listed, you can manually include it. Please refer to your operator documentation<br>Enable debug under gemeral tab to see the response from SMS operator.",
	),

);

return apply_filters( 'xoo_ml_admin_settings_sections', $sections );