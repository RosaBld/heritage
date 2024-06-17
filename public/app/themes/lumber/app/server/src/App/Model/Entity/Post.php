<?php

namespace App\Model\Entity;

use Spruce\Model\Post as Base;
use Timber\ImageHelper;

class Post extends Base {
    public function getLinkedNews() 
    {
        if ($this->get_field("linked_news_items") != "") {
            return array_map(function($id) {
                return new Post($id);
            }, $this->get_field("linked_news_items"));
        }
        return false;
    }
    public function getLinkedProjects() 
    {
        if ($this->get_field("linked_projects_items") != "") {
            return array_map(function($id) {
                return new Post($id);
            }, $this->get_field("linked_projects_items"));
        }
        return false;
    }
}
