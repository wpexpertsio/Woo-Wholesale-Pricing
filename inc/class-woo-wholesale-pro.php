<?php

Class Woo_Wholesale_Pro {
	
	function __construct() {
		add_action('admin_menu', array($this,'wwp_woo_wholesale_pro_menu'),99);
	}
	
	function wwp_woo_wholesale_pro_menu() {
		add_submenu_page( 'woocommerce', 'WooCommerce Wholesale Pricing PRO', 'WooCommerce Wholesale Pricing <span class="wwp_pro">PRO</span>','manage_options', 'woo-wholesale-pricing-pro', array($this,'woo_wholesale_pricing_pro_callback'));
	}
	
	function woo_wholesale_pricing_pro_callback() {
		$html .= '<div class=wwp-pro-wrap>
				<h1 class="wwp-pro-title">WooCommerce Wholesale Pricing <span class="wwp-pro-box">PRO</span></h1>
				<p>A <strong>WooCommerce Extention</strong> that gives an ability to your store to better success with wholesale pricing. You can easily manage your existing store with wholesale pricing. Just you need to add a wholesaler customer by selecting their role “Wholesaler”, <strong>wholesale prices only visible for Wholesaler Customers not for public customers.</strong></p>

				<p><strong>Still Confused?</strong> <a href="https://goo.gl/fMV97w">Click Here</a> for more information.</p>';
				
			$html .= '<div class="wwp-features-boxes"><div class="feature-box">
					<span class="dashicons dashicons-format-aside"></span>
						<h3>Wholesaler Registration Form</h3>
						<p>With font-end Wholeasler registration form any customer can register and beccome your wholesaler customer. You can enable and disable this registration form with easy settings.</p>
					</div>';
					
			$html .= '<div class="feature-box">
			<span class="dashicons dashicons-clipboard"></span>
				<h3>Bulk Add Wholesale Prices</h3>
				<p>With WooCommerce Wholesale pricing Pro you can easily manage multiple products with wholesale pricing in single window to make things easy to manage.</p>
			</div>';
			
			$html .= '<div class="feature-box">
			<span class="dashicons dashicons-editor-paste-text"></span>
				<h3>Labels Settings</h3>
				<p>Looking for different labels in front end or want to customize them? With Pro version you can do that with simple to use settings and overwrite the labels as per your requirement.</p>
			</div></div>';
			
			$html .= '<h3 class="wwp-pro-feat">FEATURES LIST</h3>';
			$html .= '<ul class="wwp-pro-list">
						<li> Add Wholesale Price in simple product</li>
						<li>Add Wholesale Price in variable product</li>
						<li>Manage Wholesale prices in bulk to make things easy</li>
						<li>Change all labels from settings</li>
						<li>Front end Wholesale Registration form</li>
						<li>Wholesale price display in front end for Wholesaler user</li>
						<li>Can add fix amount discount</li>
						<li>Can add percentage discount</li>
					</ul>';
					
			$html .= '<div class="wwp-buy-btn"><a href="https://goo.gl/fMV97w"><span class="wwp-pro-link" id="wwp-pro-link">GET PRO NOW</span></a></div>';
			
			$html .= '';
			
		$html .= '</div>';
		
		echo $html;
	}
}