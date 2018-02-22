<?php
/**
 * Plugin Name: WooCommerce Wholesale Pricing
 * Version: 1.4
 * Description: A WooCommerce extention that gives an ability to your store to better success with wholesale pricing. You can easily manage your existing store with wholesale pricing. Just you need to add a wholesaler customer by selecting his role "Wholesaler", wholesale pricing is not view for public users only wholesaler customer can see them.
 * Author: wpexpertsio
 * Author URI: https://wpexperts.io/
 */
 
// Class To Implement Wholesale Functionality
include 'inc/class-woo-wholesale.php';
// WooCommerce Wholesale Tab
include 'inc/class-woo-wholesale-pro.php';
// Class Woo Wholesale Functions
include 'inc/class-woo-wholesale-functions.php';
$woo_wholesale_functions = new Woo_Wholesale_Functions();

// To initiate Woo_Easy_Wholesale Object
$woo_wholesale = new Woo_Easy_Wholesale();

$woo_wholesale_pro = new Woo_Wholesale_Pro();

function wwp_script_style() {
    wp_enqueue_script( 'wwp-script', plugin_dir_url( __FILE__ ) . 'js/script.js', array(), '1.0.0', true );
}
add_action( 'wp_enqueue_scripts', 'wwp_script_style' );

function wwp_admin_script_style() {
    wp_enqueue_style( 'wwp-admin-style', plugin_dir_url( __FILE__ ) . 'css/wwp-admin-style.css' );
    wp_enqueue_style( 'wwp-admin-roboto-style', 'https://fonts.googleapis.com/css?family=Roboto:100,300' );
}
add_action( 'admin_enqueue_scripts', 'wwp_admin_script_style' );