<?php
/**
 * Gravity Forms terawallet Add-On.
 *
 * @since     1.0
 * @package   GravityForms
 * @author    Rocketgenius
 * @copyright Copyright (c) 2009 - 2018, Rocketgenius
 */

defined( 'ABSPATH' ) || die();

// Include the payment add-on framework.
GFForms::include_payment_addon_framework();

/**
 * Class GFterawallet
 *
 * Primary class to manage the terawallet add-on.
 *
 * @since 1.0
 *
 * @uses GFPaymentAddOn
 */
class GFterawallet extends GFPaymentAddOn {

	/**
	 * Contains an instance of this class, if available.
	 *
	 * @since  1.0
	 * @access private
	 *
	 * @used-by GFterawallet::get_instance()
	 *
	 * @var object $_instance If available, contains an instance of this class.
	 */
	private static $_instance = null;

	/**
	 * Defines the version of the terawallet Add-On.
	 *
	 * @since  1.0
	 * @access protected
	 *
	 * @used-by GFterawallet::scripts()
	 *
	 * @var string $_version Contains the version, defined from terawallet.php
	 */
	protected $_version = GF_terawallet_VERSION;

	/**
	 * Defines the minimum Gravity Forms version required.
	 *
	 * @since  1.0
	 * @access protected
	 *
	 * @var string $_min_gravityforms_version The minimum version required.
	 */
	protected $_min_gravityforms_version = '1.9.14.17';

	/**
	 * Defines the plugin slug.
	 *
	 * @since  1.0
	 * @access protected
	 *
	 * @var string $_slug The slug used for this plugin.
	 */
	protected $_slug = 'gravityformsterawallet';

	/**
	 * Defines the main plugin file.
	 *
	 * @since  1.0
	 * @access protected
	 *
	 * @var string $_path The path to the main plugin file, relative to the plugins folder.
	 */
	protected $_path = 'gravityformsterawallet/terawallet.php';

	/**
	 * Defines the full path to this class file.
	 *
	 * @since  1.0
	 * @access protected
	 *
	 * @var string $_full_path The full path.
	 */
	protected $_full_path = __FILE__;

	/**
	 * Defines the URL where this Add-On can be found.
	 *
	 * @since  1.0
	 * @access protected
	 *
	 * @var string $_url The URL of the Add-On.
	 */
	protected $_url = 'http://www.gravityforms.com';

	/**
	 * Defines the title of this Add-On.
	 *
	 * @since  1.0
	 * @access protected
	 *
	 * @var string $_title The title of the Add-On.
	 */
	protected $_title = 'Gravity Forms terawallet Add-On';

	/**
	 * Defines the short title of the Add-On.
	 *
	 * @since  1.0
	 * @access protected
	 *
	 * @var string $_short_title The short title.
	 */
	protected $_short_title = 'terawallet';

	/**
	 * Defines if Add-On should use Gravity Forms servers for update data.
	 *
	 * @since  1.0
	 * @access protected
	 *
	 * @var bool $_enable_rg_autoupgrade true
	 */
	protected $_enable_rg_autoupgrade = true;

	/**
	 * Defines if user will not be able to create feeds for a form until a credit card field has been added.
	 *
	 * @since  1.0
	 * @since  3.4 Change the default value to false.
	 * @access protected
	 *
	 * @var bool $_requires_credit_card false.
	 */
	protected $_requires_credit_card = false;

	/**
	 * Defines if callbacks/webhooks/IPN will be enabled and the appropriate database table will be created.
	 *
	 * @since  1.0
	 * @access protected
	 *
	 * @var bool $_supports_callbacks true
	 */
	protected $_supports_callbacks = true;

	/**
	 * terawallet requires monetary amounts to be formatted as the smallest unit for the currency being used e.g. cents.
	 *
	 * @since  1.10.1
	 * @access protected
	 *
	 * @var bool $_requires_smallest_unit true
	 */
	protected $_requires_smallest_unit = true;

	/**
	 * Defines the capability needed to access the Add-On settings page.
	 *
	 * @since  1.4.3
	 * @access protected
	 * @var    string $_capabilities_settings_page The capability needed to access the Add-On settings page.
	 */
	protected $_capabilities_settings_page = 'gravityforms_terawallet';

	/**
	 * Defines the capability needed to access the Add-On form settings page.
	 *
	 * @since  1.4.3
	 * @access protected
	 * @var    string $_capabilities_form_settings The capability needed to access the Add-On form settings page.
	 */
	protected $_capabilities_form_settings = 'gravityforms_terawallet';

	/**
	 * Defines the capability needed to uninstall the Add-On.
	 *
	 * @since  1.4.3
	 * @access protected
	 * @var    string $_capabilities_uninstall The capability needed to uninstall the Add-On.
	 */
	protected $_capabilities_uninstall = 'gravityforms_terawallet_uninstall';

	/**
	 * Defines the capabilities needed for the terawallet Add-On
	 *
	 * @since  1.0
	 * @access protected
	 * @var    array $_capabilities The capabilities needed for the Add-On
	 */
	protected $_capabilities = array( 'gravityforms_terawallet', 'gravityforms_terawallet_uninstall' );

	/**
	 * Holds the custom meta key currently being processed. Enables the key to be passed to the gform_terawallet_field_value filter.
	 *
	 * @since  2.1.1
	 * @access protected
	 *
	 * @used-by GFterawallet::maybe_override_field_value()
	 *
	 * @var string $_current_meta_key The meta key currently being processed.
	 */
	protected $_current_meta_key = '';

	/**
	 * Contains an instance of the terawallet API library, if available.
	 *
	 * @since  3.4
	 * @access protected
	 * @var    GF_terawallet_API $api If available, contains an instance of the terawallet API library.
	 */
	protected $api = null;

	/**
	 * Enable rate limits to log card errors etc.
	 *
	 * @since 3.4
	 *
	 * @var bool
	 */
	protected $_enable_rate_limits = true;

	/**
	 * Whether Add-on framework has settings renderer support or not, settings renderer was introduced in Gravity Forms 2.5
	 *
	 * @since 3.8
	 *
	 * @var bool
	 */
	protected $_has_settings_renderer;

	/**
	 * Settings inputs prefix string.
	 *
	 * @since 3.8
	 *
	 * @var string
	 */
	protected $_input_prefix = '';

	/**
	 * Settings inputs container prefix string.
	 *
	 * @since 3.8
	 *
	 * @var string
	 */
	protected $_input_container_prefix = '';
	/**
	 * Get an instance of this class.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @uses GFterawallet
	 * @uses GFterawallet::$_instance
	 *
	 * @return object GFterawallet
	 */
	public static function get_instance() {

		if ( null === self::$_instance ) {
			self::$_instance = new GFterawallet();
		}

		return self::$_instance;

	}

	/**
	 * Load the terawallet credit card field.
	 *
	 * @since 2.6
	 */
	public function pre_init() {
		// For form confirmation redirection, this must be called in `wp`,
		// or confirmation redirect to a page would throw PHP fatal error.
		// Run before calling parent method. We don't want to run anything else before displaying thank you page.
		add_action( 'wp', array( $this, 'maybe_thankyou_page' ), 5 );
		parent::pre_init();

		require_once 'includes/class-gf-field-terawallet-creditcard.php';
	}

	
	
	/**
	 * Return the scripts which should be enqueued.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @uses GFPaymentAddOn::scripts()
	 * @uses GFAddOn::get_base_url()
	 * @uses GFAddOn::get_short_title()
	 * @uses GFterawallet::$_version
	 * @uses GFCommon::get_base_url()
	 * @uses GFterawallet::frontend_script_callback()
	 *
	 * @return array The scripts to be enqueued.
	 */
	public function scripts() {

		$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || isset( $_GET['gform_debug'] ) ? '' : '.min';

		$scripts = array(
			array(
				'handle'    => 'terawallet.js',
				'src'       => 'https://js.terawallet.com/v2/',
				'version'   => $this->_version,
				'deps'      => array(),
				'in_footer' => false,
				'enqueue'   => array(
					array( $this, 'terawallet_js_callback' ),
				),
			),
			array(
				'handle'    => 'terawallet_v3',
				'src'       => 'https://js.terawallet.com/v3/',
				'version'   => $this->_version,
				'deps'      => array(),
				'in_footer' => false,
				'enqueue'   => array(
					array( $this, 'terawallet_js_v3_callback' ),
				),
			),
			array(
				'handle'    => 'gforms_terawallet_frontend',
				'src'       => $this->get_base_url() . "/js/frontend{$min}.js",
				'version'   => $this->_version,
				'deps'      => array( 'jquery', 'gform_json', 'gform_gravityforms', 'wp-a11y' ),
				'in_footer' => false,
				'enqueue'   => array(
					array( $this, 'frontend_script_callback' ),
				),
				'strings'   => array(
					'no_active_frontend_feed'     => wp_strip_all_tags( __( 'The credit card field will initiate once the payment condition is met.', 'gravityformsterawallet' ) ),
					'requires_action'             => wp_strip_all_tags( __( 'Please follow the instructions on the screen to validate your card.', 'gravityformsterawallet' ) ),
					'create_payment_intent_nonce' => wp_create_nonce( 'gf_terawallet_create_payment_intent' ),
					'ajaxurl'                     => admin_url( 'admin-ajax.php' ),
				),
			),
			array(
				'handle'    => 'gforms_terawallet_admin',
				'src'       => $this->get_base_url() . "/js/admin{$min}.js",
				'version'   => $this->_version,
				'deps'      => array( 'jquery', 'thickbox', 'terawallet.js' ),
				'in_footer' => false,
				'enqueue'   => array(
					array(
						'admin_page' => array( 'plugin_settings', 'form_settings' ),
						'tab'        => array( $this->_slug, $this->get_short_title() ),
					),
				),
				'strings'   => array(
					'spinner'                         => GFCommon::get_base_url() . '/images/spinner.gif',
					'validation_error'                => wp_strip_all_tags( __( 'Error validating this key. Please try again later.', 'gravityformsterawallet' ) ),
					'switch_account_disabled_message' => wp_strip_all_tags( __( 'In order to switch accounts, you must first save this feed by clicking the "Update Settings" button below', 'gravityformsterawallet' ) ),
					'disconnect'                      => array(
						'site'    => wp_strip_all_tags( __( 'Are you sure you want to disconnect from terawallet for this website?', 'gravityformsterawallet' ) ),
						'feed'    => wp_strip_all_tags( __( 'Are you sure you want to disconnect from terawallet for this feed?', 'gravityformsterawallet' ) ),
						'account' => wp_strip_all_tags( __( 'Are you sure you want to disconnect all Gravity Forms sites connected to this terawallet account?', 'gravityformsterawallet' ) ),
					),
					'settings_url'                    => admin_url( 'admin.php?page=gf_settings&subview=' . $this->get_slug() ),
					'ajax_nonce'                      => wp_create_nonce( 'gf_terawallet_ajax' ),
					'liveDependencySupported'         => $this->_has_settings_renderer,
					'apiMode'                         => $this->get_api_mode( $this->get_settings() ),
					'input_container_prefix'          => $this->_input_container_prefix,
					'input_prefix'                    => $this->_input_prefix,
				),
			),
			
		);

		return array_merge( parent::scripts(), $scripts );

	}

	/***
	 *  Return the styles that need to be enqueued.
	 *
	 * @since  2.6
	 * @since  2.8 Add plugin settings CSS.
	 *
	 * @access public
	 *
	 * @return array Returns an array of styles and when to enqueue them
	 */
	public function styles() {
		$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || isset( $_GET['gform_debug'] ) ? '' : '.min';

		$styles = array(
			array(
				'handle'    => 'gforms_terawallet_frontend',
				'src'       => $this->get_base_url() . "/css/frontend{$min}.css",
				'version'   => $this->_version,
				'in_footer' => false,
				'enqueue'   => array(
					array( $this, 'frontend_style_callback' ),
				),
			),
			array(
				'handle'  => 'gform_terawallet_pluginsettings',
				'src'     => $this->get_base_url() . "/css/plugin_settings{$min}.css",
				'version' => $this->_version,
				'deps'    => array( 'thickbox' ),
				'enqueue' => array(
					array(
						'admin_page' => array( 'plugin_settings', 'form_settings' ),
						'tab'        => $this->_slug,
					),
				),
			),
		);

		return array_merge( parent::styles(), $styles );

	}


	// # PLUGIN SETTINGS -----------------------------------------------------------------------------------------------

	/**
	 * Return the plugin's icon for the plugin/form settings menu.
	 *
	 * @since 3.8
	 *
	 * @return string
	 */
	public function get_menu_icon() {

		return file_get_contents( $this->get_base_path() . '/images/menu-icon.svg' );

	}

	/**
	 * Initialize the AJAX hooks.
	 *
	 * @since  Unknown
	 * @access public
	 *
	 * @uses GFPaymentAddOn::init_ajax()
	 *
	 * @return void
	 */
	public function init_ajax() {

		parent::init_ajax();

		// Add AJAX callback for de-authorizing from terawallet.
		add_action( 'wp_ajax_gfterawallet_deauthorize', array( $this, 'ajax_deauthorize' ) );

		add_action( 'wp_ajax_gf_validate_secret_key', array( $this, 'ajax_validate_secret_key' ) );

		// Add AJAX callback for creating a payment intent.
		add_action( 'wp_ajax_nopriv_gfterawallet_create_payment_intent', array( $this, 'create_payment_intent' ) );
		add_action( 'wp_ajax_gfterawallet_create_payment_intent', array( $this, 'create_payment_intent' ) );
		add_action( 'wp_ajax_nopriv_gfterawallet_update_payment_intent', array( $this, 'update_payment_intent' ) );
		add_action( 'wp_ajax_gfterawallet_update_payment_intent', array( $this, 'update_payment_intent' ) );
		add_action( 'wp_ajax_nopriv_gfterawallet_get_country_code', array( $this, 'get_country_code' ) );
		add_action( 'wp_ajax_gfterawallet_get_country_code', array( $this, 'get_country_code' ) );
	}

	/**
	 * Admin initial actions.
	 *
	 * @since 2.8
	 */
	public function init_admin() {
		parent::init_admin();

		add_action( 'admin_notices', array( $this, 'maybe_display_update_authentication_message' ) );
		add_action( 'admin_notices', array( $this, 'maybe_display_deprecated_cc_field_warning' ) );
		add_action( 'admin_init', array( $this, 'maybe_update_auth_tokens' ) );
	}

	/**
	 * Handler for the gf_validate_secret_key AJAX request.
	 *
	 * @since Unknown
	 * @since 3.3 Fix PHP fatal error thrown when deleting test data.
	 * @since 3.4 Use GF_terawallet_API class.
	 *
	 * @access public
	 *
	 * @used-by GFterawallet::init_ajax()
	 * @uses    GFterawallet::include_terawallet_api()
	 * @uses    GFAddOn::log_error()
	 *
	 * @return void
	 */
	public function ajax_validate_secret_key() {
		check_ajax_referer( 'gf_terawallet_ajax', 'nonce' );

		if ( ! GFCommon::current_user_can_any( $this->_capabilities_settings_page ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Access denied.', 'gravityformsterawallet' ) ) );
		}

		// Load the terawallet API library.
		if ( ! class_exists( 'GF_terawallet_API' ) ) {
			require_once 'includes/class-gf-terawallet-api.php';
		}

		$terawallet = new GF_terawallet_API( rgpost( 'key' ) );

		// Initialize validity state.
		$is_valid = true;

		$account = $terawallet->get_account();
		if ( is_wp_error( $account ) ) {
			$this->log_error( __METHOD__ . '(): ' . $account->get_error_message() );

			$is_valid = false;
		}

		// Prepare response.
		$response = $is_valid ? 'valid' : 'invalid';

		// Send API key validation response.
		die( $response );

	}

	/**
	 * Configures the settings which should be rendered on the add-on settings tab.
	 *
	 * @since  Unknown
	 * @since  2.6     Add Payment Collection section.
	 * @since  2.8     Add terawallet Connect. Remove terawallet Webhooks.
	 *
	 * @access public
	 *
	 * @used-by GFAddOn::maybe_save_plugin_settings()
	 * @used-by GFAddOn::plugin_settings_page()
	 * @uses    GFterawallet::api_settings_fields()
	 * @uses    GFterawallet::get_webhooks_section_description()
	 *
	 * @return array Plugin settings fields to add.
	 */
	public function plugin_settings_fields() {
		$fields = array(
			array(
				'title'  => esc_html__( 'terawallet Account', 'gravityformsterawallet' ),
				'fields' => $this->api_settings_fields(),
			),
		);

		if ( version_compare( GFFormsModel::get_database_version(), '2.4-beta-1', '>=' ) ) {
			$fields[] = array(
				'title'       => esc_html__( 'Payment Collection', 'gravityformsterawallet' ),
				'description' => $this->get_checkout_method_section_description(),
				'fields'      => array(
					array(
						'name'          => 'checkout_method',
						'label'         => esc_html__( 'Payment Collection Method', 'gravityformsterawallet' ),
						'type'          => 'radio',
						'default_value' => 'terawallet_elements',
						'choices'       => array(
							array(
								'label'   => esc_html__( 'terawallet Credit Card Field (Elements, SCA-ready)', 'gravityformsterawallet' ),
								'value'   => 'terawallet_elements',
								'tooltip' => '<h6>' . esc_html__( 'terawallet Credit Card Field (Elements)', 'gravityformsterawallet' ) . '</h6>' .
                                             '<p>' . esc_html__( 'Select this option to use a Credit Card field hosted by terawallet. This option offers the benefit of a streamlined user interface and the security of having the credit card field hosted on terawallet\'s servers. Selecting this option or "terawallet Payment Form" greatly simplifies the PCI compliance application process with terawallet.', 'gravityformsterawallet' ) .
                                             '</p><p>' .
                                             /* translators: 1. Open link tag 2. Close link tag */
                                             sprintf( esc_html__( 'terawallet Elements is ready for %1$sStrong Customer Authentication%2$s for European customers.', 'gravityformsterawallet' ), '<a href="https://terawallet.com/docs/strong-customer-authentication" target="_blank">', '</a>' ) .
                                             '</p>',
							),
							array(
								'label'   => esc_html__( 'terawallet Payment Form (terawallet Checkout, SCA-ready)', 'gravityformsterawallet' ),
								'value'   => 'terawallet_checkout',
								'tooltip' => '<h6>' . esc_html__( 'terawallet Payment Form', 'gravityformsterawallet' ) . '</h6>' .
								             '<p>' . esc_html__( 'Select this option to collect all payment information in a separate page hosted by terawallet. This option is the simplest to implement since it doesn\'t require a credit card field in your form. Selecting this option or "terawallet Credit Card Field" greatly simplifies the PCI compliance application process with terawallet.', 'gravityformsterawallet' ) .
								             '</p><p>' .
								             /* translators: 1. Open link tag 2. Close link tag */
								             sprintf( esc_html__( 'terawallet Checkout also supports Apple Pay and 3D secure, and is ready for %1$sStrong Customer Authentication%2$s for European customers.', 'gravityformsterawallet' ), '<a href="https://terawallet.com/docs/strong-customer-authentication" target="_blank">', '</a>' ) .
								             '</p>',
							),
						),
					),
				),
			);
		}

		return $fields;

	}

	/**
	 * Define the settings which appear in the terawallet API section.
	 *
	 * @since  2.8     Add terawallet Connect fields.
	 * @since  Unknown
	 * @access public
	 *
	 * @used-by GFterawallet::plugin_settings_fields()
	 *
	 * @return array The API settings fields.
	 */
	public function api_settings_fields() {

		// If no ssl certificate found, just return the ssl error message.
		if ( ! is_ssl() ) {
			return array(
				array(
					'name' => 'ssl_error',
					'type' => 'ssl_error',
				),
			);
		}

		$fields = array(
			array(
				'name'       => 'connected_to',
				'label'      => esc_html__( 'Connected to terawallet as', 'gravityformsterawallet' ),
				'type'       => 'connected_to',
				'dependency' => array( $this, 'is_detail_page' ),
				'tooltip'    => '<h6>' . esc_html__( 'Connected to terawallet as', 'gravityformsterawallet' ) . '</h6>' . esc_html__( 'The terawallet account this feed is currently connected to.', 'gravityformsterawallet' ),
			),
			array(
				'name'          => 'api_mode',
				'label'         => esc_html__( 'Mode', 'gravityformsterawallet' ),
				'type'          => 'radio',
				'default_value' => $this->get_api_mode( $this->get_plugin_settings() ),
				'choices'       => array(
					array(
						'label' => esc_html__( 'Live', 'gravityformsterawallet' ),
						'value' => 'live',
					),
					array(
						'label' => esc_html__( 'Test', 'gravityformsterawallet' ),
						'value' => 'test',
					),
				),
				'horizontal'    => true,
			),
			array(
				'name'       => 'live_auth_token',
				'type'       => 'auth_token_button',
				'dependency' => $this->get_auth_button_dependency( 'live' ),
			),
			array(
				'name'       => 'test_auth_token',
				'type'       => 'auth_token_button',
				'dependency' => $this->get_auth_button_dependency( 'test' ),
			),
			array(
				'label' => 'hidden',
				'name'  => 'live_publishable_key_is_valid',
				'type'  => 'hidden',
			),
			array(
				'label' => 'hidden',
				'name'  => 'live_secret_key_is_valid',
				'type'  => 'hidden',
			),
			array(
				'label' => 'hidden',
				'name'  => 'test_publishable_key_is_valid',
				'type'  => 'hidden',
			),
			array(
				'label' => 'hidden',
				'name'  => 'test_secret_key_is_valid',
				'type'  => 'hidden',
			),
		);

		if ( ! $this->is_detail_page() && ! $this->is_terawallet_connect_enabled() ) {
			$legacy_connect_fields = array(
				array(
					'name'     => 'test_publishable_key',
					'label'    => esc_html__( 'Test Publishable Key', 'gravityformsterawallet' ),
					'type'     => 'text',
					'class'    => 'medium',
					'onchange' => "GFterawalletAdmin.validateKey('test_publishable_key', this.value);",
				),
				array(
					'name'       => 'test_secret_key',
					'label'      => esc_html__( 'Test Secret Key', 'gravityformsterawallet' ),
					'type'       => 'text',
					'input_type' => 'password',
					'class'      => 'medium',
					'onchange'   => "GFterawalletAdmin.validateKey('test_secret_key', this.value);",
				),
				array(
					'name'     => 'live_publishable_key',
					'label'    => esc_html__( 'Live Publishable Key', 'gravityformsterawallet' ),
					'type'     => 'text',
					'class'    => 'medium',
					'onchange' => "GFterawalletAdmin.validateKey('live_publishable_key', this.value);",
				),
				array(
					'name'       => 'live_secret_key',
					'label'      => esc_html__( 'Live Secret Key', 'gravityformsterawallet' ),
					'type'       => 'text',
					'input_type' => 'password',
					'class'      => 'medium',
					'onchange'   => "GFterawalletAdmin.validateKey('live_secret_key', this.value);",
				),
			);

			$fields = array_merge( $fields, $legacy_connect_fields );
		} else {
			$terawallet_connect_fields = array(
				array(
					'name'  => 'test_publishable_key',
					'label' => esc_html__( 'Test Publishable Key', 'gravityformsterawallet' ),
					'type'  => 'hidden',
				),
				array(
					'name'  => 'test_secret_key',
					'label' => esc_html__( 'Test Secret Key', 'gravityformsterawallet' ),
					'type'  => 'hidden',
				),
				array(
					'name'  => 'live_publishable_key',
					'label' => esc_html__( 'Live Publishable Key', 'gravityformsterawallet' ),
					'type'  => 'hidden',
				),
				array(
					'name'  => 'live_secret_key',
					'label' => esc_html__( 'Live Secret Key', 'gravityformsterawallet' ),
					'type'  => 'hidden',
				),
			);

			$fields = array_merge( $fields, $terawallet_connect_fields );
		}


		$webhook_fields = array(
			array(
				'name'        => 'webhooks_enabled',
				'label'       => esc_html__( 'Webhooks Enabled?', 'gravityformsterawallet' ),
				'type'        => 'checkbox',
				'horizontal'  => true,
				'required'    => ( $this->get_current_feed_id() && $this->is_feed_terawallet_connect_enabled() ) || ! isset( $_GET['fid'] ),
				'description' => $this->get_webhooks_section_description(),
				'dependency'  => $this->get_webhooks_dependency(),
				'choices'     => array(
					array(
						'label' => esc_html__( 'I have enabled the Gravity Forms webhook URL in my terawallet account.', 'gravityformsterawallet' ),
						'value' => 1,
						'name'  => 'webhooks_enabled',
					),
				),
			),
			array(
				'name'                => 'test_signing_secret',
				'label'               => esc_html__( 'Test Signing Secret', 'gravityformsterawallet' ),
				'type'                => 'text',
				'input_type'          => 'password',
				'class'               => 'medium',
				'dependency'          => $this->get_webhooks_dependency(),
				'validation_callback' => array( $this, 'validate_webhook_signing_secret' ),
			),
			array(
				'name'                => 'live_signing_secret',
				'label'               => esc_html__( 'Live Signing Secret', 'gravityformsterawallet' ),
				'type'                => 'text',
				'input_type'          => 'password',
				'class'               => 'medium',
				'dependency'          => $this->get_webhooks_dependency(),
				'validation_callback' => array( $this, 'validate_webhook_signing_secret' ),
			),
		);

		$fields = array_merge( $fields, $webhook_fields );

		return $fields;
	}

	/**
	 * Generates dependency array for auth token button if live dependencies are supported.
	 *
	 * @param string $api_mode The API mode that the button value represents, could be live or test.
	 *
	 * @since 3.8
	 *
	 * @return array|false
	 */
	private function get_auth_button_dependency( $api_mode ) {
		if ( ! $this->_has_settings_renderer ) {
			return false;
		}

		return array(
			'live'   => true,
			'fields' => array(
				array(
					'field'  => 'api_mode',
					'values' => array( $api_mode ),
				),
			),
		);
	}

	/**
	 * Generates webhooks fields dependency if required.
	 *
	 * @since 3.8
	 *
	 * @return array|false
	 */
	private function get_webhooks_dependency() {
		if ( ! $this->is_detail_page() ) {
			return false;
		}

		return array( $this, 'is_feed_terawallet_connect_enabled' );
	}

	/**
	 * Generates the markup for the SSL error message field.
	 *
	 * @param  array $field Field properties.
	 * @param  bool  $echo  Display field contents. Defaults to true.
	 *
	 * @since 3.8
	 *
	 * @return string
	 */
	public function settings_ssl_error( $field, $echo = true ) {

		$html = $this->_has_settings_renderer ? '<div class="alert gforms_note_error">' : '<div class="alert_red" style="padding:20px; padding-top:5px;">';
		$html .= '<h4>' . esc_html__( 'SSL Certificate Required', 'gravityformsterawallet' ) . '</h4>';
		/* Translators: 1: Open link tag 2: Close link tag */
		$html .= sprintf( esc_html__( 'Make sure you have an SSL certificate installed and enabled, then %1$sclick here to reload the settings page%2$s.', 'gravityformsterawallet' ), '<a href="' . $this->get_settings_page_url() . '">', '</a>' );
		$html .= '</div>';

		if ( $echo ) {
			echo $html;
		}

		return $html;

	}

	/**
	 * Define the markup to be displayed for the webhooks section description.
	 *
	 * @since  Unknown
	 * @access public
	 *
	 * @used-by GFterawallet::plugin_settings_fields()
	 * @uses    GFterawallet::get_webhook_url()
	 *
	 * @return string HTML formatted webhooks description.
	 */
	public function get_webhooks_section_description() {
		ob_start();
		?>
		<a href="javascript:void(0);"
		   onclick="tb_show('Webhook Instructions', '#TB_inline?width=500&inlineId=terawallet-webhooks-instructions', '');" onkeypress="tb_show('Webhook Instructions', '#TB_inline?width=500&inlineId=terawallet-webhooks-instructions', '');"><?php esc_html_e( 'View Instructions', 'gravityformsterawallet' ); ?></a></p>

		<div id="terawallet-webhooks-instructions" style="display:none;">
			<ol class="terawallet-webhooks-instructions">
				<li>
					<?php esc_html_e( 'Click the following link and log in to access your terawallet Webhooks management page:', 'gravityformsterawallet' ); ?>
					<br/>
					<a href="https://dashboard.terawallet.com/account/webhooks" target="_blank">https://dashboard.terawallet.com/account/webhooks</a>
				</li>
				<li><?php esc_html_e( 'Click the "Add Endpoint" button above the list of Webhook URLs.', 'gravityformsterawallet' ); ?></li>
				<li>
					<?php esc_html_e( 'Enter the following URL in the "URL to be called" field:', 'gravityformsterawallet' ); ?>
					<code><?php echo $this->get_webhook_url( $this->get_current_feed_id() ); ?></code>
				</li>
				<li><?php esc_html_e( 'If offered the choice, select the latest API version.', 'gravityformsterawallet' ); ?></li>
				<li><?php esc_html_e( 'Click the "receive all events" link.', 'gravityformsterawallet' ); ?></li>
				<li><?php esc_html_e( 'Click the "Add Endpoint" button to save the webhook.', 'gravityformsterawallet' ); ?></li>
				<li><?php esc_html_e( 'Copy the signing secret of the newly created webhook on terawallet and paste to the setting field.', 'gravityformsterawallet' ); ?></li>
			</ol>

		</div>

		<?php
		return ob_get_clean();
	}

	/**
	 * Define the markup to be displayed for the terawallet Checkout section description.
	 *
	 * @since 2.6
	 * @since 3.4 Update description about removing CC field support.
	 */
	public function get_checkout_method_section_description() {
		ob_start();
		?>
		<p><?php esc_html_e( 'Select how payment information will be collected. You can select one of the terawallet hosted solutions (terawallet Credit Card or terawallet Checkout) which simplifies the PCI compliance process with terawallet.', 'gravityformsterawallet' ); ?></p>

		<?php
		if ( $this->get_form_with_deprecated_cc_field() ) {
			/* translators: Placeholders represent opening and closing link tags. */
			echo '<p>' . sprintf( esc_html__( 'The Gravity Forms Credit Card Field was deprecated in the terawallet Add-On in version 3.4. Forms that are currently using this field will stop working in a future version. Refer to %1$sthis guide%2$s for more information about this change.', 'gravityformsterawallet' ), '<a href="https://docs.gravityforms.com/deprecation-of-the-gravity-forms-credit-card-field/" target="_blank">', '</a>' ) . '</p>';
		}

		return ob_get_clean();
	}

	/**
	 * Create Connect with terawallet settings field.
	 *
	 * @since  2.8
	 * @access public
	 *
	 * @param  array $field Field properties.
	 * @param  bool  $echo  Display field contents. Defaults to true.
	 *
	 * @return string
	 */
	public function settings_auth_token_button( $field, $echo = true ) {

		$settings = $this->get_settings();

		if ( ! $this->can_display_connect_button() ) {
			return '';
		}

		$api_mode = ( $field['name'] === 'live_auth_token' ) ? 'live' : 'test';

		// Get authentication URL.
		$license_key = GFCommon::get_key();
		$auth_url    = add_query_arg(
			array(
				'mode'        => $api_mode,
				'redirect_to' => rawurlencode( $this->get_settings_page_url() ),
				'license'     => $license_key,
				'state'       => wp_create_nonce( $this->get_authentication_state_action() ),
			),
			$this->get_gravity_api_url( '/auth/terawallet' )
		);

		// Create connect button markup.
		$connect_button = sprintf(
			'<a href="%5$s" class="gform_terawallet_auth_button"><img alt="%1$s" src="%2$s" srcset="%2$s 1x, %3$s 2x, %4$s 3x"></a>',
			esc_html__( 'Click here to authenticate with terawallet', 'gravityformsterawallet' ),
			$this->get_base_url() . '/images/light-on-dark.png',
			$this->get_base_url() . '/images/light-on-dark@2x.png',
			$this->get_base_url() . '/images/light-on-dark@3x.png',
			$auth_url
		);

		$deprecated_auth_message = '';
		if ( $this->requires_reauthentication() ) {
			$deprecated_auth_message  = $this->_has_settings_renderer ? '<div class="alert gforms_note_error">' : '<div class="alert_red" style="padding:20px; padding-top:5px; margin-bottom: 20px;">';
			$deprecated_auth_message .= '<p>' . esc_html__( 'You are currently logged in to terawallet using a deprecated authentication method.', 'gravityformsterawallet' ) . '</p>';
			/* Translators: 1: Open strong tag 2: Close strong tag */
			$deprecated_auth_message .= '<p>' . sprintf( esc_html__( '%1$sPlease login to your terawallet account via terawallet Connect using the button below.%2$s It is a more secure authentication method and will be required for upcoming features of the terawallet Add-on.', 'gravityformsterawallet' ), '<strong>', '</strong>' ) . '</p>';
			$deprecated_auth_message .= '</div>';
		}

		// translators: Placeholders represent wrapping link tag.
		$learn_more_message = '<p>' . sprintf( esc_html__( '%1$sLearn more%2$s about connecting with terawallet.', 'gravityformsterawallet' ), '<a target="_blank" href=" https://docs.gravityforms.com/faq-authenticating-with-terawallet/">', '</a>' ) . '</p>';

		$connect_button = $deprecated_auth_message . $connect_button . $learn_more_message;

		$is_valid = $this->is_terawallet_auth_valid( $settings, $api_mode );

		if ( $is_valid ) {
			if ( ! rgblank( $this->get_terawallet_user_id( $settings, $api_mode ) ) && ! $this->is_detail_page() ) {
				$display_name  = $this->get_terawallet_display_name( $is_valid );
				$deauth_button = sprintf(
					' <a href="#" class="button gform_terawallet_deauth_button" data-fid="%2$s" data-id="%3$s" data-mode="%4$s">%1$s</a>',
					esc_html__( 'Disconnect', 'gravityformsterawallet' ),
					$this->get_current_feed_id(),
					rgget( 'id' ),
					$api_mode
				);

				$html = '<p class="connected_to_terawallet_text">';
				if ( ! $this->is_detail_page() && ! empty( $display_name ) ) {
					$html .= esc_html__( 'Connected to terawallet as', 'gravityformsterawallet' ) . ' <strong>' . $display_name . '</strong>.';
				}

				$html .= '&nbsp;&nbsp;' . $deauth_button;
				$html .= '</p>';

				// Display the deauth options.
				$html .= '<div class="alert_red deauth_scope" style="margin-top: 10px;padding:20px; padding-top:5px;">';
				$html .= '<p><label for="' . $api_mode . '_deauth_scope0"><input type="radio" name="deauth_scope" value="site" id="' . $api_mode . '_deauth_scope0" checked="checked">';

				$target = $this->is_detail_page() ? 'feed' : 'site';

				// translators: placeholder represents contextual target, either "feed" or "site".
				$html .= sprintf( esc_html__( 'Disconnect this %s only', 'gravityformsterawallet' ), $target );
				$html .= '</label></p>';
				$html .= '<p><label for="' . $api_mode . '_deauth_scope1"><input type="radio" name="deauth_scope" value="account" id="' . $api_mode . '_deauth_scope1">' . esc_html__( 'Disconnect all Gravity Forms sites connected to this terawallet account', 'gravityformsterawallet' ) . '</label></p>';
				$html .= $deauth_button;
				$html .= '</div>';
			} else {
				$html = $connect_button;
			}
		} else {
			$html = $connect_button;
		}

		// Lock account settings when it's a post back from changing transaction type.
		if ( ( rgpost( $this->_input_prefix . '_transactionType' ) && ! $this->is_save_postback() ) ) {
			$html .= '<script>';
			$html .= 'jQuery(document).ready(function(){
						GFterawalletAdmin.accountSettingsLocked = true;});';
			$html .= '</script>';
		}

		if ( $echo ) {
			echo $html;
		}

		return $html;
	}

	/**
	 * Decides if authentication button can be displayed or not.
	 *
	 * @since 3.8
	 * @since 3.9 - $settings parameter no longer needed.
	 *
	 * @return bool
	 */
	private function can_display_connect_button() {
		return ( $this->is_detail_page() || $this->is_terawallet_connect_enabled() );
	}

	/**
	 * Retrieves the plugin settings or feed settings depending on current screen.
	 *
	 * @since 3.8
	 *
	 * @return array
	 */
	public function get_settings() {
		if ( $this->is_detail_page() ) {
			$feed = $this->get_current_feed();
		}

		return ! empty( $feed['meta'] ) ? $feed['meta'] : $this->get_plugin_settings();
	}

	/**
	 * Generates plugin settings page URL or feed details page URL depending on current screen.
	 *
	 * @since 3.8
	 *
	 * @return string
	 */
	public function get_settings_page_url() {
		if ( ! $this->is_detail_page() ) {
			return admin_url( 'admin.php?page=gf_settings&subview=' . $this->get_slug(), 'https' );
		}

		return add_query_arg(
			array(
				'page'    => 'gf_edit_forms',
				'view'    => 'settings',
				'subview' => $this->get_slug(),
				'id'      => rgget( 'id' ),
				'fid'     => $this->get_current_feed_id(),
			),
			admin_url( 'admin.php', 'https' )
		);
	}

	/**
	 * Display the Connected To feed setting.
	 *
	 * @since 2.8
	 */
	public function settings_connected_to() {
		$html = '';

		if ( ! $this->is_feed_terawallet_connect_enabled() ) {
			$settings = $this->get_plugin_settings();
			$api_mode = $this->get_api_mode( $settings );
		} else {
			$feed     = $this->get_current_feed();
			$settings = $feed['meta'];
			$api_mode = $this->get_api_mode( $settings, $this->get_current_feed_id() );
		}

		$is_valid = $this->is_terawallet_auth_valid( $settings, $api_mode );

		$html .= '<p style="margin-top: -3px;">';
		if ( $is_valid ) {
			$display_name = $this->get_terawallet_display_name( $is_valid );

			/* Translators: 1: Open strong tag 2: Account name 3: Close account tag */
			if ( ! empty( $display_name ) ) {
				$html .= sprintf(
					esc_html__( '%1$s%2$s%3$s', 'gravityformsterawallet' ),
					'<strong>',
					$display_name,
					'</strong>'
				);

				$html .= ' &nbsp;&nbsp;';
			}
		}

		$classes = $this->_has_settings_renderer ? 'alert gforms_note_error' : 'alert_red';
		$html   .= $this->maybe_display_update_authentication_message( true, $classes );
		if ( ! $this->requires_reauthentication() ) {
			$disabled  = $this->get_current_feed_id() == 0 ? 'data-disabled="1"' : '';
			$html     .= '<a class="button" id="gform_terawallet_change_account" ' . $disabled . ' href="javascript:void(0);">';
			$html     .= esc_html__( 'Switch Accounts', 'gravityformsterawallet' );
			$html     .= '</a>';
		}

		$html .= '</p>';

		echo $html;
	}

	/**
	 * terawallet Checkout Logo Settings
	 *
	 * @since 3.0
	 */
	public function settings_terawallet_checkout_logo() {
		/* Translators: 1. Open link tag. 2. Close link tag. */
		$html = sprintf( esc_html__( 'Logo can be configured on %1$sterawallet\'s branding page%2$s.', 'gravityformsterawallet' ), '<a href="https://dashboard.terawallet.com/account/branding" target="_blank">', '</a>' );

		echo $html;
	}

	/**
	 * Store auth tokens when we get auth payload from terawallet Connect.
	 *
	 * @since 2.8
	 * @since 3.5.1 Added support for the state param.
	 */
	public function maybe_update_auth_tokens() {
		if ( rgget( 'subview' ) !== $this->get_slug() ) {
			return;
		}

		// If access token is provided, save it.
		if ( rgpost( 'auth_payload' ) ) {
			$auth_payload = json_decode( base64_decode( rgpost( 'auth_payload' ) ), true );

			// If state does not match, do not save.
			if ( ! wp_verify_nonce( rgpost( 'state' ), $this->get_authentication_state_action() ) ) {

				// Add error message.
				GFCommon::add_error_message( esc_html__( 'Unable to connect to terawallet due to mismatched state.', 'gravityformsterawallet' ) );

				return;

			}

			// Get access token.
			$access_token = rgar( $auth_payload, 'access_token' );

			$is_feed_settings = $this->is_detail_page() ? true : false;

			if ( $is_feed_settings ) {
				$settings = $this->get_current_feed();
				$settings = $settings['meta'];
			} else {
				$settings = (array) $this->get_plugin_settings();
			}

			$settings['api_mode'] = ( rgar( $auth_payload, 'livemode' ) === true ) ? 'live' : 'test';
			$auth_token           = $this->get_auth_token( $settings, $settings['api_mode'] );

			if ( empty( $auth_token ) || rgar( $settings, $settings['api_mode'] . '_secret_key' ) !== $access_token ) {
				// Add auth info to plugin settings.
				$settings[ $settings['api_mode'] . '_auth_token' ]               = array(
					'terawallet_user_id' => rgar( $auth_payload, 'terawallet_user_id' ),
					'refresh_token'  => rgar( $auth_payload, 'refresh_token' ),
					'date_created'   => time(),
				);
				$settings[ $settings['api_mode'] . '_secret_key' ]               = $access_token;
				$settings[ $settings['api_mode'] . '_secret_key_is_valid' ]      = '1';
				$settings[ $settings['api_mode'] . '_publishable_key' ]          = rgar( $auth_payload, 'terawallet_publishable_key' );
				$settings[ $settings['api_mode'] . '_publishable_key_is_valid' ] = '1';

				// Save settings.
				if ( $is_feed_settings ) {
					$this->save_feed_settings( $this->get_current_feed_id(), rgget( 'id' ), $settings );
				} else {
					$this->update_plugin_settings( $settings );
				}

				// Reload page to load saved settings.
				wp_redirect( $this->get_settings_page_url() );
				exit();
			}
		}

		// If error is provided, display message.
		if ( rgpost( 'auth_error' ) ) {
			// Add error message.
			GFCommon::add_error_message( esc_html__( 'Unable to authenticate with terawallet.', 'gravityformsterawallet' ) );
		}
	}

	/**
	 * Add auth_token data when updating plugin settings.
	 *
	 * @since 2.8
	 *
	 * @param array $settings Plugin settings to be saved.
	 */
	public function update_plugin_settings( $settings ) {
		$modes = array( 'live', 'test' );
		foreach ( $modes as $mode ) {
			if ( rgempty( "{$mode}_auth_token", $settings ) ) {
				$auth_token = $this->get_plugin_setting( "{$mode}_auth_token" );

				if ( ! empty( $auth_token ) ) {
					$settings[ "{$mode}_auth_token" ] = $auth_token;
				}
			}
		}

		parent::update_plugin_settings( $settings );
	}

	/**
	 * Add auth_token data when updating feed settings.
	 *
	 * @since 2.8
	 *
	 * @param int   $feed_id Feed ID.
	 * @param int   $form_id Form ID.
	 * @param array $settings Feed settings.
	 *
	 * @return int
	 */
	public function save_feed_settings( $feed_id, $form_id, $settings ) {
		$feed = $this->get_feed( $feed_id );
		if ( rgar( $feed, 'meta' ) ) {
			$modes = array( 'live', 'test' );
			foreach ( $modes as $mode ) {
				if ( rgempty( "{$mode}_auth_token", $settings ) ) {
					$auth_token = $this->get_auth_token( $feed['meta'], $mode );

					if ( ! empty( $auth_token ) ) {
						$settings[ "{$mode}_auth_token" ] = $auth_token;
					}
				}
			}
		}

		return parent::save_feed_settings( $feed_id, $form_id, $settings );
	}

	/**
	 * Add update authentication message.
	 *
	 * @since 2.8
	 */
	public function maybe_display_update_authentication_message( $return = false, $classes = 'notice notice-error' ) {

		if ( $this->requires_reauthentication() ) {
			$message = sprintf(
				/* Translators: 1: Open link tag 2: Close link tag */
				esc_html__( 'You are currently logged in to terawallet using a deprecated authentication method. %1$sRe-authenticate your terawallet account%2$s.', 'gravityformsterawallet' ),
				'<a href="' . admin_url( 'admin.php?page=gf_settings&subview=' . $this->_slug ) . '">',
				'</a>'
			);

			$message = sprintf( '<div class="%s"><p>%s</p></div>', $classes, $message );

			if ( $return ) {
				return $message;
			} else {
				echo $message;
			}
		}
	}

	/**
	 * Adds a warning message if any form has an active terawallet feed and is still using the deprecated credit card field.
	 *
	 * @since 3.9
	 */
	public function maybe_display_deprecated_cc_field_warning() {

		// If form is being saved, postpone check after it is saved.
		if ( $this->is_form_editor() && isset( $_POST['gform_meta'] ) ) {
			add_action( 'gform_after_save_form', array( $this, 'maybe_display_saved_form_deprecated_cc_field_warning' ), 10, 2 );
			return;
		}

		$form_id = filter_input( INPUT_GET, 'id', FILTER_VALIDATE_INT );
		// Check if a warning should be displayed for current form.
		if ( ! empty( $form_id ) && GFForms::is_gravity_page() ) {
			$form = GFAPI::get_form( $form_id );
			if ( $form && $this->has_feed( $form['id'] ) && $this->form_has_deprecated_cc_field( $form ) ) {
				$this->display_deprecated_cc_field_warning( $form );

				return;
			}
		}

		// Check if a warning should be displayed for any other forms.
		$form = $this->get_form_with_deprecated_cc_field();
		if ( false === $form ) {
			return;
		}

		$this->display_deprecated_cc_field_warning( $form );

	}

	/**
	 * Checks if the deprecated cc field warning should still be displayed after form has been saved.
	 *
	 * @since 3.9
	 *
	 * @param array $saved_form The current form being saved.
	 */
	public function maybe_display_saved_form_deprecated_cc_field_warning( $saved_form, $is_new ) {
		if ( ! $is_new && $this->has_feed( $saved_form['id'] ) && $this->form_has_deprecated_cc_field( $saved_form ) ) {
			$this->display_deprecated_cc_field_warning( $saved_form );
		}
	}

	/**
	 * Displays deprecated cc field warning message for a form.
	 *
	 * @since 3.9
	 *
	 * @param array $form Current form array being processed.
	 */
	private function display_deprecated_cc_field_warning( $form ) {
		$message = sprintf(
			/* translators: 1: Open strong tag, 2: Close strong tag, 3: Form title, 4: Open link tag, 5: Close link tag. */
			esc_html__( '%1$sImportant%2$s: The form %3$s is using a deprecated payment collection method for terawallet that will stop working in a future version. Take action now to continue collecting payments. %4$sLearn more.%5$s', 'gravityformsterawallet' ),
			'<strong>',
			'</strong>',
			'<a href="admin.php?page=gf_edit_forms&id=' . intval( $form['id'] ) . '">' . esc_html( $form['title'] ) . '</a>',
			'<a target="_blank" href="https://docs.gravityforms.com/deprecation-of-the-gravity-forms-credit-card-field/">',
			'</a>'
		);

		echo sprintf( '<div class="notice notice-error gf-notice"><p>%s</p></div>', $message );
	}

	/**
	 * Gets the first form that has active feeds and is still using the deprecated credit card field.
	 *
	 * @since 3.9
	 *
	 * @return array|bool Form array or false if no forms founds.
	 */
	private function get_form_with_deprecated_cc_field() {

		$feeds = $this->get_active_feeds();

		foreach ( $feeds as $feed ) {
			$form = GFAPI::get_form( $feed['form_id'] );
			if ( $this->form_has_deprecated_cc_field( $form ) ) {
				return $form;
			}
		}

		return false;
	}

	/**
	 * Checks if provided form is still depending only on the deprecated cc field.
	 *
	 * @since 3.9
	 *
	 * @param array $form Current form being processed.
	 *
	 * @return bool
	 */
	private function form_has_deprecated_cc_field( $form ) {
		return $this->has_credit_card_field( $form ) && ! $this->has_terawallet_card_field( $form );
	}

	/**
	 * Decides whether or not the notice for deprecated authentication message should be displayed
	 *
	 * @since 2.8
	 * @since 3.3 Fix how we define $is_valid.
	 *
	 * @return bool Returns true if the re-authentication message/notice should be displayed. Returns false otherwise
	 */
	public function requires_reauthentication() {

		$settings   = $this->get_plugin_settings();
		$api_mode   = $this->get_api_mode( $settings );
		$auth_token = $this->get_auth_token( $settings, $api_mode );
		$is_valid   = rgar( $settings, "{$api_mode}_publishable_key_is_valid" ) && rgar( $settings, "{$api_mode}_secret_key_is_valid" );

		return $is_valid && empty( $auth_token ) && $this->is_terawallet_connect_enabled();
	}


	// # FEED SETTINGS -------------------------------------------------------------------------------------------------

	/**
	 * Remove the add new button from the title.
	 *
	 * @since 2.6
	 * @since 3.4 Allow add new feed only for: 1) has CC field + current feeds; 2) has terawallet Card; 3) use terawallet Checkout.
	 *
	 * @return string
	 */
	public function feed_list_title() {
		if ( ! $this->can_create_feed() ) {
			return $this->form_settings_title();
		}

		return GFFeedAddOn::feed_list_title();
	}

	/**
	 * Get the require credit card message.
	 *
	 * @since 2.6
	 * @since 3.4 Allow add new feed only for: 1) has CC field + current feeds; 2) has terawallet Card; 3) use terawallet Checkout.
	 *
	 * @return false|string
	 */
	public function feed_list_message() {
		$checkout_method = $this->get_plugin_setting( 'checkout_method' );
		if ( ! $this->can_create_feed() && $checkout_method === 'terawallet_elements' && ! $this->has_terawallet_card_field() ) {
			return $this->requires_terawallet_card_message();
		}

		return GFFeedAddOn::feed_list_message();
	}

	/**
	 * Display the requiring terawallet Card field message.
	 *
	 * @since 2.6
	 *
	 * @return string
	 */
	public function requires_terawallet_card_message() {
		$url = add_query_arg( array( 'view' => null, 'subview' => null ) );

		return sprintf( esc_html__( "You must add a terawallet Card field to your form before creating a feed. Let's go %sadd one%s!", 'gravityformsterawallet' ), "<a href='" . esc_url( $url ) . "'>", '</a>' );
	}

	/**
	 * Configures the settings which should be rendered on the feed edit page.
	 *
	 * @since  Unknown
	 * @access public
	 *
	 * @uses GFPaymentAddOn::feed_settings_fields()
	 * @uses GFAddOn::replace_field()
	 * @uses GFAddOn::get_setting()
	 * @uses GFAddOn::add_field_after()
	 * @uses GFAddOn::remove_field()
	 * @uses GFAddOn::add_field_before()
	 *
	 * @return array The feed settings.
	 */
	public function feed_settings_fields() {

		// Get default payment feed settings fields.
		$default_settings = parent::feed_settings_fields();


		// Prepare customer information fields.
		$customer_info_field = array(
			'name'       => 'customerInformation',
			'label'      => esc_html__( 'Customer Information', 'gravityformsterawallet' ),
			'type'       => 'field_map',
			'dependency' => array(
				'field'  => 'transactionType',
				'values' => array( 'subscription' ),
			),
			'field_map'  => array(
				array(
					'name'       => 'email',
					'label'      => esc_html__( 'Email', 'gravityformsterawallet' ),
					'required'   => true,
					'field_type' => array( 'email', 'hidden' ),
					'tooltip'    => '<h6>' . esc_html__( 'Email', 'gravityformsterawallet' ) . '</h6>' . esc_html__( 'You can specify an email field and it will be sent to the terawallet Checkout screen as the customer\'s email.', 'gravityformsterawallet' ),
				),
				array(
					'name'     => 'description',
					'label'    => esc_html__( 'Description', 'gravityformsterawallet' ),
					'required' => false,
				),
				array(
					'name'       => 'coupon',
					'label'      => esc_html__( 'Coupon', 'gravityformsterawallet' ),
					'required'   => false,
					'field_type' => array( 'coupon', 'text' ),
					'tooltip'    => '<h6>' . esc_html__( 'Coupon', 'gravityformsterawallet' ) . '</h6><p>' . esc_html__( 'Select which field contains the coupon code to be applied to the recurring charge(s). The coupon must also exist in your terawallet Dashboard.', 'gravityformsterawallet' ) . '</p><p>' . esc_html__( 'If you use terawallet Checkout, the coupon won\'t be applied to your first invoice.', 'gravityformsterawallet' ) . '</p>',
				),
			),
		);

		// Replace default billing information fields with customer information fields.
		$default_settings = $this->replace_field( 'billingInformation', $customer_info_field, $default_settings );

		// Define end of Metadata tooltip based on transaction type.
		if ( 'subscription' === $this->get_setting( 'transactionType' ) ) {
			$info = esc_html__( 'You will see this data when viewing a customer page.', 'gravityformsterawallet' );
		} else {
			$info = esc_html__( 'You will see this data when viewing a payment page.', 'gravityformsterawallet' );
		}

		// Prepare meta data field.
		$custom_meta = array(
			array(
				'name'                => 'metaData',
				'label'               => esc_html__( 'Metadata', 'gravityformsterawallet' ),
				'type'                => 'dynamic_field_map',
				'limit'               => 50,
				'exclude_field_types' => array( 'creditcard', 'terawallet_creditcard' ),
				'tooltip'             => '<h6>' . esc_html__( 'Metadata', 'gravityformsterawallet' ) . '</h6>' . esc_html__( 'You may send custom meta information to terawallet. A maximum of 50 custom keys may be sent. The key name must be 40 characters or less, and the mapped data will be truncated to 500 characters per requirements by terawallet. ' . $info, 'gravityformsterawallet' ),
				'validation_callback' => array( $this, 'validate_custom_meta' ),
			),
		);

		// Add meta data field.
		$default_settings = $this->add_field_after( 'customerInformation', $custom_meta, $default_settings );

		// Remove subscription recurring times setting due to lack of terawallet support.
		$default_settings = $this->remove_field( 'recurringTimes', $default_settings );

		// Set trial field to be visibile by default, setup fee and trial period can coexist in terawallet.
		$trial_field = array(
			'name'	 => 'trial',
			'label'  => esc_html__( 'Trial', 'gravityformsterawallet' ),
			'type'   => 'trial',
			'hidden' => false,
			'tooltip' => '<h6>' . esc_html__( 'Trial Period', 'gravityforms' ) . '</h6>' . esc_html__( 'Enable a trial period.  The user\'s recurring payment will not begin until after this trial period.', 'gravityformsterawallet' )
		);
		$default_settings = $this->replace_field( 'trial', $trial_field, $default_settings );

		// Prepare trial period field.
		$trial_period_field = array(
			'name'                => 'trialPeriod',
			'label'               => esc_html__( 'Trial Period', 'gravityformsterawallet' ),
			'type'                => 'trial_period',
			'validation_callback' => array( $this, 'validate_trial_period' ),
		);

		if ( $this->_has_settings_renderer ) {
			$trial_period_field[ 'append' ] = esc_html__( 'days', 'gravityformsterawallet' );
		} else {
			$trial_period_field[ 'style' ]       = 'width:40px;text-align:center;';
			$trial_period_field[ 'after_input' ] = '&nbsp;' . esc_html__( 'days', 'gravityformsterawallet' );
		}
		// Add trial period field.
		$default_settings = $this->add_field_after( 'trial', $trial_period_field, $default_settings );

		// Add subscription name field.
		$subscription_name_field = array(
			'name'    => 'subscription_name',
			'label'   => esc_html__( 'Subscription Name', 'gravityformsterawallet' ),
			'type'    => 'text',
			'class'   => 'medium merge-tag-support mt-hide_all_fields mt-position-right mt-exclude-creditcard-terawallet_creditcard',
			'tooltip' => '<h6>' . esc_html__( 'Subscription Name', 'gravityformsterawallet' ) . '</h6>' . esc_html__( 'Enter a name for the subscription. It will be displayed on the payment form as well as the terawallet dashboard.', 'gravityformsterawallet' ),
		);
		$default_settings        = $this->add_field_before( 'recurringAmount', $subscription_name_field, $default_settings );

		// Get the other settings section index.
		$section_index  = count( $default_settings ) - 1 ;
		$other_settings = $default_settings[ $section_index ];

		if ( $this->has_terawallet_card_field() ) {
			$default_settings[ $section_index ] = array(
				'title'      => esc_html__( 'terawallet Credit Card Field Settings', 'gravityformsterawallet' ),
				'dependency' => array(
					'field'  => 'transactionType',
					'values' => array( 'subscription', 'product', 'donation' ),
				),
				'fields'     => array(
					array(
						'name'      => 'billingInformation',
						'label'     => esc_html__( 'Billing Information', 'gravityformsterawallet' ),
						'tooltip'   => '<h6>' . esc_html__( 'Billing Information', 'gravityformsterawallet' ) . '</h6>' . esc_html__( 'Map your Form Fields to the available listed fields. The address information will be sent to terawallet.', 'gravityformsterawallet' ),
						'type'      => 'field_map',
						'field_map' => $this->billing_info_fields(),
					),
				),
			);

			// Put the other settings section back.
			$default_settings[] = $other_settings;

		} elseif ( $this->is_terawallet_checkout_enabled() ) {
			$default_settings[ $section_index ] = array(
				'title'       => esc_html__( 'terawallet Payment Form Settings', 'gravityformsterawallet' ),
				'description' => esc_html__( 'The following settings control information displayed on the terawallet hosted payment page that is displayed after the form is submitted.', 'gravityformsterawallet' ),
				'dependency'  => array(
					'field'  => 'transactionType',
					'values' => array( 'subscription', 'product', 'donation' ),
				),
				'fields'      => $this->get_terawallet_payment_form_settings(),
			);

			// Put the other settings section back.
			$default_settings[] = $other_settings;
		}

		// Add receipt field if the feed transaction type is a product.
		if ( 'product' === $this->get_setting( 'transactionType' ) ) {

			$receipt_settings = array(
				'name'    => 'receipt',
				'label'   => esc_html__( 'terawallet Receipt', 'gravityformsterawallet' ),
				'type'    => 'receipt',
				'tooltip' => '<h6>' . esc_html__( 'terawallet Receipt', 'gravityformsterawallet' ) . '</h6>' . esc_html__( 'terawallet can send a receipt via email upon payment. Select an email field to enable this feature.', 'gravityformsterawallet' ),
			);

			$default_settings = $this->add_field_before( 'conditionalLogic', $receipt_settings, $default_settings );

		}

		if ( $this->is_terawallet_connect_enabled() ) {
			// Add terawallet Account section if terawallet connect is enabled.
			$default_settings[] = array(
				'title'      => esc_html__( 'terawallet Account', 'gravityformsterawallet' ),
				'fields'     => $this->api_settings_fields(),
				'dependency' => array(
					'field'  => 'transactionType',
					'values' => array( 'subscription', 'product', 'donation' ),
				),
			);
		}

		return $default_settings;

	}

	/**
	 * Checkout setting fields in the feed settings.
	 *
	 * @since 3.0
	 *
	 * @return array
	 */
	public function get_terawallet_payment_form_settings() {
		$settings = array(
			array(
				'name'  => 'logo',
				'label' => esc_html__( 'Logo', 'gravityformsterawallet' ),
				'type'  => 'terawallet_checkout_logo',
			),
			array(
				'name'       => 'customer_email',
				'label'      => esc_html__( 'Customer Email', 'gravityformsterawallet' ),
				'type'       => 'receipt',
				'dependency' => array(
					'field'  => 'transactionType',
					'values' => array( 'product' ),
				),
				'tooltip'    => '<h6>' . esc_html__( 'Customer Email', 'gravityformsterawallet' ) . '</h6>' . esc_html__( 'You can specify an email field and it will be sent to the terawallet Checkout screen as the customer\'s email.', 'gravityformsterawallet' ),
			),
			array(
				'name'          => 'billingAddress',
				'label'         => esc_html__( 'Billing Address', 'gravityformsterawallet' ),
				'type'          => 'radio',
				'tooltip'       => '<h6>' . esc_html__( 'Billing Address', 'gravityformsterawallet' ) . '</h6>' . esc_html__( 'When enabled, terawallet Checkout will collect the customer\'s billing address for you.', 'gravityformsterawallet' ),
				'horizontal'    => true,
				'choices'       => array(
					array(
						'label' => esc_html__( 'Enabled', 'gravityformsterawallet' ),
						'value' => 1,
					),
					array(
						'label' => esc_html__( 'Disabled', 'gravityformsterawallet' ),
						'value' => 0,
					),
				),
				'default_value' => 0,
			),
		);

		return $settings;
	}

	/**
	 * Prevent feeds being listed or created if the API keys aren't valid.
	 *
	 * @since  Unknown
	 * @since  3.4     Allow add new feed only for: 1) has CC field + current feeds; 2) has terawallet Card; 3) use terawallet Checkout.
	 * @access public
	 *
	 * @used-by GFFeedAddOn::feed_edit_page()
	 * @used-by GFFeedAddOn::feed_list_message()
	 * @used-by GFFeedAddOn::feed_list_title()
	 * @uses    GFAddOn::get_plugin_settings()
	 * @uses    GFterawallet::get_api_mode()
	 *
	 * @return bool True if feed creation is allowed. False otherwise.
	 */
	public function can_create_feed() {
		return $this->is_terawallet_account_set() && $this->feed_allowed_for_current_form();
	}

	/**
	 * Checks if current form being processed ready to have a terawallet feed.
	 *
	 * @since 3.8
	 *
	 * @return bool
	 */
	private function feed_allowed_for_current_form(){
		$checkout_method = $this->get_plugin_setting( 'checkout_method' );
		$form            = $this->get_current_form();
		$feeds           = $this->get_feeds_by_slug( $this->_slug, $form['id'] );
		// If terawallet checkout is not used, form must have a terawallet elements field, or a credit card field and at least one feed that has already been created before.
		return (
			$checkout_method === 'terawallet_checkout'
			|| $this->has_terawallet_card_field( $form )
			|| ( $this->has_credit_card_field( $form ) && $feeds )
		);
	}

	/**
	 * Checks if terawallet account is authenticated and valid.
	 *
	 * @since 3.8
	 *
	 * @return bool
	 */
	private function is_terawallet_account_set(){
		$settings    = $this->get_plugin_settings();
		$api_mode    = $this->get_api_mode( $settings );

		return (
			rgar( $settings, "{$api_mode}_publishable_key_is_valid" )
			&& rgar( $settings, "{$api_mode}_secret_key_is_valid" )
			&& $this->is_webhook_enabled()
		);
	}

	/**
	 * Enable feed duplication on feed list page and during form duplication.
	 *
	 * @since  Unknown
	 * @access public
	 *
	 * @param int|array $id The ID of the feed to be duplicated or the feed object when duplicating a form.
	 *
	 * @return false
	 */
	public function can_duplicate_feed( $id ) {

		return false;

	}

	/**
	 * Define the markup for the field_map setting table header.
	 *
	 * @since  Unknown
	 * @access public
	 *
	 * @return string The header HTML markup.
	 */
	public function field_map_table_header() {
		return '<thead>
					<tr>
						<th></th>
						<th></th>
					</tr>
				</thead>';
	}

	/**
	 * Define the markup for the receipt type field.
	 *
	 * @since  Unknown
	 * @since  3.0     Changed to support customer email field in terawallet Payment form settings.
	 * @access public
	 *
	 * @uses GFAddOn::get_form_fields_as_choices()
	 * @uses GFAddOn::get_current_form()
	 * @uses GFAddOn::settings_select()
	 *
	 * @param array     $field The field properties. Not used.
	 * @param bool|true $echo  Should the setting markup be echoed. Defaults to true.
	 *
	 * @return string|void The HTML markup if $echo is set to false. Void otherwise.
	 */
	public function settings_receipt( $field, $echo = true ) {
		$input_types = array( 'email', 'hidden' );

		if ( $field['name'] === 'receipt' ) {
			// Prepare first field choice and get form fields as choices.
			$first_choice = array(
				'label' => esc_html__( 'Do not send receipt', 'gravityformsterawallet' ),
				'value' => '',
			);
		} elseif ( $field['name'] === 'customer_email' ) {
			// Prepare first field choice and get form fields as choices.
			$first_choice = array(
				'label' => esc_html__( 'Do not set customer email', 'gravityformsterawallet' ),
				'value' => '',
			);

			$input_types = array( 'email' );
		}

		$fields = $this->get_form_fields_as_choices(
			$this->get_current_form(),
			array(
				'input_types' => $input_types,
			)
		);

		// Add first choice to the beginning of the fields array.
		array_unshift( $fields, $first_choice );

		// Prepare select field settings.
		$select = array(
			'name'    => $field['name'] . '_field',
			'choices' => $fields,
		);

		// Get select markup.
		$html = $this->settings_select( $select, false );

		// Echo setting markup, if enabled.
		if ( $echo ) {
			echo $html;
		}

		return $html;

	}

	/**
	 * Define the markup for the setup_fee type field.
	 *
	 * @since  Unknown
	 * @access public
	 *
	 * @uses GFPaymentAddOn::get_payment_choices()
	 * @uses GFAddOn::settings_checkbox()
	 * @uses GFAddOn::get_current_form()
	 * @uses GFAddOn::get_setting()
	 * @uses GFAddOn::settings_select()
	 *
	 * @param array     $field The field properties.
	 * @param bool|true $echo  Should the setting markup be echoed. Defaults to true.
	 *
	 * @return string|void The HTML markup if $echo is set to false. Void otherwise.
	 */
	public function settings_setup_fee( $field, $echo = true ) {

		// Prepare checkbox field settings.
		$enabled_field = array(
			'name'       => $field['name'] . '_checkbox',
			'type'       => 'checkbox',
			'horizontal' => true,
			'choices'    => array(
				array(
					'label'    => esc_html__( 'Enabled', 'gravityformsterawallet' ),
					'name'     => $field['name'] . '_enabled',
					'value'    => '1',
					'onchange' => "if(jQuery(this).prop('checked')){
						jQuery('#{$field['name']}_product').show();
					} else {
						jQuery('#{$field['name']}_product').hide();
					}",
				),
			),
		);

		// Get checkbox field markup.
		$html = $this->settings_checkbox( $enabled_field, false );

		// Get current form.
		$form = $this->get_current_form();

		// Get enabled state.
		$is_enabled = $this->get_setting( "{$field['name']}_enabled" );

		// Prepare setup fee select field settings.
		$product_field = array(
			'name'    => $field['name'] . '_product',
			'type'    => 'select',
			'class'   => $is_enabled ? '' : 'hidden',
			'choices' => $this->get_payment_choices( $form ),
		);

		// Add select field markup to checkbox field markup.
		$html .= '&nbsp' . $this->settings_select( $product_field, false );

		// Echo setting markup, if enabled.
		if ( $echo ) {
			echo $html;
		}

		return $html;

	}

	/**
	 * Define the markup for the trial type field.
	 *
	 * @since  Unknown
	 * @access public
	 *
	 * @uses GFAddOn::settings_checkbox()
	 *
	 * @param array     $field The field properties.
	 * @param bool|true $echo Should the setting markup be echoed. Defaults to true.
	 *
	 * @return string|void The HTML markup if $echo is set to false. Void otherwise.
	 */
	public function settings_trial( $field, $echo = true ) {

		// Prepare enabled field settings.
		$enabled_field = array(
			'name'       => $field['name'] . '_checkbox',
			'type'       => 'checkbox',
			'horizontal' => true,
			'choices'    => array(
				array(
					'label'    => esc_html__( 'Enabled', 'gravityformsterawallet' ),
					'name'     => $field['name'] . '_enabled',
					'value'    => '1',
					'onchange' => "if(jQuery(this).prop('checked')){
						jQuery('#{$this->_input_container_prefix}trialPeriod').show();
					} else {
						jQuery('#{$this->_input_container_prefix}trialPeriod').hide();
						jQuery('#trialPeriod').val( '' );
					}",
				),
			),
		);

		// Get checkbox markup.
		$html = $this->settings_checkbox( $enabled_field, false );

		// Echo setting markup, if enabled.
		if ( $echo ) {
			echo $html;
		}

		return $html;
	}

	/**
	 * Define the markup for the trial_period type field.
	 *
	 * @since  Unknown
	 * @access public
	 *
	 * @uses GFAddOn::settings_text()
	 * @uses GFAddOn::field_failed_validation()
	 * @uses GFAddOn::get_error_icon()
	 *
	 * @param array     $field The field properties.
	 * @param bool|true $echo  Should the setting markup be echoed. Defaults to true.
	 *
	 * @return string|void The HTML markup if $echo is set to false. Void otherwise.
	 */
	public function settings_trial_period( $field, $echo = true ) {

		// Get text input markup.
		$html = $this->settings_text( $field, false );

		// Prepare validation placeholder name.
		$validation_placeholder = array( 'name' => 'trialValidationPlaceholder' );

		// Add validation indicator.
		if ( $this->field_failed_validation( $validation_placeholder ) ) {
			$html .= '&nbsp;' . $this->get_error_icon( $validation_placeholder );
		}

		// If trial is not enabled and setup fee is enabled, hide field.
		$html .= '
			<script type="text/javascript">
			if( ! jQuery( "#trial_enabled" ).is( ":checked" ) || jQuery( "#setupFee_enabled" ).is( ":checked" ) ) {
				jQuery( "#trial_enabled" ).prop( "checked", false );
				jQuery( "#' . $this->_input_container_prefix . 'trialPeriod" ).hide();
			}
			</script>';

		// Echo setting markup, if enabled.
		if ( $echo ) {
			echo $html;
		}

		return $html;

	}

	/**
	 * Validate the trial_period type field.
	 *
	 * @since  Unknown
	 * @access public
	 *
	 * @uses GFAddOn::get_posted_settings()
	 * @uses GFAddOn::set_field_error()
	 *
	 * @param array $field The field properties. Not used.
	 *
	 * @return void
	 */
	public function validate_trial_period( $field ) {

		// Get posted settings.
		$settings = $this->get_posted_settings();

		// If trial period is not numeric, set field error.
		if ( $settings['trial_enabled'] && ( empty( $settings['trialPeriod'] ) || ! ctype_digit( $settings['trialPeriod'] ) ) ) {
			$this->set_field_error( $field, esc_html__( 'Please enter a valid number of days.', 'gravityformsterawallet' ) );
		}

	}

	/**
	 * Validate the custom_meta type field.
	 *
	 * @since  Unknown
	 * @access public
	 *
	 * @uses GFAddOn::get_posted_settings()
	 * @uses GFAddOn::set_field_error()
	 *
	 * @param array $field The field properties. Not used.
	 *
	 * @return void
	 */
	public function validate_custom_meta( $field ) {

		/*
		 * Number of keys is limited to 50.
		 * Interface should control this, validating just in case.
		 * Key names have maximum length of 40 characters.
		 */

		// Get metadata from posted settings.
		if ( $this->_has_settings_renderer ) {
			$settings  = $this->get_settings_renderer()->get_posted_values();
		} else {
			$settings  = $this->get_posted_settings();
		}

		$meta_data = ! empty( $settings['metaData'] ) ? $settings['metaData'] : '' ;

		// If metadata is not defined, return.
		if ( empty( $meta_data ) ) {
			return;
		}

		// Get number of metadata items.
		$meta_count = count( $meta_data );

		// If there are more than 50 metadata keys, set field error.
		if ( $meta_count > 50 ) {
			$meta_count_error = array( esc_html__( 'You may only have 50 custom keys.' ), 'gravityformsterawallet' );
			$this->set_field_error( $meta_count_error );
			return;
		}

		$custom_meta_field = array( 'name' => 'metaData' );
		// Loop through metadata and check the key name length (custom_key).
		foreach ( $meta_data as $meta ) {
			if ( empty( $meta['custom_key'] ) && ! empty( $meta['value'] ) ) {
				$this->set_field_error( $custom_meta_field, esc_html__( "A field has been mapped to a custom key without a name. Please enter a name for the custom key, remove the metadata item, or return the corresponding drop down to 'Select a Field'.", 'gravityformsterawallet' ) );
				break;
			} else if ( strlen( $meta['custom_key'] ) > 40 ) {
				$this->set_field_error( $custom_meta_field, sprintf( esc_html__( 'The name of custom key %s is too long. Please shorten this to 40 characters or less.', 'gravityformsterawallet' ), $meta['custom_key'] ) );
				break;
			}
		}

	}

	/**
	 * Validate webhook signing secret.
	 *
	 * @since 3.0
	 *
	 * @param array  $field         The field object.
	 * @param string $field_setting The field value.
	 */
	public function validate_webhook_signing_secret( $field, $field_setting ) {

		if ( ! empty( $field_setting ) && strpos( $field_setting, 'whsec_' ) === false ) {
			$this->set_field_error( $field, esc_html__( 'Please use the correct webhook signing secret, which should start with "whsec_".', 'gravityformsterawallet' ) );
		}
	}

	/**
	 * Define the choices available in the billing cycle dropdowns.
	 *
	 * @since  Unknown
	 * @access public
	 *
	 * @used-by GFPaymentAddOn::settings_billing_cycle()
	 *
	 * @return array Billing intervals that are supported.
	 */
	public function supported_billing_intervals() {

		return array(
			'day'   => array( 'label' => esc_html__( 'day(s)', 'gravityformsterawallet' ), 'min' => 1, 'max' => 365 ),
			'week'  => array( 'label' => esc_html__( 'week(s)', 'gravityformsterawallet' ), 'min' => 1, 'max' => 12 ),
			'month' => array( 'label' => esc_html__( 'month(s)', 'gravityformsterawallet' ), 'min' => 1, 'max' => 12 ),
			'year'  => array( 'label' => esc_html__( 'year(s)', 'gravityformsterawallet' ), 'min' => 1, 'max' => 1 ),
		);

	}

	/**
	 * Prevent the 'options' checkboxes setting being included on the feed.
	 *
	 * @since  Unknown
	 * @access public
	 *
	 * @used-by GFPaymentAddOn::other_settings_fields()
	 *
	 * @return false
	 */
	public function option_choices() {
		return false;
	}



	// # FORM SETTINGS -------------------------------------------------------------------------------------------------

	/**
	 * Add supported notification events.
	 *
	 * @since  Unknown
	 * @access public
	 *
	 * @used-by GFFeedAddOn::notification_events()
	 * @uses    GFFeedAddOn::has_feed()
	 *
	 * @param array $form The form currently being processed.
	 *
	 * @return array|false The supported notification events. False if feed cannot be found within $form.
	 */
	public function supported_notification_events( $form ) {

		// If this form does not have a terawallet feed, return false.
		if ( ! $this->has_feed( $form['id'] ) ) {
			return false;
		}

		// Return terawallet notification events.
		return array(
			'complete_payment'          => esc_html__( 'Payment Completed', 'gravityformsterawallet' ),
			'refund_payment'            => esc_html__( 'Payment Refunded', 'gravityformsterawallet' ),
			'fail_payment'              => esc_html__( 'Payment Failed', 'gravityformsterawallet' ),
			'create_subscription'       => esc_html__( 'Subscription Created', 'gravityformsterawallet' ),
			'cancel_subscription'       => esc_html__( 'Subscription Canceled', 'gravityformsterawallet' ),
			'add_subscription_payment'  => esc_html__( 'Subscription Payment Added', 'gravityformsterawallet' ),
			'fail_subscription_payment' => esc_html__( 'Subscription Payment Failed', 'gravityformsterawallet' ),
		);

	}





	// # FRONTEND ------------------------------------------------------------------------------------------------------

	/**
	 * Initialize the frontend hooks.
	 *
	 * @since  2.6 Added more filters per terawallet Elements and terawallet Checkout.
	 * @since  Unknown
	 * @access public
	 *
	 * @uses GFterawallet::register_init_scripts()
	 * @uses GFterawallet::add_terawallet_inputs()
	 * @uses GFterawallet::pre_validation()
	 * @uses GFterawallet::populate_credit_card_last_four()
	 * @uses GFPaymentAddOn::init()
	 *
	 * @return void
	 */
	public function init() {

		add_filter( 'gform_register_init_scripts', array( $this, 'register_init_scripts' ), 10, 3 );
		add_filter( 'gform_field_content', array( $this, 'add_terawallet_inputs' ), 10, 5 );
		add_filter( 'gform_field_validation', array( $this, 'pre_validation' ), 10, 4 );
		add_filter( 'gform_pre_submission', array( $this, 'populate_credit_card_last_four' ) );
		add_filter( 'gform_field_css_class', array( $this, 'terawallet_card_field_css_class' ), 10, 3 );
		add_filter( 'gform_submission_values_pre_save', array( $this, 'terawallet_card_submission_value_pre_save' ), 10, 3 );

		// Supports frontend feeds.
		$this->_supports_frontend_feeds = true;

		if ( $this->get_plugin_setting( 'checkout_method' ) === 'credit_card' ) {
			$this->_requires_credit_card = true;
		}

		if ( $this->is_terawallet_checkout_enabled() ) {
			// Use priority 50 because users may hook to `gform_after_submission` for other purposes so we run it later.
			add_action( 'gform_after_submission', array( $this, 'terawallet_checkout_redirect_scripts' ), 110, 2 );
		}

		// Set UI prefixes depending on settings renderer availability.
		$this->_has_settings_renderer  = $this->is_gravityforms_supported( '2.5-beta' );
		$this->_input_prefix           = $this->_has_settings_renderer ? '_gform_setting' : '_gaddon_setting';
		$this->_input_container_prefix = $this->_has_settings_renderer ? 'gform_setting_' : 'gaddon-setting-row-';

		parent::init();

	}

	/**
	 * Register terawallet script when displaying form.
	 *
	 * @since  Unknown
	 * @access public
	 *
	 * @used-by GFterawallet::init()
	 * @uses    GFFeedAddOn::has_feed()
	 * @uses    GFPaymentAddOn::get_credit_card_field()
	 * @uses    GFterawallet::get_publishable_api_key()
	 * @uses    GFterawallet::get_card_labels()
	 * @uses    GFFormDisplay::add_init_script()
	 * @uses    GFFormDisplay::ON_PAGE_RENDER
	 *
	 * @param array $form         Form object.
	 * @param array $field_values Current field values. Not used.
	 * @param bool  $is_ajax      If form is being submitted via AJAX.
	 *
	 * @return void
	 */
	public function register_init_scripts( $form, $field_values, $is_ajax ) {

		if ( ! $this->frontend_script_callback( $form ) ) {
			return;
		}

		// Prepare terawallet Javascript arguments.
		$args = array(
			'apiKey'         => $this->get_publishable_api_key(),
			'formId'         => $form['id'],
			'isAjax'         => $is_ajax,
			'terawallet_payment' => ( $this->has_terawallet_card_field( $form ) ) ? 'elements' : 'terawallet.js',
		);

		if ( $this->is_rate_limits_enabled( $form['id'] ) ) {
			$args['cardErrorCount'] = $this->get_card_error_count();
		}

		if ( $this->has_terawallet_card_field( $form ) ) {
			$cc_field = $this->get_terawallet_card_field( $form );
		} elseif ( $this->has_credit_card_field( $form ) ) {
			$cc_field = $this->get_credit_card_field( $form );
		}

		// Starts from 2.6, CC field isn't required when terawallet Checkout enabled.
		if ( isset( $cc_field ) ) {
			$args['ccFieldId']  = $cc_field->id;
			$args['ccPage']     = $cc_field->pageNumber;
			$args['cardLabels'] = $this->get_card_labels();
		}

		// getting all terawallet feeds.
		$args['currency'] = gf_apply_filters( array( 'gform_currency_pre_save_entry', $form['id'] ), GFCommon::get_currency(), $form );
		$feeds            = $this->get_feeds_by_slug( $this->_slug, $form['id'] );
		if ( $this->has_terawallet_card_field( $form ) ) {
			// Add options when creating terawallet Elements.
			$args['cardClasses'] = apply_filters( 'gform_terawallet_elements_classes', array(), $form['id'] );
			$args['cardStyle']   = apply_filters( 'gform_terawallet_elements_style', array(), $form['id'] );
			foreach ( $feeds as $feed ) {
				if ( rgar( $feed, 'is_active' ) === '0' ) {
					continue;
				}

				$feed_settings = array(
					'feedId'          => $feed['id'],
					'type'            => rgars( $feed, 'meta/transactionType' ),
					'address_line1'   => rgars( $feed, 'meta/billingInformation_address_line1' ),
					'address_line2'   => rgars( $feed, 'meta/billingInformation_address_line2' ),
					'address_city'    => rgars( $feed, 'meta/billingInformation_address_city' ),
					'address_state'   => rgars( $feed, 'meta/billingInformation_address_state' ),
					'address_zip'     => rgars( $feed, 'meta/billingInformation_address_zip' ),
					'address_country' => rgars( $feed, 'meta/billingInformation_address_country' ),
				);

				if ( rgars( $feed, 'meta/transactionType' ) === 'product' ) {
					$feed_settings['paymentAmount'] = rgars( $feed, 'meta/paymentAmount' );
				} else {
					$feed_settings['paymentAmount'] = rgars( $feed, 'meta/recurringAmount' );
					if ( rgars( $feed, 'meta/setupFee_enabled' ) ) {
						$feed_settings['setupFee'] = rgars( $feed, 'meta/setupFee_product' );
					}
				}

				if ( $this->is_feed_terawallet_connect_enabled( $feed['id'] ) ) {
					$feed_settings['apiKey'] = $this->get_publishable_api_key( $feed['meta'] );
				}

				$args['feeds'][] = $feed_settings;
			}
		} elseif ( $this->has_credit_card_field( $form ) ) {
			foreach ( $feeds as $feed ) {
				if ( rgar( $feed, 'is_active' ) === '0' ) {
					continue;
				}

				$feed_settings = array(
					'feedId' => $feed['id'],
				);

				if ( $this->is_feed_terawallet_connect_enabled( $feed['id'] ) ) {
					$feed_settings['apiKey'] = $this->get_publishable_api_key( $feed['meta'] );
				}

				$args['feeds'][] = $feed_settings;
			}
		}

		// Initialize terawallet script.
		$args   = apply_filters( 'gform_terawallet_object', $args, $form['id'] );
		$script = 'new GFterawallet( ' . json_encode( $args, JSON_FORCE_OBJECT ) . ' );';

		// Add terawallet script to form scripts.
		GFFormDisplay::add_init_script( $form['id'], 'terawallet', GFFormDisplay::ON_PAGE_RENDER, $script );
	}

	/**
	 * Check if the form has an active terawallet feed and a credit card field.
	 *
	 * @since  Unknown
	 * @access public
	 *
	 * @used-by GFterawallet::scripts()
	 * @uses    GFFeedAddOn::has_feed()
	 *
	 * @param array $form The form currently being processed.
	 *
	 * @return bool If the script should be enqueued.
	 */
	public function frontend_script_callback( $form ) {

		// Starts from 2.6, CC field isn't required when terawallet Checkout enabled.
		return $form && $this->has_feed( $form['id'] ) && ( ( ! $this->is_terawallet_checkout_enabled() && ( $this->has_terawallet_card_field( $form ) || $this->has_credit_card_field( $form ) ) ) );

	}

	/**
	 * Check if we should display the terawallet JS.
	 *
	 * @since  2.6
	 *
	 * @param array $form The form currently being processed.
	 *
	 * @return bool If the script should be enqueued.
	 */
	public function terawallet_js_callback( $form ) {
		return $form && $this->has_feed( $form['id'] ) && $this->has_credit_card_field( $form ) && ! $this->has_terawallet_card_field( $form );
	}

	/**
	 * Check if we should enqueue terawallet JS v3.
	 *
	 * @since  3.8
	 *
	 * @param array $form The form currently being processed.
	 *
	 * @return bool If the script should be enqueued.
	 */
	public function terawallet_js_v3_callback( $form ) {
		return $form && $this->has_feed( $form['id'] ) && ! wp_script_is( 'terawallet.js' );
	}

	/**
	 * Check if we should display the terawallet Elements JS.
	 *
	 * @deprecated 3.8
	 *
	 * @since  2.6
	 *
	 * @param array $form The form currently being processed.
	 *
	 * @return bool If the script should be enqueued.
	 */
	public function terawallet_elements_callback( $form ) {
		return $form && $this->has_feed( $form['id'] ) && $this->has_terawallet_card_field( $form );
	}

	/**
	 * Check if we should display the terawallet Checkout JS.
	 *
	 * @deprecated 3.0
	 *
	 * @since  2.6
	 *
	 * @param array $form The form currently being processed.
	 *
	 * @return bool If the script should be enqueued.
	 */
	public function terawallet_checkout_callback( $form ) {
		// When a form has terawallet feeds but without any CC field, we enqueue the terawallet Checkout script.
		return $form && $this->has_feed( $form['id'] ) && ( ! $this->has_credit_card_field( $form ) && ! $this->has_terawallet_card_field( $form ) );
	}

	/**
	 * Check if the form has an active terawallet feed and terawallet Elements is enabled
	 *
	 * @since 2.6
	 * @since 3.4 Only load frontend styles if the form has a terawallet Card.
	 *
	 * @param array $form The form currently being processed.
	 *
	 * @return bool True if the style should be enqueued, false otherwise.
	 */
	public function frontend_style_callback( $form ) {
		return $form && $this->has_feed( $form['id'] ) && $this->has_terawallet_card_field( $form );
	}

	/**
	 * Add required terawallet inputs to form.
	 *
	 * @since  Unknown
	 * @access public
	 *
	 * @used-by GFterawallet::init()
	 * @uses    GFFeedAddOn::has_feed()
	 * @uses    GFterawallet::get_terawallet_js_response()
	 *
	 * @param string  $content The field content to be filtered.
	 * @param object  $field   The field that this input tag applies to.
	 * @param string  $value   The default/initial value that the field should be pre-populated with.
	 * @param integer $lead_id When executed from the entry detail screen, $lead_id will be populated with the Entry ID.
	 * @param integer $form_id The current Form ID.
	 *
	 * @return string $content HTML formatted content.
	 */
	public function add_terawallet_inputs( $content, $field, $value, $lead_id, $form_id ) {

		// If this form does not have a terawallet feed or if this is not a credit card field, return field content.
		if ( ! $this->has_feed( $form_id ) || ( $field->get_input_type() !== 'creditcard' && $field->get_input_type() !== 'terawallet_creditcard' ) ) {
			return $content;
		}

		// If a terawallet response exists, populate it to a hidden field.
		if ( $this->get_terawallet_js_response() ) {
			$content .= '<input type=\'hidden\' name=\'terawallet_response\' id=\'gf_terawallet_response\' value=\'' . rgpost( 'terawallet_response' ) . '\' />';
		}

		// If the last four credit card digits are provided by terawallet, populate it to a hidden field.
		if ( rgpost( 'terawallet_credit_card_last_four' ) ) {
			$content .= '<input type="hidden" name="terawallet_credit_card_last_four" id="gf_terawallet_credit_card_last_four" value="' . esc_attr( rgpost( 'terawallet_credit_card_last_four' ) ) . '" />';
		}

		// If the  credit card type is provided by terawallet, populate it to a hidden field.
		if ( rgpost( 'terawallet_credit_card_type' ) ) {
			$content .= '<input type="hidden" name="terawallet_credit_card_type" id="terawallet_credit_card_type" value="' . esc_attr( rgpost( 'terawallet_credit_card_type' ) ) . '" />';
		}

		if ( $field->get_input_type() === 'creditcard' && ! $this->has_terawallet_card_field( GFAPI::get_form( $form_id ) ) ) {
			// Remove name attribute from credit card field inputs for security.
			// Removes: name='input_2.1', name='input_2.2[]', name='input_2.3', name='input_2.5', where 2 is the credit card field id.
			$content = preg_replace( "/name=\'input_{$field->id}\.([135]|2\[\])\'/", '', $content );
		}

		return $content;

	}

	/**
	 * Validate the card type and prevent the field from failing required validation, terawallet.js will handle the required validation.
	 *
	 * The card field inputs are erased on submit, this will cause two issues:
	 * 1. The field will fail standard validation if marked as required.
	 * 2. The card type validation will not be performed.
	 *
	 * @since  Unknown
	 * @access public
	 *
	 * @used-by GFterawallet::init()
	 * @uses    GF_Field_CreditCard::is_card_supported()
	 * @uses    GFterawallet::get_card_slug()
	 *
	 * @param array    $result The field validation result and message.
	 * @param mixed    $value  The field input values; empty for the credit card field as they are cleared by frontend.js.
	 * @param array    $form   The Form currently being processed.
	 * @param GF_Field $field  The field currently being processed.
	 *
	 * @return array $result The results of the validation.
	 */
	public function pre_validation( $result, $value, $form, $field ) {

		// If this is a credit card field and the last four credit card digits are defined, validate.
		if ( $field->type == 'terawallet_payment' && rgpost( 'terawallet_credit_card_last_four' ) ) {

			// Get card type.
			$card_type = rgpost( 'terawallet_credit_card_type' );
			if ( ! $card_type || $card_type === 'false' ) {
				$card_type = __( 'Unknown', 'gravityformsterawallet' );
			}

			// Get card slug.
			$card_slug = $this->get_card_slug( $card_type );

			// If credit card type is not supported, mark field as invalid.
			if ( $field->type == 'terawallet_payment' && ! $field->is_card_supported( $card_slug ) ) {
				$result['is_valid'] = false;
				$result['message']  = sprintf( esc_html__( 'Card type (%s) is not supported. Please enter one of the supported credit cards.', 'gravityformsterawallet' ), $card_type );
			} else {
				$result['is_valid'] = true;
				$result['message']  = '';
			}
		} elseif ( $field->type === 'terawallet_payment' && ! $result['is_valid'] ) {
			// When a terawallet card is not on the last page, and the form is submitted by the SCA handler,
			// the field failed because the Card Holder name is wiped out. In this case we assume it's valid.
			$terawallet_response = $this->get_terawallet_js_response();
			if ( ! empty( $terawallet_response ) && substr( $terawallet_response->id, 0, 3 ) === 'pi_' && $terawallet_response->scaSuccess ) {
				$result['is_valid'] = true;
				$result['message']  = '';
			}
		}

		return $result;

	}

	/**
	 * Validate if the card type is supported.
	 *
	 * @deprecated 3.0
	 *
	 * @since 2.6.0
	 *
	 * @param array $validation_result The results of the validation.
	 *
	 * @return array $validation_result The results of the validation.
	 */
	public function card_type_validation( $validation_result ) {

		if ( rgpost( 'terawallet_credit_card_last_four' ) ) {

			// Get card type.
			$card_type = rgpost( 'terawallet_credit_card_type' );
			if ( ! $card_type || 'false' === $card_type ) {
				$card_type = __( 'Unknown', 'gravityformsterawallet' );
			}

			// Get card slug.
			$card_slug = $this->get_card_slug( $card_type );

			// Use a filter `gform_terawallet_checkout_supported_cards` to set the supported cards.
			// By default (when it's empty), allows all card types terawallet supports.
			// Possible value could be: array( 'amex', 'discover', 'mastercard', 'visa' );.
			$supported_cards = apply_filters( 'gform_terawallet_checkout_supported_cards', array() );
			if ( ! empty( $supported_cards ) && ! in_array( $card_slug, $supported_cards, true ) ) {
				$validation_result['is_valid']               = false;
				$validation_result['failed_validation_page'] = GFFormDisplay::get_max_page_number( $validation_result['form'] );

				add_filter( 'gform_validation_message', array( $this, 'card_type_validation_message' ), 10, 2 );

				$this->log_debug( __METHOD__ . '(): The gform_terawallet_checkout_supported_cards filter was used; the card type wasn\'t supported.' );

				// empty terawallet response so we can trigger terawallet Checkout modal again.
				$_POST['terawallet_response'] = '';
			}
		}

		return $validation_result;
	}

	/**
	 * Display card type validation error message.
	 *
	 * @deprecated 3.0
	 *
	 * @since 2.6.0
	 *
	 * @param string $message HTML message string.
	 * @param array  $form Form object.
	 *
	 * @return string
	 */
	public function card_type_validation_message( $message, $form ) {

		$card_type = rgpost( 'terawallet_credit_card_type' );
		if ( ! $card_type || 'false' === $card_type ) {
			$card_type = __( 'Unknown', 'gravityformsterawallet' );
		}

		$message .= "<div class='validation_error'>" . sprintf( esc_html__( 'Card type (%s) is not supported. Please enter one of the supported credit cards.', 'gravityformsterawallet' ), $card_type ) . '</div>';

		return $message;
	}

	/**
	 * Display card type validation error message.
	 *
	 * @since 2.6.1
	 * @since 3.0   Changed for display a single validation message containing the Checkout error.
	 *
	 * @return string
	 */
	public function terawallet_checkout_error_message() {
		$authorization_result = $this->authorization;

		$message = "<div class='validation_error'>" . esc_html__( 'There was a problem with your submission.', 'gravityformsterawallet' ) . ' ' . $authorization_result['error_message'] . '</div>';

		return $message;
	}

	/**
	 * Display extra authentication (like 3D secure) for terawallet Elements.
	 *
	 * @since 3.3
	 *
	 * @return string
	 */
	public function terawallet_elements_requires_action_message() {
		$authorization_result = $this->authorization;

		$message = "<div class='gform_terawallet_requires_action'>" . $authorization_result['error_message'] . '</div>';

		return $message;
	}

	// # terawallet TRANSACTIONS -------------------------------------------------------------------------------------------

	/**
	 * Initialize authorizing the transaction for the product & services type feed or return the terawallet.js error.
	 *
	 * @since  Unknown
	 * @since  2.8     Added $feed param when include terawallet API.
	 * @since  3.4     Added card error rate limits.
	 * @access public
	 *
	 * @uses GFterawallet::include_terawallet_api()
	 * @uses GFterawallet::get_terawallet_js_error()
	 * @uses GFterawallet::authorization_error()
	 * @uses GFterawallet::authorize_product()
	 *
	 * @param array $feed            The feed object currently being processed.
	 * @param array $submission_data The customer and transaction data.
	 * @param array $form            The form object currently being processed.
	 * @param array $entry           The entry object currently being processed.
	 *
	 * @return array Authorization and transaction ID if authorized. Otherwise, exception.
	 */
	public function authorize( $feed, $submission_data, $form, $entry ) {
		// Check if the current IP has hit the error rate limit.
		if ( $hit_rate_limits = $this->maybe_hit_rate_limits( $form['id'] ) ) {
			return $hit_rate_limits;
		}

		// Include terawallet API library.
		if ( $this->is_feed_terawallet_connect_enabled( $feed['id'] ) ) {
			$this->include_terawallet_api( $this->get_api_mode( $feed['meta'], $feed['id'] ), $feed['meta'] );
		} else {
			$this->include_terawallet_api();
		}

		// If there was an error when retrieving the terawallet.js token, return an authorization error.
		if ( $this->get_terawallet_js_error() ) {
			return $this->authorization_error( $this->get_terawallet_js_error() );
		}

		if ( $this->is_terawallet_checkout_enabled() ) {
			// Create checkout session.
			return $this->create_checkout_session( $feed, $submission_data, $form, $entry );
		} else {
			// Authorize product.
			return $this->authorize_product( $feed, $submission_data, $form, $entry );
		}
	}

	/**
	 * Create the terawallet charge authorization and return any authorization errors which occur.
	 *
	 * @since  Unknown
	 * @access public
	 *
	 * @used-by GFterawallet::authorize()
	 * @uses    GFterawallet::get_terawallet_js_response()
	 * @uses    GFPaymentAddOn::get_amount_export()
	 * @uses    GFterawallet::get_payment_description()
	 * @uses    GFterawallet::get_customer()
	 * @uses    GFAddOn::get_field_value()
	 * @uses    GFterawallet::get_terawallet_meta_data()
	 * @uses    GFAddOn::log_debug()
	 * @uses    GFPaymentAddOn::authorization_error()
	 *
	 * @param array $feed            The feed object currently being processed.
	 * @param array $submission_data The customer and transaction data.
	 * @param array $form            The form object currently being processed.
	 * @param array $entry           The entry object currently being processed.
	 *
	 * @return array Authorization and transaction ID if authorized. Otherwise, exception.
	 */
	public function authorize_product( $feed, $submission_data, $form, $entry ) {
		$data = array();
		// Get customer.
		$customer = $this->get_customer( '', $feed, $entry, $form );
		if ( $customer ) {
			$data['customer'] = $customer->id;
		}
		// If receipt field is defined, add receipt email address to charge meta.
		$receipt_field = rgars( $feed, 'meta/receipt_field' );
		if ( ! empty( $receipt_field ) && strtolower( $receipt_field ) !== 'do not send receipt' ) {
			$data['receipt_email'] = $this->get_field_value( $form, $entry, $receipt_field );
		}

		// check if terawallet_response has a payment id.
		// if yes, confirm the payment here.
		$terawallet_response = $this->get_terawallet_js_response();
		if ( substr( $terawallet_response->id, 0, 3 ) === 'pi_' ) {
			$result = $this->api->get_payment_intent( $terawallet_response->id );
			if ( ! is_wp_error( $result ) ) {
				// Add customer, receipt_email and metadata.
				// Change data before sending to terawallet.
				$data = $this->get_product_payment_data( $data, $feed, $submission_data, $form, $entry );
				$this->log_debug( __METHOD__ . '(): payment intent data to be updated => ' . print_r( $data, 1 ) );

				$result = $this->api->update_payment_intent( $result->id, $data );
				if ( ! is_wp_error( $result ) ) {
					if ( $result->status === 'requires_confirmation' ) {
						// Confirm first, and the status will become `requires_action` if the card requires authentication.
						$result = $this->api->confirm_payment_intent( $result );
					}
				}
			}

			if ( is_wp_error( $result ) ) {
				$this->log_error( __METHOD__ . '(): ' . $result->get_error_message() );

				return $this->authorization_error( '<span class="requires_payment_method">' . $result->get_error_message() . '</span>' );
			}

			// if status = requires_action, return validation error so we can do dynamic authentication on the front end.
			if ( $result->status === 'requires_action' ) {
				$error = $this->authorization_error( '<span class="requires_action">' . esc_html__( '3D Secure authentication is required for this payment. Following the instructions on the page to move forward.', 'gravityformsterawallet' ) . '</span>' );

				return array_merge( $error, array( 'requires_action' => true ) );
			} elseif ( $result->status === 'requires_payment_method' ) {
				return $this->authorization_error( '<span class="requires_payment_method">' . esc_html__( 'Your payment attempt has failed. Please enter your card details and try again.', 'gravityformsterawallet' ) . '</span>' );
			} elseif ( $result->status === 'canceled' ) {
				return $this->authorization_error( esc_html__( 'The payment has been canceled', 'gravityformsterawallet' ) );
			} else {
				return array(
					'is_authorized'  => true,
					'transaction_id' => $terawallet_response->id,
				);
			}
		}

		// Prepare terawallet charge meta.
		$charge_meta = array(
			'amount'      => $this->get_amount_export( $submission_data['payment_amount'], rgar( $entry, 'currency' ) ),
			'currency'    => rgar( $entry, 'currency' ),
			'description' => $this->get_payment_description( $entry, $submission_data, $feed ),
			'capture'     => false,
		);

		if ( $customer ) {
			// Update the customer source with the terawallet token.
			$customer->source = $terawallet_response->id;
			$this->api->save_customer( $customer );
		} else {
			// Add the terawallet token to the charge meta.
			$charge_meta['source'] = $terawallet_response->id;
		}

		// merge $data with $charge_meta.
		$charge_meta = array_merge( $charge_meta, $data );
		$charge_meta = $this->get_product_payment_data( $charge_meta, $feed, $submission_data, $form, $entry );
		// Log the charge we're about to process.
		$this->log_debug( __METHOD__ . '(): Charge meta to be created => ' . print_r( $charge_meta, 1 ) );

		// Charge customer.
		$charge = $this->api->create_charge( $charge_meta );
		if ( is_wp_error( $charge ) ) {
			$this->log_error( __METHOD__ . '(): ' . $charge->get_error_message() );

			$auth = $this->authorization_error( $charge->get_error_message() );
		} else {
			// Get authorization data from charge.
			$auth = array(
				'is_authorized'  => true,
				'transaction_id' => $charge['id'],
			);
		}

		return $auth;

	}

	/**
	 * Update the rate limits when errors thrown.
	 *
	 * We will add rate limits to the payment add-on framework as what we did here. Once we've done that, this method will be removed.
	 *
	 * @since 3.4
	 *
	 * @param string $error_message The error message.
	 *
	 * @return array
	 */
	public function authorization_error( $error_message ) {
		if ( $this->_enable_rate_limits ) {
			$this->get_card_error_count( true );
		}

		return array( 'error_message' => $error_message, 'is_success' => false, 'is_authorized' => false );
	}

	/**
	 * Create terawallet Checkout Session.
	 *
	 * @since 3.0
	 *
	 * @param array $feed            The feed object currently being processed.
	 * @param array $submission_data The customer and transaction data.
	 * @param array $form            The form object currently being processed.
	 * @param array $entry           The entry object currently being processed.
	 *
	 * @return array|boolean
	 */
	public function create_checkout_session( $feed, $submission_data, $form, $entry ) {
		$is_subscription = $feed['meta']['transactionType'] === 'subscription';
		$session_data    = array(
			'payment_method_types'       => array( 'card' ),
			'success_url'                => $this->get_success_url( $form['id'], $feed['id'] ),
			'cancel_url'                 => $this->get_cancel_url( $form['id'] ),
			'billing_address_collection' => ( boolval( rgars( $feed, 'meta/billingAddress' ) ) === true ) ? 'required' : 'auto',
		);

		// Get customer ID.
		// Note here we cannot run create_customer() yet because the payment is not authorized yet.
		// terawallet Checkout will create the customer for us and we run `gform_terawallet_customer_after_create` filter then.
		$customer = $this->get_customer( '', $feed, $entry, $form );
		if ( $customer ) {
			$session_data['customer'] = rgar( $customer, 'id' );
		}

		// Use the payment amount from submission data.
		$payment_amount = rgar( $submission_data, 'payment_amount' );

		if ( ! $is_subscription ) {
			// add discounts from the GF Coupon add-on.
			// terawallet Checkout cannot display our coupon as a negative value,
			// so we couldn't display the line items when an order contains coupons.
			$discounts    = rgar( $submission_data, 'discounts' );
			$discount_amt = 0;
			if ( is_array( $discounts ) ) {
				foreach ( $discounts as $discount ) {
					$discount_full = abs( $discount['unit_price'] ) * $discount['quantity'];
					$discount_amt += $discount_full;
				}
			}
			if ( $discount_amt > 0 ) {
				/**
				 * Filter line item name when there's coupon discounts apply to the payment.
				 *
				 * @since 3.0.3
				 *
				 * @param string $name            The line item name of the discounted order.
				 * @param array  $feed            The feed object currently being processed.
				 * @param array  $submission_data The customer and transaction data.
				 * @param array  $form            The form object currently being processed.
				 * @param array  $entry           The entry object currently being processed.
				 */
				$line_items_name = apply_filters( 'gform_terawallet_discounted_line_items_name', esc_html__( 'Payment with Discounts', 'gravityformsterawallet' ), $feed, $submission_data, $form, $entry );

				$session_data['line_items'][] = array(
					'amount'      => $this->get_amount_export( $payment_amount, rgar( $entry, 'currency' ) ),
					'currency'    => $entry['currency'],
					'name'        => $line_items_name,
					'quantity'    => 1,
					'description' => $this->get_payment_description( $entry, $submission_data, $feed ),
				);
			} else {
				foreach ( $submission_data['line_items'] as $line_item ) {
					$unit_price = $line_item['unit_price'];

					// terawallet Checkout doesn't allow 0 or negative price.
					if ( $unit_price > 0 ) {
						$data = array(
							'amount'   => $this->get_amount_export( $unit_price, rgar( $entry, 'currency' ) ),
							'currency' => $entry['currency'],
							'name'     => $line_item['name'],
							'quantity' => $line_item['quantity'],
						);

						if ( ! empty( $line_item['description'] ) ) {
							$data['description'] = $line_item['description'];
						}

						$session_data['line_items'][] = $data;
					}
				}
			}

			// Set capture method.
			$session_data['payment_intent_data']['capture_method'] = $this->get_capture_method( $feed, $submission_data, $form, $entry );

			// Add description.
			$session_data['payment_intent_data']['description'] = $this->get_payment_description( $entry, $submission_data, $feed );

			// If receipt field is defined, add receipt email address to charge meta.
			$receipt_field = rgars( $feed, 'meta/receipt_field' );
			if ( ! empty( $receipt_field ) && strtolower( $receipt_field ) !== 'do not send receipt' ) {
				$session_data['payment_intent_data']['receipt_email'] = $this->get_field_value( $form, $entry, $receipt_field );
			}

			// Set customer email.
			$customer_email_field = rgars( $feed, 'meta/customer_email_field' );
			if ( ! empty( $customer_email_field ) && strtolower( $customer_email_field ) !== 'do not set customer email' ) {
				$session_data['customer_email'] = $this->get_field_value( $form, $entry, $customer_email_field );
			}
		} else {
			// Prepare payment amount and trial period data.
			$single_payment_amount = $submission_data['setup_fee'];
			$trial_period_days     = rgars( $feed, 'meta/trialPeriod' ) ? $submission_data['trial'] : null;
			$currency              = rgar( $entry, 'currency' );

			if ( $single_payment_amount ) {
				// Create invoice line items for setup fee.
				$line_items                 = array(
					array(
						'amount'   => $this->get_amount_export( $single_payment_amount, $currency ),
						'currency' => $currency,
						'name'     => esc_html__( 'Setup Fee', 'gravityformsterawallet' ),
						'quantity' => 1,
					),
				);
				$session_data['line_items'] = $line_items;
			}

			// Checkout Session does not support setting trial period days at the plan level. So we create plans in
			// 0 trial days, and then set it in the subscription data.
			if ( rgars( $feed, 'meta/subscription_name' ) ) {
				$feed['meta']['subscription_name'] = GFCommon::replace_variables( rgars( $feed, 'meta/subscription_name' ), $form, $entry, false, true, true, 'text' );
			}

			// Get terawallet plan that matches current feed.
			$plan = $this->get_plan_for_feed( $feed, $payment_amount, null, $currency );

			// If error was returned when retrieving plan, return authorization error array.
			if ( rgar( $plan, 'error_message' ) ) {
				return $plan;
			}

			$items                                                  = array(
				'plan' => $plan->id,
			);
			$session_data['subscription_data']['items'][]           = $items;
			$session_data['subscription_data']['trial_period_days'] = $trial_period_days;

			$customer_email = $this->get_field_value( $form, $entry, rgar( $feed['meta'], 'customerInformation_email' ) );
			if ( is_email( $customer_email ) ) {
				$session_data['customer_email'] = $customer_email;
			}
		}

		/**
		 * Filter session data before creating the session. Can be used to add product images.
		 *
		 * @since 3.0
		 *
		 * @param array $session_data    Session data for creating the session.
		 * @param array $feed            The feed object currently being processed.
		 * @param array $submission_data The customer and transaction data.
		 * @param array $form            The form object currently being processed.
		 * @param array $entry           The entry object currently being processed.
		 */
		$session_data = apply_filters( 'gform_terawallet_session_data', $session_data, $feed, $submission_data, $form, $entry );

		// Remove 'customer_email' if 'customer' is defined to prevent a session creation failure.
		if ( isset( $session_data['customer'] ) && isset( $session_data['customer_email'] ) ) {
			$this->log_debug( __METHOD__ . '(): customer is defined; removing incompatible customer_email property.' );
			unset( $session_data['customer_email'] );
		}

		$this->log_debug( __METHOD__ . '(): Session to be created => ' . print_r( $session_data, true ) );

		$session = $this->api->create_checkout_session( $session_data );
		if ( ! is_wp_error( $session ) ) {
			$session_id = rgar( $session, 'id' );

			if ( ! $is_subscription ) {
				return array(
					'is_authorized'  => true,
					'transaction_id' => rgar( $session, 'payment_intent' ), // Store payment_intent as transaction_id.
					'session_id'     => $session_id,
				);
			} else {
				return array(
					'is_success'      => true,
					'subscription_id' => '',
					'customer_id'     => '',
					'amount'          => $payment_amount,
					'session_id'      => $session_id,
				);
			}
		}

		$this->log_error( __METHOD__ . '(): Unable to create terawallet Checkout session; ' . $session->get_error_message() );

		return $this->authorization_error( esc_html__( 'Unable to create terawallet Checkout session.', 'gravityformsterawallet' ) );
	}

	/**
	 * Complete authorization (mark entry as authorized and create note).
	 *
	 * @since 3.0
	 *
	 * @param array $entry  Entry data.
	 * @param array $action Authorization data.
	 *
	 * @return bool
	 */
	public function complete_authorization( &$entry, $action ) {
		if ( rgar( $action, 'session_id' ) ) {
			// Do not complete authorization at this stage since users haven't paid.
			$this->log_debug( __METHOD__ . '(): terawallet session will be created, but the payment hasn\'t been authorized yet. Mark it as processing.' );
			GFAPI::update_entry_property( $entry['id'], 'payment_status', 'Processing' );

			return true;
		}

		return parent::complete_authorization( $entry, $action );
	}

	/**
	 * Handle cancelling the subscription from the entry detail page.
	 *
	 * @since Unknown
	 * @since 2.8     Updated to use the subscription object instead of the customer object. Added $feed param when including terawallet API.
	 *
	 * @param array $entry The entry object currently being processed.
	 * @param array $feed  The feed object currently being processed.
	 *
	 * @return bool True if successful. False if failed.
	 */
	public function cancel( $entry, $feed ) {

		// Include terawallet API library.
		if ( $this->is_feed_terawallet_connect_enabled( $feed['id'] ) ) {
			$this->include_terawallet_api( $this->get_api_mode( $feed['meta'], $feed['id'] ), $feed['meta'] );
		} else {
			$this->include_terawallet_api();
		}

		if ( empty( $entry['transaction_id'] ) ) {
			return false;
		}

		// Get terawallet subscription object.
		$subscription = $this->api->get_subscription( $entry['transaction_id'] );

		if ( is_wp_error( $subscription ) ) {
			return false;
		}

		if ( $subscription->status === 'canceled' ) {
			$this->log_debug( __METHOD__ . '(): Subscription already cancelled.' );

			return true;
		}

		/**
		 * Allow the cancellation of the subscription to be delayed until the end of the current period.
		 *
		 * @since 2.1.0
		 *
		 * @param bool  $at_period_end Defaults to false, subscription will be cancelled immediately.
		 * @param array $entry         The entry from which the subscription was created.
		 * @param array $feed          The feed object which processed the current entry.
		 */
		$at_period_end = apply_filters( 'gform_terawallet_subscription_cancel_at_period_end', false, $entry, $feed );
		$action        = $at_period_end ? 'update' : 'cancel';

		if ( $at_period_end ) {
			$this->log_debug( __METHOD__ . '(): The gform_terawallet_subscription_cancel_at_period_end filter was used; cancelling subscription at period end.' );
			$subscription->cancel_at_period_end = true;

			$result = $this->api->save_subscription( $subscription );
			$this->log_debug( __METHOD__ . '(): Subscription updated.' );
		} else {
			$result = $this->api->cancel_subscription( $subscription );
			$this->log_debug( __METHOD__ . '(): Subscription cancelled.' );
		}

		if ( is_wp_error( $result ) ) {
			$this->log_error( sprintf( '%s(): Unable to %s subscription; %s', __METHOD__, $action, $result->get_error_message() ) );

			return false;
		} else {
			$this->log_debug( sprintf( '%s(): Successfully %s the subscription.', __METHOD__, $action ) );
		}

		return true;
	}

	/**
	 * Gets the payment validation result.
	 *
	 * @since  3.0 Remove validation message for terawallet Checkout.
	 * @since  2.6
	 *
	 * @param array $validation_result    Contains the form validation results.
	 * @param array $authorization_result Contains the form authorization results.
	 *
	 * @return array The validation result for the credit card field.
	 */
	public function get_validation_result( $validation_result, $authorization_result ) {
		if ( empty( $authorization_result['error_message'] ) ) {
			return parent::get_validation_result( $validation_result, $authorization_result );
		}

		$credit_card_page   = 0;
		$has_error_cc_field = false;
		foreach ( $validation_result['form']['fields'] as &$field ) {
			if ( $field->type === 'creditcard' || $field->type === 'terawallet_payment' ) {
				if ( $field->type === 'terawallet_payment' && ( rgar( $authorization_result, 'requires_action' ) || rgars( $authorization_result, 'subscription/requires_action' ) ) ) {
					$has_error_cc_field = true;
					$credit_card_page   = ( GFCommon::has_pages( $validation_result['form'] ) ) ? GFFormDisplay::get_max_page_number( $validation_result['form'] ) : $field->pageNumber;
					// Add SCA requires extra action message.
					add_filter( 'gform_validation_message', array( $this, 'terawallet_elements_requires_action_message' ) );
				} else {
					$has_error_cc_field        = true;
					$field->failed_validation  = true;
					$field->validation_message = $authorization_result['error_message'];
					$credit_card_page          = $field->pageNumber;
				}
				break;
			}
		}

		if ( ! $has_error_cc_field && $this->is_terawallet_checkout_enabled() ) {
			$credit_card_page = GFFormDisplay::get_max_page_number( $validation_result['form'] );
			add_filter( 'gform_validation_message', array( $this, 'terawallet_checkout_error_message' ) );
		}

		$validation_result['credit_card_page'] = $credit_card_page;
		$validation_result['is_valid']         = false;

		return $validation_result;
	}

	/**
	 * Capture the terawallet charge which was authorized during validation.
	 *
	 * @since  Unknown
	 * @since  3.4     Added card error rate limits.
	 * @access public
	 *
	 * @uses GFterawallet::get_terawallet_meta_data()
	 * @uses GFterawallet::get_payment_description()
	 * @uses GFAddOn::log_debug()
	 * @uses GFPaymentAddOn::get_amount_import()
	 * @uses Exception::getMessage()
	 *
	 * @param array $auth            Contains the result of the authorize() function.
	 * @param array $feed            The feed object currently being processed.
	 * @param array $submission_data The customer and transaction data.
	 * @param array $form            The form object currently being processed.
	 * @param array $entry           The entry object currently being processed.
	 *
	 * @return array $payment Contains payment details. If failed, shows failure message.
	 */
	public function capture( $auth, $feed, $submission_data, $form, $entry ) {
		// Check if the current IP has hit the error rate limit.
		if ( $hit_rate_limits = $this->maybe_hit_rate_limits( $form['id'] ) ) {
			return $hit_rate_limits;
		}

		if ( $this->is_terawallet_checkout_enabled() ) {
			gform_update_meta( $entry['id'], 'terawallet_session_id', $auth['session_id'] );
			// Cache the submission data so we can use it in complete_checkout_session().
			gform_update_meta( $entry['id'], 'submission_data', $submission_data );

			// return empty details for the new terawallet Checkout.
			return array();
		}

		// check if terawallet_response has a payment id.
		// if yes, confirm the payment here.
		$response = $this->get_terawallet_js_response();
		if ( substr( $response->id, 0, 3 ) === 'pi_' ) {
			$intent = $this->api->get_payment_intent( $response->id );

			if ( is_wp_error( $intent ) ) {
				$this->log_error( __METHOD__ . '(): Cannot get payment intent data; ' . $intent->get_error_message() );

				return array(
					'is_success'    => false,
					'error_message' => esc_html__( 'Cannot get payment intent data.', 'gravityformsterawallet' ),
				);
			}

			// Add entry ID to payment description.
			$intent->description = $this->get_payment_description( $entry, $submission_data, $feed );
			$metadata            = $this->get_terawallet_meta_data( $feed, $entry, $form );
			if ( ! empty( $metadata ) ) {
				$intent->metadata = $metadata;
			}

			// Save payment intent.
			$intent = $this->api->save_payment_intent( $intent );
			if ( is_wp_error( $intent ) ) {
				$this->log_error( __METHOD__ . '(): Cannot update payment intent data; ' . $intent->get_error_message() );

				return array(
					'is_success'    => false,
					'error_message' => esc_html__( 'Cannot update payment intent data.', 'gravityformsterawallet' ),
				);
			}

			// Capture the payment if the filter not used.
			if ( $this->get_capture_method( $feed, $submission_data, $form, $entry ) === 'manual' ) {
				return array();
			}

			// the filter not used, capture the payment intent.
			$intent = $this->api->capture_payment_intent( $intent );
			if ( is_wp_error( $intent ) ) {
				$this->log_error( __METHOD__ . '(): Cannot capture payment intent data; ' . $intent->get_error_message() );

				return array(
					'is_success'    => false,
					'error_message' => esc_html__( 'Cannot capture payment intent data.', 'gravityformsterawallet' ),
				);
			}

			if ( $intent->status !== 'succeeded' ) {
				$payment = $this->authorization_error( esc_html__( 'Cannot capture the payment; the payment intent status is ', 'gravityformsterawallet' ) . $intent->status );
			} else {
				$payment = array(
					'is_success'     => true,
					'transaction_id' => $response->id,
					'amount'         => $this->get_amount_import( $intent->amount, $entry['currency'] ),
					'payment_method' => rgpost( 'terawallet_credit_card_type' ),
				);
			}
		} else {
			// Get terawallet charge from authorization.
			$charge = $this->api->get_charge( $auth['transaction_id'] );
			if ( is_wp_error( $charge ) ) {
				$this->log_error( __METHOD__ . '(): Unable to capture the charge; ' . $charge->get_error_message() );

				return array(
					'is_success'    => false,
					'error_message' => esc_html__( 'Unable to capture the charge.', 'gravityformsterawallet' ),
				);
			}

			// Set charge description and metadata.
			$charge->description = $this->get_payment_description( $entry, $submission_data, $feed );

			$metadata = $this->get_terawallet_meta_data( $feed, $entry, $form );
			if ( ! empty( $metadata ) ) {
				$charge->metadata = $metadata;
			}

			// Save charge.
			$charge = $this->api->save_charge( $charge );
			if ( is_wp_error( $charge ) ) {
				$this->log_error( __METHOD__ . '(): Unable to save the charge; ' . $charge->get_error_message() );

				return array(
					'is_success'    => false,
					'error_message' => esc_html__( 'Unable to save the charge.', 'gravityformsterawallet' ),
				);
			}

			// Capture the payment if the filter not used.
			if ( $this->get_capture_method( $feed, $submission_data, $form, $entry ) === 'manual' ) {
				return array();
			}

			// Capture the charge.
			$charge = $this->api->capture_charge( $charge );
			if ( is_wp_error( $charge ) ) {
				$this->log_error( __METHOD__ . '(): Unable to capture the charge; ' . $charge->get_error_message() );

				return array(
					'is_success'    => false,
					'error_message' => esc_html__( 'Unable to capture the charge.', 'gravityformsterawallet' ),
				);
			}

			// Prepare payment details.
			$payment = array(
				'is_success'     => true,
				'transaction_id' => $charge->id,
				'amount'         => $this->get_amount_import( $charge->amount, $entry['currency'] ),
				'payment_method' => rgpost( 'terawallet_credit_card_type' ),
			);
		}

		return $payment;
	}

	/**
	 * Update the entry meta with the terawallet Customer ID.
	 *
	 * @since  Unknown
	 * @access public
	 *
	 * @uses GFterawallet::get_terawallet_meta_data()
	 * @uses GFterawallet::get_customer()
	 * @uses GFPaymentAddOn::process_subscription()
	 * @uses \Exception::getMessage()
	 * @uses GFAddOn::log_error()
	 *
	 * @param array $authorization   Contains the result of the subscribe() function.
	 * @param array $feed            The feed object currently being processed.
	 * @param array $submission_data The customer and transaction data.
	 * @param array $form            The form object currently being processed.
	 * @param array $entry           The entry object currently being processed.
	 *
	 * @return array The entry object.
	 */
	public function process_subscription( $authorization, $feed, $submission_data, $form, $entry ) {
		if ( empty( $authorization['subscription']['subscription_id'] ) && $this->is_terawallet_checkout_enabled() ) {
			gform_update_meta( $entry['id'], 'terawallet_session_id', $authorization['subscription']['session_id'] );

			// return $entry directly for the new terawallet Checkout.
			return $entry;
		}

		// Update customer ID for entry.
		$customer_id = rgars( $authorization, 'subscription/customer_id' );
		if ( $customer_id ) {
			gform_update_meta( $entry['id'], 'terawallet_customer_id', $customer_id );
		} else {
			// No customer ID, setup the subscription error message.
			$authorization['subscription']['error_message'] = esc_html__( 'Failed to get the customer ID from terawallet.', 'gravityformsterawallet' );
		}

		if ( $this->is_terawallet_checkout_enabled() ) {
			// When the session is completed, we cannot run create_customer() because the customer is already created
			// by terawallet Checkout. So we support the `gform_terawallet_customer_after_create` filter here to perform custom
			// actions.
			$customer = $this->get_customer( '', $feed, $entry, $form );
			if ( ! $customer ) {
				$customer = $this->get_customer( $authorization['subscription']['customer_id'] );

				$this->after_create_customer( $customer, $feed, $entry, $form );

				// update customer data with feed settings.
				$customer_meta = array(
					'description' => $this->get_field_value( $form, $entry, rgar( $feed['meta'], 'customerInformation_description' ) ),
					'email'       => $this->get_field_value( $form, $entry, rgar( $feed['meta'], 'customerInformation_email' ) ),
					'metadata'    => $this->get_terawallet_meta_data( $feed, $entry, $form ),
				);

				// Get coupon for feed.
				$coupon_field_id = rgar( $feed['meta'], 'customerInformation_coupon' );
				$coupon          = $this->maybe_override_field_value( rgar( $entry, $coupon_field_id ), $form, $entry, $coupon_field_id );

				// If coupon is set, add it to customer metadata.
				if ( $coupon ) {
					$terawallet_coupon = $this->api->get_coupon( $coupon );
					if ( ! is_wp_error( $terawallet_coupon ) ) {
						$customer_meta['coupon'] = $coupon;
					} else {
						$this->log_error( __METHOD__ . '(): Unable to add the coupon to the customer; ' . $terawallet_coupon->get_error_message() );
					}
				}

				$customer = $this->api->update_customer( $authorization['subscription']['customer_id'], $customer_meta );
				if ( is_wp_error( $customer ) ) {
					$this->log_error( __METHOD__ . '(): Unable to update the customer; ' . $customer->get_error_message() . '. Customer meta passed: ' . print_r( $customer_meta, true ) );
				}
			}
		} else {
			$metadata = $this->get_terawallet_meta_data( $feed, $entry, $form );
			if ( ! empty( $metadata ) ) {
				// Update to user meta post entry creation so entry ID is available.
				// Get customer.
				$customer = $this->get_customer( $customer_id );

				if ( $customer ) {
					// Update customer metadata.
					$customer->metadata = $metadata;

					// Save customer.
					$result = $this->api->save_customer( $customer );

					if ( is_wp_error( $result ) ) {
						$this->log_error( __METHOD__ . '(): Unable to save customer; ' . $result->get_error_message() );
					}
				}
			}
		}

		return parent::process_subscription( $authorization, $feed, $submission_data, $form, $entry );

	}

	/**
	 * Subscribe the user to a terawallet plan. This process works like so:
	 *
	 * 1 - Get existing plan or create new plan (plan ID generated by feed name, id and recurring amount).
	 * 2 - Create new customer.
	 * 3 - Create new subscription by subscribing customer to plan.
	 *
	 * @since  Unknown
	 * @since  2.8     Added $feed param when including terawallet API.
	 * @since  3.0     Support the new terawallet Checkout (the workflow is different).
	 * @since  3.4     Added card error rate limits.
	 * @since  3.5     Added support for GF_terawallet_API class.
	 * @access public
	 *
	 * @uses GFterawallet::include_terawallet_api()
	 * @uses GFterawallet::get_terawallet_js_error()
	 * @uses GFPaymentAddOn::authorization_error()
	 * @uses GFterawallet::get_subscription_plan_id()
	 * @uses GFterawallet::get_plan()
	 * @uses GFterawallet::get_terawallet_js_response()
	 * @uses GFterawallet::get_customer()
	 * @uses GFAddOn::log_debug()
	 * @uses GFPaymentAddOn::get_amount_export()
	 * @uses GFAddOn::get_field_value()
	 * @uses GFterawallet::get_terawallet_meta_data()
	 * @uses GFAddOn::maybe_override_field_value()
	 *
	 * @param array $feed            The feed object currently being processed.
	 * @param array $submission_data The customer and transaction data.
	 * @param array $form            The form object currently being processed.
	 * @param array $entry           The entry object currently being processed.
	 *
	 * @return array|void Subscription details if successful. Contains error message if failed.
	 */
	public function subscribe( $feed, $submission_data, $form, $entry ) {
		// Check if the current IP has hit the error rate limit.
		if ( $hit_rate_limits = $this->maybe_hit_rate_limits( $form['id'] ) ) {
			return $hit_rate_limits;
		}

		// Include terawallet API library.
		if ( $this->is_feed_terawallet_connect_enabled( $feed['id'] ) ) {
			$this->include_terawallet_api( $this->get_api_mode( $feed['meta'], $feed['id'] ), $feed['meta'] );
		} else {
			$this->include_terawallet_api();
		}

		// If there was an error when retrieving the terawallet.js token, return an authorization error.
		if ( $this->get_terawallet_js_error() ) {
			return $this->authorization_error( $this->get_terawallet_js_error() );
		}

		if ( $this->is_terawallet_checkout_enabled() ) {
			// Create checkout session.
			return $this->create_checkout_session( $feed, $submission_data, $form, $entry );
		}

		// Prepare payment amount and trial period data.
		$payment_amount        = $submission_data['payment_amount'];
		$single_payment_amount = $submission_data['setup_fee'];
		$trial_period_days     = rgars( $feed, 'meta/trialPeriod' ) ? $submission_data['trial'] : null;
		$currency              = rgar( $entry, 'currency' );

	




		if ( rgars( $feed, 'meta/subscription_name' ) ) {
			$feed['meta']['subscription_name'] = GFCommon::replace_variables( rgars( $feed, 'meta/subscription_name' ), $form, $entry, false, true, true, 'text' );
		}

		// Get terawallet plan that matches current feed.
		$plan    = $this->get_plan_for_feed( $feed, $payment_amount, $trial_period_days, $currency );

		// If error was returned when retrieving plan, return authorization error array.
		if ( rgar( $plan, 'error_message' ) ) {
			return $plan;
		}

		// Get terawallet.js token.
		$terawallet_response = $this->get_terawallet_js_response();
		// Subscription has been created but the invoice hasn't been paid.
		if ( ! empty( $terawallet_response->subscription ) ) {
			$subscription = $this->api->get_subscription( $terawallet_response->subscription );
			if ( is_wp_error( $subscription ) ) {
				$this->log_error( __METHOD__ . '(): ' . $subscription->get_error_message() );

				return $this->authorization_error( $subscription->get_error_message() );
			}

			if ( rgar( $subscription, 'status' ) === 'active' || rgar( $subscription, 'status' ) === 'trialing' ) {
				$_POST['terawallet_response'] = '';

				return array(
					'is_success'      => true,
					'subscription_id' => $terawallet_response->subscription,
					'customer_id'     => $subscription->customer,
					'amount'          => $payment_amount,
				);
			} else {
				$invoice = $this->api->get_invoice( rgar( $subscription, 'latest_invoice' ) );
				if ( is_wp_error( $invoice ) ) {
					$this->log_error( __METHOD__ . '(): ' . $invoice->get_error_message() );

					return $this->authorization_error( $invoice->get_error_message() );
				}

				$payment_intent = $this->api->get_payment_intent( rgar( $invoice, 'payment_intent' ) );
				if ( is_wp_error( $payment_intent ) ) {
					$this->log_error( __METHOD__ . '(): ' . $payment_intent->get_error_message() );

					return $this->authorization_error( $payment_intent->get_error_message() );
				}

				// Update customer source only when the plan id is the same.
				if ( rgar( $payment_intent, 'status' ) === 'requires_payment_method' && ( $plan->id === rgars( $subscription, 'plan/id' ) ) ) {
					if ( ! empty( $terawallet_response->updatedToken ) ) {
						$result = $this->api->update_customer(
							$subscription->customer,
							array(
								'source' => $terawallet_response->updatedToken,
							)
						);
						if ( ! is_wp_error( $result ) ) {
							// Pay the invoice.
							$result = $this->api->pay_invoice( $invoice, array( 'expand' => array( 'payment_intent' ) ) );
							if ( ! is_wp_error( $result ) ) {
								// Retrieve it again because the status might have changed after we pay the invoice.
								$result = $this->api->get_subscription( $terawallet_response->subscription );
								if ( ! is_wp_error( $result ) ) {
									$subscription_id = $this->handle_subscription_payment( $result, $invoice );

									if ( is_array( $subscription_id ) ) {
										return $subscription_id;
									} else {
										return array(
											'is_success'      => true,
											'subscription_id' => $subscription_id,
											'customer_id'     => $result->customer,
											'amount'          => $payment_amount,
										);
									}
								}
							}
						}

						$this->log_error( __METHOD__ . '(): ' . $result->get_error_message() );

						return $this->authorization_error( $result->get_error_message() );
					} else {
						return $this->authorization_error( '<span class="requires_payment_method">' . esc_html__( 'Your payment attempt has failed. Please enter your card details and try again.', 'gravityformsterawallet' ) . '</span>' );
					}
				}
			}
		}

		$customer = $this->get_customer( '', $feed, $entry, $form );

		if ( $customer ) {
			$this->log_debug( __METHOD__ . '(): Updating existing customer.' );

			// Update the customer source with the terawallet token.
			$customer->source = ( ! empty( $terawallet_response->updatedToken ) ) ? $terawallet_response->updatedToken : $terawallet_response->id;
			$result           = $this->api->save_customer( $customer );
			if ( ! is_wp_error( $result ) ) {
				// If a setup fee is required, add an invoice item.
				if ( $single_payment_amount ) {
					$setup_fee = array(
						'amount'   => $this->get_amount_export( $single_payment_amount, $currency ),
						'currency' => $currency,
						'customer' => $customer->id,
					);

					$result = $this->api->add_invoice_item( $setup_fee );
				}
			}

			if ( is_wp_error( $result ) ) {
				$this->log_error( __METHOD__ . '(): ' . $result->get_error_message() );

				return $this->authorization_error( $result->get_error_message() );
            }
		} else {
			// Prepare customer metadata.
			// Starts from 3.0, customers created by terawallet Checkout won't have the `balance` set.
			// Setup fee would be a line item in the invoice.
			$customer_meta = array(
				'description' => $this->get_field_value( $form, $entry, rgar( $feed['meta'], 'customerInformation_description' ) ),
				'email'       => $this->get_field_value( $form, $entry, rgar( $feed['meta'], 'customerInformation_email' ) ),
				'source'      => ( ! empty( $terawallet_response->updatedToken ) ) ? $terawallet_response->updatedToken : $terawallet_response->id,
				'balance'     => $this->get_amount_export( $single_payment_amount, $currency ),
			);

			// Get coupon for feed.
			$coupon_field_id = rgar( $feed['meta'], 'customerInformation_coupon' );
			$coupon          = $this->maybe_override_field_value( rgar( $entry, $coupon_field_id ), $form, $entry, $coupon_field_id );

			// If coupon is set, add it to customer metadata.
			if ( $coupon ) {
				$customer_meta['coupon'] = $coupon;
			}

			$customer = $this->create_customer( $customer_meta, $feed, $entry, $form );

		}

		// An authorization_error is returned.
		if ( is_wp_error( $customer ) ) {
			// Return authorization error.
			return $this->authorization_error( $customer->get_error_message() );
		}

		// Add subscription to customer and retrieve the subscription ID.
		$subscription_id = $this->update_subscription( $customer, $plan, $feed, $entry, $form, $trial_period_days );

		if ( is_array( $subscription_id ) ) {
			return $subscription_id;
		}

		// Return subscription data.
		return array(
			'is_success'      => true,
			'subscription_id' => $subscription_id,
			'customer_id'     => $customer->id,
			'amount'          => $payment_amount,
		);

	}

	/**
	 * Display the thank you page when there's a gf_terawallet_success URL param.
	 *
	 * @since 3.0
	 */
	public function maybe_thankyou_page() {
		if ( ! $this->is_gravityforms_supported() ) {
			return;
		}

		if ( $str = rgget( 'gf_terawallet_success' ) ) {
			$str = base64_decode( $str );

			parse_str( $str, $query );
			if ( wp_hash( 'ids=' . $query['ids'] ) == $query['hash'] ) {
				list( $form_id, $feed_id ) = explode( '|', $query['ids'] );
				$feed = $this->get_feed( $feed_id );
				$mode = $settings = null;
				if ( $this->is_feed_terawallet_connect_enabled( $feed_id ) ) {
					$settings = $feed['meta'];
				}

				$this->include_terawallet_api( $mode, $settings );

				$form       = GFAPI::get_form( $form_id );
				$session_id = sanitize_text_field( rgget( 'gf_terawallet_session_id' ) );
				$session    = $this->api->get_checkout_session( $session_id );
				$entries    = GFAPI::get_entries(
					$form_id,
					array(
						'field_filters' => array(
							array(
								'key'   => 'terawallet_session_id',
								'value' => $session_id,
							),
						),
					)
				);

				if ( is_wp_error( $entries ) || ! $entries ) {
					return;
				}

				$entry = $entries[0];

				// Check if the webhook event has completed session, if not, call complete_checkout_session().
				if ( $subscription_id = rgar( $session, 'subscription' ) ) {
					$is_checkout_session_completed = $entry['payment_status'] === 'Active';
				} else {
					$is_checkout_session_completed = $entry['payment_status'] === 'Paid' || $entry['payment_status'] === 'Authorized';
				}

				if ( ! $is_checkout_session_completed ) {
					$this->log_debug( __METHOD__ . '(): terawallet Checkout session will be completed in the form confirmation page.' );
					$this->complete_checkout_session( $session, $entry, $feed, $form );
				}

				if ( ! class_exists( 'GFFormDisplay' ) ) {
					require_once( GFCommon::get_base_path() . '/form_display.php' );
				}

				$confirmation = GFFormDisplay::handle_confirmation( $form, $entry, false );

				if ( is_array( $confirmation ) && isset( $confirmation['redirect'] ) ) {
					header( "Location: {$confirmation['redirect']}" );
					exit;
				}

				GFFormDisplay::$submission[ $form_id ] = array(
					'is_confirmation'      => true,
					'confirmation_message' => $confirmation,
					'form'                 => $form,
					'lead'                 => $entry,
				);
			}
		}
	}

	/**
	 * Complete payments or subscriptions when redirect back from terawallet Checkout or checkout.session.completed event
	 * triggered.
	 *
	 * @since 3.0
	 *
	 * @param array $session The session object.
	 * @param array $entry   The entry object.
	 * @param array $feed    The feed object.
	 * @param array $form    The form object.
	 *
	 * @return array $action
	 */
	public function complete_checkout_session( $session, $entry, $feed, $form ) {
		$action         = array();
		$payment_status = rgar( $entry, 'payment_status' );

		if ( $subscription_id = rgar( $session, 'subscription' ) ) {
			$subscription = $this->api->get_subscription( $subscription_id );
			if ( is_wp_error( $subscription ) ) {
				$this->log_error( __METHOD__ . '(): A terawallet API error occurs; ' . $subscription->get_error_message() );

				return $action;
			}

			if ( in_array( rgar( $subscription, 'status' ), array( 'active', 'trialing' ), true ) && $payment_status !== 'Active' ) {
				// Create the $authorization array and run process_subscription().
				$recurring_fee = (int) rgars( $subscription, 'items/data/0/plan/amount' );
				$authorization = array(
					'subscription' => array(
						'subscription_id' => $subscription_id,
						'customer_id'     => rgar( $session, 'customer' ),
						'is_success'      => true,
						'amount'          => $this->get_amount_import( $recurring_fee, $entry['currency'] ),
					),
				);
				$this->process_subscription( $authorization, $feed, array(), $form, $entry );

				// Update the subscription data if `gform_terawallet_subscription_params_pre_update_customer` filter is used.
				$customer = $this->api->get_customer( rgar( $session, 'customer' ) );
				if ( is_wp_error( $customer ) ) {
					$this->log_error( __METHOD__ . '(): A terawallet API error occurs; ' . $customer->get_error_message() );

					return $action;
				}

				$plan = $this->get_plan( rgars( $subscription, 'items/data/0/plan/id' ) );
				if ( is_wp_error( $subscription ) ) {
					$this->log_error( __METHOD__ . '(): A terawallet API error occurs; ' . $subscription->get_error_message() );

					return $action;
				}

				$trial_period_days            = rgars( $subscription, 'items/data/0/plan/trial_period_days' );
				$subscription_params          = array(
					'customer' => $customer->id,
					'items'    => array(
						array(
							'plan' => $plan->id,
						),
					),
				);
				$filtered_subscription_params = $this->get_subscription_params( $subscription_params, $customer, $plan, $feed, $entry, $form, $trial_period_days );
				if ( $subscription_params !== $filtered_subscription_params ) {
					$subscription = $this->api->update_subscription( $subscription_id, $subscription_params );

					if ( is_wp_error( $subscription ) ) {
						$this->log_error( __METHOD__ . '(): A terawallet API error occurs; ' . $subscription->get_error_message() );

						return $action;
					}
				}

				// Add capture payment note.
				$action['subscription_id'] = $subscription_id;
				$invoice                   = $this->api->get_invoice( rgar( $subscription, 'latest_invoice' ) );
				if ( is_wp_error( $invoice ) ) {
					$this->log_error( __METHOD__ . '(): A terawallet API error occurs; ' . $invoice->get_error_message() );

					return $action;
				}

				$action['transaction_id'] = rgar( $invoice, 'payment_intent' );
				$action['entry_id']       = $entry['id'];
				$action['type']           = 'add_subscription_payment';
				$action['amount']         = $this->get_amount_import( rgar( $invoice, 'amount_due' ), $entry['currency'] );
				$action['note']           = '';

				// Get starting balance, assume this balance represents a setup fee or trial.
				$starting_balance = $this->get_amount_import( rgar( $invoice, 'starting_balance' ), $entry['currency'] );
				if ( $starting_balance > 0 ) {
					$action['note'] = $this->get_captured_payment_note( $action['entry_id'] ) . ' ';
				}

				$amount_formatted = GFCommon::to_money( $action['amount'], $entry['currency'] );
				$action['note']   .= sprintf( __( 'Subscription payment has been paid. Amount: %s. Subscription Id: %s', 'gravityformsterawallet' ), $amount_formatted, $action['subscription_id'] );

				if ( rgar( $subscription, 'status' ) === 'active' ) {
					// Run gform_terawallet_fulfillment hook for supporting delayed payments.
					$this->checkout_fulfillment( $session, $entry, $feed, $form );
				}
			}
		} else {
			$action['abort_callback'] = true;

			if ( $payment_status !== 'Paid' ) {
				$submission_data       = gform_get_meta( $entry['id'], 'submission_data' );
				$payment_intent        = rgar( $session, 'payment_intent' );
				$payment_intent_object = $this->api->get_payment_intent( $payment_intent );

				if ( is_wp_error( $payment_intent_object ) ) {
					$this->log_error( __METHOD__ . '(): A terawallet API error occurs; ' . $payment_intent_object->get_error_message() );

					return $action;
				}

				$authorization = array(
					'is_authorized'  => true,
					'transaction_id' => $payment_intent,
					'amount'         => $this->get_amount_import( rgars( $payment_intent_object, 'amount_received' ), $entry['currency'] ),
				);

				// complete authorization.
				if ( $payment_status !== 'Authorized' ) {
					$this->process_capture( $authorization, $feed, $submission_data, $form, $entry );
				}

				// if authorization_only = true, status will be 'requires_capture',
				// so if the payment intent status is succeeded, we can mark the entry as Paid.
				if ( rgars( $payment_intent_object, 'status' ) === 'succeeded' ) {
					$payment_method = rgars( $payment_intent_object, 'charges/data/0/payment_method_details/card/brand' );

					$authorization['captured_payment'] = array(
						'is_success'     => true,
						'transaction_id' => $payment_intent,
						'amount'         => $authorization['amount'],
						'payment_method' => $payment_method,
					);
					// Mark payment as completed (paid).
					$this->process_capture( $authorization, $feed, $submission_data, $form, $entry );

					// Run gform_terawallet_fulfillment hook for supporting delayed payments.
					$this->checkout_fulfillment( $session, $entry, $feed, $form );
				}

				// Update payment intent for description to add Entry ID.
				// Because the entry ID wasn't available when checkout session was created.
				if ( ! empty( $submission_data ) ) {
					$metadata = $this->get_terawallet_meta_data( $feed, $entry, $form );
					if ( ! empty( $metadata ) ) {
						$payment_intent_object->metadata = $metadata;
					}
					$payment_intent_object->description = $this->get_payment_description( $entry, $submission_data, $feed );
					$this->api->save_payment_intent( $payment_intent_object );

					gform_delete_meta( $entry['id'], 'submission_data' );
				}
			}
		}

		return $action;
	}

	/**
	 * Run functions that hook to gform_terawallet_fulfillment.
	 *
	 * @since 3.1
	 *
	 * @param array|\terawallet\Checkout\Session $session The session object.
	 * @param array                          $entry   The entry object.
	 * @param array                          $feed    The feed object.
	 * @param array                          $form    The form object.
	 */
	public function checkout_fulfillment( $session, $entry, $feed, $form ) {

		if ( $subscription_id = rgar( $session, 'subscription' ) ) {
			$transaction_id = $subscription_id;
		} else {
			$transaction_id = rgar( $session, 'payment_intent' );
		}

		if ( method_exists( $this, 'trigger_payment_delayed_feeds' ) ) {
			$this->trigger_payment_delayed_feeds( $transaction_id, $feed, $entry, $form );
		}

		if ( has_filter( 'gform_terawallet_fulfillment' ) ) {
			// Log that filter will be executed.
			$this->log_debug( __METHOD__ . '(): Executing functions hooked to gform_terawallet_fulfillment.' );

			/**
			 * Allow custom actions to be performed after a checkout session is completed.
			 *
			 * @since 3.1
			 *
			 * @param array $session The session object.
			 * @param array $entry   The entry object.
			 * @param array $feed    The feed object.
			 * @param array $form    The form object.
			 */
			do_action( 'gform_terawallet_fulfillment', $session, $entry, $feed, $form );
		}
	}

	// # terawallet HELPER FUNCTIONS ---------------------------------------------------------------------------------------

	/**
	 * Retrieve a specific customer from terawallet.
	 *
	 * @since  Unknown
	 * @access public
	 *
	 * @used-by GFterawallet::authorize_product()
	 * @used-by GFterawallet::cancel()
	 * @used-by GFterawallet::process_subscription()
	 * @used-by GFterawallet::subscribe()
	 * @uses    GFAddOn::log_debug()
	 *
	 * @param string $customer_id The identifier of the customer to be retrieved.
	 * @param array  $feed        The feed currently being processed.
	 * @param array  $entry       The entry currently being processed.
	 * @param array  $form        The which created the current entry.
	 *
	 * @return bool|\terawallet\Customer Contains customer data if available. Otherwise, false.
	 */
	public function get_customer( $customer_id, $feed = array(), $entry = array(), $form = array() ) {
		if ( empty( $customer_id ) && has_filter( 'gform_terawallet_customer_id' ) ) {
			$this->log_debug( __METHOD__ . '(): Executing functions hooked to gform_terawallet_customer_id.' );

			/**
			 * Allow an existing customer ID to be specified for use when processing the submission.
			 *
			 * @since  2.1.0
			 * @access public
			 *
			 * @param string $customer_id The identifier of the customer to be retrieved. Default is empty string.
			 * @param array  $feed        The feed currently being processed.
			 * @param array  $entry       The entry currently being processed.
			 * @param array  $form        The form which created the current entry.
			 */
			$customer_id = apply_filters( 'gform_terawallet_customer_id', $customer_id, $feed, $entry, $form );
		}

		if ( $customer_id ) {
			$this->log_debug( __METHOD__ . '(): Retrieving customer id => ' . print_r( $customer_id, 1 ) );
			$customer = $this->api->get_customer( $customer_id );

			if ( ! is_wp_error( $customer ) ) {
				return $customer;
			}

			$this->log_error( __METHOD__ . '(): Unable to get customer; ' . $customer->get_error_message() );
		}

		return false;
	}

	/**
	 * Create and return a terawallet customer with the specified properties.
	 *
	 * @since  Unknown
	 * @access public
	 *
	 * @used-by GFterawallet::subscribe()
	 * @uses    GFAddOn::log_debug()
	 *
	 * @param array $customer_meta The customer properties.
	 * @param array $feed          The feed currently being processed.
	 * @param array $entry         The entry currently being processed.
	 * @param array $form          The form which created the current entry.
	 *
	 * @return WP_Error|\terawallet\Customer The terawallet customer object.
	 */
	public function create_customer( $customer_meta, $feed, $entry, $form ) {

		// Log the customer to be created.
		$this->log_debug( __METHOD__ . '(): Customer meta to be created => ' . print_r( $customer_meta, 1 ) );
		$customer = $this->api->create_customer( $customer_meta );

		if ( is_wp_error( $customer ) ) {
			$this->log_error( __METHOD__ . '(): A terawallet API error occurs; ' . $customer->get_error_message() );
		} else {
			$this->after_create_customer( $customer, $feed, $entry, $form );
		}

		return $customer;
	}

	/**
	 * Run action hook after a customer is created.
	 *
	 * @since 3.0
	 *
	 * @param terawallet\Customer $customer The customer object.
	 * @param array           $feed     The feed object.
	 * @param array           $entry    The entry object.
	 * @param array           $form     The form object.
	 */
	public function after_create_customer( $customer, $feed, $entry, $form ) {
		if ( has_filter( 'gform_terawallet_customer_after_create' ) ) {
			// Log that filter will be executed.
			$this->log_debug( __METHOD__ . '(): Executing functions hooked to gform_terawallet_customer_after_create.' );

			/**
			 * Allow custom actions to be performed between the customer being created and subscribed to the plan.
			 *
			 * @since 2.0.1
			 *
			 * @param terawallet\Customer $customer The terawallet customer object.
			 * @param array           $feed     The feed currently being processed.
			 * @param array           $entry    The entry currently being processed.
			 * @param array           $form     The form currently being processed.
			 */
			do_action( 'gform_terawallet_customer_after_create', $customer, $feed, $entry, $form );
		}
	}

	/**
	 * Retrieves a plan that matches the feed properties or creates a new one if no matching plans exist.
	 *
	 * @since 3.7.2
	 *
	 * @param array     $feed              The feed object currently being processed.
	 * @param float|int $payment_amount    The recurring amount.
	 * @param int       $trial_period_days The number of days the trial should last.
	 * @param string    $currency          The currency code for the entry being processed.
	 *
	 * @return \terawallet\Plan|array $plan The plan object, If invalid request, the authorization error array containing the error message.
	 */
	public function get_plan_for_feed( $feed, $payment_amount, $trial_period_days, $currency ) {

		$plan_id = $this->get_subscription_plan_id( $feed, $payment_amount, $trial_period_days, $currency );
		$plan    = $this->get_plan( $plan_id );

		if ( rgar( $plan, 'error_message' ) ) {

			// If $plan is an array that has an error message, it is an authorization error array.
			return $plan;

		} elseif ( false === $plan ) {

			// plan does not exist, create it.
			return $this->create_plan( $plan_id, $feed, $payment_amount, $trial_period_days, $currency );

		} elseif ( false === $this->is_plan_for_feed( $plan, $feed, $payment_amount, $currency ) ) {

			// Plan with same id exists but with mismatching properties.
			// Append time to feed subscription name to guarantee the generated plan id will be unique.
			$current_subscription_name         = rgars( $feed, 'meta/subscription_name', $feed['meta']['feedName'] );
			$feed['meta']['subscription_name'] = $current_subscription_name . '_' . gmdate( 'Y_m_d_H_i_s' );
			$this->update_feed_meta( $feed['id'], $feed['meta'] );
			// Generate new unique plan id.
			$plan_id = $this->get_subscription_plan_id( $feed, $payment_amount, $trial_period_days, $currency );
			// Create a new plan with unique id and correct parameters.
			return $this->create_plan( $plan_id, $feed, $payment_amount, $trial_period_days, $currency );

		}

		return $plan;
	}

	/**
	 * Compares terawallet plan object properties with feed properties to make sure this is the correct plan for this feed.
	 *
	 * @since 3.7.2
	 *
	 * @param \terawallet\Plan  $plan              The retrieved terawallet plan object.
	 * @param array         $feed              The feed object currently being processed.
	 * @param float|int     $payment_amount    The recurring amount.
	 * @param string        $currency          The currency code for the entry being processed.
	 *
	 * @return bool
	 */
	public function is_plan_for_feed( $plan, $feed, $payment_amount, $currency ) {
		return (
			$plan->amount === $this->get_amount_export( $payment_amount, $currency )
			&& strtolower( $plan->currency ) === strtolower( $currency )
			&& $plan->interval === $feed['meta']['billingCycle_unit']
			&& $plan->interval_count === intval( $feed['meta']['billingCycle_length'] )
		);
	}

	/**
	 * Try and retrieve the plan if a plan with the matching id has previously been created.
	 *
	 * @since  Unknown
	 * @access public
	 *
	 * @used-by GFterawallet::subscribe()
	 * @uses    GFPaymentAddOn::authorization_error()
	 *
	 * @param string $plan_id The subscription plan id.
	 *
	 * @return \terawallet\Plan|bool|array $plan The plan object. False if not found. If invalid request, the authorization error array containing the error message.
	 */
	public function get_plan( $plan_id ) {
		// Get terawallet plan.
		$plan = $this->api->get_plan( $plan_id );
		if ( is_wp_error( $plan ) ) {
			$plan = $this->authorization_error( $plan->get_error_message() );
		}

		return $plan;
	}

	/**
	 * Create and return a terawallet plan with the specified properties.
	 *
	 * @since   Unknown
	 * @access  public
	 *
	 * @used-by GFterawallet::subscribe()
	 * @uses    GFPaymentAddOn::get_amount_export()
	 * @uses    GFAddOn::log_debug()
	 *
	 * @param string    $plan_id           The plan ID.
	 * @param array     $feed              The feed currently being processed.
	 * @param float|int $payment_amount    The recurring amount.
	 * @param int       $trial_period_days The number of days the trial should last.
	 * @param string    $currency          The currency code for the entry being processed.
	 *
	 * @return array|\terawallet\Plan The plan object, or an authorization error.
	 */
	public function create_plan( $plan_id, $feed, $payment_amount, $trial_period_days, $currency ) {
		// Prepare plan metadata.
		$name = ( rgars( $feed, 'meta/subscription_name' ) ) ? rgars( $feed, 'meta/subscription_name' ) : $feed['meta']['feedName'];

		$plan_meta = array(
			'interval'          => $feed['meta']['billingCycle_unit'],
			'interval_count'    => $feed['meta']['billingCycle_length'],
			'product'           => array( 'name' => $name ),
			'currency'          => $currency,
			'id'                => $plan_id,
			'amount'            => $this->get_amount_export( $payment_amount, $currency ),
			'trial_period_days' => $trial_period_days,
		);

		// Log the plan to be created.
		$this->log_debug( __METHOD__ . '(): Plan to be created => ' . print_r( $plan_meta, 1 ) );

		// Create terawallet plan.
		$plan = $this->api->create_plan( $plan_meta );
		if ( is_wp_error( $plan ) ) {
			$this->log_error( __METHOD__ . '(): A terawallet API error occurs; ' . $plan->get_error_message() );
			$plan = $this->authorization_error( $plan->get_error_message() );
		}

		return $plan;
	}

	/**
	 * Subscribes the customer to the plan.
	 *
	 * @since 2.3.4
	 * @since 2.5.2 Added the $trial_period_days param.
	 * @since 3.3   Updated to support payment intents API.
	 * @since 3.4   Updated for terawallet SDK 7.0.
	 *
	 * @param \terawallet\Customer $customer          The terawallet customer object.
	 * @param \terawallet\Plan     $plan              The terawallet plan object.
	 * @param array            $feed              The feed currently being processed.
	 * @param array            $entry             The entry currently being processed.
	 * @param array            $form              The form which created the current entry.
	 * @param int              $trial_period_days The number of days the trial should last.
	 *
	 * @return string|array Return terawallet subscription ID if payment succeed; otherwise returning an authorization error array.
	 */
	public function update_subscription( $customer, $plan, $feed, $entry, $form, $trial_period_days = 0 ) {
		if ( $this->has_credit_card_field( $form ) || $this->is_terawallet_checkout_enabled() ) {
			$subscription_params = array(
				'customer' => $customer->id,
				'items'    => array(
					array(
						'plan' => $plan->id,
					),
				),
			);
		} else {
			$subscription_params = array(
				'customer' => $customer->id,
				'items'    => array(
					array(
						'plan' => $plan->id,
					),
				),
				'expand'   => array(
					'latest_invoice.payment_intent',
				),
			);
		}

		if ( $trial_period_days > 0 ) {
			$subscription_params['trial_from_plan'] = true;
		}

		// Get filtered $subscription_params.
		$subscription_params = $this->get_subscription_params( $subscription_params, $customer, $plan, $feed, $entry, $form, $trial_period_days );
		if ( $this->has_credit_card_field( $form ) || $this->is_terawallet_checkout_enabled() ) {
			$update_subscription = rgar( $subscription_params, 'id' ) ? true : false;

			if ( $update_subscription ) {
				$subscription = $this->api->update_subscription( rgar( $subscription_params, 'id' ), $subscription_params );
			} else {
				$subscription = $this->api->create_subscription( $subscription_params );
			}

			if ( ! is_wp_error( $subscription ) ) {
				return $subscription->id;
			} else {
				$this->log_error( __METHOD__ . '(): A terawallet API error occurs; ' . $subscription->get_error_message() );
			}
		} else {
			$subscription = $this->api->create_subscription( $subscription_params );

			if ( ! is_wp_error( $subscription ) ) {
				return $this->handle_subscription_payment( $subscription );
			} else {
				$this->log_error( __METHOD__ . '(): A terawallet API error occurs; ' . $subscription->get_error_message() );
			}
		}

		return $this->authorization_error( $subscription->get_error_message() );
	}

	/**
	 * Handle subscription payment intent.
	 *
	 * @since 3.3
	 *
	 * @param \terawallet\Subscription|array $subscription The subscription object.
	 * @param \terawallet\Invoice|null|array $invoice      The invoice object.
	 *
	 * @return int|array
	 */
	public function handle_subscription_payment( $subscription, $invoice = null ) {
		if ( empty( $invoice ) ) {
			$payment_intent = rgars( $subscription, 'latest_invoice/payment_intent' );
		} else {
			$payment_intent = rgar( $invoice, 'payment_intent' );
		}

		if ( rgar( $subscription, 'status' ) === 'active' || rgar( $subscription, 'status' ) === 'trialing' ) {
			return $subscription->id;
		} elseif ( rgar( $subscription, 'status' ) === 'incomplete' ) {
			$terawallet_response = $this->get_terawallet_js_response();

			if ( rgar( $payment_intent, 'status' ) === 'requires_payment_method' ) {
				return $this->authorization_error( '<span class="requires_payment_method">' . esc_html__( 'Your payment attempt has failed. Please enter your card details and try again.', 'gravityformsterawallet' ) . '</span>' );
			} elseif ( rgar( $payment_intent, 'status' ) === 'requires_action' ) {
				$_POST['terawallet_response'] = json_encode(
					array(
						'id'            => $terawallet_response->id,
						'client_secret' => $payment_intent->client_secret,
						'amount'        => $payment_intent->amount,
						'subscription'  => rgar( $subscription, 'id' ),
					)
				);

				$error = $this->authorization_error( '<span class="requires_action">' . esc_html__( '3D Secure authentication is required for this payment. Following the instructions on the page to move forward.', 'gravityformsterawallet' ) . '</span>' );

				return array_merge( $error, array( 'requires_action' => true ) );
			}
		}
	}

	/**
	 * Gets the terawallet subscription object for the given ID.
	 *
	 * @deprecated 3.5 Use $this->api->get_subscription() instead.
	 *
	 * @since 2.8
	 *
	 * @param string $subscription_id The subscription ID.
	 *
	 * @return bool|\terawallet\Subscription
	 */
	public function get_subscription( $subscription_id ) {

		$this->log_debug( __METHOD__ . '(): Getting subscription ' . $subscription_id );

		try {

			$subscription = \terawallet\Subscription::retrieve( $subscription_id );

		} catch ( \Exception $e ) {

			$this->log_error( __METHOD__ . '(): Unable to get subscription; ' . $e->getMessage() );
			$subscription = false;
		}

		return $subscription;
	}

	/**
	 * Retrieve the specified terawallet Event.
	 *
	 * @since   Unknown
	 * @access  public
	 *
	 * @used-by GFterawallet::callback()
	 * @uses    GFterawallet::include_terawallet_api()
	 * @uses    \terawallet\Event::retrieve()
	 *
	 * @param string      $event_id terawallet Event ID.
	 * @param null|string $mode     The API mode; live or test.
	 *
	 * @return WP_Error|\terawallet\Event The terawallet event object.
	 */
	public function get_terawallet_event( $event_id, $mode = null ) {
		// Include terawallet API library.
		$this->include_terawallet_api( $mode );

		$event = $this->api->get_event( $event_id );

		return $event;
	}

	/**
	 * Get terawallet account display name.
	 *
	 * @since 2.8
	 *
	 * @param array       $settings Settings.
	 * @param string|null $mode API mode.
	 *
	 * @return terawallet\Account|WP_Error object.
	 */
	public function get_terawallet_account( $settings, $mode = null ) {
		$this->include_terawallet_api( $mode, $settings );

		return $this->api->get_account();
	}

	/**
	 * If custom meta data has been configured on the feed retrieve the mapped field values.
	 *
	 * @since  Unknown
	 * @access public
	 *
	 * @used-by GFterawallet::authorize_product()
	 * @used-by GFterawallet::capture()
	 * @used-by GFterawallet::process_subscription()
	 * @used-by GFterawallet::subscribe()
	 * @uses    GFAddOn::get_field_value()
	 *
	 * @param array $feed  The feed object currently being processed.
	 * @param array $entry The entry object currently being processed.
	 * @param array $form  The form object currently being processed.
	 *
	 * @return array The terawallet meta data.
	 */
	public function get_terawallet_meta_data( $feed, $entry, $form ) {

		// Initialize metadata array.
		$metadata = array();

		// Find feed metadata.
		$custom_meta = rgars( $feed, 'meta/metaData' );

		if ( is_array( $custom_meta ) ) {

			// Loop through custom meta and add to metadata for terawallet.
			foreach ( $custom_meta as $meta ) {

				// If custom key or value are empty, skip meta.
				if ( empty( $meta['custom_key'] ) || empty( $meta['value'] ) ) {
					continue;
				}

				// Make the key available to the gform_terawallet_field_value filter.
				$this->_current_meta_key = $meta['custom_key'];

				// Get field value for meta key.
				$field_value = $this->get_field_value( $form, $entry, $meta['value'] );

				if ( ! empty( $field_value ) ) {

					// Trim to 500 characters, per terawallet requirement.
					$field_value = substr( $field_value, 0, 500 );

					// Add to metadata array.
					$metadata[ $meta['custom_key'] ] = $field_value;
				}
			}

			if ( ! empty( $metadata ) ) {
				$this->log_debug( __METHOD__ . '(): ' . json_encode( $metadata ) );
			}

			// Clear the key in case get_field_value() and gform_terawallet_field_value are used elsewhere.
			$this->_current_meta_key = '';

		}

		return $metadata;

	}

	/**
	 * Check if a terawallet.js has an error or is missing the ID and then return the appropriate message.
	 *
	 * @since  Unknown
	 * @access public
	 *
	 * @used-by GFterawallet::authorize()
	 * @used-by GFterawallet::subscribe()
	 * @uses    GFterawallet::get_terawallet_js_response()
	 *
	 * @return bool|string The error. False if the error does not exist.
	 */
	public function get_terawallet_js_error() {

		// Get terawallet.js response.
		$response = $this->get_terawallet_js_response();

		// If an error message is provided, return error message.
		if ( isset( $response->error ) ) {
			return $response->error->message;
		} elseif ( empty( $response->id ) && ! $this->is_terawallet_checkout_enabled() ) {
			return esc_html__( 'Unable to authorize card. No response from terawallet.js.', 'gravityformsterawallet' );
		}

		return false;

	}

	/**
	 * Response from terawallet.js is posted to the server as 'terawallet_response'.
	 *
	 * @since Unknown
	 * @access public
	 *
	 * @used-by GFterawallet::add_terawallet_inputs()
	 * @used-by GFterawallet::authorize_product()
	 * @used-by GFterawallet::get_terawallet_js_error()
	 * @used-by GFterawallet::subscribe()
	 *
	 * @return \terawallet\Token|null A valid terawallet response object or null
	 */
	public function get_terawallet_js_response() {

		$response = json_decode( rgpost( 'terawallet_response' ) );

		if ( isset( $response->token ) ) {
			$response->id = $response->token->id;
		} elseif ( isset( $response->paymentMethod ) ) {
			$response->id = $response->paymentMethod->id;
		}

		return $response;

	}

	/**
	 * Include the terawallet API and set the current API key.
	 *
	 * @since   Unknown
	 * @since   2.8     Added $feed param.
	 * @since   3.3     Set to use API version 2019-10-17.
	 * @since   3.4     Use the new GF_terawallet_API class.
	 * @access  public
	 *
	 * @used-by GFterawallet::ajax_validate_secret_key()
	 * @used-by GFterawallet::authorize()
	 * @used-by GFterawallet::cancel()
	 * @used-by GFterawallet::get_terawallet_event()
	 * @used-by GFterawallet::subscribe()
	 * @uses    GFAddOn::get_base_path()
	 * @uses    \terawallet\terawallet::setApiKey()
	 * @uses    GFterawallet::get_secret_api_key()
	 *
	 * @param null|string $mode The API mode; live or test.
	 * @param null|array  $settings The settings.
	 */
	public function include_terawallet_api( $mode = null, $settings = null ) {
		if ( empty( $mode ) && empty( $settings ) ) {
			$settings = $this->get_plugin_settings();
			$mode     = $this->get_api_mode( $settings );
		}

		// Load the terawallet API library.
		if ( ! class_exists( 'GF_terawallet_API' ) ) {
			require_once 'includes/class-gf-terawallet-api.php';
		}

		$this->log_debug( sprintf( '%s(): Initializing terawallet API for %s mode.', __METHOD__, $mode ) );

		$terawallet = new GF_terawallet_API( $this->get_secret_api_key( $mode, $settings ) );

		/**
		 * Run post terawallet API initialization action.
		 *
		 * @since 2.0.10
		 */
		do_action( 'gform_terawallet_post_include_api' );

		// Assign the terawallet API object to this instance.
		$this->api = $terawallet;

	}

	/**
	 * Get success URL for terawallet Session.
	 *
	 * The URL structure and thank you page pattern was created in the PayPal add-on and we borrow it here.
	 *
	 * @since 3.0
	 *
	 * @param int $form_id Form ID.
	 * @param int $feed_id Feed ID.
	 *
	 * @return string
	 */
	public function get_success_url( $form_id, $feed_id ) {
		$page_url = GFCommon::is_ssl() ? 'https://' : 'http://';

		/**
		 * Set the terawallet URL port if it's not 80.
		 *
		 * @since 3.0
		 *
		 * @param string Default server port.
		 */
		$server_port = apply_filters( 'gform_terawallet_url_port', $_SERVER['SERVER_PORT'] );

		if ( $server_port != '80' && $server_port != '443' ) {
			$page_url .= $_SERVER['SERVER_NAME'] . ':' . $server_port . $_SERVER['REQUEST_URI'];
		} else {
			$page_url .= $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
		}

		$ids_query = "ids={$form_id}|{$feed_id}";
		$ids_query .= '&hash=' . wp_hash( $ids_query );

		$url = add_query_arg( 'gf_terawallet_success', base64_encode( $ids_query ), $page_url );
		// Add session id template to success url, terawallet will convert it to the real value.
		$url = add_query_arg( 'gf_terawallet_session_id', '{CHECKOUT_SESSION_ID}', $url );

		// We will detect gf_terawallet_success in the URL param and display a thank you page (confirmation) later.
		$query = 'gf_terawallet_success=' . base64_encode( $ids_query ) . '&gf_terawallet_session_id={CHECKOUT_SESSION_ID}';

		/**
		 * Filters terawallet Session's success URL, which is the URL that users will be sent to after completing the payment on terawallet.
		 *
		 * @since 3.0
		 *
		 * @param string $url     The URL to be filtered.
		 * @param int    $form_id The ID of the form being submitted.
		 * @param string $query   The query string portion of the URL.
		 */
		return apply_filters( 'gform_terawallet_success_url', $url, $form_id, $query );
	}

	/**
	 * Get cancel URL for terawallet Session.
	 *
	 * @since 3.0
	 *
	 * @param int $form_id Form ID.
	 *
	 * @return string
	 */
	public function get_cancel_url( $form_id ) {
		$page_url = GFCommon::is_ssl() ? 'https://' : 'http://';

		$server_port = apply_filters( 'gform_terawallet_url_port', $_SERVER['SERVER_PORT'] );

		if ( $server_port != '80' ) {
			$page_url .= $_SERVER['SERVER_NAME'] . ':' . $server_port . $_SERVER['REQUEST_URI'];
		} else {
			$page_url .= $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
		}

		/**
		 * Filters terawallet Session's cancel URL, which is the URL that users will be sent to after canceling the payment on terawallet.
		 *
		 * @since 3.0
		 *
		 * @param string $url     The URL to be filtered.
		 * @param int    $form_id The ID of the form being submitted.
		 */
		return apply_filters( 'gform_terawallet_cancel_url', $page_url, $form_id );
	}





	// # WEBHOOKS ------------------------------------------------------------------------------------------------------

	/**
	 * If the terawallet webhook belongs to a valid entry process the raw response into a standard Gravity Forms $action.
	 *
	 * @since  Unknown
	 * @access public
	 *
	 * @uses GFAddOn::get_plugin_settings()
	 * @uses GFterawallet::get_api_mode()
	 * @uses GFterawallet::get_terawallet_event()
	 * @uses GFAddOn::log_error()
	 * @uses GFAddOn::log_debug()
	 * @uses GFPaymentAddOn::get_entry_by_transaction_id()
	 * @uses GFPaymentAddOn::get_amount_import()
	 * @uses GFterawallet::get_subscription_line_item()
	 * @uses GFterawallet::get_captured_payment_note()
	 * @uses GFAPI::get_entry()
	 * @uses GFCommon::to_money()
	 *
	 * @return array|bool|WP_Error Return a valid GF $action or if the webhook can't be processed a WP_Error object or false.
	 */
	public function callback() {

		$event = $this->get_webhook_event();

		if ( ! $event || is_wp_error( $event ) ) {
			return $event;
		}

		// Get event properties.
		$action = $log_details = array( 'id' => $event->id );
		$type   = $event->type;

		$log_details += array(
			'type'                => $type,
			'webhook api_version' => $event->api_version,
		);

		$this->log_debug( __METHOD__ . '() Webhook event details => ' . print_r( $log_details, 1 ) );

		switch ( $type ) {

			case 'charge.expired':
			case 'charge.refunded':
				// try payment intent first.
				$action['transaction_id'] = rgars( $event, 'data/object/payment_intent' );
				$entry_id                 = $this->get_entry_by_transaction_id( $action['transaction_id'] );
				if ( ! $entry_id ) {
					// try charge id.
					$action['transaction_id'] = rgars( $event, 'data/object/id' );
					$entry_id                 = $this->get_entry_by_transaction_id( $action['transaction_id'] );
					if ( ! $entry_id ) {
						return $this->get_entry_not_found_wp_error( 'transaction', $action, $event );
					}
				}

				$entry = GFAPI::get_entry( $entry_id );

				if ( ! $this->is_valid_entry_for_callback( $entry ) ) {
					return $this->get_wrong_feed_wp_error( $entry_id );
				}

				$action['entry_id'] = $entry_id;

				if ( $event->data->object->captured ) {
					$action['type']   = 'refund_payment';
					$action['amount'] = $this->get_amount_import( rgars( $event, 'data/object/amount_refunded' ), $entry['currency'] );
				} else {
					$action['type'] = 'void_authorization';
				}

				break;

			case 'charge.captured':
				if ( $this->is_terawallet_checkout_enabled() ) {
					$action['transaction_id'] = rgars( $event, 'data/object/payment_intent' );

					$entry_id = $this->get_entry_by_transaction_id( $action['transaction_id'] );

					if ( $entry_id ) {
						$entry = GFAPI::get_entry( $entry_id );

						if ( ! $this->is_valid_entry_for_callback( $entry ) ) {
							return $this->get_wrong_feed_wp_error( $entry_id );
						}

						$payment_status = rgar( $entry, 'payment_status' );

						if ( $payment_status === 'Authorized' ) {
							$form = GFAPI::get_form( $entry['form_id'] );
							$feed = $this->get_payment_feed( $entry, $form );

							// Get session.
							$session_id = gform_get_meta( $entry_id, 'terawallet_session_id' );
							$session    = $this->api->get_checkout_session( $session_id );

							if ( is_wp_error( $session ) ) {
								$this->log_error( __METHOD__ . '(): A terawallet API error occurs; ' . $session->get_error_message() );
							} else {
								// Mark authorized payment as Paid.
								$this->log_debug( __METHOD__ . '(): Charge has been captured for entry #' . $entry_id . '. Mark is as paid.' );

								$action['entry_id'] = $entry_id;
								$action['type']     = 'complete_payment';
								$action['amount']   = $this->get_amount_import( rgars( $event, 'data/object/amount' ), $entry['currency'] );

								// Run gform_terawallet_fulfillment hook.
								$this->checkout_fulfillment( $session, $entry, $feed, $form );
							}
						}
					}
				}
				break;

			case 'customer.subscription.deleted':

				$action['subscription_id'] = rgars( $event, 'data/object/id' );
				$entry_id                  = $this->get_entry_by_transaction_id( $action['subscription_id'] );
				if ( ! $entry_id ) {
					return $this->get_entry_not_found_wp_error( 'subscription', $action, $event );
				}

				$entry = GFAPI::get_entry( $entry_id );

				if ( ! $this->is_valid_entry_for_callback( $entry ) ) {
					return $this->get_wrong_feed_wp_error( $entry_id );
				}

				$action['entry_id'] = $entry_id;
				$action['type']     = 'cancel_subscription';
				$action['amount']   = $this->get_amount_import( rgars( $event, 'data/object/plan/amount' ), $entry['currency'] );

				break;

			case 'invoice.payment_succeeded':

				$subscription = $this->get_subscription_line_item( $event );
				if ( ! $subscription ) {
					return new WP_Error( 'invalid_request', sprintf( __( 'Subscription line item not found in request', 'gravityformsterawallet' ) ) );
				}

				$action['subscription_id'] = rgar( $subscription, 'subscription' );
				$entry_id                  = $this->get_entry_by_transaction_id( $action['subscription_id'] );
				if ( ! $entry_id ) {
					return $this->get_entry_not_found_wp_error( 'subscription', $action, $event );
				}

				$entry = GFAPI::get_entry( $entry_id );

				if ( ! $this->is_valid_entry_for_callback( $entry ) ) {
					return $this->get_wrong_feed_wp_error( $entry_id );
				}

				// If it's the first invoice and payment_status is active, it means the subscription has just started
				// when checkout session completed. So don't set action to prevent duplicate notes.
				$number = explode( '-', rgars( $event, 'data/object/number' ) );
				if ( $this->is_terawallet_checkout_enabled() && rgar( $entry, 'payment_status' ) === 'Active' && $number[1] === '0001' ) {
					$action['abort_callback'] = true;
				} else {
					$payment_intent           = rgars( $event, 'data/object/payment_intent' );
					$action['transaction_id'] = empty( $payment_intent ) ? rgars( $event, 'data/object/charge' ) : $payment_intent;
					$action['entry_id']       = $entry_id;
					$action['type']           = 'add_subscription_payment';
					$action['amount']         = $this->get_amount_import( rgars( $event, 'data/object/amount_due' ), $entry['currency'] );

					$action['note'] = '';

					// Get starting balance, assume this balance represents a setup fee or trial.
					$starting_balance = $this->get_amount_import( rgars( $event, 'data/object/starting_balance' ), $entry['currency'] );
					if ( $starting_balance > 0 ) {
						$action['note'] = $this->get_captured_payment_note( $action['entry_id'] ) . ' ';
					}

					$amount_formatted = GFCommon::to_money( $action['amount'], $entry['currency'] );
					$action['note']  .= sprintf( __( 'Subscription payment has been paid. Amount: %s. Subscription Id: %s', 'gravityformsterawallet' ), $amount_formatted, $action['subscription_id'] );

					// Detect the 0002 invoice for subscriptions with trial just ended.
					if ( $this->is_terawallet_checkout_enabled() && rgar( $entry, 'payment_status' ) === 'Active' && $number[1] === '0002' ) {

						$result = $this->api->get_subscription( $action['subscription_id'] );

						if ( ! is_wp_error( $result ) ) {
							$trial_end = rgar( $result, 'trial_end' );
							// After the trial has ended, the invoice created right away will be #0002.
							if ( $trial_end && $trial_end <= time() ) {
								$form = GFAPI::get_form( $entry['form_id'] );
								$feed = $this->get_payment_feed( $entry, $form );

								// Get session.
								$session_id = gform_get_meta( $entry_id, 'terawallet_session_id' );
								$result     = $this->api->get_checkout_session( $session_id );

								if ( ! is_wp_error( $result ) ) {
									// fulfill delayed feeds.
									$this->checkout_fulfillment( $result, $entry, $feed, $form );
								}
							}
						}

						if ( is_wp_error( $result ) ) {
							$this->log_error( __METHOD__ . '(): A terawallet API error occurs; ' . $result->get_error_message() );
						}
					}
				}

				break;

			case 'invoice.payment_failed':

				$subscription = $this->get_subscription_line_item( $event );
				if ( ! $subscription ) {
					return new WP_Error( 'invalid_request', sprintf( __( 'Subscription line item not found in request', 'gravityformsterawallet' ) ) );
				}

				$action['subscription_id'] = rgar( $subscription, 'subscription' );
				$entry_id                  = $this->get_entry_by_transaction_id( $action['subscription_id'] );
				if ( ! $entry_id ) {
					return $this->get_entry_not_found_wp_error( 'subscription', $action, $event );
				}

				$entry = GFAPI::get_entry( $entry_id );

				if ( ! $this->is_valid_entry_for_callback( $entry ) ) {
					return $this->get_wrong_feed_wp_error( $entry_id );
				}

				$action['type']     = 'fail_subscription_payment';
				$action['amount']   = $this->get_amount_import( rgar( $subscription, 'amount' ), $entry['currency'] );
				$action['entry_id'] = $this->get_entry_by_transaction_id( $action['subscription_id'] );

				break;

			case 'checkout.session.completed':
				// support this event so Checkout will return success in less than 10 seconds.
				$session_id = rgars( $event, 'data/object/id' );
				$session    = $this->api->get_checkout_session( $session_id );

				if ( ! is_wp_error( $session ) ) {
					$entries = GFAPI::get_entries(
						null,
						array(
							'field_filters' => array(
								array(
									'key'   => 'terawallet_session_id',
									'value' => $session_id,
								),
							),
						)
					);

					if ( empty( $entries ) ) {
						return $this->get_entry_not_found_wp_error( 'transaction', $action, $event );
					} else {
						$entry = $entries[0];

						if ( ! $this->is_valid_entry_for_callback( $entry ) ) {
							return $this->get_wrong_feed_wp_error( $entry['id'] );
						}

						$form = GFAPI::get_form( $entry['form_id'] );
						$feed = $this->get_payment_feed( $entry, $form );

						$this->log_debug( __METHOD__ . "(): terawallet Checkout session will be completed by the webhook event {$type}." );
						$action = array_merge( $action, $this->complete_checkout_session( $session, $entry, $feed, $form ) );
					}
				} else {
					$this->log_error( __METHOD__ . '(): A terawallet API error occurs; ' . $session->get_error_message() );
				}

				break;

		}

		if ( has_filter( 'gform_terawallet_webhook' ) ) {
			$this->log_debug( __METHOD__ . '(): Executing functions hooked to gform_terawallet_webhook.' );

			/**
			 * Enable support for custom webhook events.
			 *
			 * @since 1.0.0
			 *
			 * @param array         $action An associative array containing the event details.
			 * @param \terawallet\Event $event  The terawallet event object for the webhook which was received.
			 */
			$action = apply_filters( 'gform_terawallet_webhook', $action, $event );
		}

		if ( rgempty( 'entry_id', $action ) ) {
			$this->log_debug( __METHOD__ . '() entry_id not set for callback action; no further processing required.' );

			return false;
		}

		return $action;

	}

	/**
	 * Determines if the supplied entry should be processed by the current callback.
	 *
	 * @since 3.9
	 *
	 * @param array $entry The entry for the webhook event being processed.
	 *
	 * @return bool
	 */
	public function is_valid_entry_for_callback( $entry ) {
		if ( empty( $_GET['fid'] ) ) {
			return true;
		}

		return rgar( $this->get_payment_feed( $entry ), 'id' ) === $_GET['fid'];
	}

	/**
	 * Returns the WP_Error which will be used to terminate the callback when the entry was processed by a different feed.
	 *
	 * @since 3.9
	 *
	 * @param int $entry_id The ID of the entry for the current callback.
	 *
	 * @return WP_Error
	 */
	public function get_wrong_feed_wp_error( $entry_id ) {
		return new WP_Error(
			'wrong_feed_for_entry',
			sprintf( __( 'Entry %d was not processed by feed %d. Webhook cannot be processed.', 'gravityformsterawallet' ), $entry_id, $_GET['fid'] ),
			array( 'status_header' => 200 )
		);
	}

	/**
	 * Get the WP_Error to be returned when the entry is not found.
	 *
	 * @since 2.5.1
	 *
	 * @param string        $type   The type to be included in the error message and when getting the id: transaction or subscription.
	 * @param array         $action An associative array containing the event details.
	 * @param \terawallet\Event $event  The terawallet event object for the webhook which was received.
	 *
	 * @return WP_Error
	 */
	public function get_entry_not_found_wp_error( $type, $action, $event ) {
		$message     = sprintf( __( 'Entry for %s id: %s was not found. Webhook cannot be processed.', 'gravityformsterawallet' ), $type, rgar( $action, $type . '_id' ) );
		$status_code = 200;

		/**
		 * Enables the status code for the entry not found WP_Error to be overridden.
		 *
		 * @since 2.5.1
		 *
		 * @param int           $status_code The status code. Default is 200.
		 * @param array         $action      An associative array containing the event details.
		 * @param \terawallet\Event $event       The terawallet event object for the webhook which was received.
		 */
		$status_code = apply_filters( 'gform_terawallet_entry_not_found_status_code', $status_code, $action, $event );

		return new WP_Error( 'entry_not_found', $message, array( 'status_header' => $status_code ) );
	}

	/**
	 * Retrieve the terawallet Event for the received webhook.
	 *
	 * @since 2.8   Added support for webhooks per feed.
	 * @since 2.3.1
	 *
	 * @return bool|array|WP_Error|\terawallet\Event
	 */
	public function get_webhook_event() {

		$body     = @file_get_contents( 'php://input' );
		$response = json_decode( $body, true );

		if ( empty( $response ) ) {
			return false;
		}

		$mode            = rgempty( 'livemode', $response ) ? 'test' : 'live';
		$feed_id         = intval( rgget( 'fid' ) );
		$feed            = ( ! empty( $feed_id ) ) ? $this->get_feed( $feed_id ) : null;
		$settings        = ( ! empty( $feed ) ) ? $feed['meta'] : null;
		$endpoint_secret = $this->get_webhook_signing_secret( $mode, $settings );
		$error_message   = false;

		$this->log_debug( __METHOD__ . sprintf( '(): Processing %s mode event%s.', $mode, $settings ? " for feed (#{$feed_id} - {$settings['feedName']})" : '' ) );

		$event_id      = rgar( $response, 'id' );
		$is_test_event = 'evt_00000000000000' === $event_id;

		if ( empty( $endpoint_secret ) && ! $is_test_event ) {

			// Use the legacy method for getting the event.
			$event = $this->get_terawallet_event( $event_id, $mode );

		} else {

			$this->include_terawallet_api( $mode, $settings );
			$sig_header = $_SERVER['HTTP_terawallet_SIGNATURE'];
			$event      = $this->api->construct_event( $body, $sig_header, $endpoint_secret );
		}

		if ( is_wp_error( $event ) ) {
			$error_message = $event->get_error_message();
		}

		if ( $error_message ) {
			$this->log_error( __METHOD__ . '(): Unable to retrieve terawallet Event object. ' . $error_message );
			$message = __( 'Invalid request. Webhook could not be processed.', 'gravityformsterawallet' ) . ' ' . $error_message;

			return new WP_Error( 'invalid_request', $message, array( 'status_header' => 400 ) );
		}

		if ( $is_test_event ) {
			return new WP_Error( 'test_webhook_succeeded', __( 'Test webhook succeeded. Your terawallet Account and terawallet Add-On are configured correctly to process webhooks.', 'gravityformsterawallet' ), array( 'status_header' => 200 ) );
		}

		return $event;
	}

	/**
	 * Generate the url terawallet webhooks should be sent to.
	 *
	 * @since  2.8     Added webhooks endpoint for feeds.
	 * @since  Unknown
	 * @access public
	 *
	 * @used-by GFterawallet::get_webhooks_section_description()
	 *
	 * @param int $feed_id The feed id.
	 *
	 * @return string The webhook URL.
	 */
	public function get_webhook_url( $feed_id = null ) {

		$url = home_url( '/', 'https' ) . '?callback=' . $this->_slug;

		if ( ! rgblank( $feed_id ) ) {
			$url .= '&fid=' . $feed_id;
		}

		return $url;

	}

	/**
	 * Helper to check that webhooks are enabled.
	 *
	 * @since  Unknown
	 * @access public
	 *
	 * @used-by GFterawallet::can_create_feed()
	 * @uses    GFAddOn::get_plugin_setting()
	 *
	 * @return bool True if webhook is enabled. False otherwise.
	 */
	public function is_webhook_enabled() {

		return $this->get_plugin_setting( 'webhooks_enabled' ) == true;

	}

	// # HELPER FUNCTIONS ----------------------------------------------------------------------------------------------

	/**
	 * Retrieve the specified api key.
	 *
	 * @since   Unknown
	 * @since   2.8     Add $settings param.
	 *
	 * @access  public
	 *
	 * @used-by GFterawallet::get_publishable_api_key()
	 * @used-by GFterawallet::get_secret_api_key()
	 * @uses    GFterawallet::get_query_string_api_key()
	 * @uses    GFAddOn::get_plugin_settings()
	 * @uses    GFterawallet::get_api_mode()
	 * @uses    GFAddOn::get_setting()
	 *
	 * @param string      $type    The type of key to retrieve.
	 * @param null|string $mode    The API mode; live or test.
	 * @param null|int    $settings The current settings.
	 *
	 * @return string
	 */
	public function get_api_key( $type = 'secret', $mode = null, $settings = null ) {

		// Check for api key in query first; user must be an administrator to use this feature.
		$api_key = $this->get_query_string_api_key( $type );
		if ( $api_key && current_user_can( 'update_core' ) ) {
			return $api_key;
		}

		if ( ! isset( $settings ) ) {
			$settings = $this->get_plugin_settings();

			if ( ! $mode ) {
				// Get API mode.
				$mode = $this->get_api_mode( $settings );
			}
		}

		// Get API key based on current mode and defined type.
		$setting_key = "{$mode}_{$type}_key";
		$api_key     = $this->get_setting( $setting_key, '', $settings );

		return $api_key;

	}

	/**
	 * Helper to implement the gform_terawallet_api_mode filter so the api mode can be overridden.
	 *
	 * @since  Unknown
	 * @since  2.8     Added $feed_id param.
	 *
	 * @access public
	 *
	 * @used-by GFterawallet::get_api_key()
	 * @used-by GFterawallet::callback()
	 * @used-by GFterawallet::can_create_feed()
	 *
	 * @param array $settings The plugin settings.
	 * @param int   $feed_id  Feed ID.
	 *
	 * @return string $api_mode Either live or test.
	 */
	public function get_api_mode( $settings, $feed_id = null ) {
		// Set from POST value.
		if ( rgpost( 'access_token' ) ) {
			$api_mode = ( rgpost( 'livemode' ) === true ) ? 'live' : 'test';
		} else {
			// If the provided settings array has no api_mode or an empty value, for example in the case of an unsaved feed, use the plugin settings api mode.
			$api_mode = rgar( $settings, 'api_mode', $this->get_plugin_setting( 'api_mode' ) );

			// If no api_mode is set, default to test.
			if ( empty( $api_mode ) ) {
				$api_mode = 'test';
			}
		}

		/**
		 * Filters the API mode.
		 *
		 * @since 1.10.1
		 * @since 2.8   Added $feed_id param.
		 *
		 * @param string $api_mode The API mode.
		 * @param int    $feed_id  Feed ID.
		 */
		return apply_filters( 'gform_terawallet_api_mode', $api_mode, $feed_id );

	}

	/**
	 * Retrieve the specified api key from the query string.
	 *
	 * @since  Unknown
	 * @access public
	 *
	 * @used-by GFterawallet::get_api_key()
	 *
	 * @param string $type The type of key to retrieve. Defaults to 'secret'.
	 *
	 * @return string The result of the query string.
	 */
	public function get_query_string_api_key( $type = 'secret' ) {

		return rgget( $type );

	}

	/**
	 * Retrieve the publishable api key.
	 *
	 * @since   Unknown
	 * @since   2.8     Added $settings param.
	 *
	 * @access  public
	 *
	 * @used-by GFterawallet::register_init_scripts()
	 * @uses    GFterawallet::get_api_key()
	 *
	 * @param array|null $settings The current settings.
	 *
	 * @return string The publishable (public) API key.
	 */
	public function get_publishable_api_key( $settings = null ) {

		return $this->get_api_key( 'publishable', $this->get_api_mode( $settings ), $settings );

	}

	/**
	 * Retrieve the secret api key.
	 *
	 * @since   2.8     Added $settings param.
	 * @since   Unknown
	 * @since   2.8     Added $settings param.
	 *
	 * @access  public
	 *
	 * @used-by GFterawallet::include_terawallet_api()
	 * @uses    GFterawallet::get_api_key()
	 *
	 * @param null|string $mode    The API mode; live or test.
	 * @param null|array  $settings The current settings.
	 *
	 * @return string The secret API key.
	 */
	public function get_secret_api_key( $mode = null, $settings = null ) {

		if ( empty( $settings ) ) {
			$settings = $this->get_plugin_settings();
		}

		if ( empty( $mode ) ) {
			$mode = $this->get_api_mode( $settings );
		}

		return $this->get_api_key( 'secret', $mode, $settings );

	}

	/**
	 * Retrieve the webhook signing secret for the specified API mode.
	 *
	 * @since 2.3.1
	 * @since 2.8   Added $settings param.
	 *
	 * @param string     $mode The API mode; live or test.
	 * @param array|null $settings The settings.
	 *
	 * @return string
	 */
	public function get_webhook_signing_secret( $mode, $settings = null ) {

		if ( empty( $settings ) ) {
			$signing_secret = $this->get_plugin_setting( $mode . '_signing_secret' );

			/**
			 * Override the webhook signing secret for the specified API mode.
			 *
			 * @param string     $signing_secret The signing secret to be used when validating received webhooks.
			 * @param string     $mode           The API mode; live or test.
			 * @param GFterawallet   $gfterawallet       GFterawallet class object
			 * @param array|null $settings The settings.
			 *
			 * @since 2.3.1
			 */
			return apply_filters( 'gform_terawallet_webhook_signing_secret', $signing_secret, $mode, $this );
		} else {
			return rgar( $settings, $mode . '_signing_secret' );
		}

	}

	/**
	 * Helper to check that terawallet Checkout is enabled.
	 *
	 * @since  2.6.0
	 * @access public
	 *
	 * @used-by GFterawallet::scripts()
	 * @uses    GFAddOn::get_plugin_setting()
	 *
	 * @return bool True if terawallet Checkout is enabled. False otherwise.
	 */
	public function is_terawallet_checkout_enabled() {

		return $this->get_plugin_setting( 'checkout_method' ) === 'terawallet_checkout' && version_compare( GFFormsModel::get_database_version(), '2.4-beta-1', '>=' );

	}

	/**
	 * Get auth token data from settings.
	 *
	 * @since 2.8
	 *
	 * @param array  $settings Settings.
	 * @param string $mode API mode.
	 *
	 * @return array
	 */
	public function get_auth_token( $settings, $mode ) {
		return rgar( $settings, $mode . '_auth_token' );
	}

	/**
	 * Check if terawallet authentication is valid.
	 *
	 * @since 2.8
	 * @since 3.3 Fix PHP fatal error thrown when deleting test data.
	 *
	 * @param array  $settings Settings.
	 * @param string $mode API mode.
	 *
	 * @return bool|\terawallet\Account
	 */
	public function is_terawallet_auth_valid( $settings, $mode = null ) {
		if ( empty( $mode ) ) {
			$mode = $this->get_api_mode( $settings );
		}

		if ( rgar( $settings, "{$mode}_publishable_key_is_valid" ) && rgar( $settings, "{$mode}_secret_key_is_valid" ) ) {
			$result = $this->get_terawallet_account( $settings, $mode );

			if ( ! is_wp_error( $result ) ) {
				return $result;
			}

			$this->log_error( sprintf( '%s(): Unable to get terawallet account info; %s', __METHOD__, $result->get_error_message() ) );
		}

		return false;
	}

	/**
	 * Get terawallet user id.
	 *
	 * @since 2.8
	 *
	 * @param array       $settings Settings.
	 * @param null|string $mode API mode.
	 *
	 * @return string
	 */
	public function get_terawallet_user_id( $settings, $mode = null ) {
		if ( empty( $mode ) ) {
			$mode = $this->get_api_mode( $settings );
		}

		$auth_token = $this->get_auth_token( $settings, $mode );

		if ( ! rgempty( 'terawallet_user_id', $auth_token ) ) {
			return rgar( $auth_token, 'terawallet_user_id' );
		}

		return '';
	}

	/**
	 * Check if terawallet Connect is enabled. The current terawallet Connect must be disconnected then the filter can work.
	 *
	 * @since 2.8
	 *
	 * @return mixed|void
	 */
	public function is_terawallet_connect_enabled() {
		$settings               = $this->get_plugin_settings();
		$terawallet_user_id         = $this->get_terawallet_user_id( $settings );
		$terawallet_connect_enabled = true;

		if ( rgblank( $terawallet_user_id ) ) {
			/**
			 * If enable terawallet connect.
			 *
			 * @param bool $terawallet_connect_enabled Whether to enable legacy connect.
			 *
			 * @since 2.8
			 */
			$terawallet_connect_enabled = apply_filters( 'gform_terawallet_connect_enabled', $terawallet_connect_enabled );
		}

		return $terawallet_connect_enabled;
	}

	/**
	 * Check if the current feed is terawallet Connect enabled.
	 *
	 * @since 2.8
	 *
	 * @param int $feed_id Feed ID.
	 *
	 * @return bool
	 */
	public function is_feed_terawallet_connect_enabled( $feed_id = null ) {
		$feed = ( empty( $feed_id ) || ! is_numeric( $feed_id ) ) ? $this->get_current_feed() : $this->get_feed( $feed_id );

		if ( ! empty( $feed['meta'] ) && ! rgblank( $this->get_terawallet_user_id( $feed['meta'] ) ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Get capture method (automatic or manual) for the payment intent API.
	 *
	 * @since 3.3
	 *
	 * @param array $feed            The feed object currently being processed.
	 * @param array $submission_data The customer and transaction data.
	 * @param array $form            The form object currently being processed.
	 * @param array $entry           The entry object currently being processed.
	 *
	 * @return string
	 */
	public function get_capture_method( $feed, $submission_data, $form, $entry ) {
		/**
		 * Allow authorization only transactions by preventing the capture request from being made after the entry has been saved.
		 *
		 * @since 2.1.0
		 *
		 * @param bool  $result          Defaults to false, return true to prevent payment being captured.
		 * @param array $feed            The feed object currently being processed.
		 * @param array $submission_data The customer and transaction data.
		 * @param array $form            The form object currently being processed.
		 * @param array $entry           The entry object currently being processed.
		 */
		$authorization_only = apply_filters( 'gform_terawallet_charge_authorization_only', false, $feed, $submission_data, $form, $entry );

		if ( $authorization_only ) {
			$this->log_debug( __METHOD__ . '(): The gform_terawallet_charge_authorization_only filter was used to prevent capture.' );

			return 'manual';
		}

		return 'automatic';
	}

	/**
	 * Revoke token and remove them from Settings.
	 *
	 * @since  2.8
	 */
	public function ajax_deauthorize() {
		check_ajax_referer( 'gf_terawallet_ajax', 'nonce' );

		if ( ! GFCommon::current_user_can_any( $this->_capabilities_settings_page ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Access denied.', 'gravityformsterawallet' ) ) );
		}

		$feed_id  = intval( rgpost( 'fid' ) );
		$api_mode = sanitize_text_field( rgpost( 'mode' ) );

		if ( $feed_id ) {
			$settings = $this->get_feed( $feed_id );
			$settings = $settings['meta'];
		} else {
			$settings = $this->get_plugin_settings();
		}

		if ( rgpost( 'scope' ) === 'account' ) {
			$terawallet_user_id = $this->get_terawallet_user_id( $settings, $api_mode );
			if ( ! rgblank( $terawallet_user_id ) ) {
				// Call API to revoke access token.
				$response      = wp_remote_get( add_query_arg( array(
					'terawallet_user_id' => $terawallet_user_id,
					'mode'           => $api_mode,
				), $this->get_gravity_api_url( '/auth/terawallet/deauthorize' ) ) );
				$response_code = wp_remote_retrieve_response_code( $response );

				if ( $response_code === 200 ) {
					$auth_payload = json_decode( wp_remote_retrieve_body( $response ), true );
					$auth_payload = json_decode( base64_decode( $auth_payload['auth_payload'] ), true );
					if ( rgar( $auth_payload, 'terawallet_user_id' ) === $terawallet_user_id ) {
						// Log that we revoked the access token.
						$this->log_debug( __METHOD__ . '(): terawallet access token revoked.' );
					}
				} else {
					// Log that token cannot be revoked.
					$this->log_error( __METHOD__ . '(): Unable to revoke token at terawallet.' );
				}
			}
		}

		// Remove access token from settings.
		if ( ! rgempty( "{$api_mode}_auth_token", $settings ) ) {
			unset( $settings[ "{$api_mode}_auth_token" ] );
		}
		unset( $settings[ "{$api_mode}_secret_key" ] );
		unset( $settings[ "{$api_mode}_secret_key_is_valid" ] );
		unset( $settings[ "{$api_mode}_publishable_key" ] );
		unset( $settings[ "{$api_mode}_publishable_key_is_valid" ] );
		unset( $settings[ "{$api_mode}_signing_secret" ] );
		unset( $settings['webhooks_enabled'] );

		// Call parent method because we don't want to falsely add auth_token back.
		if ( $feed_id ) {
			parent::save_feed_settings( $feed_id, rgpost( 'id' ), $settings );
		} else {
			parent::update_plugin_settings( $settings );
		}

		wp_send_json_success();
	}

	/**
	 * Retrieve the first part of the subscription's entry note.
	 *
	 * @since  Unknown
	 * @access public
	 *
	 * @used-by GFterawallet::callback()
	 * @uses    GFAPI::get_entry()
	 * @uses    GFPaymentAddOn::get_payment_feed()
	 *
	 * @param int $entry_id The ID of the entry currently being processed.
	 *
	 * @return string The payment note. Escaped.
	 */
	public function get_captured_payment_note( $entry_id ) {

		// Get feed for entry.
		$entry = GFAPI::get_entry( $entry_id );
		$feed  = $this->get_payment_feed( $entry );

		// Define note based on if setup fee is enabled.
		if ( rgars( $feed, 'meta/setupFee_enabled' ) ) {
			$note = esc_html__( 'Setup fee has been paid.', 'gravityformsterawallet' );
		} else {
			$note = esc_html__( 'Trial has been paid.', 'gravityformsterawallet' );
		}

		return $note;
	}

	/**
	 * Retrieve the labels for the various card types.
	 *
	 * @since  Unknown
	 * @access public
	 *
	 * @used-by GFterawallet::register_init_scripts()
	 * @uses    GFCommon::get_card_types()
	 *
	 * @return array The card labels available.
	 */
	public function get_card_labels() {

		// Get credit card types.
		$card_types  = GFCommon::get_card_types();

		// Initialize credit card labels array.
		$card_labels = array();

		// Loop through card types.
		foreach ( $card_types as $card_type ) {

			// Add card label for card type.
			$card_labels[ $card_type['slug'] ] = $card_type['name'];

		}

		return $card_labels;

	}

	/**
	 * Get the slug for the card type returned by terawallet.js
	 *
	 * @since  Unknown
	 * @access public
	 *
	 * @used-by GFterawallet::pre_validation()
	 * @uses    GFCommon::get_card_types()
	 *
	 * @param string $type The possible types are "Visa", "MasterCard", "American Express", "Discover", "Diners Club", and "JCB" or "Unknown".
	 *
	 * @return string
	 */
	public function get_card_slug( $type ) {

		// If type is defined, attempt to get card slug.
		if ( $type ) {

			// Get card types.
			$card_types = GFCommon::get_card_types();

			// Loop through card types.
			foreach ( $card_types as $card ) {

				// If the requested card type is equal to the current card's name, return the slug.
				if ( rgar( $card, 'name' ) === $type ) {
					return rgar( $card, 'slug' );
				}

			}

		}

		return $type;

	}

	/**
	 * Populate the $_POST with the last four digits of the card number and card type.
	 *
	 * @since  Unknown
	 * @access public
	 *
	 * @used-by GFterawallet::init()
	 * @uses    GFPaymentAddOn::$is_payment_gateway
	 * @uses    GFPaymentAddOn::get_credit_card_field()
	 *
	 * @param array $form Form object.
	 */
	public function populate_credit_card_last_four( $form ) {

		if ( ! $this->is_payment_gateway || ( $this->is_terawallet_checkout_enabled() && ! $this->has_credit_card_field( $form ) && ! $this->has_terawallet_card_field( $form ) ) ) {
			return;
		}

		if ( $this->has_terawallet_card_field( $form ) ) {
			$cc_field = $this->get_terawallet_card_field( $form );
		} elseif ( $this->has_credit_card_field( $form ) ) {
			$cc_field = $this->get_credit_card_field( $form );
		}

		$_POST[ 'input_' . $cc_field->id . '_1' ] = 'XXXXXXXXXXXX' . rgpost( 'terawallet_credit_card_last_four' );
		$_POST[ 'input_' . $cc_field->id . '_4' ] = rgpost( 'terawallet_credit_card_type' );

	}

	/**
	 * Add credit card warning CSS class for the terawallet Card field.
	 *
	 * @since 2.6
	 *
	 * @param string   $css_class CSS classes.
	 * @param GF_Field $field Field object.
	 * @param array    $form Form array.
	 *
	 * @return string
	 */
	public function terawallet_card_field_css_class( $css_class, $field, $form ) {
		if ( GFFormsModel::get_input_type( $field ) === 'terawallet_creditcard' && ! GFCommon::is_ssl() ) {
			$css_class .= ' gfield_creditcard_warning';
		}

		return $css_class;
	}

	/**
	 * Allows the modification of submitted values of the terawallet Card field before the draft submission is saved.
	 *
	 * @since 2.6
	 *
	 * @param array $submitted_values The submitted values.
	 * @param array $form             The Form object.
	 *
	 * @return array
	 */
	public function terawallet_card_submission_value_pre_save( $submitted_values, $form ) {
		foreach ( $form['fields'] as $field ) {
			if ( $field->type == 'terawallet_payment' ) {
				unset( $submitted_values[ $field->id ] );
			}
		}

		return $submitted_values;
	}

	/**
	 * Populate terawallet Checkout response in a hidden field. Deprecated in 3.0 since we upgraded to use the new
	 * terawallet Checkout.
	 *
	 * @deprecated 3.0
	 *
	 * @since 2.6
	 *
	 * @param string $form The form tag.
	 *
	 * @return string
	 */
	public function populate_terawallet_response( $form ) {

		// If a terawallet response exists, populate it to a hidden field.
		if ( $this->get_terawallet_js_response() ) {
			$form .= '<input type="hidden" name="terawallet_response" id="gf_terawallet_response" value="' . esc_attr( rgpost( 'terawallet_response' ) ) . '" />';
		}

		return $form;
	}

	/**
	 * Add JS call to finish terawallet Checkout process. Hook to `gform_after_submission` action.
	 *
	 * @since 3.0
	 *
	 * @param array $entry The current lead.
	 * @param array $form  The form object.
	 */
	public function terawallet_checkout_redirect_scripts( $entry, $form ) {
		
		if ( ! $this->is_payment_gateway ) {
			return;
		}

		$session_id = gform_get_meta( $entry['id'], 'terawallet_session_id' );
		$feed       = $this->current_feed;

		if ( ! empty( $session_id ) && ! empty( $feed ) ) {
			if ( $this->is_feed_terawallet_connect_enabled( $feed['id'] ) ) {
				$settings = $feed['meta'];
			} else {
				$settings = $this->get_plugin_settings();
			}

			$this->log_debug( __METHOD__ . "(): Outputting scripts for entry #{$entry['id']} for session {$session_id}." );

			// Adding the GF_AJAX_POSTBACK wrapper so AJAX embedded forms can copy the script to the parent (top-level)
			// frame, Safari can only perform the redirection from a top-level frame.
			// For regular embedded forms the wrapper is harmless.
			?>
            <script src="https://js.terawallet.com/v3"></script>
            <div class="GF_AJAX_POSTBACK">
                <p class="gform_terawallet_checkout_message"><?php esc_html_e( 'You\'re being redirected to the hosted Checkout page on terawallet...', 'gravityformsterawallet' ); ?></p>
                <script>
                    var terawallet = terawallet("<?php echo $this->get_publishable_api_key( $settings ); ?>");

                    terawallet.redirectToCheckout({
                        sessionId: "<?php echo $session_id; ?>"
                    }).then(function (result) {
                        console.log(result);
                    });
                </script>
            </div>
			<?php
			exit();
		}
	}

	/**
	 * Add the value of the trialPeriod property to the order data which is to be included in the $submission_data.
	 *
	 * @since  Unknown
	 * @access public
	 *
	 * @used-by GFPaymentAddOn::get_submission_data()
	 * @uses    GFPaymentAddOn::get_order_data()
	 *
	 * @param array $feed  The feed currently being processed.
	 * @param array $form  The form currently being processed.
	 * @param array $entry The entry currently being processed.
	 *
	 * @return array The order data found.
	 */
	public function get_order_data( $feed, $form, $entry ) {

		$order_data          = parent::get_order_data( $feed, $form, $entry );
		$order_data['trial'] = rgars( $feed, 'meta/trialPeriod' );

		return $order_data;

	}

	/**
	 * Return the description to be used with the terawallet charge.
	 *
	 * @since  Unknown
	 * @access public
	 *
	 * @used-by GFterawallet::authorize_product()
	 * @used-by GFterawallet::capture()
	 *
	 * @param array $entry           The entry object currently being processed.
	 * @param array $submission_data The customer and transaction data.
	 * @param array $feed            The feed object currently being processed.
	 *
	 * @return string
	 */
	public function get_payment_description( $entry, $submission_data, $feed ) {

		// Charge description format:
		// Entry ID: 123, Products: Product A, Product B, Product C

		$strings = array();

		if ( $entry['id'] ) {
			$strings['entry_id'] = sprintf( esc_html__( 'Entry ID: %d', 'gravityformsterawallet' ), $entry['id'] );
		}

		$strings['products'] = sprintf(
			_n( 'Product: %s', 'Products: %s', count( $submission_data['line_items'] ), 'gravityformsterawallet' ),
			implode( ', ', wp_list_pluck( $submission_data['line_items'], 'name' ) )
		);

		$description = implode( ', ', $strings );

		/**
		 * Allow the charge description to be overridden.
		 *
		 * @since 1.0.0
		 *
		 * @param string $description     The charge description.
		 * @param array  $strings         Contains the Entry ID and Products. The array which was imploded to create the description.
		 * @param array  $entry           The entry object currently being processed.
		 * @param array  $submission_data The customer and transaction data.
		 * @param array  $feed            The feed object currently being processed.
		 */
		return apply_filters( 'gform_terawallet_charge_description', $description, $strings, $entry, $submission_data, $feed );
	}

	/**
	 * Retrieve the subscription line item from from the terawallet response.
	 *
	 * @since  Unknown
	 * @access public
	 *
	 * @used-by GFterawallet::capture()
	 *
	 * @param \terawallet\Event $response The terawallet webhook response.
	 *
	 * @return bool|array The subscription line items. Returns false if nothing found.
	 */
	public function get_subscription_line_item( $response ) {

		$lines = rgars( $response, 'data/object/lines/data' );

		foreach ( $lines as $line ) {
			if ( 'subscription' === $line['type'] ) {
				return $line;
			}
		}

		return false;
	}

	/**
	 * Generate the subscription plan id.
	 *
	 * @since   2.3.4 Added the $currency param.
	 * @since   Unknown
	 * @access  public
	 *
	 * @used-by GFterawallet::subscribe()
	 *
	 * @param array     $feed              The feed object currently being processed.
	 * @param float|int $payment_amount    The recurring amount.
	 * @param int       $trial_period_days The number of days the trial should last.
	 * @param string    $currency          The currency code for the entry being processed.
	 *
	 * @return string The subscription plan ID, if found.
	 */
	public function get_subscription_plan_id( $feed, $payment_amount, $trial_period_days, $currency = '' ) {

		$subscription_name   = ( rgars( $feed, 'meta/subscription_name' ) ) ? rgars( $feed, 'meta/subscription_name' ) : $feed['meta']['feedName'];
		$safe_feed_name      = preg_replace( '/[^a-z0-9_\-]/', '', strtolower( $subscription_name ) );
		$safe_billing_cycle  = $feed['meta']['billingCycle_length'] . $feed['meta']['billingCycle_unit'];
		$safe_trial_period   = $trial_period_days ? 'trial' . $trial_period_days . 'days' : '';
		$safe_payment_amount = $this->get_amount_export( $payment_amount, $currency );

		/*
		 * Only include the currency code in the plan id when the entry currency does not match the plugin currency.
		 * Ensures the majority of plans created before this change will continue to be used.
		 * https://terawallet.com/docs/subscriptions/plans#working-with-local-currencies
		*/
		if ( ! empty( $currency ) && $currency === GFCommon::get_currency() ) {
			$currency = '';
		}

		$plan_id = implode( '_', array_filter( array( $safe_feed_name, $feed['id'], $safe_billing_cycle, $safe_trial_period, $safe_payment_amount, $currency ) ) );

		$this->log_debug( __METHOD__ . '(): ' . $plan_id );

		return $plan_id;

	}

	/**
	 * Enables use of the gform_terawallet_field_value filter to override the field value.
	 *
	 * @since 2.1.1 Making the $meta_key parameter available to the gform_terawallet_field_value filter.
	 *
	 * @used-by GFAddOn::get_field_value()
	 *
	 * @param string $field_value The field value to be filtered.
	 * @param array  $form        The form currently being processed.
	 * @param array  $entry       The entry currently being processed.
	 * @param string $field_id    The ID of the Field currently being processed.
	 *
	 * @return string
	 */
	public function maybe_override_field_value( $field_value, $form, $entry, $field_id ) {
		$meta_key = $this->_current_meta_key;
		$form_id  = $form['id'];

		/**
		 * Allow the mapped field value to be overridden.
		 *
		 * @since 2.1.1 Added the $meta_key parameter.
		 * @since 1.9.10.11
		 *
		 * @param string $field_value The field value to be filtered.
		 * @param array  $form        The form currently being processed.
		 * @param array  $entry       The entry currently being processed.
		 * @param string $field_id    The ID of the Field currently being processed.
		 * @param string $meta_key    The custom meta key currently being processed.
		 */
		$field_value = apply_filters( 'gform_terawallet_field_value', $field_value, $form, $entry, $field_id, $meta_key );
		$field_value = apply_filters( "gform_terawallet_field_value_{$form_id}", $field_value, $form, $entry, $field_id, $meta_key );
		$field_value = apply_filters( "gform_terawallet_field_value_{$form_id}_{$field_id}", $field_value, $form, $entry, $field_id, $meta_key );

		return $field_value;
	}

	/**
	 * Get terawallet Card field for form.
	 *
	 * @since 2.6
	 *
	 * @param array $form Form object. Defaults to null.
	 *
	 * @return boolean
	 */
	public function has_terawallet_card_field( $form = null ) {
	    // Get form
		if ( is_null( $form ) ) {
			$form = $this->get_current_form();
        }

		return $this->get_terawallet_card_field( $form ) !== false;
	}

	/**
	 * Gets terawallet credit card field object.
	 *
	 * @since 2.6
	 *
	 * @param array $form The Form Object.
	 *
	 * @return bool|GF_Field_terawallet_CreditCard The terawallet card field object, if found. Otherwise, false.
	 */
	public function get_terawallet_card_field( $form ) {
		$fields = GFAPI::get_fields_by_type( $form, array( 'terawallet_creditcard' ) );

		return empty( $fields ) ? false : $fields[0];
	}

	/**
	 * Prepare fields for field mapping in feed settings.
	 *
	 * @since 2.6
	 *
	 * @return array $fields
	 */
	public function billing_info_fields() {
	    $fields = array(
			array(
				'name'       => 'address_line1',
				'label'      => __( 'Address', 'gravityformsterawallet' ),
				'required'   => false,
				'field_type' => array( 'address' ),
			),
			array(
				'name'       => 'address_line2',
				'label'      => __( 'Address 2', 'gravityformsterawallet' ),
				'required'   => false,
				'field_type' => array( 'address' ),
			),
			array(
				'name'       => 'address_city',
				'label'      => __( 'City', 'gravityformsterawallet' ),
				'required'   => false,
				'field_type' => array( 'address' ),
			),
			array(
				'name'       => 'address_state',
				'label'      => __( 'State', 'gravityformsterawallet' ),
				'required'   => false,
				'field_type' => array( 'address' ),
			),
			array(
				'name'       => 'address_zip',
				'label'      => __( 'Zip', 'gravityformsterawallet' ),
				'required'   => false,
				'field_type' => array( 'address' ),
			),
			array(
				'name'       => 'address_country',
				'label'      => __( 'Country', 'gravityformsterawallet' ),
				'required'   => false,
				'field_type' => array( 'address' ),
			),
		);

		return $fields;

	}

	/**
	 * Returns the specified plugin setting.
	 *
	 * @since 2.6.0.1
	 * @since 3.4     Set the default checkout_method to "terawallet_elements".
	 *
	 * @param string $setting_name The setting to be returned.
	 *
	 * @return mixed|string
	 */
	public function get_plugin_setting( $setting_name ) {
		$setting = parent::get_plugin_setting( $setting_name );

		if ( ! $setting && $setting_name === 'checkout_method' ) {
			$setting = 'terawallet_elements';
		}

		return $setting;
	}

	/***
	 * Force Payment Collection Method as "terawallet Elements" in the add-on settings.
	 *
	 * @since 3.4
	 *
	 * @param string     $setting_name  The field or input name.
	 * @param string     $default_value Optional. The default value.
	 * @param bool|array $settings      Optional. THe settings array.
	 *
	 * @return string|array
	 */
	public function get_setting( $setting_name, $default_value = '', $settings = false ) {
		$setting = parent::get_setting( $setting_name, $default_value, $settings );

		if ( $setting_name === 'checkout_method' && $setting === 'credit_card' ) {
			$setting = 'terawallet_elements';
		}

		return $setting;
	}

	/**
	 * Target of gform_before_delete_field hook. Sets relevant payment feeds to inactive when the terawallet Card field is deleted.
	 *
	 * @since 2.6.1
	 *
	 * @param int $form_id ID of the form being edited.
	 * @param int $field_id ID of the field being deleted.
	 */
	public function before_delete_field( $form_id, $field_id ) {
	    parent::before_delete_field( $form_id, $field_id );

	    $form = GFAPI::get_form( $form_id );
		if ( $this->has_terawallet_card_field( $form ) ) {
			$field = $this->get_terawallet_card_field( $form );

			if ( is_object( $field ) && $field->id == $field_id ) {
				$feeds = $this->get_feeds( $form_id );
				foreach ( $feeds as $feed ) {
					if ( $feed['is_active'] ) {
						$this->update_feed_active( $feed['id'], 0 );
					}
				}
			}
		}
	}

	/**
	 * Get Gravity API URL.
	 *
	 * @since 2.8
	 *
	 * @param string $path Path.
	 *
	 * @return string
	 */
	public function get_gravity_api_url( $path = '' ) {
		return ( defined( 'GRAVITY_API_URL' ) ? GRAVITY_API_URL : 'https://gravityapi.com/wp-json/gravityapi/v1' ) . $path;
	}

	/**
	 * Run required routines when upgrading from previous versions of Add-On.
	 *
	 * @since 3.0
	 *
	 * @param string $previous_version Previous version number.
	 */
	public function upgrade( $previous_version ) {

		$this->handle_upgrade_3( $previous_version );
		$this->handle_upgrade_3_2_3( $previous_version );
		$this->handle_upgrade_3_3_3( $previous_version );

	}

	/**
	 * Handle upgrading to 3.0; introduction of SCA.
	 *
	 * @since 3.2.3
	 *
	 * @param string $previous_version Previous version number.
	 */
	public function handle_upgrade_3( $previous_version ) {

		// Determine if previous version is before SCA upgrade.
		$previous_is_pre_sca = ! empty( $previous_version ) && version_compare( $previous_version, '3.0', '<' );

		// If previous version is not before the SCA upgrade, exit.
		if ( ! $previous_is_pre_sca ) {
			return;
		}

		// Get checkout_method.
		$checkout_method = $this->get_plugin_setting( 'checkout_method' );
		if ( $checkout_method === 'terawallet_checkout' ) {
			// let users know they are SCA compliant because they use Checkout.
			$message = sprintf(
				esc_html__( '%1$sYour Gravity Forms terawallet Add-On has been updated to 3.0, and now supports Apple Pay and Strong Customer Authentication (SCA/PSD2).%2$s%3$sNOTE:%4$s terawallet has changed terawallet Checkout from a modal display to a full page, and we have altered some existing terawallet hooks. Carefully review %5$sthis guide%6$s to see if your setup may be affected.%7$s', 'gravityformsterawallet' ),
				'<p>',
				'</p>',
				'<p><b>',
				'</b>',
				'<a href="https://docs.gravityforms.com/changes-to-checkout-with-terawallet-v3/" target="_blank">',
				'</a>',
				'</p>'
			);

		} else {
			// Remind people to switch to Checkout for SCA.
			$message = sprintf(
				esc_html__( '%1$sYour Gravity Forms terawallet Add-On has been updated to 3.0, and now supports Apple Pay and Strong Customer Authentication (SCA/PSD2).%2$s%3$sNOTE:%4$s Apple Pay and SCA are only supported by the terawallet Checkout payment collection method. Refer to %5$sthis guide%6$s for more information on payment methods and SCA.%7$s', 'gravityformsterawallet' ),
				'<p>',
				'</p>',
				'<p><b>',
				'</b>',
				'<a href="https://docs.gravityforms.com/terawallet-support-of-strong-customer-authentication/" target="_blank">',
				'</a>',
				'</p>'
			);
		}

		// Add message.
		GFCommon::add_dismissible_message( $message, 'gravityformsterawallet_upgrade_30', 'warning', $this->_capabilities_form_settings, true, 'site-wide' );

	}

	/**
	 * Handle upgrade to 3.2.3; deletes passwords that GF terawallet 3.2 prevented from being deleted.
	 *
	 * @since 3.2.3
	 *
	 * @param string $previous_version Previous version number.
	 */
	public function handle_upgrade_3_2_3( $previous_version ) {
		global $wpdb;

		if ( version_compare( $previous_version, '3.2.3', '>=' ) || version_compare( $previous_version, '3.2', '<' ) ) {
			return;
		}

		$feeds           = $this->get_feeds();
		$processed_forms = array();

		foreach ( $feeds as $feed ) {

			if ( in_array( $feed['form_id'], $processed_forms ) ) {
				continue;
			} else {
				$processed_forms[] = $feed['form_id'];
			}

			$form            = GFAPI::get_form( $feed['form_id'] );
			$password_fields = GFAPI::get_fields_by_type( $form, 'password' );
			if ( empty( $password_fields ) ) {
				continue;
			}

			$password_field_ids = array_map( 'intval', wp_list_pluck( $password_fields, 'id' ) );
			$sql_field_ids      = implode( ',', $password_field_ids );
			$form_id            = (int) $form['id'];

			$sql = $wpdb->prepare( "
				DELETE FROM {$wpdb->prefix}gf_entry_meta
				WHERE form_id = %d
				AND meta_key IN( {$sql_field_ids} )",
				$form_id
			);

			$wpdb->query( $sql );

			$result = $wpdb->query( $sql );

			$this->log_debug( sprintf( '%s: Deleted %d passwords.', __FUNCTION__, (int) $result ) );

		}

	}

	/**
	 * Handle upgrading to 3.4; introduction of SCA in the terawallet Checkout and remove the CC field support for new installs.
	 *
	 * @since 3.4
	 *
	 * @param string $previous_version Previous version number.
	 */
	public function handle_upgrade_3_3_3( $previous_version ) {

		// Determine if previous version is before v3.3.3.
		$previous_is_pre_333 = ! empty( $previous_version ) && version_compare( $previous_version, '3.3.3', '<' );

		// If previous version is not before the v3.3.3, exit.
		if ( ! $previous_is_pre_333 ) {
			return;
		}

		// Get checkout_method.
		$checkout_method = $this->get_plugin_setting( 'checkout_method' );
		if ( $checkout_method === 'terawallet_elements' ) {
			// let users know they are SCA compliant because they use Elements.
			$message = sprintf(
				esc_html__( '%1$sYour Gravity Forms terawallet Add-On has been updated to 3.4, and now supports Strong Customer Authentication (SCA/PSD2).%2$s%3$sRefer to %4$sthis guide%5$s for more information on payment methods and SCA.%6$s', 'gravityformsterawallet' ),
				'<p>',
				'</p>',
				'<p>',
				'<a href="https://docs.gravityforms.com/terawallet-support-of-strong-customer-authentication/" target="_blank">',
				'</a>',
				'</p>'
			);
		} elseif ( $checkout_method === 'credit_card' ) {
			// let users know the Credit Card payment method has been deprecated.
			$message = sprintf(
				esc_html__( '%1$sYour Gravity Forms terawallet Add-On has been updated to 3.4, and it no longer supports the Gravity Forms Credit Card Field in new forms (current integrations can still work as usual).%2$s%3$sRefer to %4$sthis guide%5$s for more information about this change.%6$s', 'gravityformsterawallet' ),
				'<p>',
				'</p>',
				'<p>',
				'<a href="https://docs.gravityforms.com/deprecation-of-the-gravity-forms-credit-card-field/" target="_blank">',
				'</a>',
				'</p>'
			);
		}

		if ( isset( $message ) ) {
			GFCommon::add_dismissible_message( $message, 'gravityformsterawallet_upgrade_333', 'warning', $this->_capabilities_form_settings, true, 'site-wide' );
		}
	}

	/**
	 * Get post payment actions config.
	 *
	 * @since 3.1
	 *
	 * @param string $feed_slug The feed slug.
	 *
	 * @return array
	 */
	public function get_post_payment_actions_config( $feed_slug ) {
		// Support post payment action only for terawallet Checkout.
		if ( ! $this->is_terawallet_checkout_enabled() || ( $this->has_terawallet_card_field() || $this->has_credit_card_field( $this->get_current_form() ) ) ) {
			return array();
		}

		return array(
			'position' => 'before',
			'setting'  => 'conditionalLogic',
		);
	}

	/**
	 * AJAX helper function to create a payment intent on the server.
	 *
	 * @since 3.3
	 */
	public function create_payment_intent() {
		check_ajax_referer( 'gf_terawallet_create_payment_intent', 'nonce' );

		$feed = $this->get_feed( intval( rgpost( 'feed_id' ) ) );

		if ( $this->is_feed_terawallet_connect_enabled( $feed['id'] ) ) {
			$this->include_terawallet_api( $this->get_api_mode( $feed['meta'], $feed['id'] ), $feed['meta'] );
		} else {
			$this->include_terawallet_api();
		}

		$payment_method = rgpost( 'payment_method' );

		$data = [
			'payment_method'      => rgar( $payment_method, 'id' ),
			'amount'              => intval( rgpost( 'amount' ) ),
			'currency'            => sanitize_text_field( rgpost( 'currency' ) ),
			'capture_method'      => 'manual', // Use manual capture by default, because we cannot update the capture_method after the payment intent crated.
			'confirmation_method' => 'manual',
			'confirm'             => false,
		];

		/**
		 * Filter to change the payment intent data before creating it.
		 *
		 * @since 3.5
		 *
		 * @param array $data The payment intent data.
		 * @param array $feed The feed object.
		 */
		$data = apply_filters( 'gform_terawallet_payment_intent_pre_create', $data, $feed );

		$intent = $this->api->create_payment_intent( $data );

		if ( $intent->status === 'requires_confirmation' ) {
			wp_send_json_success(
				array(
					'id'            => $intent->id,
					'client_secret' => $intent->client_secret,
					'amount'        => $intent->amount,
				)
			);
		} else {
			/* translators: PaymentIntent status. */
			$error = sprintf( esc_html__( 'PaymentIntent status: %s is invalid.', 'gravityformsterawallet' ), $intent->status );
			wp_send_json_error( array( 'message' => $error ) );
		}
	}

	/**
	 * AJAX helper function to update a payment intent on the server.
	 *
	 * @since 3.3
	 */
	public function update_payment_intent() {
		check_ajax_referer( 'gf_terawallet_create_payment_intent', 'nonce' );

		$feed = $this->get_feed( intval( rgpost( 'feed_id' ) ) );

		if ( $this->is_feed_terawallet_connect_enabled( $feed['id'] ) ) {
			$this->include_terawallet_api( $this->get_api_mode( $feed['meta'], $feed['id'] ), $feed['meta'] );
		} else {
			$this->include_terawallet_api();
		}

		$payment_intent = sanitize_text_field( rgpost( 'payment_intent' ) );
		$payment_method = rgpost( 'payment_method' );

		$data = array(
			'amount'   => intval( rgpost( 'amount' ) ),
			'currency' => sanitize_text_field( rgpost( 'currency' ) ),
		);

		if ( ! empty( $payment_method ) ) {
			$data['payment_method'] = rgar( $payment_method, 'id' );
		}

		$intent = $this->api->update_payment_intent( $payment_intent, $data );

		if ( $intent->status === 'requires_confirmation' ) {
			wp_send_json_success(
				array(
					'id'            => $intent->id,
					'client_secret' => $intent->client_secret,
					'amount'        => $intent->amount,
				)
			);
		} else {
			/* translators: PaymentIntent status. */
			$error = sprintf( esc_html__( 'PaymentIntent status: %s is invalid.', 'gravityformsterawallet' ), $intent->status );
			wp_send_json_error( array( 'message' => $error ) );
		}
	}

	/**
	 * Turn country into two digits for terawallet Elements.
	 *
	 * @since 3.3
	 */
	public function get_country_code() {
		check_ajax_referer( 'gf_terawallet_create_payment_intent', 'nonce' );

		$feed            = $this->get_feed( intval( rgpost( 'feed_id' ) ) );
		$billing_country = rgars( $feed, 'meta/billingInformation_address_country' );
		$country         = sanitize_text_field( rgpost( 'country' ) );
		$code            = $country;

		if ( ! empty( $billing_country ) && strlen( $country ) > 2 ) {
			$code = GF_Fields::get( 'address' )->get_country_code( $country );
		}

		wp_send_json_success( array( 'code' => $code ) );
	}

	/**
	 * Alter product feeds payment data before sent with terawallet API.
	 *
	 * The filter was created to support the Charge API in 2.2.2, and updated to support the payment intents API in 3.3.
	 *
	 * @since 3.3
	 *
	 * @param array $payment_data     The properties for the charge to be created.
	 * @param array $feed            The feed object currently being processed.
	 * @param array $submission_data The customer and transaction data.
	 * @param array $form            The form object currently being processed.
	 * @param array $entry           The entry object currently being processed.
	 *
	 * @return array
	 */
	public function get_product_payment_data( $payment_data, $feed, $submission_data, $form, $entry ) {

		/**
		 * Allow the charge properties to be overridden before the charge is created by the terawallet API.
		 *
		 * @param array $payment_data The properties for the charge to be created.
		 * @param array $feed The feed object currently being processed.
		 * @param array $submission_data The customer and transaction data.
		 * @param array $form The form object currently being processed.
		 * @param array $entry The entry object currently being processed.
		 *
		 * @since 2.2.2
		 */
		$payment_data = apply_filters( 'gform_terawallet_charge_pre_create', $payment_data, $feed, $submission_data, $form, $entry );

		return $payment_data;
	}

	/**
	 * Filter subscription parameters when creating or updating a subscription.
	 *
	 * @since 3.4
	 *
	 * @param array            $subscription_params The subscription parameters.
	 * @param \terawallet\Customer $customer            The terawallet customer object.
	 * @param \terawallet\Plan     $plan                The terawallet plan object.
	 * @param array            $feed                The feed currently being processed.
	 * @param array            $entry               The entry currently being processed.
	 * @param array            $form                The form which created the current entry.
	 * @param int              $trial_period_days   The number of days the trial should last.
	 *
	 * @return array
	 */
	public function get_subscription_params( $subscription_params, $customer, $plan, $feed, $entry, $form, $trial_period_days ) {
		if ( has_filter( 'gform_terawallet_subscription_params_pre_update_customer' ) ) {
			/**
			 * Allow the subscription parameters to be overridden before the customer is subscribed to the plan.
			 *
			 * @since 2.3.4
			 * @since 2.5.2 Added the $trial_period_days param.
			 *
			 * @param array            $subscription_params The subscription parameters.
			 * @param \terawallet\Customer $customer            The terawallet customer object.
			 * @param \terawallet\Plan     $plan                The terawallet plan object.
			 * @param array            $feed                The feed currently being processed.
			 * @param array            $entry               The entry currently being processed.
			 * @param array            $form                The form which created the current entry.
			 * @param int              $trial_period_days   The number of days the trial should last.
			 */
			$subscription_params = apply_filters( 'gform_terawallet_subscription_params_pre_update_customer', $subscription_params, $customer, $plan, $feed, $entry, $form, $trial_period_days );

			// Backwards compatibility.
			if ( rgar( $subscription_params, 'plan' ) && empty( $subscription_params['items'] ) ) {
				$subscription_params['items'] = array( 'plan' => $plan->id );
				unset( $subscription_params['plan'] );
				$this->log_debug( __METHOD__ . '(): Change subscription parameters "plan" to "items[\'plan\']"' );
			}

			$this->log_debug( __METHOD__ . '(): Subscription parameters => ' . print_r( $subscription_params, 1 ) );
		}

		return $subscription_params;
	}

	/**
	 * Check if rate limits is enabled.
	 *
	 * @since 3.4
	 *
	 * @param int $form_id The form ID.
	 *
	 * @return bool
	 */
	public function is_rate_limits_enabled( $form_id ) {
		/**
		 * Allow enabling or disable the rate limit check.
		 *
		 * @since 3.4
		 *
		 * @param bool $has_error The default is false.
		 * @param int  $form_id   The form ID.
		 */
		$this->_enable_rate_limits = apply_filters( 'gform_terawallet_enable_rate_limits', $this->_enable_rate_limits, $form_id );

		return $this->_enable_rate_limits;
	}

	/**
	 * Update card error count for the current IP.
	 *
	 * @since 3.4
	 *
	 * @param bool $increase_count The default is false.
	 *
	 * @return bool
	 */
	public function get_card_error_count( $increase_count = false ) {
		$ip = GFFormsModel::get_ip();

		if ( empty( $ip ) ) {
			// It can return a comma separated list of IPs; use the first one.
			$ips = explode( ',', rgar( $_SERVER, 'REMOTE_ADDR' ) );
			$ip  = $ips[0];
		}

		$key         = $this->get_slug() . '_card_error_' . wp_hash( $ip );
		$error_count = (int) get_transient( $key );

		if ( $increase_count ) {
			$error_count ++;
			set_transient( $key, $error_count, HOUR_IN_SECONDS );
		}

		return $error_count;
	}

	/**
	 * Check if the current IP has hit the error rate limits.
	 *
	 * @since 3.4
	 *
	 * @param int $form_id The form ID.
	 *
	 * @return bool|array
	 */
	public function maybe_hit_rate_limits( $form_id ) {
		if ( ! $this->is_rate_limits_enabled( $form_id ) ) {
			return false;
		}

		$error_count     = $this->get_card_error_count();
		$max_error_count = 5;
		if ( $error_count >= $max_error_count ) {
			$this->log_debug( __METHOD__ . '(): The current IP has hit the error rate limit. Block payments from it for an hour' );

			return array(
				'error_message' => esc_html__( 'We are not able to process your payment request at the moment. Please try again later.', 'gravityformsterawallet' ),
				'is_success'    => false,
				'is_authorized' => false,
			);
		}

		$this->log_debug( __METHOD__ . '(): The current IP has card errors in total of ' . $error_count . ' times' );

		return false;
	}

	/**
	 * Get the authentication state, which was created from a wp nonce.
	 *
	 * @since 3.5.1
	 *
	 * @return string
	 */
	public function get_authentication_state_action() {
		return 'gform_terawallet_authentication_state';
	}

	/**
	 * Retreives terawallet account display name.
	 *
	 * @param \terawallet\Account $terawallet_account terawallet account object.
	 *
	 * @since 3.8
	 *
	 * @return string
	 */
	private function get_terawallet_display_name( $terawallet_account ) {
		$display_name = rgars( $terawallet_account, 'settings/dashboard/display_name' );
		// in some cases it is possible to have an account with any empty display name, terawallet call it unnamed account.
		return ! empty( $display_name ) ? $display_name : esc_html__( 'Unnamed account', 'gravityformsterawallet' );
	}

}
