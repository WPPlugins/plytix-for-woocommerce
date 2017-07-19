<?php

class Match_Modal {

	protected static $post_id;

	static function init_match_plugin($post_id) {
		self::$post_id = $post_id;
		self::add_scripts();
		add_action( 'admin_footer-post.php'    , array('Match_Modal', 'add_templates' ) );
		add_action( 'admin_footer-post-new.php'    , array('Match_Modal', 'add_templates' ) );
	}


	/**
	 * Dumps the contents of template-data.php into the foot of the document.
	 * WordPress itself function-wraps the script tags rather than including them directly
	 * ( example: https://github.com/WordPress/WordPress/blob/master/wp-includes/media-template.php )
	 * but this isn't necessary for this example.
	 */
	public static function add_templates() {
		include 'template-data.php';
	}


	/**
	 * Enqueue the script and styles necessary to for the modal.
	 */
	public static function add_scripts( ) {
		$base = plugin_dir_url( __FILE__ );
		wp_enqueue_script( 'match_modal', $base . 'js/modal.js', array(
			'jquery',
			'backbone',
			'underscore',
			'wp-util'
		) );
		wp_localize_script( 'match_modal', 'match_object',
			array(
				'post_id'  => self::$post_id,
				'ajax_url' => admin_url('admin-ajax.php')
			) );
		wp_enqueue_style( 'match_modal', $base . 'css/modal.css' );

		/**
		 * Loading selectize Jquery Library
		 */
		wp_enqueue_style ( 'plytix-selectize' , $base . 'css/selectize.css' );
		wp_enqueue_script( 'plytix-selectize' , $base . 'js/selectize.min.js' );
	}

	/**
	 * AJAX method that returns an HTML-fragment containing the various templates used by Backbone
	 * to construct the UI.
	 * @internal Obviously, this is part of a particular Backbone pattern that I enjoy using.
	 *           Feel free to remove this method ( and the associated action hook ) if you assemble the
	 *           UI using direct DOM manipulation, jQuery objects, or HTML strings.
	 */
	public function get_template_data() {
		include( 'template-data.php' );
		die(); // you must die from an ajax call
	}

}
