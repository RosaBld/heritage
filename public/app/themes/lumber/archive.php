<?php

use App\Site;
use Spruce\Utility\Debug;

/**
 * The template for displaying Archive pages.
 *
 * Used to display archive-type pages if nothing more specific matches a query.
 * For example, puts together date-based pages if no date.php file exists.
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * Methods for TimberHelper can be found in the /lib sub-directory
 *
 * @package  WordPress
 * @subpackage  Timber
 * @since   Timber 0.2
 */

$isArchive = true;

include __dir__ . '/index.php';

// $templates = array( 'custom/core/archive.twig', 'custom/core/index.twig', 'core/archive.twig', 'core/index.twig' );

// $context = Timber\Timber::get_context();

// $context['title'] = 'Archive';
// if ( is_day() ) {
// 	$context['title'] = 'Archive: '.get_the_date( 'D M Y' );
// } else if ( is_month() ) {
// 	$context['title'] = 'Archive: '.get_the_date( 'M Y' );
// } else if ( is_year() ) {
// 	$context['title'] = 'Archive: '.get_the_date( 'Y' );
// } else if ( is_tag() ) {
// 	$context['title'] = single_tag_title( '', false );
// } else if ( is_category() ) {
// 	$context['title'] = single_cat_title( '', false );
// 	array_unshift( $templates, 'core/archive-' . get_query_var( 'cat' ) . '.twig' );
// } else if ( is_post_type_archive() ) {
// 	$context['title'] = post_type_archive_title( '', false );
// 	array_unshift( $templates, 'core/archive-' . get_post_type() . '.twig' );
// }

// $context['posts'] = new Timber\PostQuery([ "limit" => -1], Site::POST_TYPES["default"]);
// $context["categories"] = Timber\Timber::get_terms("category");

// Timber\Timber::render( $templates, $context );
