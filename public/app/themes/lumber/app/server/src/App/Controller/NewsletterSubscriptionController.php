<?php

namespace App\Controller;

use Spruce\Utility\Debug;
use Timber\Timber;
use App\Site;

class NewsletterSubscriptionController {

    protected $site;
    protected $content;
    protected $functions;

    public function __construct(Site $site) {
        $this->site = $site;
        $this->createSqlTable();
        $this->addAdminPages();
        $this->addRoute();

    }

    protected function addAdminPages() {
        add_action( 'admin_menu', array($this, 'subscriptionPageMenu'), 1 );
	}

    public function subscriptionPageMenu() {
		add_menu_page(
			pll__('Newsletter Subscriptions'),
			pll__('Newsletter Subscriptions'),
			'manage_options',
			'newsletter-subscriptions/list.php',
			array($this, 'newsletterSubscriptionAdminPage'),
			'dashicons-tickets',
			25
		);
	}

	public function newsletterSubscriptionAdminPage(){
		ob_start();
		global $wpdb;
		$context = Timber::get_context();
		$context["subscriptions"] = $wpdb->get_results("select * from {$wpdb->base_prefix}subscription");
		print Timber::fetch( 'admin/newsletter-subscriptions/list.html.twig', $context );
		echo ob_get_clean();
		ob_end_flush();
	}

    protected function addRoute() {
        $site = $this->site;

        \Routes::map("/api/newsletter/subscribe", function($args) use ($site) {
			global $wpdb;
			$filters = array(
				'email' => FILTER_SANITIZE_STRING | FILTER_SANITIZE_EMAIL,
				'language' => FILTER_SANITIZE_URL,
			);

			$data = [
				"email" => $_POST["email"],
				"language" => $_POST["language"],
			];
			
			$sanitized_data = filter_var_array( $data, $filters );

			try {
				if(!($wpdb->query($wpdb->prepare("select * from {$wpdb->prefix}subscription where email=%s", $sanitized_data["email"])))) {
					$s = $wpdb->query(
						$wpdb->prepare(
							"
							INSERT INTO {$wpdb->prefix}subscription
							( email, language_code )
							VALUES ( %s, %s)
							",
							array(
								$sanitized_data["email"],
								$sanitized_data["language"]
							)
						)
					);
				}
			} catch (\Exception $e) {

			}
			//
			wp_send_json([ 
				"success" => true,
				"message" => pll__("L'email a bien été encodée dans la base de données.")
			]);
			die();
		});
    }

    function createSqlTable() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
    
        $sql = "CREATE TABLE if not exists `{$wpdb->base_prefix}subscription` (
        `id` bigint(20) not null AUTO_INCREMENT,
        `email` varchar(255) NOT NULL,
        `language_code` varchar(255) NOT NULL,
        `date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id)
        ) $charset_collate;";
    
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

}
