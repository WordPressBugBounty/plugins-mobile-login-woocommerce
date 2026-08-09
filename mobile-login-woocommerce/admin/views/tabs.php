<?php

//Exit if accessed directly
if(!defined('ABSPATH')){
	return;
}


$tabs = array(
	'phone' => array(
		'title'			=> 'General',
		'id' 			=> 'phone',
		'option_key' 	=> 'xoo-ml-phone-options',
		'icon' 			=> 'xoo-icon-setting',
	),

	'services' => array(
		'title'			=> 'SMS Operators',
		'id' 			=> 'services',
		'option_key' 	=> 'xoo-ml-services-options',
		'icon' 			=> 'xoo-icon-sms',
	),

	'pro' => array(
		'title'			=> 'PRO',
		'id' 			=> 'pro',
		'option_key' 	=> '',
		'icon' 			=> 'xoo-icon-crown',
	),



);

return apply_filters( 'xoo_ml_admin_settings_tabs', $tabs );