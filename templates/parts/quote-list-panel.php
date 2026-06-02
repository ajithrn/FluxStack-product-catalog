<?php
/**
 * Quote List Panel
 *
 * Slide-out panel that displays the user's quote list items.
 * Content is rendered dynamically by JavaScript from localStorage.
 *
 * @package FS_Product_Catalog
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$settings    = \FSProductCatalog\Settings::get_all();
$panel_title = ! empty( $settings['quote_list_panel_title'] ) ? $settings['quote_list_panel_title'] : __( 'Quote List', 'fs-product-catalog' );
?>

<?php
$position = $settings['quote_list_float_position'] ?? 'bottom-right';
?>
<div class="fs-quote-list-overlay" id="fs-quote-list-overlay" aria-hidden="true"></div>

<div class="fs-quote-list-panel fs-quote-list-panel--<?php echo esc_attr( $position ); ?>" id="fs-quote-list-panel" aria-hidden="true" role="dialog" aria-label="<?php echo esc_attr( $panel_title ); ?>">
	<div class="fs-quote-list-header">
		<h3 class="fs-quote-list-header__title">
			<span class="fs-quote-list-header__text"><?php echo esc_html( $panel_title ); ?></span>
			<span class="fs-quote-list-header__count" id="fs-quote-list-header-count"></span>
		</h3>
		<button type="button" class="fs-quote-list-header__close" id="fs-quote-list-close" aria-label="<?php esc_attr_e( 'Close quote list', 'fs-product-catalog' ); ?>">
			&times;
		</button>
	</div>

	<div class="fs-quote-list-items" id="fs-quote-list-items">
		<!-- Items rendered by JS -->
	</div>

	<div class="fs-quote-list-empty" id="fs-quote-list-empty" style="display:none;">
		<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" opacity="0.4">
			<path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
		</svg>
		<p class="fs-quote-list-empty__title" id="fs-quote-list-empty-title"></p>
		<p class="fs-quote-list-empty__text" id="fs-quote-list-empty-text"></p>
		<a href="<?php echo esc_url( get_post_type_archive_link( 'fs-products' ) ); ?>" class="fs-quote-list-empty__link">
			<?php esc_html_e( 'Browse Products', 'fs-product-catalog' ); ?>
		</a>
	</div>

	<div class="fs-quote-list-footer" id="fs-quote-list-footer">
		<button type="button" class="fs-quote-list-footer__clear" id="fs-quote-list-clear">
			<?php esc_html_e( 'Clear All', 'fs-product-catalog' ); ?>
		</button>
		<a href="#" class="fs-quote-list-footer__submit" id="fs-quote-list-submit">
			<?php echo esc_html( ! empty( $settings['quote_list_submit_btn_text'] ) ? $settings['quote_list_submit_btn_text'] : __( 'Request Quote', 'fs-product-catalog' ) ); ?>
		</a>
	</div>
</div>
