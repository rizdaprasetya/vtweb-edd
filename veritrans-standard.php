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
-frontend images
-field label di backend
-field notif url ala amazon di backend
-cek kalo multiple item di cart, totalnya bener ga?
-bin?
-installements?
-tanya fitur list
Done (to be tested)
-add challenge payment status
-add cancel payment status
-parse customer details to VTWeb
-3ds enabled option
-payment enabled options
*/

//exit if opened directly
if ( ! defined( 'ABSPATH' ) ) exit;
// registers the gateway
function veritrans_register_gateway($gateways) {
	global $edd_options;

	// $checkout_label = 'Credit Card via Veritrans';
	$checkout_label = 'Credit card via Veritrans';

	//check checkout label field from backend, then set if not null and not empty string
	if(isset($edd_options['vt_checkout_label']) and $edd_options['vt_checkout_label'] != ''){
		$checkout_label = $edd_options['vt_checkout_label'];
	}

	$gateways['veritrans'] = array(
		'admin_label' => 'Veritrans',
		'checkout_label' => __($checkout_label, 'veritrans')
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

#To add payment status challenge
function add_edd_payment_statuses( $payment_statuses ) {
    
    $payment_statuses['challenge']   = 'Challenge';
    $payment_statuses['cancel']   = 'Cancel';

    return $payment_statuses;   
}
add_filter( 'edd_payment_statuses', 'add_edd_payment_statuses' );

/**
 * Registers challenge statuses as post statuses so we can use them in Payment History navigation
 */
function register_post_type_statuses() {
 
    // Payment Statuses
    register_post_status( 'challenge', array(
        'label'                     => _x( 'Challenge', 'challenge, payment status', 'edd' ),
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop( 'Challange <span class="count">(%s)</span>', 'Challenge <span class="count">(%s)</span>', 'edd' )
    ) );
    register_post_status( 'cancel', array(
        'label'                     => _x( 'Cancel', 'cancel, payment status', 'edd' ),
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop( 'Cancel <span class="count">(%s)</span>', 'Cancel <span class="count">(%s)</span>', 'edd' )
    ) );
}
add_action( 'init', 'register_post_type_statuses' );
 
 
/**
 * Adds challenge payment statuses to the Payment History navigation
 */
function edd_payments_new_views( $views ) {
     
    $views['challenge']  = sprintf( '<a href="%s">%s</a>', add_query_arg( array( 'status' => 'challenge', 'paged' => FALSE ) ), 'Challenge' ); 
    $views['cancel']  = sprintf( '<a href="%s">%s</a>', add_query_arg( array( 'status' => 'cancel', 'paged' => FALSE ) ), 'Cancel' );
     
    return $views;
 
}
add_filter( 'edd_payments_table_views', 'edd_payments_new_views' );

function veritrans_gateway_cc_form() {
return;
}
add_action('edd_veritrans_cc_form', 'veritrans_gateway_cc_form');


// add form to display payment method images
function vt_payment_images_form() {

	ob_start(); ?>

	<fieldset id="edd_cc_fields" class="edd-veritrans-fields">
		<p class="edd-veritrans-profile-wrapper">
			<div>adadwadawdad</div>
			<div>dadd</div>
			<span class="edd-veritrans-profile-name"><?php echo $profile['name']; ?></span>
		</p>

		<div id="edd-veritrans-address-box"></div>
	</fieldset>

	<?php
	$form = ob_get_clean();
	echo $form;
}
add_action('edd_veritrans_cc_form', 'vt_payment_images_form');

// adds the settings to the Payment Gateways section
function veritrans_add_settings($settings) {
 
	$veritrans_settings = array(
		array(
			'id' => '_edd_veritrans_gateway_settings',
			'name' => '<strong>'.__('Veritrans Gateway Settings', 'veritrans').'</strong>',
			'desc' => __('Configure the gateway settings', 'veritrans'),
			'type' => 'header'
		),
		array(
			'id' => 'vt_checkout_label',
			'name' => __('Checkout Label', 'veritrans'),
			'desc' => __('Payment gateway text label that will be shown as payment options to your customers (Default = "Credit card via Veritrans")'),
			'type' => 'text',
			'size' => 'regular'
		),
		array(
			'id' => 'vt_production_api_key',
			'name' => __('Production API Key', 'veritrans'),
			'desc' => __('Enter your live API key, found in your production MAP Account Settings <br> (make sure to <strong>disable</strong> "Test Mode" settings above, if you wish to use production mode)', 'veritrans'),
			'type' => 'text',
			'size' => 'regular'
		),
		array(
			'id' => 'vt_sandbox_api_key',
			'name' => __('Sandbox API Key', 'veritrans'),
			'desc' => __('Enter your test API key, found in your sandbox MAP Account Settings <br> (make sure to <strong>enable</strong> "Test Mode" settings above, if you wish to use sandbox mode)', 'veritrans'),
			'type' => 'text',
			'size' => 'regular'
		),
		array(
			'id' => 'vt_3ds',
			'name' => __('Enable 3D Secure', 'veritrans'),
			'desc' => __('You must enable 3D Secure. Please contact us if you wish to disable this feature in the Production environment.'),
			'type' => 'checkbox',
		),
		array(
			'id' => '_edd_veritrans_payment_settings',
			'name' => '<strong>'.__('Veritrans Payment Channel', 'veritrans').'</strong>',
			'desc' => __('Configure the accepted payment channel settings', 'veritrans'),
			'type' => 'header'
		),
		array(
			'id' => 'vt_credit_card',
			'name' => __('Accept Credit Card', 'veritrans'),
			'desc' => __('Please contact us if you wish to enable this feature in Production'),
			'type' => 'checkbox'
		),
		array(
			'id' => 'vt_mandiri_clickpay',
			'name' => __('Accept Mandiri Clickpay', 'veritrans'),
			'desc' => __('Please contact us if you wish to enable this feature in Production'),
			'type' => 'checkbox'
		),
		array(
			'id' => 'vt_cimb_clicks',
			'name' => __('Accept CIMB Click', 'veritrans'),
			'desc' => __('Please contact us if you wish to enable this feature in Production'),
			'type' => 'checkbox'
		),
		array(
			'id' => 'vt_bank_transfer',
			'name' => __('Accept Bank Transfer', 'veritrans'),
			'desc' => __('Please contact us if you wish to enable this feature in Production'),
			'type' => 'checkbox'
		),
		array(
			'id' => 'vt_bri_epay',
			'name' => __('Accept BRI Epay', 'veritrans'),
			'desc' => __('Please contact us if you wish to enable this feature in Production'),
			'type' => 'checkbox'
		),
		array(
			'id' => 'vt_telkomsel_cash',
			'name' => __('Accept Telkomsel Cash', 'veritrans'),
			'desc' => __('Please contact us if you wish to enable this feature in Production'),
			'type' => 'checkbox'
		),
		array(
			'id' => 'vt_xl_tunai',
			'name' => __('Accept XL Tunai', 'veritrans'),
			'desc' => __('Please contact us if you wish to enable this feature in Production'),
			'type' => 'checkbox'
		),
		array(
			'id' => 'vt_echannel',
			'name' => __('Accept Mandiri Bill', 'veritrans'),
			'desc' => __('Please contact us if you wish to enable this feature in Production'),
			'type' => 'checkbox'
		),
		array(
			'id' => 'vt_bbm_money',
			'name' => __('Accept BBM Money', 'veritrans'),
			'desc' => __('Please contact us if you wish to enable this feature in Production'),
			'type' => 'checkbox'
		),
		array(
			'id' => 'vt_cstore',
			'name' => __('Accept Indomaret', 'veritrans'),
			'desc' => __('Please contact us if you wish to enable this feature in Production'),
			'type' => 'checkbox'
		),
		array(
			'id' => 'vt_indosat_dompetku',
			'name' => __('Accept Indosat Dompetku', 'veritrans'),
			'desc' => __('Please contact us if you wish to enable this feature in Production'),
			'type' => 'checkbox'
		),
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
 		error_log('purchase data: '.print_r($purchase_data,true)); //debugan
 		error_log('purchase summary: '.print_r($purchase_summary,true)); //debugan
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
				'gross_amount' 		=> $purchase_data['price']
				),
			'customer_details' 	=> array(
				'first_name' 		=> $purchase_data['user_info']['first_name'],
				'last_name' 		=> $purchase_data['user_info']['last_name'],
				'email' 			=> $purchase_data['user_info']['email'],
				'billing_address' 	=> array(
					'first_name' 		=> $purchase_data['user_info']['first_name'],
					'last_name' 		=> $purchase_data['user_info']['last_name'],
					),
				),
			'item_details' => $transaction_details
		);

		//get enabled payment opts from backend
		$enabled_payments = edd_get_vtpayment_ops();
		if (!empty($enabled_payments)) {
			$vt_params['vtweb']['enabled_payments'] = $enabled_payments;
		}

		// error_log('vt_3ds '.$edd_options['vt_3ds']); //debugan
   		// get rid of cart contents
		edd_empty_cart();
		// Redirect to veritrans
		error_log('vt_params: '.print_r($vt_params,true)); //debugan

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

/**
 * Get Enabled Payment from backend settings
 * @return array $enabled_payment
 **/
function edd_get_vtpayment_ops()
{
	global $edd_options;

	//get 3ds opts from backend
	Veritrans_Config::$is3ds = $edd_options['vt_3ds'] ? true : false;
	error_log('vt_3ds '.$edd_options['vt_3ds']); //debugan
	error_log('credit_card '.$edd_options['vt_credit_card']); //debugan

    $enabled_payments = array();
    if ($edd_options['vt_credit_card']){
      $enabled_payments[] = 'credit_card';
		error_log('masuk cc '.$edd_options['credit_card']); //debugan
    }
    if ($edd_options['vt_mandiri_clickpay']){
      $enabled_payments[] = 'mandiri_clickpay';
    }
    if ($edd_options['vt_cimb_clicks']){
      $enabled_payments[] = 'cimb_clicks';
    }
    if ($edd_options['vt_bank_transfer']){
      $enabled_payments[] = 'bank_transfer';   
    }
    if ($edd_options['vt_bri_epay']){
      $enabled_payments[] = 'bri_epay';
    }
    if ($edd_options['vt_telkomsel_cash']){
      $enabled_payments[] = 'telkomsel_cash';
    }
    if ($edd_options['vt_xl_tunai']){
      $enabled_payments[] = 'xl_tunai';
    }
    if ($edd_options['vt_echannel']){
      $enabled_payments[] = 'echannel';
    }
    if ($edd_options['vt_bbm_money']){
      $enabled_payments[] = 'bbm_money';
    }
    if ($edd_options['vt_cstore']){
      $enabled_payments[] = 'cstore';
    }
    if ($edd_options['vt_indosat_dompetku']){
      $enabled_payments[] = 'indosat_dompetku';
    }
    error_log('enabled payments array'.print_r($enabled_payments,true)); //debugan
    return $enabled_payments;
}

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
	// error_log('serverKey: '.Veritrans_Config::$serverKey); //debugan
	// error_log('isProduction: '.Veritrans_Config::$isProduction); //debugan
	
	$notif = new Veritrans_Notification();
	error_log('$notif '.print_r($notif)); //debugan

	$transaction = $notif->transaction_status;
	$fraud = $notif->fraud_status;
	$order_id = $notif->order_id;

	// error_log('$order_id '.$order_id); //debugan
	// error_log('$fraud '.$fraud); //debugan
	// error_log('$transaction '.$transaction); //debugan
	
	if ($transaction == 'capture') {
		if ($fraud == 'challenge') {
		 	// TODO Set payment status in merchant's database to 'challenge'
			edd_update_payment_status($order_id, 'challenge');
			error_log('challenge gan!'); //debugan
		}
		else if ($fraud == 'accept') {
		 	edd_update_payment_status($order_id, 'complete');
			error_log('accepted gan!'); //debugan
		}
	}
	else if ($transaction == 'cancel') {
		edd_update_payment_status($order_id, 'cancel');
			error_log('cancelled gan!'); //debugan
	}
	else if ($transaction == 'deny') {
	 	edd_update_payment_status($order_id, 'failed');
			error_log('denied gan!'); //debugan
	}
};
add_action( 'edd_veritrans_notification', 'edd_veritrans_notification' );

function edd_listen_for_veritrans_notification() {
	global $edd_options;

	// check if payment url http://site.com/?edd-listener=veritrans
	if ( isset( $_GET['edd-listener'] ) && $_GET['edd-listener'] == 'veritrans' ) {
		// error_log('masuk edd_listen_for_veritrans_notification, '.$_GET['edd-listener']); //debugan
		do_action( 'edd_veritrans_notification' );
	}

}
add_action( 'init', 'edd_listen_for_veritrans_notification' );