<?php
/**
 * Search results page
 *
 * Methods for TimberHelper can be found in the /lib sub-directory
 *
 * @package  WordPress
 * @subpackage  Timber
 * @since   Timber 0.1
 */

$templates = array( 'custom/core/search.twig', 'custom/core/archive.twig', 'custom/core/index.twig', 'core/search.twig', 'core/archive.twig', 'core/index.twig' );

$context          = Timber\Timber::get_context();
$context['title'] = 'Résultats pour la recherche ' . get_search_query();
$context['posts'] = new Timber\PostQuery();
$context["is_search_page"] = true;

Timber\Timber::render( $templates, $context );
