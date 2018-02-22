jQuery(document).ready(function() {
    jQuery( '.variations_form' ).on( 'found_variation', function( event, variation ) {
        console.log( variation['price_html'] );
    });
});