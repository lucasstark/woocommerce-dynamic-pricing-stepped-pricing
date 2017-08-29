<?php

/**
 * Plugin Name:  WooCommerce Dynamic Pricing - Stepped Pricing
 */
class WC_Dynamic_Pricing_Stepped_Pricing {
	private static $instance;

	public static function register() {
		if ( self::$instance == null ) {
			self::$instance = new WC_Dynamic_Pricing_Stepped_Pricing();
		}
	}

	private $_module;

	public function __construct() {
		add_filter( 'wc_dynamic_pricing_load_modules', array( $this, 'inject_module' ) );
	}

	public function on_plugins_loaded() {


	}

	public function inject_module( $modules ) {
		if ( $this->_module == null ) {
			require_once 'inc/class-wc-dynamic-pricing-advanced-category-stepped.php';
			$this->_module = WC_Dynamic_Pricing_Advanced_Category_Stepped::instance();
		}

		$modules['advanced_category'] = $this->_module;
		return $modules;
	}


}


WC_Dynamic_Pricing_Stepped_Pricing::register();