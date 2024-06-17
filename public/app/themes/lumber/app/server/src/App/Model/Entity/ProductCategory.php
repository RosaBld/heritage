<?php

/* Must to be used with WooCommerce */
namespace App\Model\Entity;

use Timber\Term;
use Spruce\Utility\Debug;
use Timber\Image;

use App\Model\Factory\ProductFactory;

Class ProductCategory extends Term {

    protected $thumbnail = null;

    public function getThumbnail() 
    {
        if ($this->meta("thumbnail_id") != false 
            && $this->meta("thumbnail_id") != ""
            && is_null($this->thumbnail)) 
        {
            $this->thumbnail = new Image($this->meta("thumbnail_id"));
        }

        return $this->thumbnail;
    }

    public function getProducts($args=[])
    {
        return ProductFactory::getProductsFromCategory($this->ID, $args);
    }

}