<?php

/* Must to be used with WooCommerce */

namespace App\Model\Entity;

use Timber\Integrations\WooCommerce\Product as ProductBase;
use Spruce\Utility\Debug;

Class Product extends ProductBase {

    public function __construct( $post = null ) 
    {
        parent::__construct($post);
    }

    public function getPicture() {
        return wp_get_attachment_url( $this->get_image_id() );
    }

    public function getAttribute($attr)
    {
        $options = $this->get_product_attribute($attr);
        return $options;
    }

    public function getFirstCategory() {
        $terms = $this->terms();
        if (isset($terms[0])) {
            return $terms[0];
        }
        return false;
    }

    public function getTopLevelCategory() {
        $terms = $this->terms("product_cat");
        $category = isset($terms[0]) ? $terms[0] : null;
        foreach ($terms as $term) {
            if ($term->parent != 0) continue;
            $category = $term;
        }
        return $category;
    }

}