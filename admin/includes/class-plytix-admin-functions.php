<?php
require_once PLYTIX_VENDORS . '/plytix-sdk-php/require_me.php';
// Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
require_once(ABSPATH . 'wp-admin/includes/image.php');

class Plytix_Admin_Functions {

    /**
     * Given a Post Id, retrieve json with all its Plytix pictures
     *
     * @param $post_id
     * @param $sizes
     * @return mixed|string|void
     */
    static function get_pictures_by_post_id( $post_id, $sizes=null ) {
        $plytix_id = get_post_meta( $post_id, 'plytix_product_id');

        /**
         * Get Pictures from Plytix ID
         */
        $options = get_option('plytix-settings');
        $client = new PlytixRetailersClient($options['api_id'], $options['api_password']);
        if (!$sizes) {
            $sizes = self::get_all_sizes();
        }
        $pictures = $client->products()->pictures($plytix_id, $sizes);

        $pictures_to_return = array();
        $pictures_to_return['id'] = current($plytix_id);
        $pictures_result = (current($pictures->getResults())) ? current($pictures->getResults()) :  array();

        $options = get_option('plytix-settings-sizes-site');
        if (!empty($pictures_result)) {
            foreach ($pictures_result->getPictures() as $k => $picture) {
                $pictures_to_return['pictures'][$k]['original'] = $picture->getOriginal()->url_to_version;
                foreach ($picture->getThumbs() as $k1 => $thumb) {
                    $pictures_to_return['pictures'][$k]['thumbs'][$k1] = $thumb->url_to_version;
                }
                $pictures_to_return['pictures'][$k]['version']    = $picture->getVersion();
                $pictures_to_return['pictures'][$k]['picture_id'] = $picture->getPictureId();
            }
        }
        return $pictures_to_return;
    }

    /**
     * Returns the list of the WordPress sizes
     * @return array
     */
    static public function get_all_sizes() {
        $sizes = array();
        global $_wp_additional_image_sizes;
        foreach (get_intermediate_image_sizes() as $s) {
            if (isset($_wp_additional_image_sizes[$s])) {
                $width = intval($_wp_additional_image_sizes[$s]['width']);
                $height = intval($_wp_additional_image_sizes[$s]['height']);
            } else {
                $width = get_option($s.'_size_w');
                $height = get_option($s.'_size_h');
            }
            array_push($sizes, $width.'x'.$height);
        }
        return $sizes;
    }

    /**
     * Uploads backup picture to WP media to use on the templates render.
     *
     * @param int $post_id    Post id where pic is gonna be attached
     * @param array $pictures Array of Picture urls size
     * @param $where_add_image  Check if picture will be on "product-picture", on "product-gallery" or on "product-variant"
     * @return bool
     */
    static function insert_picture ($post_id, $pictures, $image_data) {
        $url = $pictures['original'];

        $upload_dir = wp_upload_dir();
        $file_info = pathinfo($url);
        $filename = $file_info['filename'] . '.' . $file_info['extension'];

        if ( wp_mkdir_p($upload_dir['path']) ) {
            $file = $upload_dir['path'] . '/' . $filename;
        } else {
            $file = $upload_dir['basedir'] . '/' . $filename;
        }

        // Check if file exists and changes url
        $n_file = 1;
        while(file_exists($file)) {
            $filename = $file_info['filename'] . '-' . $n_file . '.' . $file_info['extension'];

            if ( wp_mkdir_p($upload_dir['path']) ) {
                $file = $upload_dir['path'] . '/' . $filename;
            } else {
                $file = $upload_dir['basedir'] . '/' . $filename;
            }
            $n_file = $n_file + 1;
        }

        file_put_contents($file, $image_data);

        // The ID of the post this attachment is for.
        $parent_post_id = $post_id;

        // Check the type of file. We'll use this as the 'post_mime_type'.
        $filetype = wp_check_filetype( basename( $filename ), null );

        // Prepare an array of post data for the attachment.
        $attachment = array(
            'post_mime_type' => $filetype['type'],
            'post_title'     => sanitize_file_name($filename),
            'post_content'   => 'Image uploaded from Plytix',
            'post_status'    => 'inherit'
        );

        // Insert the attachment.
        $attach_id = wp_insert_attachment($attachment, $file, $parent_post_id);

        if ($attach_id == 0) {
            return false;
        } else {
            update_post_meta($attach_id, 'plytix_picture_version', $pictures['version']);
            update_post_meta($attach_id, 'plytix_picture_id', $pictures['picture_id']);
        }

        // Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
        require_once(ABSPATH . 'wp-admin/includes/image.php');

        // Generate the metadata for the attachment, and update the database record.
        $attach_data = wp_generate_attachment_metadata( $attach_id, $file );
        wp_update_attachment_metadata( $attach_id, $attach_data );

        return $attach_id;
    }

    static public function set_product_gallery_and_thumb ($post_id, $attach_id, $to_product_gallery, $is_thumbnail) {
        //Adding images to product gallery
        if ($to_product_gallery == 'true') {
            $old_pics = get_post_meta($post_id, '_product_image_gallery', true);
            $new_pics = '';
            if (!empty($old_pics)) {
                $new_pics = $old_pics . ', ' . $attach_id;
            } else {
                $new_pics = $attach_id;
            }
            update_post_meta($post_id, '_product_image_gallery', $new_pics);
        }

        //Setting it as thumbnail
        if ($is_thumbnail == 'true') {
            update_post_meta($post_id, '_thumbnail_id', $attach_id);
        }
    }

    /**
     * Get the number of products to update
     */
    static function get_number_to_update(){
        global $wpdb;
        $query = "SELECT COUNT(*) AS number_to_update FROM `".$wpdb->prefix."postmeta` WHERE `meta_key` = 'plytix_need_update' AND `meta_value` > '0'";
        $posts = $wpdb->get_results($query);
        return $posts[0]->number_to_update;
    }

    /**
     * Get the number of products to check
     */
    static function get_number_to_check($time){
        global $wpdb;
        $query = "SELECT COUNT(*) AS number_to_check FROM `".$wpdb->prefix."postmeta` WHERE `meta_key` = 'plytix_check_date' AND `meta_value` < '".$time."'";
        $posts = $wpdb->get_results($query);
        return $posts[0]->number_to_check;
    }

    /**
     * Test if there is new available pictures for an array of posts
     */
    static function check_bunch_of_posts($posts_id) {
        global $wpdb;
        try {
            $options = get_option('plytix-settings');
            $client = new PlytixRetailersClient($options['api_id'], $options['api_password']);

            $products_to_check = array();
            $pl_ids_and_post_ids = array();
            foreach ($posts_id as $post_id) {
                $pl_id = get_post_meta($post_id, 'plytix_product_id', true);
                $pictures = self::get_local_pl_pictures_by_pl_id($post_id, $pl_id);
                if (!empty($pictures)) {
                    $products_to_check[$pl_id] = $pictures;
                    if (get_post_meta($post_id, 'plytix_check_date')) {
                        update_post_meta($post_id, 'plytix_check_date', time());
                    } else {
                        add_post_meta($post_id, 'plytix_check_date', time());
                    }
                    $pl_ids_and_post_ids[$pl_id] = $post_id;
                }
            }

            if (!empty($products_to_check)) {
                $products = $client->products()->update_latest_pictures($products_to_check, null);
                foreach ($products_to_check as $pl_id => $pictures) {
                    if (self::is_product_in_response($pl_id, $products)) {
                        $number_of_new_pictures = count($products[$pl_id]['pictures']);
                        if (get_post_meta($pl_ids_and_post_ids[$pl_id], 'plytix_need_update', true)>=0){
                            update_post_meta($pl_ids_and_post_ids[$pl_id], 'plytix_need_update', $number_of_new_pictures);
                        } else {
                            add_post_meta($pl_ids_and_post_ids[$pl_id], 'plytix_need_update', $number_of_new_pictures);
                        }
                    } else {
                        if (get_post_meta($pl_ids_and_post_ids[$pl_id], 'plytix_need_update', true)>=0){
                            update_post_meta($pl_ids_and_post_ids[$pl_id], 'plytix_need_update', 0);
                        } else {
                            add_post_meta($pl_ids_and_post_ids[$pl_id], 'plytix_need_update', 0);
                        }
                    }
                }
                return $products;
            } else {
                return $products_to_check;
            }
        } catch (Exception $e) {
            error_log($e->getMessage());
        }
    }

    static private function is_product_in_response($pl_id, $products){
        $is = false;
        foreach ($products as $key => $value) {
            if ($key == $pl_id) {
                $is = true;
            }
        }
        return $is;
    }

    static private function get_local_pl_pictures_by_pl_id ($post_id, $pl_id) {
        global $wpdb;
        $query  = "SELECT `ID` FROM `".$wpdb->prefix."posts` WHERE post_parent = '" . $post_id . "'";
        $posts = $wpdb->get_results($query);
        $pictures = array();
        foreach ($posts as $pictures_post_id) {
            $pictures[get_post_meta($pictures_post_id->ID, 'plytix_picture_id', true)] = get_post_meta($pictures_post_id->ID, 'plytix_picture_version', true);
        }
        return $pictures;
    }

    /**
     * Update the latest pictures
     * @param $products
     */
    static public function update_latest_pictures($products) {
        global $wpdb;
        foreach ($products as $prod) {
            foreach ($prod['pictures'] as $i => $PictureModel) {
                /**
                 * Get the new picture and its new path
                 */
                $url = $prod['pictures'][$i]['original'];
                $upload_dir = wp_upload_dir();
                $image_data = file_get_contents($url['url_to_version']);
                $file_info = pathinfo($url['url_to_version']);
                $filename = $file_info['filename'] . '.' . $file_info['extension'];
                if (wp_mkdir_p($upload_dir['path'])) {
                    $file = $upload_dir['path'] . '/' . $filename;
                } else {
                    $file = $upload_dir['basedir'] . '/' . $filename;
                }
                /**
                 * Get the post_id and the post parent id of the old picture
                 */
                $query  = 'SELECT `post_parent`, `post_id` FROM `'.$wpdb->prefix.'postmeta` ';
                $query .= 'JOIN  `'.$wpdb->prefix.'posts` ON  `post_id` =  `ID` ';
                $query .= 'WHERE `meta_value` = "' . $prod['pictures'][$i]['picture_id'] . '";';
                $post_id = $wpdb->get_row($query);
                /**
                 * Delete the old picture
                 */
                $file_to_delete = get_post_meta($post_id->post_id, '_wp_attached_file', true);
                $url_to_delete = $upload_dir['basedir'] . '/' . $file_to_delete;
                if (file_exists($url_to_delete)) {
                    unlink($url_to_delete);
                }

                /**
                 * Delete the old thumbnails
                 */
                $metadata = wp_get_attachment_metadata($post_id->post_id);
                if(is_array($metadata)){
                    foreach ($metadata["sizes"] as $thissize){
                        $subdir = strtok($metadata['file'], '/');
                        $subdir = $subdir . '/' . strtok('/');
                        $thumb_url_to_delete = $upload_dir['basedir'] . '/' . $subdir . '/' . $thissize['file'];
                    	if (file_exists($thumb_url_to_delete)) {
                    		unlink($thumb_url_to_delete);
                    	}
                    }
                }

                /**
                 * Insert the new picture and its metadata
                 */
                file_put_contents($file, $image_data);
                wp_update_attachment_metadata( $post_id->post_id, wp_generate_attachment_metadata( $post_id->post_id, $file ) );
                update_attached_file( $post_id->post_id, $file);

                update_post_meta($post_id->post_id, 'plytix_picture_version', $prod['pictures'][$i]['version']);
                update_post_meta($post_id->post_id, 'plytix_picture_id', $prod['pictures'][$i]['picture_id']);

                //Clear cache
                wp_cache_flush();

                update_post_meta($post_id->post_parent, 'plytix_need_update', 0);
            }
        }
    }

    /**
     * Update a product by bulk
     */
    function update_product_pictures_by_post_ids($post_ids) {
        if (isset($post_ids)) {
            $products_to_update = Plytix_Admin_Functions::check_bunch_of_posts($post_ids);
    		if(isset($products_to_update)){
    			Plytix_Admin_Functions::update_latest_pictures($products_to_update);
    		}
        }
    }

    /**
     * Returns the product by ID
     */
    static function get_plytix_product_by_id($plytix_id) {
        $options = get_option('plytix-settings');
        $client = new PlytixRetailersClient($options['api_id'], $options['api_password']);
        return $client->products()->get($plytix_id);
    }

    /**
     * Returns the list of the registered Plytix products with plytix images
     * @return array
     */
    static public function get_all_plytix_products_with_plytix_images() {
        global $wpdb;
        $query  = "SELECT DISTINCT `post_parent`, `post_id`, `meta_key`, `meta_value` ";
        $query .= "FROM `".$wpdb->prefix."postmeta` ";
        $query .= "JOIN `".$wpdb->prefix."posts` ON  `".$wpdb->prefix."postmeta`.post_id =  `wp_posts`.ID ";
        $query .= "WHERE `".$wpdb->prefix."postmeta`.meta_key LIKE 'plytix_picture%' ";
        $query .= "ORDER BY meta_key;";
        $posts = $wpdb->get_results($query);

        $plytix_products = array();
        foreach ($posts as $post) {
            $plytix_id = implode(" ", get_post_meta($post->post_parent, 'plytix_product_id'));
            if ($post->meta_key == 'plytix_picture_id') {
                if (!isset($plytix_products[$plytix_id])) {
                    $plytix_products[$plytix_id] = array($post->meta_value => $post->post_id);
                } else {
                    $plytix_products[$plytix_id][$post->meta_value] = $post->post_id;
                }
            } elseif ($post->meta_key == 'plytix_picture_version') {
                $array_pictures = $plytix_products[$plytix_id];
                $array_pictures[array_search($post->post_id, $array_pictures)] = $post->meta_value;
                $plytix_products[$plytix_id] = $array_pictures;
            }
        }
        return $plytix_products;
    }

    /**
     * Returns the list of the registered Plytix products with updates
     */
    static public function get_all_plytix_products_updatables(){
        global $wpdb;
        $query  = "SELECT DISTINCT `post_id` FROM `".$wpdb->prefix."postmeta` WHERE `meta_key` = 'plytix_need_update' AND `meta_value` > '0' ";
        $posts = $wpdb->get_results($query);
        return count($posts);
    }

    /**
     * It Takes setting options request, save it to Plytix
     * and continue with normal WP option behaviour.
     * todo: Waiting for API to accept search by name/settings site instead of listing them
     *
     * @param $args
     * @throws Exception
     */
    static function update_site_info_to_plytix ( $args ) {
        $options = get_option('plytix-settings');
        $client = new PlytixRetailersClient($options['api_id'], $options['api_password']);
        $update = false;

        $url = self::filter_url($_REQUEST['plytix-settings-sizes-site']['site_url']);
        $sites = $client->sites()->listSites()->getResults();
        foreach ($sites as $site) {
            if ($site->getUrl() == $url && $site->getProtocol() == $_REQUEST['plytix-settings-sizes-site']['protocol']) {
                $update = $site;
                break;
            }
        }

        $new_site = new SiteModel();
        $new_site->setName($_REQUEST['plytix-settings-sizes-site']['site_name']);
        $new_site->setUrl($url);
        $new_site->setProtocol($_REQUEST['plytix-settings-sizes-site']['protocol']);
        $new_site->setDebug(false);
        $new_site->setTimezone($_REQUEST['plytix-settings-sizes-site']['timezone']);

        /**
        * Information to send to Plytix
        */
        $woocommerce_info = null;
        $plytix_info = null;
        foreach (get_plugins() as $plugin) {
            if ($plugin['Name'] == 'Plytix') {
                $plytix_info = $plugin;
            } elseif ($plugin['Name'] == 'WooCommerce') {
                $woocommerce_info = $plugin;
            }
        }
        global $wp_version;
        $info = array(
            'platform_name' => 'WordPress',
            'platform_version' => $wp_version,
            'subplatform_name' => $woocommerce_info['Name'],
            'subplatform_version' => $woocommerce_info['Version'],
            'plytix_plugin_version' => $plytix_info['Version']
        );

        $new_site->setInfo($info);

        if (!$update) {
            $client->sites()->create($new_site);
        } else {
            $new_site->setId($update->getId());
            $client->sites()->update($new_site);
        }
    }

    /**
     * Check if API credentials are valid.
     *
     * @param $request
     * @return bool
     */
    static function plytix_api_settings_validation_hook( $request ) {
        $client   = new PlytixRetailersClient($request['plytix-settings']['api_id'], $request['plytix-settings']['api_password']);
        $valid_id = $client->credentials()->checkCredentials();
        return (bool)$valid_id;
    }

    /**
     * Returns all Valid TimeZones
     *
     * @return array
     */
    static function get_valid_time_zones() {
        $options = get_option('plytix-settings');
        $client = new PlytixRetailersClient($options['api_id'], $options['api_password']);
        return json_decode($client->sites()->getTimeZones());
    }

    /**
     * Filter URL (No HTTP, no subfolders e.g:/wp)
     *
     * @param $url
     * @return mixed|string
     */
    static public function filter_url($url) {
        $search = array(
            'http://',
            'https://',
        );
        $url = str_replace($search, '', $url);
        if (strpos($url, '/')) {
            $url = substr($url, 0, strpos($url, '/'));
        }
        return $url;
    }

    /**
     * returns only the protocol and host of an url
     *
     * @param $url
     * @return mixed|string
     */
    static private function get_protocol_and_host($url) {
        $pattern = "#(https?://)([^/]*)(.*)#i";
        $replace = '$1$2';
        return preg_replace($pattern,$replace, $url);
    }
}
