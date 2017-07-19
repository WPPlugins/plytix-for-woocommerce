<?php
require_once PLYTIX_PLUGIN_ADMIN . '/includes/class-pl-meta-box-plytix-data.php';
require_once PLYTIX_PLUGIN_ADMIN . '/includes/class-plytix-admin-functions.php';
require_once PLYTIX_PLUGIN_ADMIN . '/includes/class-plytix-admin-match-functions.php';
require_once PLYTIX_PLUGIN_ADMIN . '/includes/match-modal/modal.php';

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://plytix.com/
 * @since      1.0.0
 *
 * @package    Plytix
 * @subpackage Plytix/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Plytix
 * @subpackage Plytix/admin
 * @author     Plytix <plytix.com>
 */
class Plytix_Admin {

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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

        /**
         * Adding Menu to Dashboard
         */
        add_action( 'admin_menu', array( $this, 'register_plytix_dashboard_menu' ), 1 );

        /**
         * Adding Settings Options
         */
        add_action( 'admin_init', array( $this, 'register_plytix_settings' ));

        /**
         *  Hook into POST settings saving to register Site into Plytix
         */
        add_action( 'admin_action_update', array( $this, 'plytix_site_settings_update_hook' ));
        add_action( 'admin_action_update', array( $this, 'plytix_api_settings_update_hook' ));

        /**
         * Registering Plytix_Id Custom Field Under General Woocommerce Tab
         */
        add_action( 'add_meta_boxes', array( $this, 'add_plytix_meta_boxes' ), 32 );
        add_action( 'woocommerce_process_product_meta', array($this, 'woo_add_custom_general_plytix_id_save'));

        /**
         * Adding picture to media
         */
        add_action('wp_ajax_upload_plytix_picture' , array($this, 'upload_picture_callback' ));

        /**
         * AJAX calls: to Match Product
         */
        add_action('wp_ajax_match_plytix_product'      , array('Plytix_Admin_Match_Functions', 'ajax_match_by_callback' ));
        add_action('wp_ajax_save_plytix_product'       , array('Plytix_Admin_Match_Functions', 'ajax_save_plytix_id_by_callback' ));
        add_action('wp_ajax_create_or_retrieve_folder' , array('Plytix_Admin_Match_Functions', 'ajax_create_or_retrieve_folder' ));
        add_action('wp_ajax_add_product_to_bank'       , array('Plytix_Admin_Match_Functions', 'ajax_add_product_to_bank' ));
        add_action('wp_ajax_get_brands'                , array('Plytix_Admin_Match_Functions', 'ajax_get_brands' ));
        add_action('wp_ajax_get_products_by_brand_id'  , array('Plytix_Admin_Match_Functions', 'ajax_get_products_by_brand_id' ));

		/**
         * Loading and Init Match Modal (only for woo products)
         */
        add_action( 'admin_enqueue_scripts', array( $this, 'match_modal_init' ) );

        /**
         * Checks if product is associated to Plytix ID
         */
        add_action( 'wc_plytix_id_enabled', true );

		/**
		 * AJAX call to load the plytix metabox info tab and match modal
		 */
		add_action('wp_ajax_get_product', array($this, 'get_product_info'));
		add_action('wp_ajax_get_name_and_picture', array($this, 'get_name_and_url'));

		/**
		 * AJAX calls to update products' pictures
		 */
		add_action('wp_ajax_update_product_pictures', array($this, 'update_product'));
	  	add_action('wp_ajax_update_product_pictures_by_id', array($this, 'update_product_by_ID'));
		add_action('wp_ajax_update_all', array($this, 'update_all_products'));

		/**
		 *	Check if any product has new pictures
		 */
		add_action('wp_ajax_reload_table', array($this, 'check_all_products'));
		add_action('wp_ajax_get_number_of_updates', array($this, 'get_number_of_updates'));
		add_action('wp_ajax_get_number_of_checks', array($this, 'get_number_of_checks'));

		/**
		 * Remove Plytix metadata from DB when a post is removed
		 */
		add_action( 'before_delete_post', array($this, 'remove_metadata'));
    }

    /**
     * Outputs Plytix API Admin Page
     */
    function showAPIAdminSettingsPage() {
        include PLYTIX_PLUGIN_ADMIN . '/partials/plytix-admin-api-settings.php';
    }

    /**
     * Outputs Plytix Admin Page
     */
    function showSiteAdminSettingsPage() {
        include PLYTIX_PLUGIN_ADMIN . '/partials/plytix-admin-site-settings.php';
    }

    /**
     * Outputs Plytix Bulk Matching Page
     */
    function showBulkAdminSettingsPage() {
        include PLYTIX_PLUGIN_ADMIN . '/partials/plytix-admin-bulk-matching.php';
    }

    /**
     * Outputs Plytix Pictures Updating Page
     */
    function showUpdatePicturesPage() {
        include PLYTIX_PLUGIN_ADMIN . '/partials/plytix-admin-update-pictures.php';
    }

    /**
     * Add WC Meta boxes.
     */
    public function add_plytix_meta_boxes() {
    	if (get_option('plytix_api_credentials') == 'ok') {
    		add_meta_box( 'woocommerce-plytix-data', __( 'Plytix', 'plytix' ), 'PL_Meta_Box_Plytix_Data::output', 'product', 'normal', 'high' );
    	}
    }

    /**
     * It Saves Plytix ID When Saving Product
     */
    public function woo_add_custom_general_plytix_id_save() {
        if (isset($_POST['plytix_product_id'])) {
            $woocommerce_text_field = $_POST['plytix_product_id'];
        	if (empty(esc_attr( $woocommerce_text_field ))){
        		$this->remove_metadata(get_the_ID());
        		delete_post_meta(get_the_ID(), 'plytix_product_id');
        	}
        }
    }

    /**
     * Registering Settings for Plytix Plugin Configuration
     */
    public function register_plytix_settings() {

        // API Settings
        register_setting(
            'plytix-settings',
            'plytix-settings'
        );

        // Site Setting
        register_setting(
            'plytix-settings-sizes-site',
            'plytix-settings-sizes-site'
        );

        // API Settings Section
        add_settings_section(
            'api_keys',
            __( 'API keys', 'plytix' ),
            function() { _e('To find this information login to your Plytix Dashboard and go to Admin > Account Configuration > API Access Information','plytix'); },
            'plytix-settings'
        );

        // Thumbs Settings Section
        add_settings_section(
            'site_size_options',
            __( 'Site Settings', 'plytix' ),
            function() { _e('Here you can change your site settings and default image sizes. If you are not familiar with the information below, simply leave everything in default and save.','plytix'); },
            'plytix-settings-sizes-site'
        );

        /**
        * Api Settings
        */
        add_settings_field(
            'api_id',
            __( 'API Account ID', 'plytix' ),
            array($this, 'api_key_settings_callback'),
            'plytix-settings',
            'api_keys',
            array('api_id')
        );

        add_settings_field(
            'api_password',
            __( 'API Account Password', 'plytix' ),
            array($this, 'api_key_settings_callback'),
            'plytix-settings',
            'api_keys',
            array('api_password')
        );

        /**
        * Site Settings
        */
        add_settings_field(
            'site_name',
            __( 'Site Name', 'plytix' ),
            array($this, 'site_info_setting_callback'),
            'plytix-settings-sizes-site',
            'site_size_options',
            array('site_name')
        );

        add_settings_field(
            'site_url',
            __( 'Site URL', 'plytix' ),
            array($this, 'site_info_setting_callback'),
            'plytix-settings-sizes-site',
            'site_size_options',
            array('site_url')
        );

        add_settings_field(
            'site_http',
            __( 'HTTP Protocol', 'plytix' ),
            array($this, 'site_protocol_setting_callback'),
            'plytix-settings-sizes-site',
            'site_size_options'
        );

        add_settings_field(
            'timezone',
            __( 'Timezone', 'plytix' ),
            array($this, 'site_timezone_setting_callback'),
            'plytix-settings-sizes-site',
            'site_size_options'
        );
    }

    /**
     * Plytix options callback for Setting HTTP Protocol
     */
    function site_protocol_setting_callback() {
        $arg1 = 'protocol';
        $http_selected = 'selected';
        $https_selected = '';
        $gray = '';

        $size_options = get_option('plytix-settings-sizes-site');
        if ($size_options == false) {
            $gray = 'style="color:gray;"';
            if (is_ssl()) {
              $http_selected  = '';
              $https_selected = 'selected';
            }
        } else {
            if ($size_options['protocol'] == 'https') {
              $http_selected  = '';
              $https_selected = 'selected';
            }
        }
        echo "<select $gray name='plytix-settings-sizes-site[{$arg1}]'>";
        echo '<option '. $http_selected  . ' value="http">http://</option>';
        echo '<option '. $https_selected . ' value="https">https://</option>';
        echo '</select>';
    }

    /**
     * Plytix options callback for Setting Timezone
     */
    function site_timezone_setting_callback() {
    	try {
    		$time_zones = Plytix_Admin_Functions::get_valid_time_zones();
            $timezone_options = get_option('plytix-settings-sizes-site');
            $gray = '';

            if ($timezone_options == false) {
                $gray = 'style="color:gray;"';
                echo "<select id=\"timezone\" $gray name='plytix-settings-sizes-site[timezone]'>";
                foreach ($time_zones as $time_zone) {
                    echo "<option value=\"$time_zone\">$time_zone</option>";
                }
            } else {
                echo "<select id=\"timezone\" name='plytix-settings-sizes-site[timezone]'>";
                foreach ($time_zones as $time_zone) {
                    $selected = ($time_zone == $timezone_options['timezone']) ? 'selected' : '';
                    echo "<option $selected value=\"$time_zone\">$time_zone</option>";
                }
            }
            echo '</select>';
    	} catch (Exception $e) {
    		echo '<p class="plytix_warning_label">Failure to establish communication with Plytix. <a href="http://support.plytix.com/hc/en-us/requests/new" target="_blank">Contact Technical Support</a></p>';
    	}
    }

    /**
     * Plytix options callback for Setting API Fields
     * @param $arg
     */
    function api_key_settings_callback($arg) {
        $arg = current($arg);
        $options = get_option('plytix-settings');
        echo "<input name='plytix-settings[$arg]' type='text' value='{$options[$arg]}' style='width: 300px'/>";
    }

    function site_info_setting_callback($arg) {
        $arg = current($arg);
        $options = get_option('plytix-settings-sizes-site');

        if ($options == false) {
            if ($arg == 'site_name') {
                $blog_name = get_bloginfo();
                echo "<input style=\"color:gray;\" name='plytix-settings-sizes-site[$arg]' type='text' value='$blog_name' />";
            } else {
                $blog_url =  get_site_url();
                echo "<input style=\"color:gray;\" name='plytix-settings-sizes-site[$arg]' type='text' value='$blog_url' />";
            }
        } else {
            echo "<input name='plytix-settings-sizes-site[$arg]' type='text' value='$options[$arg]' />";
        }
    }

    /**
    * Registering Dashboard Menu for Plytix
    */
    public function register_plytix_dashboard_menu() {
        add_menu_page(
            __('Plytix API Keys','plytix'),
            'Plytix',
            'manage_options',
            'plytix',
            array($this, 'showAPIAdminSettingsPage'),
            PLYTIX_PLUGIN_BASE_URL .'/admin/images/plytix_icon.png'
        );

        add_submenu_page(
            'plytix',
            __('API Keys','plytix'),
            'API Keys',
            'manage_options',
            'plytix',
            array($this, 'showAPIAdminSettingsPage')
        );

        add_submenu_page(
            'plytix',
            __('Plytix Site Configuration','plytix'),
            'Site Configuration',
            'manage_options',
            'plytix_site',
            array($this, 'showSiteAdminSettingsPage')
        );

        add_submenu_page(
            'plytix',
            __('Bulk Matching','plytix'),
            'Bulk Matching',
            'manage_options',
            'plytix_bulk',
            array($this, 'showBulkAdminSettingsPage')
        );

        add_submenu_page(
            'plytix',
            __('Update Pictures','plytix'),
            'Update Pictures',
            'manage_options',
            'plytix_update',
            array($this, 'showUpdatePicturesPage')
        );
    }

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Plytix_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Plytix_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/plytix-admin.css', array(), $this->version, 'all' );

	}

    /**
     * Ajax Call to upload selected picture
     */
    function upload_picture_callback() {
        //Retrive data from the view
        $to_product_gallery = $_REQUEST['to_product_gallery'];
        $is_thumbnail = $_REQUEST['is_thumbnail'];
        $post_id  = intval($_REQUEST['post_id']);
        $pictures  = Plytix_Admin_Functions::get_pictures_by_post_id($post_id);
        $plytix_orders = $_REQUEST['plytix_orders'];

        //Prepare the curl_multi
        $handles = [];
        $mh = curl_multi_init();
        foreach($plytix_orders as $plytix_order) {
            $ch = curl_init($pictures['pictures'][$plytix_order]['original']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_multi_add_handle($mh, $ch);
            $handles[] = $ch;
        }

        //Make the requests asynchronously
        $running = null;
        do {
            curl_multi_exec($mh, $running);
        } while ($running);

        //Retrieve the plytix pictures
        $images = array();
        foreach($handles as $handle) {
            $images[] = curl_multi_getcontent($handle);
            curl_multi_remove_handle($mh, $handle);
        }
        curl_multi_close($mh);

        //Add them to wordpress and the filesystem
        foreach ($plytix_orders as $id => $plytix_order) {
            $attachment = Plytix_Admin_Functions::insert_picture($post_id, $pictures['pictures'][$plytix_order], $images[$id]);

            //Setting it in the product gallery or as product image if needed
            if(($to_product_gallery == 'true')||($is_thumbnail == 'true')){
                Plytix_Admin_Functions::set_product_gallery_and_thumb($post_id, $attachment, $to_product_gallery, $is_thumbnail);
            }
        }

        if ($plytix_orders) {
            echo $plytix_orders;
        } else {
            echo 'FAIL';
        }
        wp_die();
    }

    /**
     * Hook into POST settings (Plytix Site) saving to register Site into Plytix
     */
    function plytix_site_settings_update_hook() {
        if (isset($_REQUEST['plytix-settings-sizes-site'])) {
            try {
                Plytix_Admin_Functions::update_site_info_to_plytix($_REQUEST);
                /**
                * On Success, we delete plytix_site_configuration_fail transient
                * And we set a flag to know all the configuration has been done.
                */
                update_option('plytix_site_configuration', "ok");
                delete_transient('plytix_config_first_time');
                set_transient('plytix_show_config_msg_ok', 1);
            } catch (Exception $e) {
                update_option('plytix_site_configuration', "error");
            }
        }
    }

    /**
     * Hook into POST settings (API) saving to register Site into Plytix
     */
    function plytix_api_settings_update_hook() {
        if (isset($_REQUEST['option_page']) && $_REQUEST['option_page'] == 'plytix-settings') {
            // If we have an API credentials and we change them, we have to
            // remove site configuration and restart the "wizard".
            if (get_option('plytix_api_credentials')) {
                delete_option('plytix-settings-sizes-site');
                delete_option('plytix_site_configuration');
                set_transient('plytix_config_first_time', 1);
            }

            if (Plytix_Admin_Functions::plytix_api_settings_validation_hook($_REQUEST)) {
                $options = get_option('plytix-settings');
                if ($options['api_id']!=$_REQUEST['plytix-settings']['api_id']) {
                    update_option('plytix_plugin_folder_id', '');
                    global $wpdb;

                    $query  = "DELETE FROM `".$wpdb->prefix."postmeta` ";
                    $query .= "WHERE " ;
                    $query .= "meta_key LIKE 'plytix_%' ";

                    $wpdb->get_results($query);
                }
                update_option('plytix_api_credentials', 'ok');
                if (get_transient('plytix_config_first_time')) {
                    set_transient('plytix_redirect', 1);
                } else {
                    set_transient('plytix_show_api_msg_ok', 1);
                }
            } else {
                update_option('plytix_api_credentials', 'error');
            }
        }
    }

    /**
     * Init Match modal only for woo products
     */
    public function match_modal_init() {
        global $post;

        if ((isset($post->ID) && $post->post_type == 'product')) {
            $plytix_id = get_post_meta($post->ID, 'plytix_product_id');
            if (empty($plytix_id) || current($plytix_id) == '') {
                Match_Modal::init_match_plugin($post->ID);
            }
        }
    }

	/**
	 * AJAX call to load the plytix metabox
	 */
	function get_product_info() {
		if (isset($_REQUEST['plytix_id'])){
			$product = Plytix_Admin_Functions::get_plytix_product_by_id($_REQUEST['plytix_id']);
			$response = array('brand_name' => $product->getBrandName(), 'product_name' => $product->getName() );
			echo json_encode($response);
			wp_die();
		}
	}

	/**
	 * AJAX call to load the plytix match modal
	 */
	function get_name_and_url() {
		$id = $_REQUEST['id'];

		$post = get_post( $id );
		$title = $post->post_title;
		$picture_post_id = get_post_meta($id, '_thumbnail_id');
		if (!empty($picture_post_id)) {
			$picture_url = get_post_meta($picture_post_id[0], '_wp_attached_file')[0];
			$upload_dir = wp_upload_dir()['url'];
			$url = strtok($upload_dir, '/') . '/';
			for ($i=0; $i < 3; $i++) {
				$url .= '/' . strtok('/');
			}
            if (get_current_blog_id()>1) {
                $url .= '/'.'sites'.'/'.get_current_blog_id();
            }
			$url = $url.'/'.$picture_url;
		} else {
			$url = "";
		}
		$response = array('product_name' => $title, 'thumb_url' => $url );
		echo json_encode($response);
		wp_die();
	}

	/**
	 * AJAX call to update a product's pictures
	 */
	function update_product() {
		$the_product = $_REQUEST['product'];
        // decode product
        $the_product = json_decode(stripslashes($the_product), true);
        if (isset($the_product)){
			Plytix_Admin_Functions::update_latest_pictures($the_product);
		}
	}

	/**
	 * AJAX call to update a product's pictures by ID
	 */
	function update_product_by_ID() {
		$post_id = $_REQUEST['product_id'];
		if (isset($post_id)){
			$product_to_update = Plytix_Admin_Functions::check_bunch_of_posts(array($post_id));
			if(isset($product_to_update)){
				Plytix_Admin_Functions::update_latest_pictures($product_to_update);
                update_post_meta($post_id, 'plytix_need_update', 0);
			}
		}
	}

	/**
	 * AJAX call to update all products's pictures at the table
	 */
    function update_all_products() {
    	global $wpdb;

    	$query = "SELECT DISTINCT `post_id` FROM `".$wpdb->prefix."postmeta` WHERE `meta_key` = 'plytix_product_id'";
    	$posts = $wpdb->get_results($query);
    	$posts_to_update = array();
    	foreach ($posts as $post) {
    	    $posts_to_update[] = $post->post_id;
    	}

    	$pieces = array_chunk($posts_to_update, 50);
    	foreach ($pieces as $key => $value) {
    		$products_to_update = Plytix_Admin_Functions::check_bunch_of_posts($value);
        	if(isset($products_to_update)){
        		Plytix_Admin_Functions::update_latest_pictures($products_to_update);
        	}
    	}
    }

	/**
	 *	AJAX Call to check all the products in order to find if anyone has new pictures
	 */
	function check_all_products() {
		 global $wpdb;

		 $query = "SELECT DISTINCT `post_id` FROM `".$wpdb->prefix."postmeta` WHERE `meta_key` = 'plytix_product_id'";
		 $posts = $wpdb->get_results($query);
		 $posts_to_check = array();
		 foreach ($posts as $post) {
		 	$posts_to_check[] = $post->post_id;
		 }

		 $pieces = array_chunk($posts_to_check, 50);
		 foreach ($pieces as $key => $value) {
		 		Plytix_Admin_Functions::check_bunch_of_posts($value);
		 }
	}

	/**
	 * AJAX Call to get the number of updates remaining
	 */
	function get_number_of_updates() {
		$posts_to_update = Plytix_Admin_Functions::get_number_to_update();
		echo $posts_to_update;
		wp_die();
	}

	/**
	 * AJAX Call to get the number of checks remaining
	 */
	function get_number_of_checks() {
		$time = $_REQUEST['myTime'];
		$posts_to_check = Plytix_Admin_Functions::get_number_to_check($time);
		echo $posts_to_check;
		wp_die();
	}

	/**
	 * Remove Plytix metadata from DB when a post is removed
	 */
	function remove_metadata($pid) {
		global $wpdb;
		$query = "SELECT DISTINCT `ID` FROM `".$wpdb->prefix."posts` WHERE `post_parent` = '".$pid."'";
		$posts = $wpdb->get_results($query);
		delete_post_meta($pid, 'plytix_need_update');
		delete_post_meta($pid, 'plytix_check_date');
		foreach ($posts as $key => $value) {
			delete_post_meta($value->ID, 'plytix_picture_id');
			delete_post_meta($value->ID, 'plytix_picture_version');
		}
	}
}
