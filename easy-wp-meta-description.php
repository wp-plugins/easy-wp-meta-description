<?php
/*
Plugin Name: Easy WP Meta Description
Plugin URI: http://easy-wp-plugin.com/meta-description/
Description: Adds meta description to each post
Version: 1.1.0
Author: Mats Westholm
Author URI: http://easy-wp-plugin.com/mats-westholm/
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Domain Path: /languages
Text Domain: easy-wp-meta-description
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

$Easy_WP_Meta_Description = new Easy_WP_Meta_Description();
$Easy_WP_Meta_Description->run();

class Easy_WP_Meta_Description{

	private $plugin_name;
	private $meta_key;
	
	function __construct(){
		$this->plugin_name = 'easy-wp-meta-description';
		$this->meta_key = '_easy_wp_meta_description';
	}
	
	function run(){
		if( is_admin() ){
			add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
			add_action( 'add_meta_boxes', array( $this, 'description_meta_box') );
			add_action( 'save_post', array( $this, 'save_description' ) );
		}
		else{
			add_action( 'wp_head', array( $this, 'insert_meta_in_head' ) );
		}
	}

	function load_plugin_textdomain(){
		load_plugin_textdomain( $this->plugin_name, false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	function insert_meta_in_head(){
		$description = '';
		if( is_single() or is_page() ){
			global $post;
			$description = get_post_meta( $post->ID, $this->meta_key, true );
//			print "Content: $content<br>Post id: $post->ID";
		}
		elseif( is_tag() or is_category() or is_tax()){
			$remove = array( '<p>', '</p>' );
			$description = trim( str_replace( $remove, '', term_description() ) );
		}
		elseif( is_front_page() ){
			$description = get_bloginfo( 'description', 'display' );
		}
		if( $description ){
				?>
<meta name="description" content="<?php print $description; ?>">
				<?php
		}
	}

	function description_meta_box(){
		$id = 'add_description';
		$title =  'Easy WP Meta Description';
		$callback = array( $this, 'add_description_meta_box' );
		$context = 'normal';
		$priority = 'high';
		$callback_args = '';
		
		// get custom posttypes
		$args = array( 'public'   => true, '_builtin' => false );
		$output = 'names';
		$operator = 'and';
		$custom_posttypes = get_post_types( $args, $output, $operator );
		$builtin_posttypes = array( 'post', 'page' );
		$screens = array_merge( $builtin_posttypes, $custom_posttypes );
		foreach ( $screens as $screen ) {
			add_meta_box( $id, $title, $callback, $screen, $context,
				 $priority, $callback_args );
		}
	}

	function add_description_meta_box(){
		wp_nonce_field( 'add_description_meta_box', 'add_description_meta_box_nonce' );
		$post_id = get_the_ID();
		$value = get_post_meta( $post_id, $this->meta_key, true );?>
<div class="wp-editor-container">
<textarea class="wp-editor-area" id="easy_wp_description" name="add_description" cols="80" rows="5"><?php print $value; ?></textarea>
</div>
<p><?php 
		_e( 'Add a meta description to your HTML code', $this->plugin_name ); ?>. <?php
		_e( 'Character count', $this->plugin_name ); ?>: <span id="easy_wp_output"></span></p>
<script>
	jQuery( document ).ready( update_output );
	jQuery( '#easy_wp_description' ).on( 'keyup', update_output );
	function update_output( event ){
		var n = jQuery( '#easy_wp_description' ).val().length;
		jQuery( '#easy_wp_output' ).text( n );
	}
</script>
		<?php
	}
	
	function save_description( $post_id ){
		if( ! isset( $_POST['add_description_meta_box_nonce'] ) ){
			return;
		}
		if( ! wp_verify_nonce( $_POST['add_description_meta_box_nonce'], 'add_description_meta_box' ) ){
			return;
		}
		if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if( ! isset( $_POST['add_description'] ) ){
			return;
		}
		$data = sanitize_text_field(	$_POST['add_description'] );
		update_post_meta( $post_id, $this->meta_key, $data );
	}	

} // class


?>
