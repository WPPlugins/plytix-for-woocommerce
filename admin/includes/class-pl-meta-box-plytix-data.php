<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * PL_Meta_Box_Plytix_Data Class.
 */
class PL_Meta_Box_Plytix_Data {
    public static function output( $post ) {
        global $post, $thepostid;
        $thepostid = $post->ID;
        ?>

        <div class="woocommerce">
            <div id="plytix-metabox"class="panel-wrap plytix_data">
                <ul class="plytix_data_tabs wc-tabs" style="display:none;">
                    <?php
                        $plytix_data_tabs = apply_filters( 'plytix_data_tabs', array(
                            'images' => array(
                                'label'  => __( 'Images', 'plytix' ),
                                'target' => 'images_plytix_data',
                                'class'  => array( 'show_if_matched' ),
                            ),
                            'info' => array(
                                'label'  => __( 'Info', 'plytix' ),
                                'target' => 'info_plytix_data',
                                'class'  => array( 'show_if_matched' ),
                            ),
                        ) );

                        foreach ( $plytix_data_tabs as $key => $tab ) {
                            $plytix_id = get_post_meta($post->ID, 'plytix_product_id');
                            if ($key == 'images') {
                                if (!(empty($plytix_id) || current($plytix_id) == '')) {
                                    ?><li class="<?php echo $key; ?>_options <?php echo $key; ?>_tab <?php echo implode( ' ' , $tab['class'] ); ?>">
                                        <a href="#<?php echo $tab['target']; ?>"><?php echo esc_html( $tab['label'] ); ?></a>
                                    </li><?php
                                }
                            } else {
                                if (!(empty($plytix_id) || current($plytix_id) == '')) {
                                    $tab['label'] = __( 'Info', 'plytix' );
                                } else {
                                    $tab['label'] = __( 'Add Images', 'plytix' );
                                }
                                ?><li class="<?php echo $key; ?>_options <?php echo $key; ?>_tab <?php echo implode( ' ' , $tab['class'] ); ?>">
                                    <a href="#<?php echo $tab['target']; ?>" onclick="print_plytix_info()"><?php echo esc_html( $tab['label'] ); ?></a>
                                </li><?php
                            }
                        }
                    ?>
                </ul>
                <div id="images_plytix_data" class="panel woocommerce_options_panel" style="display:none;">
                    <?php
                    $width = get_option('thumbnail_size_w');
                    $height = get_option('thumbnail_size_h');
                    $sizes = [$width.'x'.$height];
                    $plytix_id = get_post_meta($post->ID, 'plytix_product_id');
                    if (!(empty($plytix_id) || current($plytix_id) == '')) {
                        try {
                            $pictures = Plytix_Admin_Functions::get_pictures_by_post_id($post->ID, $sizes);
                            echo '<div class="options_group hide_if_grouped">';
                            for ($i = 0; $i < count($pictures['pictures']); $i++) {
                                echo '<p class="plytix_img_gallery" style="margin:8px 8px 8px 8px; border:1px solid #ccc; box-shadow:inset 0 0 0 5px #fff; float:left; width:150px; height:auto;">';
                                echo '<img id="__img_plytix" alt="" draggable="false" data-order="'.$i.'" style="background:white; width:inherit; height:auto;" src="'. $pictures['pictures'][$i]['thumbs'][$width.'x'.$height] .'">';
                                echo '</p>';
                            }
                            echo '</div>';
                        }catch(Exception $e) {
                            echo '<p class="plytix_warning_label">Failure to establish communication with Plytix. <a href="http://support.plytix.com/hc/en-us/requests/new" target="_blank">Contact Technical Support</a></p>';
                        }
                    }?>
                    <div class="options_group hide_if_grouped" style="display: none;">
                        <p class="form-field _plytix_field ">
                        </p>
                    </div>
                    <div class="options_group hide_if_grouped" style="display: block;">
                        <p class="form-field _submit_plytix_field ">
                            <label for="_confirmed" id="label_confirm_gallery"></label>
                            <a id="plytix_update_pictures_button" class="button button-primary button-large" style="display: none">Update pictures</a>
                            <button type="button" class="button-plytix to-media-gallery media-button button-primary button-large media-button-select" disabled="disabled" style="float: right">
                                <?php echo __('Add To Media Gallery', 'plytix'); ?>
                            </button>
                            <button type="button" class="button-plytix to-product-gallery media-button button-primary button-large media-button-select" disabled="disabled" style="float: right">
                                <?php echo __('Add To Product Gallery', 'plytix'); ?>
                            </button>
                            <button type="button" class="button-plytix is-thumbnail media-button button-primary button-large media-button-select" disabled="disabled" style="float: right">
                                <?php echo __('Add As Product Image', 'plytix'); ?>
                            </button>
                        </p>
                    </div>
                </div>

                <div id="info_plytix_data" class="panel woocommerce_options_panel" style="display:none;">
                    <div class="options_group hide_if_grouped">
                    <?php if ((isset($post->ID) && $post->post_type == 'product')) {
                            $plytix_id = get_post_meta($post->ID, 'plytix_product_id');
                            if (empty($plytix_id) || current($plytix_id) == '') {
                                ?><p class="form-field plytix_product_id_field ">
                                <label for="plytix_product_id">Plytix Search</label>
                                <a class="button button-primary button-large"
                                    onclick="openModal()"
                                    style="height: 25px; line-height: 23px;">
                                    Find this product
                                </a>
                                </p><?php
                            } else {
                                woocommerce_wp_hidden_input(array('id' => 'plytix_product_id'));
                                ?>
                                <div id="plytix_info_tab">
                                    <img id="plytix_loading_image" src="<?php echo plugins_url( 'images/plytix_loading.gif', dirname(__FILE__) );?>">
                                    <div id="plytix_exist_tab" style="display:none;">
                                        <h3 id="plytix_info_tab_table_title">Product matched with Plytix</h3>
                                        <table>
                                            <tr>
                                                <td rowspan="3">
                                                    <?php echo '<img alt="Picture not available" draggable="false" src="' . $pictures['pictures'][0]['thumbs'][$width.'x'.$height] . '">';?>
                                                </td>
                                                <td>
                                                    <p id="plytix_product_name"><b>Product name: </b></p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <p id="plytix_brand_name"><b>Brand name: </b></p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <p id="plytix_product_id_label"><b>Id: </b><i><?php echo $plytix_id[0]?></i></p>
                                                </td>
                                            </tr>
                                        </table>
                                        <a id="plytix_unmatch_product_button" class="button unmatch-button button-primary button-large">Unmatch this product</a>
                                        <a id="plytix_undo_button" class="button unmatch-button button-primary button-large" style="display: none">Undo</a>
                                        <p id="plytix_remember_save_product_message" class="plytix_warning_label" hidden>Update the product to save changes</p>
                                    </div>
                                </div>
                                <?php
                            }
                    } ?>
                    </div>
                    <p id="_label_message_plytix"><a href="" id="_href_message"></a></p>
                </div>
            </div>
        </div>

        <script>
        <?php
            if(isset($pictures)){
                $product_to_update = Plytix_Admin_Functions::check_bunch_of_posts(array($post->ID));
                if(!empty($product_to_update)){
                ?>
                    jQuery('#label_confirm_gallery').text('This product has some new pictures! Nice!');
                    jQuery('#label_confirm_gallery').css({'color':'green', 'width':'250px'});
                    jQuery('#plytix_update_pictures_button').toggle();

                    /**
                     * Update the product's pictures
                     */
                    jQuery('#plytix_update_pictures_button').click(function(){
                        var ajax_url = '<?php echo admin_url( 'admin-ajax.php' ); ?>';
                        var input_data = {
                            'action'  : 'update_product_pictures',
                            'product' : '<?php echo json_encode($product_to_update) ?>'
                        };
                        jQuery.post(ajax_url, input_data).done(function(){
                            jQuery('#label_confirm_gallery').text('');
                            jQuery('#plytix_update_pictures_button').toggle();
                            location.reload();
                        });
                    });
                  <?php
                }
            }
        ?>

        /**
         * Load info tab
         */
        var first_time = "yes";
        function print_plytix_info (){
            <?php
            if (isset($pictures)){
            ?>
                if (first_time == "yes"){
                    var ajax_url = '<?php echo admin_url( 'admin-ajax.php' ); ?>';
                    var input_data = {
                        'action'      : 'get_product',
                        'plytix_id'   : '<?php echo $plytix_id[0]?>'
                    };
                    jQuery.post(ajax_url, input_data).done(function(data) {
                        data = JSON.parse(data);
                        jQuery("#plytix_loading_image").toggle();
                        jQuery("#plytix_product_name").append(data.product_name);
                        jQuery("#plytix_brand_name").append(data.brand_name);
                        jQuery("#plytix_exist_tab").toggle();
                        first_time = "no";
                    });
                }
            <?php
            }
            ?>
        }

        /**
         * Unmatch the current product
         */
        var ply_pr_id = "";
        jQuery('#plytix_unmatch_product_button').click(function(){
            ply_pr_id = jQuery('#plytix_product_id').val();

            jQuery('#plytix_product_id').val("");
            jQuery('#plytix-metabox table').toggle();
            jQuery('#plytix_info_tab_table_title').html("The product will be unmatched");
            jQuery('#plytix_unmatch_product_button').toggle();
            jQuery('#plytix_undo_button').toggle();
            jQuery('#plytix_remember_save_product_message').toggle();
        });

        /**
         * Undo unmatch Product
         */
        jQuery('#plytix_undo_button').click(function(){
            jQuery('#plytix_product_id').val(ply_pr_id);
            jQuery('#plytix-metabox table').toggle();
            jQuery('#plytix_info_tab_table_title').html("Product matched with Plytix");
            jQuery('#plytix_unmatch_product_button').toggle();
            jQuery('#plytix_undo_button').toggle();
            jQuery('#plytix_remember_save_product_message').toggle();
        });

        /**
         * Select images from Plytix and add button
         */
        var img_to_add = [];
        jQuery('.plytix_img_gallery').click(function () {
            var id_to_add = jQuery(this).find('img').data('order');
            if (!jQuery(this).hasClass('selected-plytix')) {
                jQuery(this).addClass('selected-plytix');
                jQuery(this).css({'box-shadow':'inset 0 0 0 5px #0073aa'});
                img_to_add.push(id_to_add);
            } else {
                jQuery(this).removeClass('selected-plytix');
                var index = img_to_add.indexOf(id_to_add);
                jQuery(this).css({'box-shadow':'inset 0 0 0 5px #fff'});
                if (index > -1) {
                    img_to_add.splice(index, 1);
                }
            }
            if (img_to_add.length == 1) {
                jQuery('.button-plytix.is-thumbnail').attr("disabled", false);
            } else {
                jQuery('.button-plytix.is-thumbnail').attr("disabled", 'disabled');
            }
            if (img_to_add.length > 0) {
                jQuery('.button-plytix.to-product-gallery').attr("disabled", false);
                jQuery('.button-plytix.to-media-gallery').attr("disabled", false);
            } else {
                jQuery('.button-plytix').attr("disabled", 'disabled');
            }
        });

        /**
         * Add to WordPress Gallery
         */
        <?php
            $plytix_id = get_post_meta($post->ID, 'plytix_product_id');
            if (!(empty($plytix_id) || current($plytix_id) == '')) {
        ?>
                jQuery('._submit_plytix_field .button-plytix.to-media-gallery').click(function() {
                    ajax_upload_picture(img_to_add, false, false);
                    unselect_images();
                });
                jQuery('._submit_plytix_field .button-plytix.to-product-gallery').click(function() {
                    ajax_upload_picture(img_to_add, true, false);
                    unselect_images();
                });
                jQuery('._submit_plytix_field .button-plytix.is-thumbnail').click(function() {
                    ajax_upload_picture(img_to_add, true, true);
                    unselect_images();
                });
        <?php
            }
        ?>

        /**
         * Performs ajax connection with backend to upload picture to media
         *
         * @param plytix_order Order in Array to know with picture is selected
         */
        function ajax_upload_picture(plytix_orders, to_product_gallery, is_thumbnail) {
            var post_id = '<?php echo $post->ID; ?>';
            var ajax_url = '<?php echo admin_url( 'admin-ajax.php' ); ?>';
            var data = {
                'action'         : 'upload_plytix_picture',
                'post_id'        : post_id,
                'plytix_orders'  : plytix_orders,
                'to_product_gallery' : to_product_gallery,
                'is_thumbnail'   : is_thumbnail
            };

            jQuery('#label_confirm_gallery').text("");
            jQuery('._submit_plytix_field').append('<div class="blockUI blockOverlay" style="z-index: 1000; border: none; margin: 0px; padding: 0px; width: 100%; height: 100%; top: 0px; left: 0px; opacity: 0.6; cursor: wait; position: absolute; background: rgb(255, 255, 255);"></div>');
            jQuery('.button-plytix').attr("disabled", 'disabled');
            jQuery.post(ajax_url, data, function(response) {
                if (response == 'FAIL') {
                    jQuery('.button-plytix').attr("disabled", false);
                    jQuery('.blockUI').remove();
                    alert('Some error may happened, try it again');
                } else {
                    jQuery('#label_confirm_gallery').text("Successful - You might need to refresh the page");
                    jQuery('#label_confirm_gallery').css({'color':'green', 'width':'500px'});
                    jQuery('.button-plytix').attr("disabled", 'disabled');
                    jQuery('.blockUI').remove();
                }
            });
        }

        /**
         * Unselect all images
         */
        function unselect_images() {
            var list = jQuery('#images_plytix_data .plytix_img_gallery');
            jQuery.each(list, function(index, value) {
                if (jQuery(value).hasClass('selected-plytix')) {
                    jQuery(value).removeClass('selected-plytix');
                    var index = img_to_add.indexOf(index);
                    jQuery(value).css({'box-shadow':'inset 0 0 0 5px #fff'});
                    if (index > -1) {
                        img_to_add.splice(index, 1);
                    }
                }
            });
            if (img_to_add.length > 0) {
                jQuery('.button-plytix').attr("disabled", false);
            } else {
                jQuery('.button-plytix').attr("disabled", 'disabled');
            }
        }

        /**
         * Open Modal function
         */
        function openModal() {
            <?php if ( (!get_option('plytix_api_credentials')) ||
                        (get_option('plytix_api_credentials') == "error") ||
                        (!get_option('plytix_site_configuration')) ||
                        (get_option('plytix_site_configuration') == "error")):?>
            jQuery('#_href_message').text('<?php echo __('Before finding your Product in Plytix, you must first set up your plugin here', 'plytix') ?>');
            jQuery('#_href_message').attr('href','admin.php?page=plytix&first_time');
            <?php else: ?>
            var type_product = '<?php echo get_product( $post->ID )->post->post_status; ?>';
            if (type_product != 'auto-draft') {
                /**
                 * Open Match Modal PopUp
                 */
                if ( aut0poietic.backbone_modal.__instance === undefined ) {
                    aut0poietic.backbone_modal.__instance = new aut0poietic.backbone_modal.Application({
                        post_id: wp.media.view.settings.post.id, where_is_trigger: 'product'
                    });
                }
            } else {
                jQuery('#_label_message_plytix').text('<?php echo __('You must save a draft of this product, before finding it in Plytix', 'plytix') ?>');
                jQuery('#_label_message_plytix').css({'color':'red'});
            }
            <?php endif; ?>
        }
        </script>
        <?php
    }
}
