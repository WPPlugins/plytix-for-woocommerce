/**
 * Backbone Application File
 * @internal Obviously, I've dumped all the code into one file. This should probably be broken out into multiple
 * files and then concatenated and minified but as it's an example, it's all one lumpy file.
 * @package aut0poietic.backbone_modal
 */

/**
 * @type {Object} JavaScript namespace for our application.
 */
var aut0poietic = {
	backbone_modal: {
		__instance: undefined
	}
};

/**
 * Flag to know from where this modal has been opened
 * values: product or bulk
 * @type {string}
 */
var where_is_trigger = 'bulk';
/**
 * If this is triggered from Product Page.
 * @type {boolean}
 */
var featured_popup = true;

/**
 * If this is triggered from Bulk Matching Page
 * @type {string}
 */
var post_id = '';

/**
 * Ajax URL from WP localized object
 * @type {string}
 */
var ajax_url = match_object.ajax_url;

/**
 * Primary Modal Application Class
 */
aut0poietic.backbone_modal.Application = Backbone.View.extend(
	{
		id: "backbone_modal_dialog",
		events: {
			"click .pl_backbone_modal-close": "closeModal",
			"click #btn-cancel": "closeModal",
			"click #btn-ok": "saveModal",
			"click .navigation-bar a": "doNothing"
		},

		/**
		 * Simple object to store any UI elements we need to use over the life of the application.
		 */
		ui: {
			nav: undefined,
			content: undefined
		},

		/**
		 * Container to store our compiled templates.
		 */
		templates: {},

		/**
		 * Instantiates the Template object and triggers load.
		 */
		initialize: function (options) {
			"use strict";

			this.options   = options;
			// Knows where this popup has been opened from
			featured_popup = options.featured;
			post_id        = options.post_id;

            if (options.where_is_trigger) {
                where_is_trigger = options.where_is_trigger;
            }

			/**
			 * If is triggered from:
			 * Product Page: post_id in match_object.post_id
			 * Bulk Match  : post_id in options.post_id
			 */
			if (!post_id) {
				post_id = match_object.post_id;
				where_is_trigger = 'product';
			}

			_.bindAll( this, 'render', 'preserveFocus', 'closeModal', 'saveModal', 'doNothing', 'selectize' );
			this.initialize_templates();
			this.render();
			this.selectize();

			var input_data = {
				'action' : 'get_name_and_picture',
				id : post_id
			}

			jQuery.post(ajax_url, input_data).done(function(data){
				data = JSON.parse(data);
				jQuery("#modal_product_name").html(data.product_name);
				if (data.thumb_url == ""){
					jQuery("#modal_product_picture_label").toggle();
					jQuery("#modal_product_picture").toggle();
				} else {
					jQuery("#modal_product_picture").attr("src", data.thumb_url);
				}
			});
		},


		/**
		 * Creates compiled implementations of the templates. These compiled versions are created using
		 * the wp.template class supplied by WordPress in 'wp-util'. Each template name maps to the ID of a
		 * script tag ( without the 'tmpl-' namespace ) created in template-data.php.
		 */
		initialize_templates: function () {
			this.templates.window = wp.template( "aut0poietic-modal-window" );
			this.templates.backdrop = wp.template( "aut0poietic-modal-backdrop" );
			this.templates.matchedProductLi = wp.template( "aut0poietic-matched-li" );
		},

		/**
		 * Assembles the UI from loaded templates.
		 */
		render: function () {
			"use strict";
			var self = this;

			// Build the base window and backdrop, attaching them to the $el.
			// Setting the tab index allows us to capture focus and redirect it in Application.preserveFocus
			this.$el.attr( 'tabindex', '0' )
				.append( this.templates.window() )
				.append( this.templates.backdrop() );

			// Ajax Call to Match Plytix Products by SKU and Name
			// First Recommendations
			var data = {
				'action' : 'match_plytix_product',
				'post_id': post_id
			};
			jQuery.post(ajax_url, data, function(response) {
				if (response == []) {
					var data = null;
				} else {
					var data  = JSON.parse(response);
				}
				print_recomendation_list(data, self);
				jQuery('#loading-block').hide();
				return false;
			});
			// Handle any attempt to move focus out of the modal.
			jQuery( document ).on( "focusin", this.preserveFocus );

			// set overflow to "hidden" on the body so that it ignores any scroll events while the modal is active
			// and append the modal to the body.
			jQuery( "body" ).css( {"overflow": "hidden"} ).append( this.$el );

			// Set focus on the modal to prevent accidental actions in the underlying page
			// Not strictly necessary, but nice to do.
			this.$el.focus();
		},

		/**
		 * Ensures that keyboard focus remains within the Modal dialog.
		 * @param e {object} A jQuery-normalized event object.
		 */
		preserveFocus: function ( e ) {
			"use strict";
			if ( this.$el[0] !== e.target && ! this.$el.has( e.target ).length ) {
				this.$el.focus();
			}
		},

		/**
		 * Closes the modal and cleans up after the instance.
		 * @param e {object} A jQuery-normalized event object.
		 */
		closeModal: function ( e ) {
			"use strict";
			e.preventDefault();
			this.undelegateEvents();
			jQuery( document ).off( "focusin" );
			jQuery( "body" ).css( {"overflow": "auto"} );
			this.remove();
			aut0poietic.backbone_modal.__instance = undefined;
		},

		/**
		 * Responds to the btn-ok.click event
		 * @param e {object} A jQuery-normalized event object.
		 * @todo You should make this your own.
		 */
		saveModal: function ( e ) {
			"use strict";
			e.preventDefault();

			var self = this;

			// Loading Block
			jQuery('#loading-block-matched').show();

			var selected_id  = jQuery('.matched_products .selected').attr('id');
			var my_plytix_id = 0;

			// FIRST:
			// Create Folder or get folder if exists
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
								/**
								 * There are two behaviours depending on where modal has been triggered from
								 * Bulk Matching: options.post_id Exists. Reload
								 * Product Page : reload with extra argument depending if it is featured or woo
								 */
								if (where_is_trigger == 'bulk') {
                                    location.reload();
								} else {
									self.closeModal(e);
									// reload #provisional with an argument to know we ave to open popup
									if (featured_popup == true) {
										window.location.replace(window.location.href + "&plytix=featured");
									} else {
										window.location.replace(window.location.href + "&plytix=woo");
									}

								}
							});
						});
				return false;
			});
		},

		/**
		 * Ensures that events do nothing.
		 * @param e {object} A jQuery-normalized event object.
		 * @todo You should probably delete this and add your own handlers.
		 */
		doNothing: function ( e ) {
			"use strict";
			e.preventDefault();
		},
		/**
		 * Events related with Selectize dropboxes
		 * @param e
		 */
		selectize: function( e ) {
			"use strict";
			var brand_id = 0;
			var self = this;

			var selectize_brand =
				jQuery('#search-account-input').selectize({
					valueField: 'id',
					labelField: 'name',
					searchField: 'name',
					options: [],
					create: false,
					onChange: function() {
						// Get Brand ID from User Selection
						brand_id = selectize_brand[0].selectize.getValue();
						//Ajax Call To Search By Brand ID
						update_recomendations(brand_id, '', self);
						// Show Product Search Box only when we have clicked on a brand already
						if (brand_id) {
							jQuery('#match_products_product').show();
							jQuery('#match_products_product .selectize-input input').attr("placeholder", '');
						} else {
							jQuery('#match_products_product').hide();
							// Reset Product Input Selectize
							selectize_product[0].selectize.destroy();
							selectize_product = product_input_selectize(self);
						}
					},
					render: {
						option: function(item, escape) {
							return '<div>' +
								'<span class="title">' +
								'<span class="name">' + escape(item.name) + '</span>' +
								'<br>' +
								'<span class="by">' + escape(item.website) + '</span>' +
								'</span>' +
								'</div>';
						}
					},
					load: function(query, callback) {
						if (query.length < 2) return callback();
						jQuery.ajax({
							url: match_object.ajax_url,
							type: 'POST',
							dataType: 'json',
							data:  {
								q: query,
								action: 'get_brands'
							},
							beforeSend: function() {
								jQuery('#loading-block-matched').show();
							},
							error: function() {
								callback();
							},
							success: function(res) {
								jQuery('#loading-block-matched').hide();
								callback(res);
							}
						});
					}
				});

			var selectize_product = product_input_selectize(self);
		}

	} );

/**
 * Initialize Product Input Selectize
 * It's wrapped in a separated function to allow us have control over it.
 * Every time it is used it will destroy itself and re-initialize.
 * This is because we need to clean its search cache and there are no native ways to do it.
 *
 * @param self
 * @returns {*}
 */
function product_input_selectize(self) {
	return jQuery('#search-product-input').selectize({
		valueField: 'id',
		labelField: 'name',
		searchField: 'name',
		options: [],
		create: false,
		render: {
			option: function(item, escape) {
				return '';
			}
		},
		load: function(query, callback) {
			//if (query.length < 2) return callback();
			jQuery.ajax({
				url: ajax_url,
				type: 'POST',
				dataType: 'json',
				data: {
					product_name : query,
					brand_id     : jQuery('#search-account-input')[0].selectize.getValue(),
					action       : 'get_products_by_brand_id'
				},
				beforeSend: function() {
					jQuery('#loading-block-matched').show();
				},
				error: function() {
					callback();
				},
				success: function(res) {
					// Tweak to show search query on placeholder
					jQuery('#match_products_product .selectize-input input').attr("placeholder", query);
					// Write onto matches div
					print_recomendation_list(res, self);
					var $input = jQuery('#match_products_product .selectize-input input');
					//backup current val
					var val = $input.val();
					// Reset Product Input and refill placeholder with query search
					// So we make sure, next time looks for the same query, it will trigger it again
					jQuery('#search-product-input')[0].selectize.destroy();
					product_input_selectize(self);
					$input = jQuery('#match_products_product .selectize-input input');
					$input.attr("placeholder", val);
					$input.val(val);
					$input.focus()
				}
			});
		}
	})
}

/**
 * It gets products matched by Brand (Plytix) ID and qs
 * If there is no brand (user deleted it) we show first recommendation
 *
 * @param brand_id Company Plytix ID
 * @param qs       Query String user is looking for
 * @param context  Context where apply changes (Modal)
 */
function update_recomendations(brand_id, qs, context) {
	jQuery('#loading-block-matched').show();
	if (brand_id) {
		var data = {
			'action'       : 'get_products_by_brand_id',
			'brand_id'     : brand_id,
			'product_name' : qs
		};
	} else {
		var data = {
			'action' : 'match_plytix_product',
			'post_id': post_id
		};
	}
	jQuery.post(ajax_url, data, function(response) {
		var data  = JSON.parse(response);
		print_recomendation_list(data, context);
		// On success, Hide loading blocking layer
		jQuery('#loading-block-matched').hide();
	});
}

/**
 * It gets the Data retrieved by the API and print it into Modal.
 *
 * @param data     Object retrieved by Ajax calls
 * @param context  Context where apply changes (Modal)
 */
function print_recomendation_list(data, context) {
	if ( typeof data === "object" && data.length > 0 ) {
		jQuery('#pl-no-results').hide();
		if (data.id) {
			// If this function is called directly from Product Input
			// It gives results one by one.
			context.ui.content = context.$( '.matched_products ul')
				.append( context.templates.matchedProductLi( {plytix_id: data.id, name: data.name, sku: data.sku, thumb: data.thumb } ) );
		} else {
			// If this function is called from Brand filter results callback
			// It gives all the results in an array
			// Clear previous list
			context.ui.content = context.$( '.matched_products ul')
				.empty();
			for (var i=0; i<data.length; i++) {
				context.ui.content = context.$( '.matched_products ul')
					.append( context.templates.matchedProductLi( {plytix_id: data[i].id, name: data[i].name, sku: data[i].sku, thumb: data[i].thumb } ) );
			}
		}
	} else {
		jQuery('#pl-no-results').show();
	}
	jQuery('#loading-block-matched').hide();
}


jQuery( document ).ajaxComplete(function() {
	defining_li_behaviour();
});

function defining_li_behaviour() {
	var rows = jQuery('.matched_products ul li');
	rows.click(function(){
		//When some LI clicked, Activate OK Button
		jQuery('#btn-ok').attr("disabled", false);
		rows.removeClass('selected');
		jQuery(this).addClass('selected');
	});
}
