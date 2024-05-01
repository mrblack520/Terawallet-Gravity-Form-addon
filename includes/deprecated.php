<?php

defined( 'ABSPATH' ) || die();

if ( ! class_exists( 'terawallet_InvoiceItem' ) ) {
	/**
	 * Class terawallet_InvoiceItem
	 *
	 * Used in the example for the gform_terawallet_customer_after_create hook.
	 *
	 * @deprecated
	 */
	class terawallet_InvoiceItem extends \terawallet\InvoiceItem {}
}

if ( ! class_exists( 'terawallet_Charge' ) ) {
	/**
	 * Class terawallet_Charge
	 *
	 * @deprecated
	 */
	class terawallet_Charge extends \terawallet\Charge {}
}
