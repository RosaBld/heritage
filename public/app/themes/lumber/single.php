<?php

use Spruce\Utility\Debug;
use App\Site;

/**
 * The Template for displaying all single posts
 *
 * Methods for TimberHelper can be found in the /lib sub-directory
 *
 * @package  WordPress
 * @subpackage  Timber
 * @since    Timber 0.1
 */

$type = get_post_type();
$postType = isset(Site::POST_TYPES[$type]) ? Site::POST_TYPES[$type] : Site::POST_TYPES["default"];
$context = Timber\Timber::get_context();
$post = Timber\Timber::query_post(false,  $postType );
$context['post'] = $post;

$context["categories"] = Timber\Timber::get_terms("category");

if ( post_password_required( $post->ID ) ) {
	Timber\Timber::render( 'core/single-password.twig', $context );
} else {
	Timber\Timber::render( array( 
		'custom/core/single-' . $post->ID . '.twig', 
		'custom/core/single-' . $post->post_type . '.twig', 
		'custom/core/single.twig',
		'core/single-' . $post->ID . '.twig', 
		'core/single-' . $post->post_type . '.twig', 
		'core/single.twig',
	), $context );
}
