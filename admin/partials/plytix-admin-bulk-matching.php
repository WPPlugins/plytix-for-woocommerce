<?php
/**
 * Provide a admin area view for Bulk Matching
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
/**
 * First of All:
 * If We haven't set up Plytix Configuration
 * Send user to First Step: API Keys
 */

$api_credentials = get_option('plytix_api_credentials');
$site_configuration = get_option('plytix_site_configuration');
?>

<?php if ( (!$api_credentials) || (!$site_configuration) ) :?>
    <div class="wrap">
        <div class="update-nag">
            <?php _e('Before Starting to match. You must set up your Account ', 'plytix'); ?>
            <?php $url = (!$api_credentials) ? "?page=plytix" :  "?page=plytix_site"; ?>
            <a href="<?php echo $url; ?>"><?php _e('here', 'plytix'); ?></a>
        </div>
    </div>
    <?php /** we make sure nothing else can be performed */ die; ?>
<?php endif; ?>

<?php
/**
 * If Plytix API Keys are not properly setup we don't go into bulk matching
 */
?>
<?php if ($api_credentials == "error") :?>
    <div class="wrap">
        <div class="error" id="error_api">
            <?php _e('API Keys are wrong, please set them up properly ', 'plytix'); ?>
            <a href="?page=plytix"><?php _e('here', 'plytix'); ?></a>
        </div>
    </div>
    <?php /** we make sure nothing else can be performed */ die; ?>
<?php endif; ?>

<?php
/**
 * If Site is not properly set, we don't go into bulk matching
 */
?>
<?php if ($site_configuration == "error") :?>
    <div class="wrap">
        <div class="error" id="error_api">
            <?php _e('Site configuration is wrong, please set up properly ', 'plytix'); ?>
            <a href="?page=plytix_site"><?php _e('here', 'plytix'); ?></a>
        </div>
    </div>
    <?php /** we make sure nothing else can be performed */ die; ?>
<?php endif; ?>

<?php
/**
 * Templates for Matching Modal Popup
 */
require_once PLYTIX_PLUGIN_ADMIN . '/includes/match-modal/template-data.php';
?>

<?php
/**
 * Handle Form when Bulk Match option
 */
if (isset($_POST['matches'])) {
    foreach($_POST['matches'] as $post_id => $plytix_id) {
        try {
            $plytix_folder   = Plytix_Admin_Match_Functions::create_folder('Plytix WP Plugin');
            $subscription_id = Plytix_Admin_Match_Functions::add_product($plytix_folder, $plytix_id);
            Plytix_Admin_Match_Functions::match_plytix_id_with_woo_id($post_id, $subscription_id);
        } catch (Exception $e) {
            error_log("Failure to establish communication with Plytix. Contact Technical Support");
        }
    }
}

?>


<?php

/**
 * Load Magnific Popup JS dependency
 */
wp_enqueue_script ('magnific-media-js' , plugin_dir_url( __FILE__ ) . '../js/jquery.magnific-popup.min.js');
wp_enqueue_style  ('magnific-media-css', plugin_dir_url( __FILE__ ) . '../css/magnific-popup.css'         );

wp_enqueue_script( 'match_modal', plugin_dir_url( __FILE__ ) . '../includes/match-modal/js/modal.js', array(
    'jquery',
    'backbone',
    'underscore',
    'wp-util'
) );
wp_localize_script( 'match_modal', 'match_object',
    array(
        'ajax_url' => admin_url('admin-ajax.php')
    ) );
wp_enqueue_style( 'match_modal', plugin_dir_url( __FILE__ ) . '../includes/match-modal/css/modal.css' );

/**
 * Loading Selectize Jquery Library
 */
wp_enqueue_style ( 'plytix-woo-admin-css' , plugins_url()   . '/woocommerce/assets/css/admin.css' );
wp_enqueue_style ( 'plytix-selectize'     , plugin_dir_url( __FILE__ ) . '../includes/match-modal/css/selectize.css' );
wp_enqueue_script( 'plytix-selectize'     , plugin_dir_url( __FILE__ ) . '../includes/match-modal/js/selectize.min.js' );


?>

<?php
/**
 * Get all products with Identifier defined and without Plytix ID
 *
 */
$paged = (isset($_REQUEST['paged'])) ? $_REQUEST['paged'] : 1;
$args = array(
    'post_type'    => 'product',
    'paged'        => $paged,
    'posts_per_page' => 10,
    'meta_query'   =>
        array(
            array(
                'relation'  => 'OR',
                array(
                    'key'		=> 'plytix_product_id',
                    'compare'	=> 'NOT EXISTS'
                ),
                array(
                    'key'		=> 'plytix_product_id',
                    'value'		=> '',
                    'compare'	=> '='
                )
            )

        )
);
$wp_query = new WP_Query($args);
$wp_query->set('posts_per_page', 10);
/**
 * Get Plytix Data from Product List
 */
$error_message = "";
try {
    $matches = Plytix_Admin_Match_Functions::bulk_match($wp_query->posts);
} catch (Exception $e) {
    $error_message = '<p class="plytix_warning_label">Failure to establish communication with Plytix. <a href="http://support.plytix.com/hc/en-us/requests/new" target="_blank">Contact Technical Support</a></p>';
}

/**
 * Convert arrays keys to identifiers
 */
$matches_sorted = convert_array_keys_to_identifiers($wp_query->posts, $matches);
?>

<?php
/**
 * The loop
 */
?>


    <div class="wrap">
    <h2><b><?php echo _e('Bulk Product Matching Engine', 'plytix') ?></b></h2>
    <?php if ( $wp_query->have_posts() ) : ?>

    <div class="updated notice is-dismissible">
        <p><?php echo _e('Match your products with Plytix Search in order to track all your data.', 'plytix') ?></p>
    </div>
    <form id="matching-form" method="post">
        <div class="tablenav top">
            <div class="alignleft actions bulkactions">
                <select id="bulk-action-selector-top" name="action">
                    <option selected="selected" value="-1">Match Products</option>
                </select>
            </div>
            <input id="doaction" class="button action" type="submit" value="Apply">
            <?php pagination('top', $wp_query, $paged);?>
        </div>
        <table class="wp-list-table widefat fixed striped posts pl-bulk-match">
            <thead>
            <tr>
                <!-- Select All inputs -->
                <th style="" class="manage-column column-cb check-column center" id="cb" scope="col">
                    <label for="cb-select-all-1" class="screen-reader-text">Select All</label>
                    <input type="checkbox" id="cb-select-all-1">
                </th>
                <th scope="col" id="thumb" class="manage-column column-thumb"><span class="wc-image tips">Image</span></th>
                <!-- WooCommerce Name -->
                <th style="" class="manage-column column-title" id="title" scope="col">
                    <span>Your Product</span>
                </th>
                <!-- Product Plytix Name -->
                <th style="" class="manage-column column-author center" id="categories" scope="col">Matching Product</th>
                <!-- Brand -->
                <th style="" class="manage-column column-author center" id="author" scope="col">Matching Brand</th>
                <!-- Candidate Thumb -->
                <th style="" class="manage-column column-thumb center" id="cand-thumb" scope="col">Matching Image</th>
                <!-- Match button -->
                <th style="" class="manage-column column-comments num sortable desc match center" id="comments" scope="col">Match Action</th>
            </tr>
            </thead>

            <tbody id="the-list">
            <?php
            foreach ($wp_query->posts as $product) {
                $sku = current(get_post_meta($product->ID, '_sku'));
                if (!empty($matches_sorted[$sku])) {
                    print_match_row($product, $matches_sorted[$sku]);
                } elseif (!empty($matches_sorted[$product->post_title])) {
                    print_match_row($product, $matches_sorted[$product->post_title]);
                } else {
                    print_manual_matching_row($product);
                }

            }
            ?>
            </tbody>
        </table>
        <div class="tablenav bottom">
            <div class="alignleft actions bulkactions">
                <select id="bulk-action-selector-top" name="action">
                    <option selected="selected" value="-1">Match Products</option>
                </select>
                <input id="doaction" class="button action" type="submit" value="Apply">
            </div>
            <?php pagination('bottom', $wp_query, $paged);?>
        </div>
    </form>
    <?php wp_reset_postdata(); ?>
<?php else : ?>
    <p><h1><?php _e( 'Hooray! All your products are matched, nothing more to do here!' ); ?></h1></p>
<?php endif; ?>
    </div>
    <?php if ($error_message != "") {
        echo $error_message;
    } ?>
<?php

function convert_array_keys_to_identifiers($products_to_match, $matches) {
    $res = array();

    foreach ($products_to_match as $product_to_match) {
        $found = false;
        foreach ($matches->getResults() as $match) {
            if (current(get_post_meta($product_to_match->ID, '_sku')) and $match->getSku()
                and (current(get_post_meta($product_to_match->ID, '_sku')) == $match->getSku())) {
                $res[$match->getSku()] = $match;
            } elseif (current(get_post_meta($product_to_match->ID, '_sku')) and $match->getEan()
                and (current(get_post_meta($product_to_match->ID, '_sku')) == $match->getEan())) {
                $res[$match->getEan()] = $match;;
            } elseif (current(get_post_meta($product_to_match->ID, '_sku')) and $match->getJan()
                and (current(get_post_meta($product_to_match->ID, '_sku')) == $match->getJan())) {
                $res[$match->getJan()] = $match;
            } elseif (current(get_post_meta($product_to_match->ID, '_sku')) and $match->getUpc()
                and (current(get_post_meta($product_to_match->ID, '_sku')) == $match->getUpc())) {
                $res[$match->getUpc()] = $match;
            } elseif (current(get_post_meta($product_to_match->ID, '_sku')) and $match->getGtin()
                and (current(get_post_meta($product_to_match->ID, '_sku')) == $match->getGtin())) {
                $res[$match->getGtin()] = $match;
            } elseif ($product_to_match->post_title and $match->getName()
                and $product_to_match->post_title == $match->getName()) {
                $res[$match->getName()] = $match;
            } elseif ($found == false and $product_to_match->post_title and $match->getName()
                and (strripos($match->getName(), $product_to_match->post_title) > -1)) {;
                $res[$product_to_match->post_title] = $match;
                $found = true;
            }
        }
    }
    return $res;
}

function print_match_row ($product, $match) {
    ?>

    <tr class="iedit author-other level-0 post-<?php echo $product->ID;?> type-post status-publish format-standard hentry category-uncategorized" id="post-<?php echo $product->ID;?>">

        <!-- Checkbox -->
        <th class="check-column center" scope="row">
            <label for="cb-select-<?php echo $product->ID;?>" class="screen-reader-text"><?php $product->post_title; ?></label>
            <input type="checkbox" value="<?php echo $match->getId(); ?>" name="matches[<?php echo $product->ID;?>]" id="cb-select-<?php echo $product->ID;?>">
            <div class="locked-indicator"></div>
        </th>

        <td class="thumb column-thumb" data-colname="Image">
            <a href="post.php?post=<?php echo $product->ID;?>&amp;action=edit">
                <?php if (has_post_thumbnail($product->ID)) { echo get_the_post_thumbnail($product->ID, 'thumbnail'); } else { echo wc_placeholder_img(); }  ?>
            </a>
        </td>
        <!-- WooCommerce Product -->
        <td class="wp_product_td">
            <strong><a title="Edit “<?php echo $product->post_title; ?>”" href="post.php?post=<?php echo $product->ID;?>&amp;action=edit" class="row-title"><?php echo $product->post_title; ?></a></strong>
            <div class="locked-info"><span class="locked-avatar"></span> <span class="locked-text"></span></div>
        </td>

        <!-- Product Name From Plytix -->
        <td class="center">
            <?php echo $match->getName();?>
        </td>

        <!-- Brand From Plytix -->
        <td class="center">
            <?php print_r($match->getBrandName());?>
        </td>

        <!-- Product Thumb From Plytix -->
        <td class="thumb column-thumb center" style="text-align: left;">
            <a class="image-popup" href="<?php echo $match->getThumb(); ?>">
                <img class="attachment-thumbnail wp-post-image" width="150" height="150" alt="" src="<?php echo $match->getThumb(); ?>"/>
            </a>
        </td>

        <!-- Match button From Plytix -->
        <td class="match">
            <div id="<?php echo $product->ID;?>" plytix_id="<?php echo $match->getId(); ?>" class="button button-primary button-large">Confirm</div>
            <div style="display: none;" id="loading_button_<?php echo $product->ID;?>" class="btn btn-lg btn-primary m-progress loading_button">Button</div>
            <div woocommerce_id="<?php echo $product->ID;?>" class="button button-large manual-button">Manual</div>
        </td>

    </tr>

    <?php

}

function print_manual_matching_row ($product) {

    ?>
    <tr class="iedit author-other level-0 post-<?php echo $product->ID;?> type-post status-publish format-standard hentry category-uncategorized" id="post-<?php echo $product->ID;?>">

        <!-- Checkbox -->
        <th class="check-column center" scope="row">
            <label for="cb-select-<?php echo $product->ID;?>" class="screen-reader-text"><?php echo $product->post_title; ?></label>
            <input type="checkbox" value="<?php echo $product->ID;?>" name="manual[<?php echo $product->ID;?>]" id="cb-select-<?php echo $product->ID;?>">
            <div class="locked-indicator"></div>
        </th>

        <td class="thumb column-thumb" data-colname="Image">
            <a href="post.php?post=<?php echo $product->ID;?>&amp;action=edit">
                <?php if (has_post_thumbnail($product->ID)) { echo get_the_post_thumbnail($product->ID, 'thumbnail'); } else { echo wc_placeholder_img(); }  ?>
            </a>
        </td>
        <!-- WooCommerce Product -->
        <td class="wp_product_td">
            <strong><a title="Edit “<?php echo $product->post_title; ?>”" href="post.php?post=<?php echo $product->ID;?>&amp;action=edit" class="row-title"><?php echo $product->post_title; ?></a></strong>
            <div class="locked-info"><span class="locked-avatar"></span> <span class="locked-text"></span></div>
        </td>

        <!-- Brand From Plytix -->
        <td class="center">
            N/A
        </td>

        <!-- Product Name From Plytix -->
        <td class="center">
            N/A
        </td>

        <!-- Product Thumb From Plytix -->
        <td class="center">
            N/A
        </td>

        <!-- Match button From Plytix -->
        <td class="match">
            <div woocommerce_id="<?php echo $product->ID;?>" class="button button-large manual-button">Manual</div>
        </td>
    </tr>

    <?php

}

/**
 * It is a local copy of class-wp-list-table.php pagination method.
 * Since it is marked as private on documentation, better to use a copy one
 * instead of use original that may change without notice.
 *
 * @param $which Top or Bottom
 * @param $wp_query WP_Query Result
 * @param $paged Page we are currently
 */
function pagination( $which, $wp_query, $paged) {
    $total_items = $wp_query->found_posts;
    $total_pages = ($wp_query->found_posts <= 10) ? 1 : ceil($wp_query->found_posts / 10);

    $infinite_scroll = false;

    $output = '<span class="displaying-num">' . sprintf( _n( '1 item', '%s items', $total_items ), number_format_i18n( $total_items ) ) . '</span>';

    $current = $paged;

    $current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );

    $current_url = remove_query_arg( array( 'hotkeys_highlight_last', 'hotkeys_highlight_first' ), $current_url );

    $page_links = array();

    $disable_first = $disable_last = '';
    if ( $current == 1 ) {
        $disable_first = ' disabled';
    }
    if ( $current == $total_pages ) {
        $disable_last = ' disabled';
    }
    $page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>",
        'first-page' . $disable_first,
        esc_attr__( 'Go to the first page' ),
        esc_url( remove_query_arg( 'paged', $current_url ) ),
        '&laquo;'
    );

    $page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>",
        'prev-page' . $disable_first,
        esc_attr__( 'Go to the previous page' ),
        esc_url( add_query_arg( 'paged', max( 1, $current-1 ), $current_url ) ),
        '&lsaquo;'
    );

    if ( 'bottom' == $which ) {
        $html_current_page = $current;
    } else {
        $html_current_page = sprintf( "%s<input class='current-page' id='current-page-selector' title='%s' type='text' name='paged' value='%s' size='%d' />",
            '<label for="current-page-selector" class="screen-reader-text">' . __( 'Select Page' ) . '</label>',
            esc_attr__( 'Current page' ),
            $current,
            strlen( $total_pages )
        );
    }
    $html_total_pages = sprintf( "<span class='total-pages'>%s</span>", number_format_i18n( $total_pages ) );
    $page_links[] = '<span class="paging-input">' . sprintf( _x( '%1$s of %2$s', 'paging' ), $html_current_page, $html_total_pages ) . '</span>';

    $page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>",
        'next-page' . $disable_last,
        esc_attr__( 'Go to the next page' ),
        esc_url( add_query_arg( 'paged', min( $total_pages, $current+1 ), $current_url ) ),
        '&rsaquo;'
    );

    $page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>",
        'last-page' . $disable_last,
        esc_attr__( 'Go to the last page' ),
        esc_url( add_query_arg( 'paged', $total_pages, $current_url ) ),
        '&raquo;'
    );

    $pagination_links_class = 'pagination-links';
    if ( ! empty( $infinite_scroll ) ) {
        $pagination_links_class = ' hide-if-js';
    }
    $output .= "\n<span class='$pagination_links_class'>" . join( "\n", $page_links ) . '</span>';

    if ( $total_pages ) {
        $page_class = $total_pages < 2 ? ' one-page' : '';
    } else {
        $page_class = ' no-pages';
    }
    $_pagination = "<div class='tablenav-pages{$page_class}'>$output</div>";

    echo $_pagination;
}

?>

<script>
    jQuery(document).ready(function() {
        //Magnific Zoom configuration
        jQuery('.image-popup').magnificPopup(
            {
                type:'image',
                closeOnContentClick: true,
                closeBtnInside: false,
                fixedContentPos: true,
                mainClass: 'mfp-no-margins mfp-with-zoom', // class to remove default margin from left and right side
                image: {
                    verticalFit: true
                },
                zoom: {
                    enabled: true,
                    duration: 300 // don't forget to change the duration also in CSS
                }
            }
        );

        jQuery('.manual-button').click(function(){
            var woocommerce_id = jQuery(this).attr('woocommerce_id');
            var post_id        = woocommerce_id;
            var ajax_url       = '<?php echo admin_url( 'admin-ajax.php' ); ?>';
            /**
             * Open Match Modal PopUp
             */
            if ( aut0poietic.backbone_modal.__instance === undefined ) {
                aut0poietic.backbone_modal.__instance = new aut0poietic.backbone_modal.Application({post_id: post_id});
            }

        });


        //Ajax Call to Match one Product From the List
        jQuery('.button-primary').click(function(){
                var selected_id = jQuery(this).attr('plytix_id');
                var post_id     = jQuery(this).attr('id');
                var ajax_url    = '<?php echo admin_url('admin-ajax.php'); ?>';

                //Replace Match Button to Icon Loading
                jQuery(this).replaceWith( jQuery('#loading_button_' + post_id) );
                jQuery('#loading_button_' + post_id).show();
                var data = { 'action' : 'create_or_retrieve_folder' };
                    jQuery.post(
                        ajax_url,
                        data,
                        function(folder_id) {
                            // SECOND:
                            // Subscribe Product into Folder
                            var data = {
                                'action': 'add_product_to_bank',
                                'folder_id': folder_id,
                                'product_id': selected_id
                            };
                            jQuery.post(
                                ajax_url,
                                data,
                                function (my_plytix_id) {
                                    my_plytix_id = my_plytix_id;
                                    // THIRD:
                                    // Once the Product is saved, Save Plytix ID into WP
                                    var data = {
                                        'action'    : 'save_plytix_product',
                                        'post_id'   : post_id,
                                        'plytix_id' : my_plytix_id
                                    };
                                    jQuery.post(ajax_url, data, function(response) {
                                        // reload #provisional
                                        location.reload();
                                        //jQuery('#loading_button').hide();
                                    });
                                });
                            return false;
                        });
            }
        );
    });
</script>
