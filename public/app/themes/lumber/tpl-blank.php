<?php
/**
 * Template Name: Blank
 * Description: The custom homepage template
 *
 * Methods for TimberHelper can be found in the /lib sub-directory
 *
 * @package  WordPress
 * @subpackage  Timber
 * @since    Timber 0.1
 */

$context = Timber\Timber::get_context();
$context['post'] = Timber\Timber::query_post();
if (function_exists("is_account_page") && is_account_page()) {
    $context["header_menu"] = $context["account_menu"];
    $context["showAccountMenu"] = false;
    $extraNode = "";
		foreach ( wc_get_account_menu_items() as $endpoint => $label ) :
			$extraNode .= '<li class="'.wc_get_account_menu_item_classes( $endpoint ).'">
				<a href="'.esc_url( wc_get_account_endpoint_url( $endpoint ) ) .'">'.esc_html( $label ) .'</a>
			</li>';
		endforeach;
    $context["accountExtraNode"] = $extraNode;
}
Timber\Timber::render( 'custom/pages/blank.html.twig', $context );
