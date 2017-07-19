<?php
require_once PLYTIX_VENDORS . '/plytix-sdk-php/' . '/require_me.php';

/**
 * Class taking care about matching Woo products with Plytix Product
 * It will try to match by Name and Identifier, otherwise by Search.
 *
 * Class Plytix_Admin_Match_Functions
 */
class Plytix_Admin_Match_Functions {

    /**
     * Callback function to echo of Json Result
     */
    public static function ajax_match_by_callback() {
        $post_id             = $_REQUEST['post_id'];
        $product_identifier  = self::get_identifier_by_id($post_id);
        $product_name        = get_the_title($post_id);
        echo self::match_by($product_name, $product_identifier);die;
    }

    /**
     * Gets matches by Name from Plytix Bank. Will return all products from product_bank where
     * name is like $name.
     *
     * @param $name Name of the product to Match
     * @return string Json
     */
    static function match_one_by_name($name) {
        $options = get_option('plytix-settings');
        $client = new PlytixRetailersClient($options['api_id'], $options['api_password']);

        $search = array(
            'name'     => $name,
            'page_length' => 1
        );
        return $client->bank()->search($search);
    }

    /**
     * Gets matchs by Name and Identifier from Plytix Bank
     *
     * @param $name Name of the product to Match
     * @param $identifier  Identifier of the product to Match
     * @return string Json
     */
    static function match_by($name, $identifier) {
        $options = get_option('plytix-settings');
        $client = new PlytixRetailersClient($options['api_id'], $options['api_password']);

        $search = array(
            'operator' => 'OR',
        );

        if ($name) {
            $search['name'] = $name;
        }
        if ($identifier) {
            $search['identifier_list'] = array($identifier);
        }

        return $client->bank()->search($search)->toJson();
    }

    /**
     * Retrieves a list of matches given a list of Identifiers
     *
     * @param $product_list
     * @return ResponseModel
     */
    static function bulk_match($product_list) {
        $options = get_option('plytix-settings');
        $client = new PlytixRetailersClient($options['api_id'], $options['api_password']);

        $search = array(
            'operator' => 'OR',
        );

        $name_list = wp_list_pluck($product_list, 'post_title');
        if ($name_list) {
            $search['name_list'] = $name_list;
        }

        $id_list = wp_list_pluck($product_list, 'ID' );
        $identifier_list = array();
        foreach ($id_list as $id) {
            $identifier = self::get_identifier_by_id($id);
            if ($identifier) {
                array_push($identifier_list, $identifier);
            }
        }
        if ($identifier_list) {
            $search['identifier_list'] = $identifier_list;
        }

        $numOfProducts = count($product_list);
        if ($numOfProducts < 10) {
            $search['page_length'] = $numOfProducts;
        } else {
            $search['page_length'] = 10;
        }

        return $client->bank()->search($search);
    }

    /**
     * Get Identifier by Post ID
     *
     * @param $post_id
     * @return mixed
     */
    private static function get_identifier_by_id($post_id) {
        return current(get_post_meta($post_id, '_sku'));
    }

    /**
     * Callback function to echo of Json Result
     */
    public static function ajax_save_plytix_id_by_callback() {
        $post_id      = $_REQUEST['post_id'];
        $plytix_id    = $_REQUEST['plytix_id'];

        echo self::match_plytix_id_with_woo_id($post_id, $plytix_id);die;
    }

    /**
     * Matchs WooCommerce Product with Plytix id
     *
     * @param $post_id
     * @param $plytix_id
     * @return bool|int
     */
    static function match_plytix_id_with_woo_id($post_id, $plytix_id) {
        return update_post_meta($post_id, 'plytix_product_id', $plytix_id);
    }

    /**
     * In order to add Products to Plytix, you need to have a Folder
     * It will create or retrieve ID if exists
     */
    public static function ajax_create_or_retrieve_folder() {
        $folder_name = 'Plytix WP Plugin';
        echo self::create_folder($folder_name);die;
    }

    /**
     * Function to Create or Retrieve WP Folder ID
     *
     * @param $folder_name
     * @return int|mixed
     */
    public static function create_folder($folder_name) {
        if ($folder_id = get_option('plytix_plugin_folder_id')) {
            return $folder_id;
        }

        $options = get_option('plytix-settings');
        $client = new PlytixRetailersClient($options['api_id'], $options['api_password']);
        $folder_id = 0;
        try {
            $new_folder = $client->folders()->create($folder_name);
            $folder_id = $new_folder->getId();
            update_option( 'plytix_plugin_folder_id', $folder_id);
        } catch (Exception $e) {
            //Todo: Waiting for folder create to be updated to retrieve ID if exists
            $folder_list = $client->folders()->get()->getFolders();
            foreach ($folder_list as $folder) {
                if ($folder->name == $folder_name) {
                    $folder_id = $folder->id;
                    update_option( 'plytix_plugin_folder_id', $folder_id);
                    break;
                }
            }
        }
        return $folder_id;
    }

    /**
     * Handles AJAX call to Add Product To Bank
     * Returns My Product ID
     */
    static function ajax_add_product_to_bank() {
        $folder_id    = $_REQUEST['folder_id'];
        $product_id   = $_REQUEST['product_id'];
        echo self::add_product($folder_id, $product_id);die;
    }


    /**
     * It adds Product to Folder
     * It returns our subscription ID
     *
     * @param $folder_id
     * @param $product_id
     * @return mixed
     * @throws Exception
     */
    static function add_product($folder_id, $product_id) {
        $options = get_option('plytix-settings');
        $client = new PlytixRetailersClient($options['api_id'], $options['api_password']);
        $folder  = $client->folders()->get($folder_id);

        $subscription_id = current($client->bank()->addTo($folder, null, array($product_id)));
        return $subscription_id->product_id;
    }

    /**
     * Handles AJAX call from Match Modal Popup
     * Returns JSON with Matches
     */
    static function ajax_get_brands() {
        $search_string = $_REQUEST['q'];
        echo self::search_brands_by_string($search_string);die;
    }

    /**
     * It search brands by string
     * It returns JSON object with matches
     *
     * @param $search_string
     * @return string Json
     */
    private static function search_brands_by_string($search_string) {
        $options = get_option('plytix-settings');
        $client = new PlytixRetailersClient($options['api_id'], $options['api_password']);
        return $client->brands()->search($search_string)->toJson();
    }

    static function ajax_get_products_by_brand_id() {
        $brand_id      = $_REQUEST['brand_id'];
        $product_name  = (isset($_REQUEST['product_name']) && $_REQUEST['product_name'] != '') ? $_REQUEST['product_name'] : null;
        echo self::get_products_by_brand($brand_id, $product_name);die;
    }

    private static function get_products_by_brand($brand_id, $product_name = null) {
        $options = get_option('plytix-settings');
        $client = new PlytixRetailersClient($options['api_id'], $options['api_password']);
        $args = array(
            'brand_id' => $brand_id,
            'name'     => $product_name,
        );

        /**
         * Limiting query in case we dont have any brand on the query
         */
        if (empty($brand_id)) {
            $args['page_length'] = 10;
        }

        return $client->bank()->search($args)->toJson();
    }

}
