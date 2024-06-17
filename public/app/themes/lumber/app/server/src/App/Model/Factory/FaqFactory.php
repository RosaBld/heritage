<?php

namespace App\Model\Factory;

use Spruce\Model\Factory;
use Spruce\Model\Post;
use Timber\Timber as Timber;
use Spruce\Utility\Debug;

Class FaqFactory extends Factory {

	protected $name = "faq";
	protected $entity = "App\Model\Entity\Faq";
	protected $categoryEntity = "App\Model\Term\Faq";
	protected $hasCategories = false;
	protected $categoryTaxonomy = "faq";
	protected $order = "ASC";
	protected $hierarchical = true;

	public function __construct() {
		parent::__construct();
	}
	
	public function init() {
		parent::init();
	}

}
