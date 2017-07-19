<?php
/**
 * Class taking care about analytics integration for different events:
 * Views (Single Product & Categories)
 * AddToCart (From Single Product & Categories)
 * RemoveFromCart
 * Checkout
 * Conversion
 * Todo: Check difference between enqueue on footer or echo script. Sometimes is failing to send track.
 *
 * Class Plytix_Integration
 */
if ( ! class_exists( 'Plytix_Integration' ) ) :

class Plytix_Integration {

    /**
     * Registering actions with Woo hooks we want to track
     */
    function __construct() {

        // Add to cart Single Product
        add_action( 'woocommerce_after_add_to_cart_button', array( $this, 'add_to_cart' ) );
        // Add to cart Archive Product
        add_action( 'wp_footer'                           , array( $this, 'loop_add_to_cart' ) );
        // Remove from Cart
        add_action( 'woocommerce_after_cart'              , array( $this, 'remove_from_cart' ) );
        add_action( 'woocommerce_after_mini_cart'         , array( $this, 'remove_from_cart' ) );
        // Product View
        add_action( 'woocommerce_after_single_product'    , array( $this, 'product_view' ));
        // Product View Category / Search
        add_action( 'woocommerce_after_shop_loop_item'    , array( $this, 'product_view_loop' ) );
        // Checkout Process
        add_action( 'woocommerce_after_checkout_form'     , array( $this, 'checkout_process' ) );
        // Convesion
        add_action( 'woocommerce_thankyou'                , array( $this, 'conversion') );
    }

    /**
     * Register Conversion products in thank you page
     * It will only work if payment method redirects to thank you page when order is paid.
     *
     * @param $order_id
     */
    function conversion($order_id) {
        $order = new WC_Order( $order_id );
        $items = $order->get_items();

        foreach ($items as $item) {
            $qty        = $item['item_meta']['_qty'][0];
            $product_id = $item['item_meta']['_product_id'][0];
            $plytix_id  = $this->get_plytix_id_by_product_id($product_id);
            if ($plytix_id) {
                echo "<script>_pl('track', '$plytix_id', 'conversion', $qty);</script>";
            }
        }
    }

    /**
     * Register Checkout Products Page
     */
    function checkout_process() {
        $cart = WC()->cart->get_cart();
        foreach ( $cart as $cart_item_key => $cart_item ) {
            $plytix_id = $this->get_plytix_id_by_product_id($cart_item['product_id']);
            if ($plytix_id) {
                $qty       = $cart_item['quantity'];
                echo "<script>_pl('track', '$plytix_id', 'checkout', $qty);</script>";
            }
        }
    }

    /**
     * Registering Product View (Loop)
     */
    function product_view_loop() {
        $plytix_id = $this->get_plytix_id_by_product_id(get_the_ID());
        if ($plytix_id) {
            $js_echo = "_pl('track', '$plytix_id', 'view', 'category');";
            wc_enqueue_js($js_echo);
        }
    }

    /**
     * Registering Product View
     */
    function product_view() {
        $plytix_id = $this->get_plytix_id_by_product_id(get_the_ID());
        if ($plytix_id) {
            $js_echo = "_pl('track', '$plytix_id', 'view', 'product');";
            wc_enqueue_js($js_echo);
        }
    }

    /**
     * Registering Remove From Cart
     */
    function remove_from_cart() {
        $js_echo  = "jQuery('.remove').click(function(){";
        $js_echo .= "var qty = jQuery(this).parent().parent().find( '.qty' ).val() ? $(this).parent().parent().find( '.qty' ).val() : '1';";
        $js_echo .= "_pl('track', jQuery(this).data('plytix_id'), 'removefromcart', qty);";
        $js_echo .= "});";
        wc_enqueue_js($js_echo);
    }

    /**
     * Registering Add To Cart (Single Product)
     */
    function add_to_cart() {
        $plytix_id = $this->get_plytix_id_by_product_id(get_the_ID());
        if ($plytix_id) {
            $js_echo  = "jQuery('.single_add_to_cart_button').click(function(){";
            $js_echo .= "_pl('track', '$plytix_id', 'addtocart', jQuery(this).parent().find(\"input[name=quantity]\").val());";
            $js_echo .= "});";
            wc_enqueue_js($js_echo);
        }
    }

    /**
     * Registering Add to cart Archive Product
     */
    function loop_add_to_cart() {
        $js_echo  = "jQuery('.add_to_cart_button').click(function(){";
        $js_echo .= "_pl('track', jQuery(this).data('plytix_id'), 'addtocart', jQuery(this).data('quantity'));";
        $js_echo .= "});";
        wc_enqueue_js($js_echo);

    }

    /**
     * Get Plytix ID by Woo - Product ID
     * @param $product_id
     * @return mixed
     */
    private function get_plytix_id_by_product_id($product_id) {
        $aux = get_post_custom_values('plytix_product_id', $product_id);
        $plytix_id = ($aux) ? current($aux) : "";
        return $plytix_id;
    }
}

endif;
