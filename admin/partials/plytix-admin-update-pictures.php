<?php
/**
 * Provide a admin area view for Update Pictures
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
 if ($api_credentials == "error") :?>
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
if ($site_configuration == "error") :?>
    <div class="wrap">
        <div class="error" id="error_api">
            <?php _e('Site configuration is wrong, please set up properly ', 'plytix'); ?>
            <a href="?page=plytix_site"><?php _e('here', 'plytix'); ?></a>
        </div>
    </div>
    <?php /** we make sure nothing else can be performed */ die; ?>
<?php endif; ?>

<?php
if (isset($_POST['manual'])) {
    $post_ids = array();
    foreach($_POST['manual'] as $post_id => $post_id2) {
        $post_ids[] = $post_id;
    }
    try {
        Plytix_Admin_Functions::update_product_pictures_by_post_ids($post_ids);
    } catch (Exception $e) {
        error_log("Failure to establish communication with Plytix. Contact Technical Support");
    }
}
?>

<?php
wp_enqueue_style ( 'plytix-woo-admin-css' , plugins_url()   . '/woocommerce/assets/css/admin.css' );

$paged = (isset($_REQUEST['paged'])) ? $_REQUEST['paged'] : 1;
$args = array(
    'post_type'    => 'product',
    'paged'        => $paged,
    'posts_per_page' => 10,
    'meta_query'   => array(
                        array(
                          'key'		=> 'plytix_need_update',
                          'value'	=> 0,
                          'compare'	=> '>'
                        )
                      )
);
$wp_query = new WP_Query($args);
$wp_query->set('posts_per_page', 10);
$args_check = array(
    'post_type'    => 'product',
    'paged'        => $paged,
    'posts_per_page' => 10,
    'meta_query'   => array(
                        array(
                          'key'		=> 'plytix_need_update',
                          'value'	=> -1,
                          'compare'	=> '>'
                        )
                      )
);
$wp_query_check = new WP_Query($args_check);
$wp_query_check->set('posts_per_page', 10);
$products_to_check = count(Plytix_Admin_Functions::get_all_plytix_products_with_plytix_images());
$products_to_update = Plytix_Admin_Functions::get_all_plytix_products_updatables();
?>
<div id="loading-div-check" class="loading-div">
    <div id="img-text-loading-check" class="img-text-loading">
        <img id="plytix_loading_image_check" src="<?php echo plugins_url( 'images/plytix_loading.gif', dirname(__FILE__) );?>">
        <h3>Checking <span id="current_number_check">0</span> of <?php echo $products_to_check; ?><h3>
    </div>
</div>
<div id="loading-div" class="loading-div">
    <div id="img-text-loading" class="img-text-loading">
        <img id="plytix_loading_image" src="<?php echo plugins_url( 'images/plytix_loading.gif', dirname(__FILE__) );?>">
        <h3>Updating <span id="current_number">0</span> of <?php echo $products_to_update; ?><h3>
    </div>
</div>
<div id="bulk_updating" class="wrap">
    <h2><b><?php echo _e('Product Image Updates', 'plytix') ?></b></h2>
    <?php if ( $wp_query->have_posts() ) : ?>
        <div class="updated notice is-dismissible">
            <p><?php echo _e('Update your products with the latest images to keep your store looking fresh', 'plytix') ?></p>
        </div>
        <a class="button button-primary button-large" onclick="reload_table()">Refresh</a>
        <form id="updating-form" method="post">
            <div class="tablenav top">
                <div class="alignleft actions bulkactions">
                    <select id="update-action-selector-top" name="action">
                        <option selected="selected" value="-1">Update Products</option>
                    </select>
                </div>
                <input id="doaction" class="button action" type="submit" value="Apply">
                <a class="button-plytix media-button button-primary button-large media-button-select" onclick="update_all()">Update All</a>
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
                    <!-- <th scope="col" id="thumb" class="manage-column column-thumb"><span class="wc-image tips">Image</span></th> -->
                    <th scope="col" id="thumb" class="manage-column column-thumb">Product</th>
                    <!-- WooCommerce Name -->
                    <th style="" class="manage-column column-title sortable desc center" id="title" scope="col">
                        <span>Product Name</span>
                    </th>
                    <th style="" class="manage-column column-title sortable desc center" id="title" scope="col">
                        <span>Last Check</span>
                    </th>
                    <th style="" class="manage-column column-title sortable desc center" id="title" scope="col">
                        <span>Number of New Pictures</span>
                    </th>
                    <!-- Match button -->
                    <th style="" class="manage-column column-comments num sortable desc match center" id="comments" scope="col"></th>
                </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($wp_query->posts as $product) {
                        print_manual_matching_row($product);
                    }
                    ?>
                </tbody>
            </table>
            <div class="tablenav top">
                <div class="alignleft actions bulkactions">
                    <select id="bulk-action-selector-top" name="action">
                        <option selected="selected" value="-1">Update Products</option>
                    </select>
                </div>
                <input id="doaction" class="button action" type="submit" value="Apply">
                <?php pagination('bottom', $wp_query, $paged);?>
            </div>
        </form>
    <?php else : ?>
        <p><h1><?php _e( 'Hooray! All your products are up to date! Click the button to check for new updates.' ); ?></h1></p>
        <a class="button button-primary button-large" onclick="reload_table()">Refresh</a>
    <?php endif; ?>
</div>

<?php
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
        <td class="center">
            <strong><a title="Edit “<?php echo $product->post_title; ?>”" href="post.php?post=<?php echo $product->ID;?>&amp;action=edit" class="row-title"><?php echo $product->post_title; ?></a></strong>
            <div class="locked-info"><span class="locked-avatar"></span> <span class="locked-text"></span></div>
        </td>

        <td class="center">
            <?php echo date('Y-m-d', get_post_meta($product->ID, 'plytix_check_date')[0]); ?>
        </td>

        <td class="center">
            <?php echo get_post_meta($product->ID, 'plytix_need_update')[0]; ?>
        </td>

        <!-- Match button From Plytix -->
        <td class="match center">
            <div woocommerce_id="<?php echo $product->ID;?>" class="button button-large manual-button">Update</div>
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

    jQuery('.manual-button').click(function(){
        var post_id = jQuery(this).attr('woocommerce_id');
        var input_data = {
            'action'  : 'update_product_pictures_by_id',
            'product_id' : post_id
        };
        var ajax_url = '<?php echo admin_url( 'admin-ajax.php' ); ?>';
        jQuery.post(ajax_url, input_data).done(function(data){
            location.reload();
        });
    });

    function reload_table(){
        var r = confirm("Checking for updates may take a while if you have many products in the queue.");
        if (r == true) {
            jQuery("#loading-div-check").toggle();
            var myVar = setInterval(myTimerCheck, 2000);
            var myTime = <?php echo time() ?>;
            var input_data = {
                'action'  : 'reload_table'
            }
            var ajax_url = '<?php echo admin_url( 'admin-ajax.php' ); ?>';
            jQuery.post(ajax_url, input_data).done(function(){
                location.reload();
            });
            function myTimerCheck(){
                if(jQuery("#current_number_check").html() < <?php echo $products_to_check ?>){
                    var input_data = {
                        'action'  : 'get_number_of_checks',
                        'myTime'  : myTime
                    }
                    var ajax_url = '<?php echo admin_url( 'admin-ajax.php' ); ?>';
                    jQuery.post(ajax_url, input_data).done(function(data){
                        jQuery("#current_number_check").html(<?php echo $products_to_check ?> - data);
                    })
                }else {
                    clearInterval(myVar);
                    jQuery("#loading-div-check").toggle();
                }
            }
        }
    }

    function update_all(){
        var r = confirm("You are about to update all your products with the newest available images. This may take a while if you have many products in the queue.");
        if (r == true) {
            jQuery("#loading-div").toggle();
            var myVar = setInterval(myTimer, 2000);
            var input_data = {
                'action'  : 'update_all'
            }
            var ajax_url = '<?php echo admin_url( 'admin-ajax.php' ); ?>';
            jQuery.post(ajax_url, input_data).done(function(){
                location.reload();
            });
            function myTimer(){
                if(jQuery("#current_number").html() < <?php echo $products_to_update ?>){
                    var input_data = {
                        'action'  : 'get_number_of_updates'
                    }
                    var ajax_url = '<?php echo admin_url( 'admin-ajax.php' ); ?>';
                    jQuery.post(ajax_url, input_data).done(function(data){
                        jQuery("#current_number").html(<?php echo $products_to_update ?> - data);
                    })
                }else {
                    clearInterval(myVar);
                    jQuery("#loading-div").toggle();
                }
            }
        }
    }
</script>
