<?php
/**
 * The main template file
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists
 *
 * Methods for TimberHelper can be found in the /lib sub-directory
 *
 * @package  WordPress
 * @subpackage  Timber
 * @since   Timber 0.1
 */

 use Spruce\Utility\Debug;


$context = Timber\Timber::get_context();
$context['posts'] = new Timber\PostQuery();

$context["categories"] = Timber\Timber::get_terms("category");
$context["showCategories"] = true;

if (isset($isArchives) && $isArchives ==  true) {
	$context['title'] = 'Archive';
	if ( is_day() ) {
		$context['title'] = 'Archive: '.get_the_date( 'D M Y' );
	} else if ( is_month() ) {
		$context['title'] = 'Archive: '.get_the_date( 'M Y' );
	} else if ( is_year() ) {
		$context['title'] = 'Archive: '.get_the_date( 'Y' );
	} else if ( is_tag() ) {
		$context['title'] = single_tag_title( '', false );
	} else if ( is_category() ) {
		$context['title'] = single_cat_title( '', false );
		array_unshift( $templates, 'core/archive-' . get_query_var( 'cat' ) . '.twig' );
	} else if ( is_post_type_archive() ) {
		$context['title'] = post_type_archive_title( '', false );
		array_unshift( $templates, 'core/archive-' . get_post_type() . '.twig' );
	}
}

$templates = array('custom/core/archive.twig', 'custom/core/index.twig', 'core/archive.twig', 'core/index.twig' );

$sickIds = get_option( 'sticky_posts' );
$stickies = [];
$items = [];
foreach ($context['posts'] as $item) {
	if (in_array($post->ID, $sickIds)) $stickies[] = $post;
	$date = \DateTime::createFromFormat("Y-m-d H:i:s", $item->post_date);
	if ($date) {
		if (!isset($items[$date->format("Y")])) $items[$date->format("Y")] = [];
		$items[$date->format("Y")][] = $item;
	}
}

$context["stickies"] = $stickies;
$context["items"] = $items;
if ( is_home() ) {
	array_unshift( $templates, 'core/home.twig' );
	array_unshift( $templates, 'custom/core/home.twig' );
}
Timber\Timber::render( $templates, $context );
