<?php
/**
 * Provide a admin area to setup a site
 * If the API keys are wrong (_transient_plytix_api_credential_fail)
 * You cannot register your site.
 *
 *
 * @link       http://plytix.com/
 * @since      1.0.0
 *
 * @package    Plytix
 * @subpackage Plytix/admin/partials
 */
?>

<?php
?>

<?php $first_time  = get_transient('plytix_config_first_time'); ?>
<?php $js_control  = false; ?>
<?php if ($first_time) : ?>
    <?php
    if (get_transient('plytix_configurarion_setup') !== "done") {
        wp_enqueue_script( 'jsTimezoneDetect', plugin_dir_url( __FILE__ ) . '../js/jstz.min.js' );
        $js_control = true;
    }
    ?>
<?php endif; ?>

<div class="wrap">

    <?php
    /**
     * First of All:
     * If We haven't set up Plytix Configuration and is not first time
     * Send user to Wizard
     */
    ?>
    <?php if (get_transient('plytix_show_config_msg_ok')) :?>
        <div class="updated notice is-dismissible">
            <p><?php _e('Your configuration has been saved.', 'plytix'); ?></p>
        </div>
        <?php delete_transient('plytix_show_config_msg_ok'); ?>
    <?php elseif ( (get_option('plytix_api_credentials') === FALSE) || ((get_option('plytix_api_credentials') == 'error') && (get_transient('plytix_config_first_time'))) ) :?>
        <div class="update-nag">
            <p>
                <?php _e('Before setting your Site, you must set up your API Keys ', 'plytix'); ?>
                <a href="?page=plytix"><?php _e('here', 'plytix'); ?></a>
            </p>
        </div>
        <?php /** we make sure nothing else can be performed */ die; ?>
    <?php endif; ?>

    <?php
    /**
     * API Credentials Check
     */
    ?>
    <?php if (get_option('plytix_api_credentials') == "error") :?>
        <div class="error" id="error_api">
            <p>
                <?php _e('API Keys are wrong, please set them up properly ', 'plytix'); ?>
                <a href="?page=plytix"><?php _e('here', 'plytix'); ?></a>
            </p>
        </div>
        <?php /** we make sure nothing else can be performed */ die; ?>
    <?php endif; ?>

    <?php
    /**
     * Site configuration Failure
     */
    ?>
    <?php if (get_option('plytix_site_configuration') == "error") :?>
        <div class="error">
            <?php _e('Something is failing. Please try again.', 'plytix'); ?>
        </div>
    <?php endif; ?>

    <h2><?php echo esc_html( get_admin_page_title() ); ?> Settings</h2>
    <?php if($first_time && ($js_control)): ?>
        <h3><?php _e('Step 2: Fill in the information below and click Finish', 'plytix'); ?></h3><hr>
    <?php endif; ?>

    <form id="example-1-form" method="post" action="options.php">
        <?php
        settings_fields( 'plytix-settings-sizes-site' );
        do_settings_sections( 'plytix-settings-sizes-site' );
        if ($first_time) {
            echo "<span style='margin-right: 10px;'><a href='" . admin_url('index.php?page=plytix&first_time') . "' class='button' id='pl_cancel'>" . __('Go Back', 'plytix') . "</a></span>";
            submit_button(__('Finish','plytix'), 'primary', 'submit', False);
        } else {
            submit_button();
        }
        ?>
    </form>
</div>

<?php // Little Script to avoid user leaving withouth filling the information ?>
<?php if ($js_control) : ?>
<script>
    var btns = document.querySelectorAll('#submit, #pl_cancel');
    for (i = 0; i < btns.length; i++) {
        btns[i].addEventListener("click", function(){
            window.btn_clicked = true;
        });
    }
    window.onbeforeunload = function(){
        if(!window.btn_clicked){
            return 'Please, click on Finish before leaving, your information needs to be saved.';
        }
    };

    jQuery('document').ready(function(){
        // TimeZone Detection
        var tz        = jstz.determine();
        var time_zone = tz.name();
        document.getElementById('timezone').value = time_zone;
    });
</script>
<?php endif; ?>
<script>
    jQuery('document').ready(function(){
        // If error, dont submit the button
        if (jQuery("#error_api").length == 1) {
            jQuery('#submit').prop('disabled', true);
        }
    });
</script>
