<?php

use Spruce\Utility\Debug;

function is_blog () {
    return ( is_archive() || is_author() || is_category() || is_home() || is_single() || is_tag()) && 'post' == get_post_type();
}

function current_id_page() {
	return is_blog() ? get_option( 'page_for_posts' ) : get_the_ID();
}

$autoloader->add("App",__dir__ . "/app/server/src");

if ( ! class_exists( 'Timber\Timber' ) ) {
	add_action( 'admin_notices', function() {
		echo '<div class="error"><p>Timber not activated. Make sure you activate the plugin in <a href="' . esc_url( admin_url( 'plugins.php#timber' ) ) . '">' . esc_url( admin_url( 'plugins.php') ) . '</a></p></div>';
	});
	
	add_filter('template_include', function($template) {
		return get_stylesheet_directory() . '/static/no-timber.html';
	});
	
	return;
}


add_action('init', 'start_session', 1);
function start_session() {
	if(!session_id()) {
		session_start();
	}
}

add_action('wp_logout','end_session');
add_action('wp_login','end_session');

function end_session() {
	session_destroy ();
}

if (!class_exists("PLL_Admin_Notices")) {
	function pll__( $string ) { 
        return $string; 
  }
	function pll_register_string(  $name,  $string,  $context = 'polylang',  $multiline = false ) {
		return $name;
	}
	function pll_count_posts(  $lang,  $args = array() ) { return 1; }
	function pll_the_languages(  $args = '' ) { return true; }
	function pll_current_language() { return true; }
	function pll_home_url() { return home_url(); }
}

$site = new App\Site();

function enqueue_glidejs() {
	// Register Glide.js
	wp_register_script('glidejs', get_template_directory_uri() . '/home.js', array('jquery'), '1.0', true);

	// Enqueue Glide.js
	wp_enqueue_script('glidejs');
}
add_action('wp_enqueue_scripts', 'enqueue_glidejs');

$filePath = __DIR__ . '/home.js';