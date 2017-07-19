<?php
/**
 * Backbone Templates
 * This file contains all of the HTML used in our modal and the workflow itself.
 *
 * Each template is wrapped in a script block ( note the type is set to "text/html" ) and given an ID prefixed with
 * 'tmpl'. The wp.template method retrieves the contents of the script block and converts these blocks into compiled
 * templates to be used and reused in your application.
 */


/**
 * The Modal Window, including sidebar and content area.
 * Add menu items to ".navigation-bar nav ul"
 * Add content to ".pl_backbone_modal-main article"
 */
?>

<script type="text/html" id='tmpl-aut0poietic-modal-window'>
	<div class="pl_backbone_modal">
		<div id="loading-block" class="loading-block" style="display: block"></div>
		<a class="pl_backbone_modal-close dashicons dashicons-no" href="#"
		   title="<?php echo __( 'Close', 'plytix' ); ?>"><span
				class="screen-reader-text"><?php echo __( 'Close', 'plytix' ); ?></span></a>

		<div class="pl_backbone_modal-content">
			<section class="pl_backbone_modal-main" role="main">
				<header><h1><?php echo __( 'Find the product using Plytix Search', 'plytix' ); ?></h1></header>
				<article>
					<div class="top">
						<div class="updated notice is-dismissible"><p><?php echo __( 'Match your product to the one in Plytix Search to access images and enable data tracking', 'plytix' ); ?></p></div>
					</div>
					<div class="match_products">
						<h3><?php echo __( 'Tune your search', 'plytix' ); ?></h3>

						<!-- Selectize Brand -->
						<div class="control-group">
							<h5><label for="search-account-input"><?php _e( 'Find the product\'s brand', 'plytix' ); ?></label></h5>
							<select id="search-account-input" class="brands" placeholder="<?php _e( 'Type a brand name', 'plytix' ); ?>"></select>
						</div>

						<!-- Selectize Product -->
						<div class="control-group" id="match_products_product">
							<h5><label for="search-product-input"><?php _e( 'Filter by product name', 'plytix' ); ?></label></h5>
							<select id="search-product-input" class="products" placeholder="<?php _e( 'Type a product name', 'plytix' ); ?>"></select>
						</div>

						<div class="control-group" id="product_description">
							<h3 id="modal_product_title"><?php echo __( 'Product to be matched', 'plytix' ); ?></h3>
							<p id="modal_product_subtitle">See possible matches for this product on the right</p>
							<h5>Name</h5>
							<span id="modal_product_name"></span>
							<h5 id="modal_product_picture_label">Picture</h5>
							<img src="" id="modal_product_picture">
						</div>

					</div>
					<div class="matched_products">
						<h3><?php echo __( 'Possible matches', 'plytix' ); ?></h3>
						<div id="pl-no-results">
							<span><?php echo __( 'There are no matches. Please refine your search.', 'plytix' ); ?></span>
						</div>
						<ul>
						</ul>
					</div>
				</article>
				<div id="loading-block-matched" class="loading-block" style="display: none"></div>
				<footer>
					<div class="inner text-right">
						<button id="btn-cancel"
								class="button button-large"><?php echo __( 'Cancel', 'plytix' ); ?></button>
						<button id="btn-ok"
								disabled="disabled"
								class="button button-primary button-large"><?php echo __( 'Done', 'plytix' ); ?></button>
					</div>
				</footer>
			</section>
		</div>
	</div>

</script>


<?php
/**
 * Li ROW for matched products
 */
?>
<script type="text/html" id='tmpl-aut0poietic-matched-li'>
	<li id="{{data.plytix_id}}">
		<div class="image">
			<img src="{{data.thumb}}">
		</div>
		<h3>{{ data.name }}</h3>
		<p>SKU:{{ data.sku }}</p>
	</li>
</script>


<?php
/**
 * The Modal Backdrop
 */
?>
<script type="text/html" id='tmpl-aut0poietic-modal-backdrop'>
	<div class="pl_backbone_modal-backdrop">&nbsp;</div>
</script>
