<?php
/**
 * Class To Add Wholesale Functionality with WooCommerce
 */
class Woo_Wholesale_Functions {

    function __construct() {
        add_action('init', array($this,'wwp_load_translation'));
		add_filter( 'woocommerce_package_rates', array($this,'wwp_apply_free_shipping_if_valid_coupon'), 100 );
    }

    function wwp_load_translation() { // Load Translation
        //print_r(plugins_url().'/woo-wholesale-pricing/lang/');
        /*load_plugin_textdomain('wwp_wholesale', FALSE, plugin_basename( wwp_plugin_directory_name ) . '/lang/');*/
        load_plugin_textdomain('wwp_wholesale', FALSE, plugins_url().'/woo-wholesale-pricing/lang/');
    }
	function wwp_apply_free_shipping_if_valid_coupon( $rates ) { // Enable Free Shipping When Available with coupon
	
		global $woocommerce;
		
		$free = array();
		
		foreach($woocommerce->cart->applied_coupons as $coupon) {
			$page = get_page_by_title($coupon,'','shop_coupon');			
			$coupon = new WC_Coupon( $page->ID );
			if( $coupon->get_free_shipping() ){		
				foreach ( $rates as $rate_id => $rate ) {			
					if ( 'flat_rate' === $rate->method_id ) {
						$rate->label = 'Free Shipping';
						$rate->cost = 0.00;
						$free[ $rate_id ] = $rate;
						break;
					}
				}
			}
		}
		
		return ! empty( $free ) ? $free : $rates;
	}
}