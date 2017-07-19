<?php
define("ALLOW_WOOCOMMERCE_PREVIOUS_TO_21", FALSE);
/**
 * Plytix Wordpress Plugin
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://plytix.com/
 * @since             1.0.0
 * @package           Plytix
 *
 * @wordpress-plugin
 * Plugin Name:       Plytix
 * Plugin URI:        http://plytix.com/
 * Description:       Get high quality product images directly from brands and enjoy customizable product analytics for all the items in your store.
 * Version:           0.7.9
 * Author:            Plytix
 * Author URI:        http://plytix.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       plytix
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Defines constants for our plugin
 */
function define_constants() {
    /**
     * Defining constants
     */
    define( "PLYTIX_PLUGIN_BASE"     , dirname( __FILE__ ) );
    define( "PLYTIX_PLUGIN_ADMIN"    , dirname( __FILE__ ) . '/admin' );
    define( "PLYTIX_PLUGIN_PUBLIC"   , dirname( __FILE__ ) . '/public' );
    define( "PLYTIX_VENDORS"         , dirname( __FILE__ ) . '/vendors' );
    define( "PLYTIX_PLUGIN_BASE_URL" , path_join(plugins_url(), basename(dirname(__FILE__))) );
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-plytix-activator.php
 */
function activate_plytix() {
    if ( class_exists( 'WC_Integration' ) && defined( 'WOOCOMMERCE_VERSION' ) && ( (version_compare( WOOCOMMERCE_VERSION, '2.1', '>=' )) || (ALLOW_WOOCOMMERCE_PREVIOUS_TO_21) ) ) {
        /**
         * Flag To indicate Plytix has been activated
         */
        add_option('plytix_activated', 1);
        /**
         * Welcome Screen
         */
        set_transient('_plytix_welcome_screen_activation_redirect', '1', 30);
    } else {
        deactivate_plugins(plugin_basename(__FILE__));
        exit ('Plytix Plugin requires the latest version of Woocommerce in order to work!');
    }
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-plytix-deactivator.php
 */
function deactivate_plytix() {
    delete_option('plytix_activated');
}

register_activation_hook( __FILE__  , 'activate_plytix' );
register_deactivation_hook( __FILE__, 'deactivate_plytix' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-plytix.php';
require plugin_dir_path( __FILE__ ) . 'updates.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_plytix() {
    if (get_option('plytix_activated')) {
        define_constants();
		require plugin_dir_path(__FILE__) . 'admin/includes/class-plytix-admin-functions.php';
        $plugin = new Plytix();
        $plugin->run();
    }
}
//run_plytix();
add_action( 'plugins_loaded', 'run_plytix' );

//Updates plytix plugin
add_action( 'plugins_loaded', 'update_plytix', 11 );

/**
 * Checking the version and updating
 */
function update_plytix() {
    $db_version =  get_option('plytix_db_version', '0.6.3');
    try {
        if (version_compare( $db_version, '2.0', '==' )) {
            update_option('plytix_db_version', '0.6.3');
            send_version_to_plytix('0.6.3');
            $db_version =  '0.6.3';
        }
        if (version_compare( $db_version, '0.7.4', '<=' )) {
            update_plytix_database_075();
            update_option('plytix_db_version', '0.7.5');
            send_version_to_plytix('0.7.5');
            $db_version =  '0.7.5';
        }
        if (version_compare( $db_version, '0.7.5', '<=' )) {
            update_option('plytix_db_version', '0.7.6');
            send_version_to_plytix('0.7.6');
            $db_version =  '0.7.6';
        }
        if (version_compare( $db_version, '0.7.6', '<=' )) {
            update_option('plytix_db_version', '0.7.7');
            send_version_to_plytix('0.7.7');
            $db_version =  '0.7.7';
        }
        if (version_compare( $db_version, '0.7.7', '<=' )) {
            update_option('plytix_db_version', '0.7.8');
            send_version_to_plytix('0.7.8');
            $db_version =  '0.7.8';
        }
        if (version_compare( $db_version, '0.7.8', '<=' )) {
            update_option('plytix_db_version', '0.7.9');
            send_version_to_plytix('0.7.9');
            $db_version =  '0.7.9';
        }

    } catch (Exception $e) {
        error_log("Error updating the Plytix database");
    }
}

/**
 * Send the plugin version to Plytix
 */
function send_version_to_plytix($version) {
    $options = get_option('plytix-settings');
    $client = new PlytixRetailersClient($options['api_id'], $options['api_password']);

    $new_site = new SiteModel();
    $info = array(
        'plytix_plugin_version' => $version
    );
    $new_site->setInfo($info);

    $options = get_option('plytix-settings-sizes-site');
    $url = Plytix_Admin_Functions::filter_url($options['site_url']);
    $sites = $client->sites()->listSites()->getResults();
    foreach ($sites as $site) {
        if ($site->getUrl() == $url && $site->getProtocol() == $options['protocol']) {
            $update = $site;
            break;
        }
    }
    $new_site->setId($update->getId());
    $client->sites()->update($new_site);
}


/**
 * Welcome Splash Screen
 * Todo: Move back to admin-welcome.php
 */
add_action('admin_menu', 'plytix_welcome_screen_pages');
add_action('admin_head', 'plytix_welcome_screen_remove_menus');
add_action('admin_init', 'plytix_welcome_screen_do_activation_redirect');

function plytix_welcome_screen_do_activation_redirect() {

    // Bail if no activation redirect
    if ( ! get_transient( '_plytix_welcome_screen_activation_redirect' ) ) {
        return;
    }

    // Delete the redirect transient
    delete_transient( '_plytix_welcome_screen_activation_redirect' );

    // Bail if activating from network, or bulk
    if ( is_network_admin() || isset( $_GET['activate-multi'] ) ) {
        return;
    }

    // Redirect to plytix-welcome
    wp_safe_redirect( add_query_arg( array( 'page' => 'plytix-welcome' ), admin_url( 'index.php' ) ) );

}

function plytix_welcome_screen_pages() {

    add_dashboard_page(
        'Welcome To Plytix Welcome Screen',
        'Welcome To Plytix Welcome Screen',
        'read',
        'plytix-welcome',
        'plytix_welcome_screen_content'
    );

}

function plytix_welcome_screen_content() {
    wp_enqueue_style  ('plytix-activation', plugin_dir_url( __FILE__ ) . './admin/css/activation.css');
    set_transient('plytix_config_first_time', 1);
    ?>
    <div id="plytix_welcome">
        <div class="header">
            <p class='logo'>
                <img src='<?php echo PLYTIX_PLUGIN_BASE_URL; ?>/admin/images/plytix_logo.png'/>
            </p>
        </div>
        <div class="info">
            <p> Welcome to Plytix!</p>
        </div>
        <div class="center">
            <p>Before you can configure your plugin, you must first have a Plytix account.
                <a href="https://auth.plytix.com/signup" target="_blank">Make one here for free.</a>
            </p>
        </div>
        <div class="return-to-settings">
            <a class="btn" href="<?php echo admin_url('index.php?page=plytix') ?>">Configure your Plytix Plugin Settings</a>
        </div>
        <p class="actions">
            <a class="btn" href="https://plytix.com/developers/" target='_blank'><?php _e( 'Documentation', 'eventon' ); ?></a>
            <a class="btn" href="https://support.plytix.com" target='_blank'><?php _e( 'Support', 'eventon' ); ?></a>
        </p>
    </div>

    <?php
}

function plytix_welcome_screen_remove_menus() {
    remove_submenu_page( 'index.php', 'plytix-welcome' );
}
