<?php

use Spruce\Utility\Debug;
use App\Helper\CacheContent;
use Spruce\Model\Post;

/**
 * Template Name: Home
 *
 * Methods for TimberHelper can be found in the /lib sub-directory
 *
 * @package  WordPress
 * @subpackage  Timber
 * @since    Timber 0.1
 */

$context = Timber\Timber::get_context();

class HomeContent extends CacheContent {

    protected $batch = [  ];

}

Timber\Timber::render( 
    'custom/pages/home.html.twig', 
    (new HomeContent($site, $context))->getContext() 
);
