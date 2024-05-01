<?php
/**
 * Plugin Name: Gravity Forms terawallet Add-On 
 * Plugin URI: https://terawaalet.com
 * Description: Integrates Gravity Forms with terawallet, enabling end users to purchase goods and services through Gravity Forms.
 * Version: 3.9
 * Author:Mr.BLack
 * Author URI: https://dorld.tech
 * License: GPL-2.0+
 * Text Domain: gravityformsterawallet
 * Domain Path: /languages
 *
 * ------------------------------------------------------------------------
 * Copyright 2023 - 2020 Mr.black, Inc.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 */
session_start(); // Start the session
defined( 'ABSPATH' ) || die();

define( 'GF_terawallet_VERSION', '3.9' );

// If Gravity Forms is loaded, bootstrap the terawallet Add-On.
add_action( 'gform_loaded', array( 'GF_terawallet_Bootstrap', 'load' ), 5 );

/**
 * Class GF_terawallet_Bootstrap
 *
 * Handles the loading of the terawallet Add-On and registers with the Add-On framework.
 *
 * @since 1.0.0
 */
class GF_terawallet_Bootstrap {

	/**
	 * If the Payment Add-On Framework exists, terawallet Add-On is loaded.
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @uses GFAddOn::register()
	 *
	 * @return void
	 */
	public static function load() {

		if ( ! method_exists( 'GFForms', 'include_payment_addon_framework' ) ) {
			return;
		}

		require_once( 'class-gf-terawallet.php' );
		require_once( 'includes/class-gf-field-terawallet-creditcard.php' );
	


		GFAddOn::register( 'GFterawallet' );
		

	}

}
register_activation_hook(__FILE__, 'create_keys');

	 /**
	* Create keys.
	*
	* @since  2.4.0
	*
	* @param  string $app_name    App name.
	* @param  string $app_user_id User ID.
	* @param  string $scope       Scope.
	*
	* @return array
	*/
    function create_keys() {
	   global $wpdb;
$scope = 'read_write';
$app_name = 'Terawallet';
$app_user_id = 1;
	   $description = sprintf(
		   '%s - API (%s)',
		   wc_trim_string( wc_clean( $app_name ), 170 ),
		   gmdate( 'Y-m-d H:i:s' )
	   );
	   $user = wp_get_current_user();

	   // Created API keys.
	   $permissions     = in_array( $scope, array( 'read', 'write', 'read_write' ), true ) ? sanitize_text_field( $scope ) : 'read';
	   $consumer_key    = 'ck_' . wc_rand_hash();
	   $consumer_secret = 'cs_' . wc_rand_hash();

	   $option_name = 'Terawallet_api';
	   $new_value = $consumer_key;
	   $autoload = 'yes'; 
	   add_option($option_name, $new_value, false, $autoload);
		$nonce	 = '';
	   
	
	   $wpdb->insert(
		   $wpdb->prefix . 'woocommerce_api_keys',
		   array(
			   'user_id'         => $user->ID,
			   'description'     => $description,
			   'permissions'     => $permissions,
			   'consumer_key'    => wc_api_hash( $consumer_key ),
			   'consumer_secret' => $consumer_secret,
			   'nonces'			=>$nonce,
			   'truncated_key'   => substr( $consumer_key, -7 ),
		   ),
		   array(
			   '%d',
			   '%s',
			   '%s',
			   '%s',
				'%s',
			   '%s',
			   '%s',
		   )
	   );

	  
	   return array(
		   'key_id'          => $wpdb->insert_id,
		   'user_id'         => $app_user_id,
		   'consumer_key'    => $consumer_key,
		   'consumer_secret' => $consumer_secret,
		//    'nonces'			=>$nonce,	
		   'key_permissions' => $permissions,
	   );
   }


/**
 * Obtains and returns an instance of the GFterawallet class
 *
 * @since  1.0.0
 * @access public
 *
 * @uses GFterawallet::get_instance()
 *
 * @return object GFterawallet
 */
function gf_terawallet() {
	return GFterawallet::get_instance();
}




/**
 * Obtains and returns an instance of the v class
 *
 * @since  1.0.0
 * @access public
 *
 * @uses GF_Field_Terawallet_CreditCard::my_after_submission_function()
 *
 * @return object GF_Field_Terawallet_CreditCard
 */


 add_action('gform_after_submission', 'payment', 10, 2);

//  add_filter('gform_get_field_value', 'payment', 10, 3);
 function payment($entry, $form){
	// print_r($entry);exit;
	$has_terawallet_field = false;
    // Loop through the form fields to find the Terawallet payment field
    foreach ($form['fields'] as $field) {
        if ($field['type'] == 'terawallet_payment') {


        
            $has_terawallet_field = true;
            break; 
        }
    }
	if ($has_terawallet_field) {
		
		$creditCardField = new GF_Field_Terawallet_CreditCard();
		return $creditCardField->my_after_submission_function($entry, $form);
		
	}
	

	
	
}


add_filter('gform_submit_button', 'custom_submit_button', 10, 2);

function custom_submit_button($button, $form) {
	$terawallet_field_exists = GFAPI::get_fields_by_type($form, 'terawallet_payment');
    $getBalance = new GF_Field_Terawallet_CreditCard();
	$total_value = 0;
	
    if (!empty($terawallet_field_exists)) {
		$walletBalance = $getBalance->get_field_input($form);

		


		if (preg_match('/([\d.]+)/', $walletBalance, $matches)) {
			$numericValue = $matches[0]; 
			$formattedBalance = number_format((float)$numericValue, 2);
		
} 
   
		$paymentAmount =$form["fields"];
		for($i = 0; $i < count($paymentAmount); $i++) {
			if ($paymentAmount[$i]['type'] == 'product') {
				$value = GFCommon::to_number($paymentAmount[$i]['basePrice']);
				$total_value += $value;
				
			}} 
		
        $script = '<script>
            document.addEventListener("DOMContentLoaded", function() {
                var walletBalance = ' . $formattedBalance . ';
                var productPrice = ' . $total_value . ';
                var submitButton = document.querySelector("form[id^=\'gform_\'] input[type=\'submit\']");

                if (walletBalance < productPrice) {
                    submitButton.classList.add("disabled", "button-disabled");
                    submitButton.disabled = true;
                } else {
                    submitButton.classList.remove("disabled", "button-disabled");
                    submitButton.disabled = false;
                }
            });
        </script>';

        // Append the JavaScript to the existing submit button
        $button .= $script;
    }

    return $button;
}
