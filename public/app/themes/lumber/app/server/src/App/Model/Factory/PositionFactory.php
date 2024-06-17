<?php

namespace App\Model\Factory;

use Spruce\Model\Factory;
use Spruce\Model\Post;
use Timber\Timber as Timber;
use Spruce\Utility\Debug;

Class PositionFactory extends Factory {

	protected $name = "position";
	protected $entity = "App\Model\Entity\Position";
	protected $categoryEntity = "App\Model\Term\Position";
	protected $hasCategories = true;
	protected $categoryTaxonomy = "position";
	protected $order = "DESC";
	protected $orderby = "date";
	protected $hierarchical = true;

	public function __construct() {
		parent::__construct();
	}
	
	public function init() {
		$this->cpt->icon("dashicons-admin-multisite");
		parent::init();
	}

}
