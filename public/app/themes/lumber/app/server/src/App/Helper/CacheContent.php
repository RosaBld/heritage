<?php

namespace App\Helper;

class CacheContent {
    protected $batch = [];
    protected $site;
    protected $post = null;
    protected $context;

    public function __construct($site, $context) {
        $this->site = $site;
        $this->context = $context;
        //
        $this->context["post"] = $this->render("post");
        return $this;
    }

    public function post() {
        $this->post = $this->site->getCurrentPost();
        return $this->post;
    }

    public function render($var) {
        if (env("USE_CACHE")) {
            return \Timber\Helper::transient( sprintf('lumber_transient_%s_%s', get_the_ID(), $var), [ $this, $var] , env("CACHE_TIME") );
        } else {
            return call_user_func([ $this, $var ]);
        }
    }

    public function getContext() {
        $this->batchRender($this->batch);
        return $this->context;
    }

    public function batchRender($variables) {
        foreach ($variables as $variable) {
            $this->context[$variable] = $this->render($variable);
        }

        return $this;
    }
}
