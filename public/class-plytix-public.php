<?php
include_once PLYTIX_PLUGIN_PUBLIC . '/includes/class-plytix-integration.php';

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://plytix.com/
 * @since      1.0.0
 *
 * @package    Plytix
 * @subpackage Plytix/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Plytix
 * @subpackage Plytix/public
 * @author     Plytix <plytix.com>
 */
class Plytix_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

        /**
         * Registering plytix_id on add_to_cart_link in the loop (archives)
         */
        add_filter( 'woocommerce_loop_add_to_cart_link', array($this,'plytix_id_add_to_cart_loop'), 10, 2 );
        /**
         * Registering plytix_id on Cart
         */
        add_filter( 'woocommerce_cart_item_remove_link', array($this,'plytix_id_in_cart'), 10, 1 );

        /**
         * Registering Plytix Integration
         */
        new Plytix_Integration();
    }

    /**
     * Add Plytix Integration to Woocommerce
     * @return array
     */
    function add_integration() {
        $integrations[] = 'Plytix_Integration';
        return $integrations;
    }

	/**
	 * Register Plytix Script
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

        $api_settings = get_option('plytix-settings');
        $api_id = $api_settings['api_id'];

        /**
         * Registering Plytix Script
         */
        wp_register_script(
            'plytix_js',
            plugin_dir_url( __FILE__ ) . 'js/plytix.js'
        );

        /**
         * Passing Over Api_key_id to the script
         */
        wp_localize_script(
            'plytix_js',
            'api_id',
            array('api_id' => $api_id)
        );

        /**
         * Loading Plytix into the FrontEnd
         */
        wp_enqueue_script(
            'plytix_js'
        );
	}

    /**
     * It registers Plytix ID intro add_to_cart_button loop
     *
     * @param $html
     * @param $product
     * @return string
     */
    function plytix_id_add_to_cart_loop( $html, $product ) {
        $new_html = $html;
        $aux = get_post_custom_values('plytix_product_id', $product->ID);
        $plytix_id = ($aux) ? current($aux) : "";

        $pos = strpos($html, ">");
        if ($pos) {
            $new_html = substr_replace($html, ' data-plytix_id="'.$plytix_id.'" "', $pos, 0);
        }

        return $new_html;
    }

    /**
     * Adds Plytix ID to remove from cart a tag
     * adding to the template withouth rewriting the filter
     * ToDo: Test if this works better than redoing the full <a tag
     *
     * @param $html
     * @return string|void
     */
    function plytix_id_in_cart($html) {
        /**
         * If we are not able to get the product ID, just return, we dont track it
         */
        if (preg_match('#data-product_id=\"(.+?)\"#', $html, $product_id) == false)
            return $html;

        $product_id = $product_id[1];
        $plytix_tmp = get_post_custom_values('plytix_product_id', $product_id);
        if (!$plytix_tmp)
        {
            return $html;
        }
        $plytix_id = current($plytix_tmp);
        $plytix_string = " data-plytix_id=$plytix_id";

        /**
         * Paste Plytix data after opening a tag
         * <a 'plytix data'
         * So we make sure we dont modify the tag itself
         */
        return substr($html, 0, 2) . $plytix_string . substr($html, 2);
    }
}
