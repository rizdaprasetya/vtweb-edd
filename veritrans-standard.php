<?php
/*
Plugin Name: Easy Digital Downloads - Veritrans Gateway
Plugin URL: 
Description: A Veritrans Payment Gateway plugin for Easy Digital Downloads
Version: 1.0
Author: Wendy kurniawan Soesanto
Author URI: 
Contributors: wendy0402, rizdaprasetya
TODO :
-parse customer details to VTWeb
-add challenge payment status
-frontend images
-field label di backend
-field notif url ala amazon di backend
-bin?
-installements?
-tanya fitur list
*/

//exit if opened directly
if ( ! defined( 'ABSPATH' ) ) exit;
// registers the gateway
function veritrans_register_gateway($gateways) {
	$gateways['veritrans'] = array(
		'admin_label' => 'Veritrans',
		'checkout_label' => __('Veritrans', 'veritrans')
	);
	return $gateways;
}
add_filter('edd_payment_gateways', 'veritrans_register_gateway');

#To add currency Rp and IDR
#
function rupiah_currencies( $currencies ) {
	if(!array_key_exists('Rp', $currencies)){
		$currencies['Rp'] = __('Indonesian Rupiah ( Rp )', 'veritrans');
	}
	return $currencies;	
}
add_filter( 'edd_currencies', 'rupiah_currencies'); 

function veritrans_gateway_cc_form() {
return;
}
add_action('edd_veritrans_cc_form', 'veritrans_gateway_cc_form');

// adds the settings to the Payment Gateways section
function veritrans_add_settings($settings) {
 
	$veritrans_settings = array(
		array(
			'id' => '_edd_ipaymu_gateway_settings',
			'name' => __('Veritrans Gateway Settings', 'veritrans'),
			'desc' => __('Configure the gateway settings', 'veritrans'),
			'type' => 'header'
		),
		array(
			'id' => 'vt_production_api_key',
			'name' => __('Production API Key', 'veritrans'),
			'desc' => __('Enter your live API key, found in your production Account Settings', 'veritrans'),
			'type' => 'text',
			'size' => 'regular'
		),
		array(
			'id' => 'vt_sandbox_api_key',
			'name' => __('Sandbox API Key', 'veritrans'),
			'desc' => __('Enter your test API key, found in your sandbox Account Settings', 'veritrans'),
			'type' => 'text',
			'size' => 'regular'
		)
	);
 
	return array_merge($settings, $veritrans_settings);	
}
add_filter('edd_settings_gateways', 'veritrans_add_settings');

// processes the payment-mode
function edd_veritrans_payment($purchase_data) {
	global $edd_options;
	require_once plugin_dir_path( __FILE__ ) . '/lib/Veritrans.php';
	/**********************************
	* set transaction mode
	**********************************/
 
	if(edd_is_test_mode()) {
		// set test credentials here
		Veritrans_Config::$isProduction = false;
		Veritrans_Config::$serverKey = $edd_options['vt_sandbox_api_key'];

	} else {
		// set live credentials here
		Veritrans_Config::$isProduction = true;
		Veritrans_Config::$serverKey = $edd_options['vt_production_api_key'];
	}
 
	// check for any stored errors
	$errors = edd_get_errors();
	if(!$errors) {
 
		$purchase_summary = edd_get_purchase_summary($purchase_data);
 
		/**********************************
		* setup the payment details
		**********************************/
 		// error_log(json_encode($purchase_data, true));

		$payment = array( 
			'price' => $purchase_data['price'], 
			'date' => $purchase_data['date'], 
			'user_email' => $purchase_data['user_email'],
			'purchase_key' => $purchase_data['purchase_key'],
			'currency' => $edd_options['currency'],
			'downloads' => $purchase_data['downloads'],
			'cart_details' => $purchase_data['cart_details'],
			'user_info' => $purchase_data['user_info'],
			'status' => 'pending'
		);
 
		// record the pending payment
		$payment = edd_insert_payment($payment);
		// create item
		$transaction_details = array();
		foreach($purchase_data['cart_details'] as $item){
			$vt_item = array(
				'id' => $item['id'],
				'price' => $item['price'],
				'quantity' => $item['quantity'],
				'name' => $item['name']
			);
			array_push($transaction_details, $vt_item);
		};

		$vt_params = array(
			'transaction_details' => array(
				'order_id' 			=> $payment,
				'gross_amount' 	=> $purchase_data['price']
				),
			'customer' 				=> array(
				'first_name' 		=> $purchase_data['user_info']['first_name'],
				'last_name' 			=> $purchase_data['user_info']['last_name'],
				'email' 				=> $purchase_data['user_email'],
				),
			'item_details' => $transaction_details
		);

    // get rid of cart contents
		// edd_empty_cart();
		// Redirect to veritrans
		wp_redirect( Veritrans_Vtweb::getRedirectionUrl($vt_params) );
		exit;
	} else {
		$fail = true; // errors were detected
	}
 
	if( $fail !== false ) {
		// if errors are present, send the user back to the purchase page so they can be corrected
		edd_send_back_to_checkout('?payment-mode=' . $purchase_data['post_data']['edd-gateway']);
	}
}
add_action('edd_gateway_veritrans', 'edd_veritrans_payment');


// to get notification from veritrans
function edd_veritrans_notification(){
	global $edd_options;

	require_once plugin_dir_path( __FILE__ ) . '/lib/Veritrans.php';
	if(edd_is_test_mode()){
		// set test credentials here
		error_log('masuk test mode');
		Veritrans_Config::$serverKey = $edd_options['vt_sandbox_api_key'];
		Veritrans_Config::$isProduction = false;
	}else {
		// set test credentials here
		error_log('masuk production mode');
		Veritrans_Config::$serverKey = $edd_options['vt_production_api_key'];
		Veritrans_Config::$isProduction = true;
	}
	error_log('serverKey: '.Veritrans_Config::$serverKey); //debugan
	error_log('isProduction: '.Veritrans_Config::$isProduction); //debugan
	
	$notif = new Veritrans_Notification();
	error_log('$notif '.print_r($notif)); //debugan

	$transaction = $notif->transaction_status;
	$fraud = $notif->fraud_status;
	$order_id = $notif->order_id;

	error_log('$order_id '.$order_id); //debugan
	error_log('$fraud '.$fraud); //debugan
	error_log('$transaction '.$transaction); //debugan
	
	if ($transaction == 'capture') {
		if ($fraud == 'challenge') {
		 	// TODO Set payment status in merchant's database to 'challenge'
			edd_update_payment_status($order_id, 'complete');
			error_log('challenge gan!'); //debugan
		}
		else if ($fraud == 'accept') {
		 	edd_update_payment_status($order_id, 'complete');
			error_log('accepted gan!'); //debugan
		}
	}
	else if ($transaction == 'cancel') {
		edd_update_payment_status($order_id, 'failure');
			error_log('cancelled gan!'); //debugan
	}
	else if ($transaction == 'deny') {
	 	edd_update_payment_status($order_id, 'failure');
			error_log('denied gan!'); //debugan
	}
};
add_action( 'edd_veritrans_notification', 'edd_veritrans_notification' );

function edd_listen_for_veritrans_notification() {
	global $edd_options;

	// check if payment url http://site.com/?edd-listener=veritrans
	if ( isset( $_GET['edd-listener'] ) && $_GET['edd-listener'] == 'veritrans' ) {
		error_log('masuk edd_listen_for_veritrans_notification, '.$_GET['edd-listener']); //debugan
		do_action( 'edd_veritrans_notification' );
	}

}
add_action( 'init', 'edd_listen_for_veritrans_notification' );