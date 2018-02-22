<?php
/**
 * Class To Add Wholesale Functionality with WooCommerce
 */
class Woo_Easy_Wholesale {

    function __construct() {
        add_role( 'wwp_wholesaler', 'Wholesaler', array( 'read' => true, 'level_0' => true ) );
        add_filter( 'woocommerce_product_data_tabs', array($this,'wwp_add_wholesale_product_data_tab') , 99 , 1 );
        add_action( 'woocommerce_product_data_panels', array($this,'wwp_add_wholesale_product_data_fields') );
        add_action( 'woocommerce_process_product_meta', array($this,'wwp_woo_wholesale_fields_save') );
        add_filter( 'woocommerce_get_price_html', array($this,'wwp_change_product_price_display') );
        add_filter( 'woocommerce_cart_item_price', array($this,'wwp_change_product_price_display') );
        add_action( 'woocommerce_before_calculate_totals', array($this,'wwp_override_product_price_cart'),99 );
        add_action( 'woocommerce_product_after_variable_attributes', array($this,'wwp_variation_settings_fields'), 10, 3 );
        add_action( 'woocommerce_save_product_variation', array($this,'wwp_save_variation_settings_fields'), 10, 2 );
        add_action( 'wp_footer', array($this,'wwp_on_variation_change') );
        add_action( 'wp_ajax_wwp_variation',  array($this,'wwp_variation_change_callback') );
        add_action( 'wp_ajax_nopriv_wwp_variation',  array($this,'wwp_variation_change_callback') );
        add_filter( 'woocommerce_variable_sale_price_html', array($this,'wwp_variable_price_format'), 10, 2 );
        add_filter( 'woocommerce_variable_price_html', array($this,'wwp_variable_price_format'), 10, 2 );
        add_action( 'admin_head', array($this,'wcpp_custom_style') );
    }

    // CSS To Add Whoelsale tab Icon
    function wcpp_custom_style() {?>
        <style>
            .wwp-wholesale-tab_tab a:before {
                font-family: Dashicons;
                content: "\f240" !important;
            }
        </style>
        <?php
    }

    // WooCommerce Wholesale Tab on Admin Product Page
    function wwp_add_wholesale_product_data_tab( $product_data_tabs ) {
            $product_data_tabs['wwp-wholesale-tab'] = array(
                'label' => __( 'Wholesale', 'my_text_domain' ),
                'target' => 'wwp_wholesale_product_data',
    //            'class' => 'hide_if_variable',
            );
            return $product_data_tabs;
        }

    // Content to add Wholesale tab
    function wwp_add_wholesale_product_data_fields() {
        global $woocommerce, $post, $product;
        ?>
        <!-- id below must match target registered in above wwp_add_wholesale_product_data_tab function -->
        <div id="wwp_wholesale_product_data" class="panel woocommerce_options_panel">
            <?php
            woocommerce_wp_checkbox(
                array(
                    'id'            => '_wwp_enable_wholesale_item',
                    'wrapper_class' => 'wwp_enable_wholesale_item',
                    'label'         => __('Enable Wholesale Item', 'woocommerce' ),
                    'description'   => __( 'Add this item for wholesale customers', 'woocommerce' )
                )
            );

            woocommerce_wp_select(
                array(
                    'id'      => '_wwp_wholesale_type',
                    'label'   => __( 'Wholesale Type', 'woocommerce' ),
                    'options' => array(
                        'fixed'   => __( 'Fixed Amount', 'woocommerce' ),
                        'percent'   => __( 'Percent', 'woocommerce' ),
                    )
                )
            );

            echo '<div class="hide_if_variable">';
            woocommerce_wp_text_input(
                array(
                    'id'          => '_wwp_wholesale_amount',
                    'label'       => __( 'Enter Wholesale Amount', 'wwp_wholesale_txt' ),
                    'placeholder' => get_woocommerce_currency_symbol().'15',
                    'desc_tip'    => 'true',
                    'description' => __( 'Enter Wholesale Price (e.g 15)', 'wwp_wholesale_txt' )
                )
            );
            echo '</div>';
            echo '<div class="show_if_variable">';
            echo '<p>For Variable Product you can add wholesale price from variations tab</p>';
            echo '</div>';
            ?>
        </div>
        <?php
    }

    // Save Wholesale tab
    function wwp_woo_wholesale_fields_save( $post_id ){

        // Wholesale Enable
        $woo_wholesale_enable = $_POST['_wwp_enable_wholesale_item'];        
        update_post_meta( $post_id, '_wwp_enable_wholesale_item', esc_attr( $woo_wholesale_enable ) );

        // Wholesale Type
        $woo_wholesale_type = $_POST['_wwp_wholesale_type'];
        if( !empty( $woo_wholesale_type ) )
            update_post_meta( $post_id, '_wwp_wholesale_type', esc_attr( $woo_wholesale_type ) );

        // Wholesale Amount
        $woo_wholesale_amount = $_POST['_wwp_wholesale_amount'];
        if( !empty( $woo_wholesale_amount ) )
            update_post_meta( $post_id, '_wwp_wholesale_amount', esc_attr( $woo_wholesale_amount ) );

    }

    // Override Product Price to wholesale price
    function wwp_change_product_price_display( $price ) {

        $post_id = get_the_ID();
        $product = wc_get_product( $post_id );

        if(is_cart())
            return $price;

        if((gettype($product) == "object") && !$product->is_type( 'simple' ))
            return $price;

        if(!$this->is_wholesaler_user( get_current_user_id() ))
            return $price;

        $original_price = get_post_meta($post_id,'_price',true);
        $enable_wholesale = get_post_meta($post_id,'_wwp_enable_wholesale_item',true);
        if(empty($enable_wholesale))
            return $price;

        $r_price = strip_tags($price);
        $r_price = str_replace(get_woocommerce_currency_symbol(),'',$r_price);
//        $this
        $wholesale_price = $this->get_wholesale_price($post_id);
        $saving_amount = round( ($r_price - $wholesale_price) );
        $saving_percent = ($r_price - $wholesale_price) / $r_price * 100;

        if(!empty($wholesale_price)) {
            $html = do_action('wwp_before_pricing');
            $html .= '<div class="wwp-wholesale-pricing-details"><p>'. apply_filters('wwp_retail_price_short_text','RRP: ') . ' <s>'.$price.'</s></p>';
            $html .= '<p>'. apply_filters('wwp_your_price_text','Your Price: ') . wc_price($wholesale_price) .'</p>';
            $html .= '<p><b>'. apply_filters('wwp_you_save_text','You Save: ') .wc_price($saving_amount) .' ('.round($saving_percent).'%)' .'</b></p>';
            $html .= '</div>';
            $html .= do_action('wwp_after_pricing');
        }
        return $html;
    }

    // Overridde Product Price on cart page
    function wwp_override_product_price_cart( $_cart ){

        if($this->is_wholesaler_user(get_current_user_id())) {
            // loop through the cart_contents
            foreach ( $_cart->cart_contents as $cart_item_key => $item ) {
                if($this->is_wholesale($item['product_id'])) {
                    $variation_id = $item['variation_id'];
                    if(!empty($variation_id)) {
                        if(!empty($this->get_variable_wholesale_price($variation_id)))
                            $item['data']->set_price($this->get_variable_wholesale_price($variation_id));
                    } else {
                        if(!empty($this->get_wholesale_price($item['product_id'])))
                            $item['data']->set_price($this->get_wholesale_price($item['product_id']));
                    }
                }
            }
        }
    }

    // Check if product is wholesale
    function is_wholesale($post_id) {
        $enable_wholesale = get_post_meta($post_id,'_wwp_enable_wholesale_item',true);
        if(!empty($enable_wholesale))
            return true;

        return false;
    }

    // Get wholesale Product Price
    function get_wholesale_price($post_id) {
        $wholesale_price = get_post_meta($post_id,'_wwp_wholesale_amount',true);

        if($this->is_wholesale($post_id)) {
            $wholesale_amount_type = get_post_meta($post_id,'_wwp_wholesale_type',true);
            if($wholesale_amount_type == 'fixed'){
                return $wholesale_price;
            } else {
                $product_price = get_post_meta($post_id,'_price',true);
                $wholesale_price = $product_price * $wholesale_price / 100;
                return $wholesale_price;
            }
        }
    }

    function get_variable_wholesale_price($variation_id) {
        $post_id = get_the_ID();
        $variable_price = get_post_meta($variation_id,'_wwp_wholesale_amount',true);

        $wholesale_amount_type = get_post_meta($variation_id,'_wwp_wholesale_type',true);
        
        if($wholesale_amount_type == 'fixed'){
            return $variable_price;
        } else {
            if(!empty($variable_price)) {
                $product_price = get_post_meta($variation_id, '_price', true);
                $wholesale_price = $product_price * $variable_price / 100;
                return $variable_price;
			}
        }
	}

    // Check if user is Wholesaler
    function is_wholesaler_user($user_id) {
        if(!empty($user_id)) {
            $user_info = get_userdata($user_id);
            $user_role = implode(', ', $user_info->roles);

            if($user_role == 'wwp_wholesaler')
                return true;
        }
        return false;
    }

    function wwp_variation_settings_fields($loop, $variation_data, $variation) {

        woocommerce_wp_text_input(
            array(
                'id'          => '_wwp_wholesale_amount[' . $variation->ID . ']',
                'label'       => __( 'Enter Wholesale Price', 'woocommerce' ),
                'desc_tip'    => 'true',
                'description' => __( 'Enter Wholesale Price Here (e.g 15)', 'woocommerce' ),
                'value'       => get_post_meta( $variation->ID, '_wwp_wholesale_amount', true ),
                'custom_attributes' => array(
                    'step' 	=> 'any',
                    'min'	=> '0'
                )
            )
        );
    }

    function wwp_save_variation_settings_fields($post_id) {
        // Text Field
        $variable_wholesale = $_POST['_wwp_wholesale_amount'][ $post_id ];
        if( ! empty( $variable_wholesale ) ) {
            update_post_meta( $post_id, '_wwp_wholesale_amount', esc_attr( $variable_wholesale ) );
        }

    }

    function wwp_on_variation_change() { ?>
        <script type="text/javascript" >

            /* Make this document ready function to work on click where you want */
            jQuery(document).ready(function($) {

                /* In front end of WordPress we have to define ajaxurl */
                var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';

                jQuery( "body").on( "found_variation" , ".variations_form", function( event, variation ) {
                    console.log( variation );

                    var data = {
                        'action': 'wwp_variation',
                        'variation_id': variation['variation_id'],
                        'variation_price': variation['price_html']
                    };
                    $.post(ajaxurl, data, function(response) {
                        if(response != '')
                            jQuery('.woocommerce-variation-price').html(response);
                    });
                });
            });
        </script> <?php
    }

    function wwp_variation_change_callback() {

        $variation_id = $_POST['variation_id'];
        $variation_price = $_POST['variation_price'];

        if(!$this->is_wholesaler_user(get_current_user_id())) {
            echo '';
            die();
        }

        $wholesale_variable_price = get_post_meta($variation_id,'_wwp_wholesale_amount',true);
        $variable_wholesale_price = $this->get_variable_wholesale_price($variation_id);
        $html = '<s>'.$variation_price.'</s>';
        $html .= '<span class="price"><span class="woocommerce-Price-amount amount">'.wc_price($variable_wholesale_price).'</span></span>';
        echo $html;

        die(); // this is required to terminate immediately and return a proper response

    }

    function wwp_variable_price_format( $price, $product ) {

        if(!$this->is_wholesaler_user(get_current_user_id()))
            return $price;

        $product_variations = $product->get_children();
        $wholesale_product_variations = array();

        foreach($product_variations as $product_variation) {
            $wholesale_product_variations[] = $this->get_variable_wholesale_price($product_variation);
        }

        sort($wholesale_product_variations);

        $html = '<div class="wwp-wholesale-pricing-details">';
        $html .= apply_filters('wwp_retail_price_short_text','RRP: ').' <s>'.$price.'</s>';
        $html .= '<p><b>'.apply_filters('wwp_your_price_text','Your Price: ').wc_price($wholesale_product_variations[0]).' - '.wc_price($wholesale_product_variations[count($wholesale_product_variations) - 1]).'</b></p>';
        $html .= '</div>';
        return $html;
    }
}