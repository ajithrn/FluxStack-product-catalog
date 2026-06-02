<?php
/**
 * Quote List Trigger Button
 *
 * Floating button that opens the quote list panel.
 * Badge count is updated dynamically by JavaScript.
 *
 * @package FS_Product_Catalog
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$settings = \FSProductCatalog\Settings::get_all();
$show_float = ! empty( $settings['quote_list_show_float'] );

if ( ! $show_float ) {
	return;
}

$position = $settings['quote_list_float_position'] ?? 'bottom-right';
?>

<button type="button"
	class="fs-quote-list-trigger fs-quote-list-trigger--<?php echo esc_attr( $position ); ?>"
	id="fs-quote-list-trigger"
	aria-label="<?php esc_attr_e( 'Open quote list', 'fs-product-catalog' ); ?>"
	style="display:none;">
	<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
		<path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/>
	</svg>
	<span class="fs-quote-list-badge" id="fs-quote-list-badge">0</span>
</button>
