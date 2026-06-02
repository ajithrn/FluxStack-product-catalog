<?php
/**
 * Quote List
 *
 * Handles the frontend quote list feature: floating trigger, panel output,
 * and localized configuration for the JS module.
 *
 * @package FS_Product_Catalog
 */

namespace FSProductCatalog;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class QuoteList
 *
 * Manages the quote list feature (localStorage-based product collection).
 */
class QuoteList {

	/**
	 * Initialize the class.
	 */
	public static function init() {
		// Output panel and trigger in footer on product pages.
		add_action( 'wp_footer', array( __CLASS__, 'render_footer_elements' ) );

		// Add quote list data to localized JS.
		add_filter( 'fs_product_frontend_localize_data', array( __CLASS__, 'add_localized_data' ) );
	}

	/**
	 * Check if quote list feature is enabled.
	 *
	 * @return bool
	 */
	public static function is_enabled() {
		return (bool) Settings::get( 'quote_list_enabled', false );
	}

	/**
	 * Render footer elements (panel + trigger button).
	 * Outputs on all frontend pages when the feature is enabled.
	 */
	public static function render_footer_elements() {
		if ( ! self::is_enabled() ) {
			return;
		}

		// Don't render in admin.
		if ( is_admin() ) {
			return;
		}

		TemplateLoader::get_template_part( 'quote-list-panel' );
		TemplateLoader::get_template_part( 'quote-list-trigger' );
	}

	/**
	 * Add quote list configuration to localized JS data.
	 *
	 * @param array $data Existing localized data.
	 * @return array
	 */
	public static function add_localized_data( $data ) {
		if ( ! self::is_enabled() ) {
			$data['quoteList'] = array( 'enabled' => false );
			return $data;
		}

		$settings = Settings::get_all();

		$form_url = ! empty( $settings['quote_list_form_url'] )
			? $settings['quote_list_form_url']
			: ( ! empty( $settings['inquiry_form_url'] ) ? $settings['inquiry_form_url'] : '/custom-quote/' );

		$data['quoteList'] = array(
			'enabled'       => true,
			'maxItems'      => absint( $settings['quote_list_max_items'] ?? 50 ),
			'formUrl'       => esc_url( $form_url ),
			'fieldSelector' => sanitize_text_field( $settings['quote_list_field_selector'] ?? '#fs-products-field' ),
			'tableSelector' => sanitize_text_field( $settings['quote_list_table_selector'] ?? '#fs-products-table' ),
			'successUrl'    => esc_url( $settings['quote_list_success_url'] ?? '' ),
			'template'      => $settings['quote_list_template'] ?? '{name} | SKU: {sku} | Part No: {part_no} | Qty: {qty}',
			'floatPosition' => $settings['quote_list_float_position'] ?? 'bottom-right',
			'showFloat'     => (bool) ( $settings['quote_list_show_float'] ?? true ),
			'showOnCards'   => (bool) ( $settings['quote_list_show_on_cards'] ?? true ),
			'showOnSingle'  => (bool) ( $settings['quote_list_show_on_single'] ?? true ),
			'i18n'          => array(
				'addToList'    => esc_html( $settings['quote_list_add_btn_text'] ?? __( 'Add to List', 'fs-product-catalog' ) ),
				'added'        => esc_html__( 'Added ✓', 'fs-product-catalog' ),
				'inList'       => esc_html__( 'In List', 'fs-product-catalog' ),
				'clearConfirm' => esc_html__( 'Remove all items from your quote list?', 'fs-product-catalog' ),
				'emptyTitle'   => esc_html__( 'Your list is empty', 'fs-product-catalog' ),
				'emptyText'    => esc_html__( 'Browse products to add items.', 'fs-product-catalog' ),
				'browseLink'   => esc_url( get_post_type_archive_link( 'fs-products' ) ),
				'requestQuote' => esc_html( $settings['quote_list_submit_btn_text'] ?? __( 'Request Quote', 'fs-product-catalog' ) ),
				'clearAll'     => esc_html__( 'Clear All', 'fs-product-catalog' ),
				'panelTitle'   => esc_html( $settings['quote_list_panel_title'] ?? __( 'Quote List', 'fs-product-catalog' ) ),
				'items'        => esc_html__( 'items', 'fs-product-catalog' ),
				'maxReached'   => esc_html__( 'Maximum items reached.', 'fs-product-catalog' ),
				'clearSuccess' => esc_html__( 'List cleared', 'fs-product-catalog' ),
				'noProducts'   => esc_html__( 'No products selected', 'fs-product-catalog' ),
			),
		);

		return $data;
	}

	/**
	 * Get the "Add to List" button HTML for a product.
	 *
	 * @param int $post_id Product post ID.
	 * @return string Button HTML.
	 */
	public static function get_add_button_html( $post_id = null ) {
		if ( ! self::is_enabled() ) {
			return '';
		}

		if ( null === $post_id ) {
			$post_id = get_the_ID();
		}

		$settings = Settings::get_all();
		$id_data  = Identification::get_all( $post_id );

		$product_name = get_the_title( $post_id );
		$product_url  = get_permalink( $post_id );
		$product_image = '';

		$thumb_id = get_post_thumbnail_id( $post_id );
		if ( $thumb_id ) {
			$thumb_src = wp_get_attachment_image_src( $thumb_id, 'thumbnail' );
			if ( $thumb_src ) {
				$product_image = $thumb_src[0];
			}
		}

		$btn_text = $settings['quote_list_add_btn_text'] ?? __( 'Add to List', 'fs-product-catalog' );

		$button = sprintf(
			'<button type="button" class="fs-add-to-list"
				data-product-id="%s"
				data-product-name="%s"
				data-product-sku="%s"
				data-product-part-no="%s"
				data-product-uom="%s"
				data-product-moq="%s"
				data-product-url="%s"
				data-product-image="%s">
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
					<path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
					<line x1="3" y1="6" x2="21" y2="6"/>
					<path d="M16 10a4 4 0 01-8 0"/>
					<path d="M12 13v4m-2-2h4"/>
				</svg>
				<span class="fs-add-to-list__text">%s</span>
			</button>',
			esc_attr( $post_id ),
			esc_attr( $product_name ),
			esc_attr( $id_data['sku'] ?? '' ),
			esc_attr( $id_data['mfr_part'] ?? '' ),
			esc_attr( $id_data['uom'] ?? '' ),
			esc_attr( $id_data['moq'] ?? '1' ),
			esc_attr( $product_url ),
			esc_attr( $product_image ),
			esc_html( $btn_text )
		);

		return $button;
	}

	/**
	 * Render the "Add to List" button.
	 *
	 * @param int $post_id Product post ID.
	 */
	public static function render_add_button( $post_id = null ) {
		echo self::get_add_button_html( $post_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
