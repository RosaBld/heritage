<?php

namespace App\Controller;

use Spruce\Utility\Debug;
use App\Helper\WooCommerceHelper as Woo;
use Twig_SimpleFunction;
use Timber;
use App\Model as Model;
use App\Model\Entity\Product;
use Automattic\WooCommerce\Admin\Overrides\Order as WooOrder;
use App\Site;

use DateTime;
use DateInterval;

class WooCommerceController {

    protected $customFields;
    static protected $intervalDateCoupon = "P7D";

    public function __construct(Site $site) {

        if ( class_exists( 'WooCommerce' ) ) {
            new Woo(array( 'subfolder' => 'templates/woocommerce/' ));
            // Woo::init(array( 'subfolder' => 'templates/woocommerce/' ));
            // Woo::disable_woocommerce_images();
            add_action( 'after_setup_theme', [ $this, 'hooks' ] );
            add_action( 'after_setup_theme', [ $this, 'action' ] );
            add_action( 'after_setup_theme', [ $this, 'setup' ] );

            // add header_menu to Timber Context
            // add_filter('timber/context', function($data) {
            //     $data["showAccountMenu"] = true;
            //     return $data;
            // });

            add_filter( 'woocommerce_add_to_cart_fragments', [ $this, 'cart_link_fragment' ] );

            $site->factories->add("product", new Model\Factory\ProductFactory());
		} else {
            // Avoid 
            add_filter( 'get_twig', function ( $twig ) {
                $twig->addFunction(new Twig_SimpleFunction('cartItem', [$this, "get_cart_item_information" ]));
                return $twig;
            });
        }
    }

    /**
     * Cart Fragments.
     *
     * Ensure cart contents update when products are added to the cart via AJAX.
     *
     * @param  array $fragments Fragments to refresh via AJAX.
     * @return array            Fragments to refresh via AJAX.
    */
    public function cart_link_fragment( $fragments ) {
        global $woocommerce;
        $fragments['div.cart-mini-contents'] = Timber\Timber::compile(
            'woocommerce/fragment/cart.twig',
            [ 'cart' => WC()->cart,  ]
        );

        return $fragments;
    }
	
	public function setup()
	{
		add_theme_support( 'woocommerce' );
    }
    
    public function action()
    {
        // remove_action( 'woocommerce_cart_is_empty', 'wc_empty_cart_message', 10 );
    }

    static public function tr($str) {
        if (function_exists("pll__")) {
            return pll__($str);
        } else {
            return __($str);
        }
    }
	
	public function hooks()
	{
        // add_filter( 'woocommerce_cart_item_thumbnail', '__return_false' );
        add_filter( 'get_twig', array( $this, 'add_to_twig' ) );
        add_filter( 'woocommerce_cart_item_name', function ( $product_link, $cart_item, $cart_item_key ) {
            $product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
            return $product->get_title();
        }, 10, 3 );

        add_filter( 'woocommerce_enqueue_styles', function ( $enqueue_styles ) {
            unset( $enqueue_styles['woocommerce-general'] );	// Remove the gloss
            unset( $enqueue_styles['woocommerce-layout'] );		// Remove the layout
            unset( $enqueue_styles['woocommerce-smallscreen'] );	// Remove the smallscreen optimisation
            return $enqueue_styles;
        });

        add_filter('woocommerce_billing_fields', function ( $fields = array() ) {

            return $fields;
            // unset($fields['billing_company']);
            // unset($fields['billing_address_1']);
            // unset($fields['billing_address_2']);
            // unset($fields['billing_state']);
            // unset($fields['billing_city']);
            // unset($fields['billing_phone']);
            // unset($fields['billing_postcode']);
            // unset($fields['billing_country']);
        
            return $fields;
        });

        add_action('woocommerce_before_cart_table', function ( ) {
            print "<h1>".self::tr("Récapitulatif de votre commande") ."</h1>";
        });

        add_action( 'woocommerce_email_after_order_table', function ( $order, $is_admin_email ) {
            try {
                if (!$is_admin_email) {
                    $couponAmount = (floatval($order->get_total() / 10));
                    if ($couponAmount > 0) {
                        $codeInfos = self::createCoupon($couponAmount);
                        print '<h2>'.self::tr("Ristourne").'</h2>';
                        print sprintf(
                            '<p>Grâce au code promo <strong>%s</strong>, recevez <strong>%s€</strong> sur votre prochaine commande en ligne à effectuer au plus tard pour le <strong>%s</strong>.</p>',
                            $codeInfos->code,
                            $codeInfos->amount,
                            $codeInfos->date
                        );
                    }
                }
            } catch(\Exception $e) {}
        }, 10, 2 );

    }

    
    public function get_cart_item_information($item) {
        //product image
        $getProductDetail = wc_get_product( $item['product_id'] );

        $hasSalePrice = $item['data']->get_sale_price() != "";
        $price = [
            "regular" => $getProductDetail->get_price_html(),
            "sale" => wc_price($item['data']->get_sale_price()),
        ];

        //
        $price["default"] = $hasSalePrice ? $price["sale"] : $price["regular"];

        return [
            "title" => $item['data']->get_title(),
            "quantity" => $item['quantity'],
            "rawPicture" => get_the_post_thumbnail_url($item['product_id']),
            "picture" => $getProductDetail->get_image("shop_thumbnail"),
            "price" => $price,
            "link" => $item["data"]->get_permalink(),
        ];
    }

    function add_to_twig( $twig ) {

        $twig->addFunction(new Twig_SimpleFunction('cartItem', [$this, "get_cart_item_information" ]));
        $twig->addFunction(new Twig_SimpleFunction('Attributes', [ $this, "get_product_attributes" ]));
        $twig->addFunction(new Twig_SimpleFunction('ProductItem', [ $this, "getProductItem" ]));
        $twig->addFunction(new Twig_SimpleFunction('WooField', function($name, $checkout, $options = null) {
            woocommerce_form_field( $name, $options, $checkout->get_value( $name ));
        }));
        $twig->addFunction(new Twig_SimpleFunction('CategoryPicture', function($category) {
            return new Timber\Image($thumbnail_id = get_term_meta( $category->term_id, 'thumbnail_id', true ));
        }));

        /**
		 * Use 'wc_action' as an optimization to 'action', which behaves a bit weird.
		 *
		 * @link https://github.com/timber/timber/pull/1773
		 */
		$twig->addFunction(new Twig_SimpleFunction( 'wc_action', function() {
			$args   = func_get_args();
			$action = $args[0];
			array_shift( $args );
			do_action_ref_array( $action, $args );
		}));

        return $twig;
    }

    static public function createCoupon($amount) {
        
        $amount = $amount; // Amount
        $discount_type = 'fixed_cart'; // Type: fixed_cart, percent, fixed_product, percent_product
        global $wpdb;
        do {
            $code = strtoupper("bp10-".substr(md5(microtime().rand()),0, rand(6,8)));
            $sql = $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_title = %s AND post_type = 'shop_coupon' ORDER BY post_date DESC LIMIT 1;", $code );
            $coupon_id = $wpdb->get_var( $sql );
        } while ($coupon_id != false );
        $coupon = array(
        'post_title' => $code,
        'post_content' => '',
        'post_status' => 'publish',
        'post_author' => 1,
        'post_type' => 'shop_coupon');

        $new_coupon_id = wp_insert_post( $coupon );
        $date = new DateTime();
        $date->add(new DateInterval(self::$intervalDateCoupon));
        //
        update_post_meta( $new_coupon_id, 'discount_type', $discount_type );
        update_post_meta( $new_coupon_id, 'coupon_amount', $amount );
        update_post_meta( $new_coupon_id, 'individual_use', 'yes' );
        update_post_meta( $new_coupon_id, 'product_ids', '' );
        update_post_meta( $new_coupon_id, 'exclude_product_ids', '' );
        update_post_meta( $new_coupon_id, 'usage_limit', '1' );
        update_post_meta( $new_coupon_id, 'expiry_date', $date->format("Y-m-d"));
        update_post_meta( $new_coupon_id, 'apply_before_tax', 'no' );
        update_post_meta( $new_coupon_id, 'free_shipping', 'no' );      
        update_post_meta( $new_coupon_id, 'exclude_sale_items', 'no' );     
        update_post_meta( $new_coupon_id, 'free_shipping', 'no' );      
        update_post_meta( $new_coupon_id, 'product_categories', '' );       
        update_post_meta( $new_coupon_id, 'exclude_product_categories', '' );       
        update_post_meta( $new_coupon_id, 'minimum_amount', '' );       
        update_post_meta( $new_coupon_id, 'customer_email', '' );       

        return (object)[
            "code" => $code,
            "amount" => $amount,
            "date" => $date->format("d-m-Y")
        ];
    }


    public function getProductItem($product) 
    {
        return new Product($product);
    }

}
