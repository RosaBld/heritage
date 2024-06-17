<?php
/**
 * Third party plugins that hijack the theme will call wp_footer() to get the footer template.
 * We use this to end our output buffer (started in header.php) and render into the view/page-plugin.twig template.
 *
 * If you're not using a plugin that requries this behavior (ones that do include Events Calendar Pro and 
 * WooCommerce) you can delete this file and header.php
 */

$timberContext = $GLOBALS['timberContext'];
if ( ! isset( $timberContext ) ) {
	throw new \Exception( 'Timber context not set in footer.' );
}
$timberContext['content'] = ob_get_contents();
ob_end_clean();
$templates = array( 'custom/core/page-plugin.twig', 'core/page-plugin.twig' );
switch(get_post_type()) {
	case "tribe_events":
		Timber\Timber::render( 'custom/pages/events.html.twig', $timberContext );
	break;
	default:
		Timber\Timber::render( $templates, $timberContext );
	break;
}
