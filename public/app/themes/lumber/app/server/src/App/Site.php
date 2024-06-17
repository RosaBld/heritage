<?php

namespace App;

use Spruce\Kernel\Site as CoreSite;
use Spruce\Utility\Debug;
use App\Controller as Controller;
use App\Model as Model;
use Timber\Timber;
use Twig_SimpleFunction;
use MailchimpTransactional;

use App\Service\MailerService;

class Site extends CoreSite {

	protected $useDefaultTheme = false;
	public $ffCacheTime = 600;
	public $useCache = false;
	protected $removeBlogPosts = false;

	const POST_TYPES = [
        "default" => "App\Model\Entity\Post",
		"position" => "App\Model\Entity\Position",
		// "faq" => "App\Model\Entity\Faq",
    ];

	public function __construct() {
		$this->menus["legal_menu"] = "Legal Menu";
		unset($this->menus["social_menu"]);
		$this->useCache = env("USE_CACHE");
		$this->ffCacheTime = env("CACHE_TIME");
		if ($this->useCache == false) {
			$this->ffCacheTime = false;
		}
		parent::__construct();
		Timber::$cache = $this->useCache;

		add_action( 'customize_register', [ $this, 'customizeRegister'] );
		$this->initCustomControllers();
		// add_action( 'wpcf7_init', [ $this, 'custom_add_form_tag_projectlist' ] );
	}

	public function custom_add_form_tag_projectlist() {
		wpcf7_add_form_tag( array( 'projectlist', 'projectlist*' ), [$this, 'custom_projectlist_form_tag_handler'], true );
	}

	public function custom_projectlist_form_tag_handler( $tag ) {
	
		$tag = new \WPCF7_FormTag( $tag );
	
		if ( empty( $tag->name ) ) {
			return '';
		}
	
		$productslist = sprintf("<option>%s</option>", pll__('Choose product'));
	
		$query = new \WP_Query(array(
			'post_type' => 'property',
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'orderby'       => 'title',
			'order'         => 'ASC',
		));
	
		while ($query->have_posts()) {
			$query->the_post();
			$productslist .= sprintf( '<option value="%1$s">%2$s</option>', esc_html( get_the_ID() ), esc_html( get_the_title() ) );
		}
	
		wp_reset_query();
	
		$productslist = sprintf(
			'<span class="wpcf7-form-control-wrap"><select name="%1$s" id="%2$s" class="field small wpcf7-form-control wpcf7-select small field">%3$s</select></span>', $tag->name,
		$tag->name . '-options',
			$productslist );
	
		return $productslist;
	}

	public function carousel($type="default") {
		switch($type):
			case "position":
				return 'data-type="slider" data-view="3.2" data-gap="40" data-xs-gap="50" data-md-view="2" data-lg-view="2.7" data-sm-view="1.5" data-xs-view="1" data-xs-focus="0"';
			break;
			case "news":
				return 'data-type="slider" data-view="2" data-gap="40" data-xs-gap="50" data-md-view="1.35" data-lg-view="2" data-sm-view="1" data-xs-view="1" data-xs-focus="0"';
			break;
			case "bigposition":
				return 'data-type="slider" data-autoplay="5000" data-view="1" data-gap="40" data-xs-gap="40" data-md-view="1" data-lg-view="1" data-sm-view="1" data-xs-view="1" data-xs-focus="0"';
			break;
		endswitch;
	}

	public function getPagesByTemplate($tpl) {
		$args = array(
            'meta_key' => '_wp_page_template',
            'meta_value' => "tpl-".$tpl.".php",
			'post_type' => 'page',
			'orderby' => 'menu_order',
        );

		$query = new \WP_Query($args);

		if ($query) {
			return $query->posts;
		}

		return [];
	}

	public function initCustomControllers() {
		$site = $this;
		if (class_exists('woocommerce')) {
			$site->wooCtrl = new Controller\WooCommerceController($this);
		}

		// $site->newsletterCtrl = new Controller\NewsletterSubscriptionController($this);
		add_action( 'acf/init', function() use ($site) {
			$GAPI = env("GOOGLE_API_KEY");
			if ($GAPI != "null")
				acf_update_setting('google_api_key', $GAPI);

		});
		
		// add_action( 'plugins_loaded', function() use ($site) {
		// 	remove_menu_page( 'admin.php?page=image-map-pro-wordpress-v6fdsfsd' );

		// });

		add_action('admin_head', function () {
			echo '<style>#toplevel_page_image-map-pro-wordpress-v6fdsfsd { display: none!important; } </style>';
		});

		return $this;
	}

	public function add_filters()
	{
		parent::add_filters();
		add_filter( 'get_twig', array( $this, 'overdrive_twig' ) );
		add_filter( 'timber/context', function ($context) {
			$context["posts_categories"] = Timber::get_terms("category");
			$context["project_categories"] = Timber::get_terms("projectcategory");
			$context["status_categories"] = Timber::get_terms("statuscategory");
			$GAPI = env("GOOGLE_API_KEY");
			if ($GAPI != "null")
				$context["gmapi"] = $GAPI;
			return $context;
		});

		add_shortcode('button', function ($atts, $content = null) {
			$default = array(
				'link' => '#',
				'outline' => false,
				'arrow' => false,
			);
			$a = shortcode_atts($default, $atts);
			$content = do_shortcode($content);
			return '<a href="'.($a['link']).'" class="Button'. ($a["arrow"] != false ? " arrow" : "") .''. (($a["outline"] != false) ? " outline" : "") .'"><span>'.$content.'</span>'.($a["arrow"] != false ? '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="15.39" height="15.104" viewBox="0 0 15.39 15.104">
						<defs>
							<clipPath id="clip-path">
								<rect width="15.104" height="15.39" fill="#fff"/>
							</clipPath>
						</defs>
						<g transform="translate(0 15.104) rotate(-90)">
							<g transform="translate(0 0)" clip-path="url(#clip-path)">
								<path d="M14.923,7.39,13.9,6.37a.621.621,0,0,0-.88,0L8.434,11.012l.437-.965a.745.745,0,0,0,.065-.3V.734A.734.734,0,0,0,8.2,0H6.9A.734.734,0,0,0,6.17.734v9.01a.726.726,0,0,0,.065.3l.437.965L2.083,6.37a.621.621,0,0,0-.88,0L.183,7.39a.621.621,0,0,0,0,.88l5.429,5.483,1.461,1.461a.612.612,0,0,0,.48.174.612.612,0,0,0,.48-.174l1.461-1.461,5.431-5.483a.623.623,0,0,0,0-.88M7.593,11.854l-.04-.04-.04.04-.04-.04a.624.624,0,0,0,.08,0,.624.624,0,0,0,.08,0Z" transform="translate(0 0)" fill="#fff"/>
							</g>
						</g>
					</svg>': '').'</a>';
		});
	}

	public function overdrive_twig($twig) {
		
		$site = $this;

		$twig->addFunction(new Twig_SimpleFunction('cache', function ($transientName, $content) use ($site) {
			if ($site->useCache) {
				return \Timber\Helper::transient( sprintf('lumber_transient_%s_%s', get_the_ID(), $transientName), $content , $site->ffCacheTime );
			} else {
				return $content;
			}
		}));

		$twig->addFilter(new \Timber\Twig_Filter('texttruncate', function( $text, $len ) {
			return TextHelper::trim_characters($text, $len);
		}));
		
		$twig->addFunction(new Twig_SimpleFunction('class_exists', function( $text) {
			return class_exists($text);
		}));

		return $twig;
	}

	protected function add_factories()
	{
		parent::add_factories();
		foreach (Site::POST_TYPES as $key => $factory) {
			if ($key == "default") continue; 
			$factory = str_replace("Entity", "Factory", sprintf("%sFactory", $factory));
			$this->factories->add($key, new $factory());
		}
	}

	function my_login_style()
	{
		print '<link rel="stylesheet" href="/app/themes/lumber/static/css/app.bundle.css" />';
	}

	public function customizeRegister( $wp_customize ) {
		$wp_customize->add_setting('footer_logo');
	
		$wp_customize->add_control( new \WP_Customize_Image_Control(
			$wp_customize,
			'footer_logo',
			array(
				'label'      => __( 'Footer logo', 'textdomain' ),
				'description' => __( 'Png or SVG file', 'textdomain' ),
				'settings'   => 'footer_logo',
				'priority'   => 10,
				'section'    => 'title_tagline',
			)
		) );
	}

}
