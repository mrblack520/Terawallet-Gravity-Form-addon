<?php



if ( ! class_exists( 'GFForms' ) ) {
	die();
}


/**
 * The terawallet Card field is a credit card field used specifically by the terawallet Add-On.
 *
 * @since 2.6
 *
 * Class GF_Field_terawallet_CreditCard
 */
class GF_Field_Terawallet_CreditCard extends GF_Field {

	/**
	 * Field type.
	 *
	 * @since 2.6
	 *
	 * @var string
	 */
	public $type = 'terawallet_payment';

	
	/**
	 * Get field button title.
	 *
	 * @since 2.6
	 *
	 * @return string
	 */
	public function get_form_editor_field_title() {
		return esc_attr__( 'TeraWallet', 'gravityformsterawallet' );
	}

	/**
	 * Returns the field's form editor icon.
	 *
	 * This could be an icon url or a dashicons class.
	 *
	 * @since 3.8
	 *
	 * @return string
	 */
	public function get_form_editor_field_icon() {
		return gf_terawallet()->get_base_url() . '/images/menu-icon.svg';
	}

	/**
	 * Returns the field's form editor description.
	 *
	 * @since 3.8
	 *
	 * @return string
	 */
	public function get_form_editor_field_description() {
		return esc_attr__( 'Allows accepting credit card information to make payments via Terawallet payment gateway.', 'gravityformsterawallet' );
	}

	/**
	 * Returns the scripts to be included for this field type in the form editor.
	 *
	 * @since  2.6
	 *
	 * @return string
	 */
	public function get_form_editor_inline_script_on_page_render() {
		
	$payment = 20;
		$js = sprintf( "function SetDefaultValues_%s(field) {field.label = '%s';
		field.inputs = [new Input(field.id + '.1', %s), new Input(field.id + '.4', %s), new Input(field.id + '.5', %s)];
		}", $this->type, esc_html__( 'TeraWallet', 'gravityformsterawallet' ), json_encode( gf_apply_filters( array( 'gform_card_details', rgget( 'id' ) ), esc_html__( 'Card Details', 'gravityformsterawallet' ), rgget( 'id' ) ) ), json_encode( gf_apply_filters( array( 'gform_card_type', rgget( 'id' ) ), esc_html__( 'Card Type', 'gravityformsterawallet' ), rgget( 'id' ) ) ), json_encode( gf_apply_filters( array( 'gform_card_name', rgget( 'id' ) ), esc_html__( 'Cardholder Name', 'gravityformsterawallet' ), rgget( 'id' ) ) ) ) . PHP_EOL;

		$js .= "gform.addFilter('gform_form_editor_can_field_be_added', function(result, type) {
            if (type === 'terawallet_payment') {
                if (GetFieldsByType(['terawallet_payment']).length > 0) {" .
			        sprintf( "alert(%s);", json_encode( esc_html__( 'Only one Stripe Card field can be added to the form', 'gravityformsterawallet' ) ) )
			       . " result = false;
				}
            }
            
            return result;
        });";

		

		$js .= "jQuery(document).bind('gform_load_field_settings', function(event, field, form) {
		if (field['type']==='terawallet_payment') {
			var imagesUrl = '" . GFCommon::get_base_url() . '/images/' . "',
			    input = field['inputs'][2],
			    isHidden = typeof input.isHidden != 'undefined' && input.isHidden ? true : false,
			    title = isHidden ? " . json_encode( esc_html__( 'Inactive', 'gravityforms' ) ) . ':' . json_encode( esc_html__( 'Active', 'gravityforms' ) ) . ",
				img = isHidden ? 'active0.png' : 'active1.png';
			jQuery('.sub_labels_setting .field_custom_inputs_ui tr:eq(0)').prepend('<td><strong>" . esc_html__( 'Show', 'gravityforms' ) . "</strong></td>');
			jQuery('.sub_labels_setting .field_custom_inputs_ui tr:eq(1)').prepend('<td></td>');
			jQuery('.sub_labels_setting .field_custom_inputs_ui tr:eq(2)').prepend('<td><img data-input_id=\'' + field['id'] + '.5\' alt=\'' + title + '\' class=\'input_active_icon cardholder_name\' src=\'' + imagesUrl + img + '\'/></td>');
			jQuery('.input_placeholders tr:eq(1)').remove();
			
			jQuery('.sub_labels_setting').on('click keypress', '.input_active_icon.cardholder_name', function(){
				var isHidden = this.src.indexOf(\"active0.png\") >= 0;
				jQuery('#input_' + field['id'] + '_1_label').toggle(!isHidden);
				jQuery('.sub_labels_setting .field_custom_inputs_ui tr:eq(2) td:eq(2) input, .sub_labels_setting .field_custom_inputs_ui tr:eq(1) td:eq(2) input').prop('disabled', isHidden);
	        });
		}
		});";

		$js .= "gform.addAction('gform_post_load_field_settings', function ([field, form]) {
			if ( field['type'] === 'terawallet_payment' ) {	        
				// Hide #field_settings when the field has error conditions.
				// This is called right after the settings are shown. So that makes it feel like there's no settings.
				if ( jQuery('.gform_stripe_card_error').length ) {
					HideSettings( 'field_settings' );
				}
			}
		});";

		return $js;
	}

	/**
	 * Get field settings in the form editor.
	 *
	 * @since 2.6
	 *
	 * @return array
	 */
	public function get_form_editor_field_settings() {
		return array(
			'conditional_logic_field_setting',
			'force_ssl_field_setting',
			'error_message_setting',
			'label_setting',
			'label_placement_setting',
			'admin_label_setting',
			'rules_setting',
			'description_setting',
			'css_class_setting',
			'sub_labels_setting',
			'sub_label_placement_setting',
			'input_placeholders_setting',
		);
	}

	/**
	 * Get form editor button.
	 *
	 * @since 2.6
	 * @since 3.4 Add the terawallet Card field only when checkout method is not Checkout.
	 *
	 * @return array
	 */
	public function get_form_editor_button() {
		if ( gf_terawallet()->get_plugin_setting( 'checkout_method' ) !== 'terawallet_checkout' ) { //need change
			return array(
				'group' => 'pricing_fields',
				'text'  => $this->get_form_editor_field_title(),
			);
		} else {
			return array();
		}
	}

	/**
	 * Used to determine the required validation result.
	 *
	 * @since 2.6
	 *
	 * @param int $form_id The ID of the form currently being processed.
	 *
	 * @return bool
	 */
	public function is_value_submission_empty( $form_id ) {
		// check only the cardholder name.
		$cardholder_name_input = GFFormsModel::get_input( $this, $this->id . '.5' );
		$hide_cardholder_name  = rgar( $cardholder_name_input, 'isHidden' );
		$cardholder_name       = rgpost( 'input_' . $this->id . '_5' );

		if ( ! $hide_cardholder_name && empty( $cardholder_name ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Get submission value.
	 *
	 * @since 2.6
	 *
	 * @param array $field_values Field values.
	 * @param bool  $get_from_post_global_var True if get from global $_POST.
	 *
	 * @return array|string
	 */
	public function get_value_submission( $field_values, $get_from_post_global_var = true ) {
		
		if ( $get_from_post_global_var ) {
			$value[ $this->id . '.1' ] = $this->get_input_value_submission( 'input_' . $this->id . '_1', rgar( $this->inputs[0], 'name' ), $field_values, true );
			$value[ $this->id . '.4' ] = $this->get_input_value_submission( 'input_' . $this->id . '_4', rgar( $this->inputs[1], 'name' ), $field_values, true );
			$value[ $this->id . '.5' ] = $this->get_input_value_submission( 'input_' . $this->id . '_5', rgar( $this->inputs[2], 'name' ), $field_values, true );
			
		} else {
			$value = $this->get_input_value_submission( 'input_' . $this->id, $this->inputName, $field_values, $get_from_post_global_var );
		}
	
		
		return $value;	

	}

	/**
	 * Get field input.
	 *
	 * @since 2.6
	 *
	 * @param array      $form  The Form Object currently being processed.
	 * @param array      $value The field value. From default/dynamic population, $_POST, or a resumed incomplete submission.
	 * @param null|array $entry Null or the Entry Object currently being edited.
	 *
	 * @return string
	 */
	public function join_with_equals_sign( $params, $query_params = array(), $key = '' ) {
		foreach ( $params as $param_key => $param_value ) {
			if ( $key ) {
				$param_key = $key . '%5B' . $param_key . '%5D'; // Handle multi-dimensional array.
			}

			if ( is_array( $param_value ) ) {
				$query_params = $this->join_with_equals_sign( $param_value, $query_params, $param_key );
			} else {
				$string         = $param_key . '=' . $param_value; // Join with equals sign.
				$query_params[] = wc_rest_urlencode_rfc3986( $string );
			}
		}

		return	implode( '%26', $query_params );
	}
	public function get_secret() {
		global $wpdb;
		
		// ck_b616c4672369f33a6d1c4d06f927e4e909082d11
		$query = $wpdb->prepare("
			SELECT   consumer_secret, permissions
			FROM {$wpdb->prefix}woocommerce_api_keys" );
	
		$key = $wpdb->get_row($query);
	
		return $key;
	}
	public function get_consumer_key() {
		global $wpdb;
		
		// ck_b616c4672369f33a6d1c4d06f927e4e909082d11
		$query = $wpdb->prepare("
			SELECT  option_value
			FROM {$wpdb->prefix}options where option_name = 'Terawallet_api' " );
	
		$ck_key = $wpdb->get_row($query);
	
		return $ck_key;
	}
	public function get_field_input( $form, $value = array(), $entry = null ) {
		

		
		


		
		$key = $this->get_secret();
		$ck_key = $this->get_consumer_key();

	
	
			$consumer_key =   $ck_key->option_value ;	
		// print_r($consumer_key);exit;
			$consumer_secret = $key->consumer_secret;
		

		$email_user=wp_get_current_user();
		$hf_username = $email_user->user_email;
		$base_uri_get=  home_url();
		$api= '/wp-json/wc/v3/wallet/balance?email=';
		$osamaUrl = $base_uri_get.$api.$hf_username;
		$api_witout_email = '/wp-json/wc/v3/wallet/balance';
		$base_uri = $base_uri_get.$api_witout_email;
		$email = rawurlencode('='.$hf_username);
		$email = str_replace("%40","%2540",$email);
		$request_uri = $base_uri;
		$nonce = uniqid();
		$timestamp = time();
		
		$oauth_signature_method = 'HMAC-SHA1';
		
		$hash_algorithm = strtolower( str_replace( 'HMAC-', '', $oauth_signature_method ) ); // sha1
		// print_r($request_uri);exit;
		$http_method = 'GET';
		$base_request_uri = rawurlencode( $request_uri   ).'&email'.$email  ;
		$params = array(  'oauth_consumer_key' => $consumer_key, 'oauth_nonce' => $nonce,'oauth_signature_method' => 'HMAC-SHA1','oauth_timestamp' => $timestamp,  );
		// $params         = normalize_parameters( $params );
		$query_string =$this->join_with_equals_sign( $params );
		// print_r($query_string);exit;
		$secret = $consumer_secret . '&';
		
		$string_to_sign = $http_method . '&' . $base_request_uri .  '%26' . $query_string;
		$oauth_signature = base64_encode( hash_hmac( $hash_algorithm, $string_to_sign, $secret, true ) );
		
		
	
		
		$curl = curl_init();
		
		curl_setopt_array($curl, array(
		  CURLOPT_URL => $osamaUrl,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => $http_method,
		  CURLOPT_HTTPHEADER => array(
			"Accept: */*",
			"Authorization: OAuth oauth_consumer_key=\"".$consumer_key."\",oauth_signature_method=\"".$oauth_signature_method."\",oauth_nonce=\"".$nonce."\",oauth_timestamp=\"".$timestamp."\",oauth_signature=\"".$oauth_signature."\"",
			"Cache-Control: no-cache",
			"Connection: keep-alive",
			"Host: localhost",
			"User-Agent: PostmanRuntime/7.13.0",
			"accept-encoding: gzip, deflate",
			"cache-control: no-cache"
		  ),
		));
		
		$response = curl_exec($curl);
		$err = curl_error($curl);
		
		curl_close($curl);
		
		if ($err) {
		  return "cURL Error #:" . $err;
		} else {
			$data = json_decode($response, true);


			if ($data !== null) {
				if (isset($data['balance']) && is_numeric($data['balance'])) {
					$balance = floatval($data['balance']);
					$formattedBalance = number_format($balance, 2);
					$currency = $data['currency'];
			
					if ($balance > 0) {
						return '<div style="color: green; font-weight: bold;">Balance: ' . $formattedBalance.'    '. $currency . '</div>';
					} else {
						return '<div style="color: red; font-weight: bold; ">Balance is Low: ' . $formattedBalance .'    '. $currency . '</div>';
					}
				} else {
					return '<div style="color: orange; font-weight: bold; ">Invalid balance data</div>';
				}
			} else {
				return '<div style="color: red; font-weight: bold; ">Invalid JSON data</div>';
			}
			
		 
		}
		
		
		
}
		





	 

	
	
	public function my_after_submission_function($entry,$form){

		function generateOAuthSignature($url, $httpMethod, $consumerKey, $signatureMethod, $timestamp, $nonce, $consumerSecret, $postData = array()) {
			// Construct the base request URI
			$baseRequestUri = rawurlencode($url);
		
			// Include parameters in the OAuth parameters
			$params = array(
				'oauth_consumer_key' => $consumerKey,
				'oauth_nonce' => $nonce,
				'oauth_signature_method' => $signatureMethod,
				'oauth_timestamp' => $timestamp,
			);
		
			// Add any additional parameters
			$params = array_merge($params, $postData);
		
			// Sort the parameters
			ksort($params);
		
			// Create the query string
			$queryString = http_build_query($params, '', '&', PHP_QUERY_RFC3986);
		
			// Construct the string to sign
			$stringToSign = $httpMethod . '&' . $baseRequestUri . '&' . rawurlencode($queryString);
		
			// Calculate the OAuth signature
			$hashAlgorithm = strtolower(str_replace('HMAC-', '', $signatureMethod));
			$secret = $consumerSecret . '&';
			$oauthSignature = base64_encode(hash_hmac($hashAlgorithm, $stringToSign, $secret, true));
		
			return $oauthSignature;
		}
		
	function makeWordPressCurlRequest($url, $httpMethod, $consumerKey, $signatureMethod, $timestamp, $nonce, $consumerSecret, $postData = array()) {
		// Generate the OAuth signature
		$oauthSignature = generateOAuthSignature($url, $httpMethod, $consumerKey, $signatureMethod, $timestamp, $nonce, $consumerSecret, $postData);
		
		$curl = curl_init();
	
		// Set cURL options
		curl_setopt_array($curl, array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => $httpMethod,
			CURLOPT_HTTPHEADER => array(
				"Authorization: OAuth oauth_consumer_key=\"{$consumerKey}\",oauth_signature_method=\"{$signatureMethod}\",oauth_timestamp=\"{$timestamp}\",oauth_nonce=\"{$nonce}\",oauth_signature=\"{$oauthSignature}\"",
			),
		));

		if ($httpMethod === 'POST') {
			curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($postData));
		}
	
		$response = curl_exec($curl);
		$err = curl_error($curl);
	
		curl_close($curl);
	
		if ($err) {
		  return "cURL Error #:" . $err;
		} else {
		$data = json_decode($response, true);
		if ($data !== null) {
			return  $data['response'] ;
		} else {
			return 'Invalid JSON data';
		}
		}	
		
		
	
		
		
		
		
		
	}
	
	function get_consumer_key_() {
		global $wpdb;
	
		
		$query = $wpdb->prepare("
			SELECT consumer_key, consumer_secret, permissions
			FROM {$wpdb->prefix}woocommerce_api_keys");
	
		$key = $wpdb->get_row($query);
	
		return $key;
	}
	function get_consumer() {
		global $wpdb;
		
		
		$query = $wpdb->prepare("
			SELECT  option_value
			FROM {$wpdb->prefix}options where option_name = 'Terawallet_api' " );
	
		$ck_key = $wpdb->get_row($query);
	
		return $ck_key;
	}	
	
	$ck_key = get_consumer();
		$key = get_consumer_key_();
		
		$b_url=  home_url();
		$api='/wp-json/wc/v3/wallet/';
		$url=$b_url.$api;
		$email_user=wp_get_current_user();
		


$hf_username = $email_user->user_email;
	
		$httpMethod = 'POST';
		$consumerKey = $ck_key->option_value;
		$signatureMethod = 'HMAC-SHA256';
		$timestamp = time();
		$nonce = uniqid();
		$consumerSecret =  $key->consumer_secret;
		$postData = array(
			'email' => $hf_username,
			'type' => 'debit',
			'amount' =>$this->get_product_price($entry,$form),
			'note' => 'Payment',
		);
	
	$response = makeWordPressCurlRequest($url, $httpMethod, $consumerKey, $signatureMethod, $timestamp, $nonce, $consumerSecret, $postData);
		

		
		return $response;
	}
	
	public function get_product_price($entry,$form){
		 
		$product_price = $form['fields'];
		$total_value = 0;
		$total_price_field_exists = false;
		for ($i = 0; $i < count($product_price); $i++) {
			if ($product_price[$i]['type'] == 'total') {
				
				$total_price_field_exists = true;
				$total_price = $entry[$form['fields'][$i]['id']];
				

			} elseif ($product_price[$i]['type'] == 'product') {
				$q_price = $product_price[$i]['inputs'];
				for ($j = 0; $j < count($q_price); $j++) {
					if($q_price[$j]['label'] == 'Quantity'){
						
						$value = GFCommon::to_number($product_price[$i]['basePrice']);
						$numericEntry = floatval($entry[$q_price[$j]['id']]);
						$total_value += $value * $numericEntry;
						
					}}
					
				}
			}
			if ($total_price_field_exists) {
				return	$payment_amount = $total_price; 
				
			} else  {
				
				return	$payment_amount = $total_value; 
				
			}
			
		}
		

	/**
	 * Returns the field markup; including field label, description, validation, and the form editor admin buttons.
	 *
	 * The {FIELD} placeholder will be replaced in GFFormDisplay::get_field_content with the markup returned by GF_Field::get_field_input().
	 *
	 * @since 2.6
	 *
	 * @param string|array $value                The field value. From default/dynamic population, $_POST, or a resumed incomplete submission.
	 * @param bool         $force_frontend_label Should the frontend label be displayed in the admin even if an admin label is configured.
	 * @param array        $form                 The Form Object currently being processed.
	 *
	 * @return string
	 */
	public function get_field_content( $value, $force_frontend_label, $form ) {
		$is_entry_detail = $this->is_entry_detail();
		$is_form_editor  = $this->is_form_editor();
		$is_admin        = $is_entry_detail || $is_form_editor;

		$field_content = parent::get_field_content( $value, $force_frontend_label, $form );

	
		
		return $field_content;
	}

	/**
	 * Get field label class.
	 *
	 * @since 2.6
	 *
	 * @return string
	 */
	public function get_field_label_class() {
		return 'gfield_label gfield_label_before_complex';
	}

	/**
	 * Get entry inputs.
	 *
	 * @since 2.6
	 *
	 * @return array|null
	 */
	public function get_entry_inputs() {
		$inputs = array();
		foreach ( $this->inputs as $input ) {
			if ( in_array( $input['id'], array( $this->id . '.1', $this->id . '.4' ), true ) ) {
				$inputs[] = $input;
			}
		}

		return $inputs;
	}

	// /**
	//  * Get the value in entry details.
	//  *
	//  * @since 2.6
	//  *
	//  * @param string|array $value    The field value.
	//  * @param string       $currency The entry currency code.
	//  * @param bool|false   $use_text When processing choice based fields should the choice text be returned instead of the value.
	//  * @param string       $format   The format requested for the location the merge is being used. Possible values: html, text or url.
	//  * @param string       $media    The location where the value will be displayed. Possible values: screen or email.
	//  *
	//  * @return string
	//  */
	public function get_value_entry_detail( $value, $currency = '', $use_text = false, $format = 'html', $media = 'screen' ) {

		if ( is_array( $value ) ) {
			$card_number = trim( rgget( $this->id . '.1', $value ) );
			$card_type   = trim( rgget( $this->id . '.4', $value ) );
			$separator   = $format === 'html' ? '<br/>' : "\n";

			return empty( $card_number ) ? '' : $card_type . $separator . $card_number;
		} else {
			return '';
		}
	}

	
	// /**
	//  * Returns the full name for the supplied card brand.
	//  *
	//  * @since 3.5
	//  *
	//  * @param string $slug The card brand supplied by terawallet.js.
	//  *
	//  * @return string
	//  */
	public function get_card_name( $slug ) {
		if ( empty( $slug ) ) {
			return $slug;
		}

		$card_types = GFCommon::get_card_types();

		foreach ( $card_types as $card ) {
			if ( rgar( $card, 'slug' ) === $slug ) {
				return rgar( $card, 'name' );
			}
		}

		return $slug;
	}

	/**
	 * Display the terawallet Card error message.
	 *
	 * @since 3.5
	 *
	 * @param string $message The error message.
	 * @param string $url     The settings URL.
	 *
	 * @return string
	 */
	private function get_card_error_message( $message, $url = '' ) {
		if ( $url ) {
			return sprintf( $message, '<div class="gform_terawallet_card_error">', '</div>', '<a href="' . $url . '" target="_blank">', '</a>' );
		}

		return sprintf( $message, '<div class="gfield_description validation_message">', '</div>' );
	}

}

GF_Fields::register( new GF_Field_Terawallet_CreditCard() );
