<?php
/**
 * The Template for the sidebar containing the main widget area
 *
 *
 * @package  WordPress
 * @subpackage  Timber
 */

$context = Timber\Timber::get_context();

Timber\Timber::render( array( 'custom/core/sidebar.twig', 'core/sidebar.twig' ), $context );
