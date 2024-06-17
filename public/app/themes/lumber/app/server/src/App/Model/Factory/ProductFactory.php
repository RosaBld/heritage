<?php

/* Must to be used with WooCommerce */
namespace App\Model\Factory;

use Spruce\Utility\Debug;
use Timber\TermGetter;
use Timber\Timber;

Class ProductFactory {

	static protected $productClassName = "App\Model\Entity\Product";
	static protected $categoryClassName = "App\Model\Entity\ProductCategory";
	
	public function __contruct() 
	{

	}

	public function getProductsCategories($args=[], $maybeArgs=[])
	{
		return TermGetter::get_terms(array_merge([
			'taxonomy'   => "product_cat",
			'number'     => 15,
			'hide_empty' => true,
		], $args), $maybeArgs, self::$categoryClassName);
	}

	static public function getProductsFromCategory($categoryId, $args = []) 
	{
		$args = array_merge(array(
			'post_type'             => 'product',
			'post_status'           => 'publish',
			'ignore_sticky_posts'   => 1,
			'posts_per_page'        => '12',
			'tax_query'             => array(
				array(
					'taxonomy'      => 'product_cat',
					'field' 		=> 'term_id', 
					'terms'         => $categoryId,
					'operator'      => 'IN' // Possible values are 'IN', 'NOT IN', 'AND'.
				),
				array(
					'taxonomy'      => 'product_visibility',
					'field'         => 'slug',
					'terms'         => 'exclude-from-catalog', // Possibly 'exclude-from-search' too
					'operator'      => 'NOT IN'
				)
			),
			"paged" => get_query_var('paged')
		), $args);

		return Timber::get_posts($args, self::$productClassName);
	}

}
