<?php
/**
 * Product Inquiry Section
 *
 * Displays a "Request a Quote" box with quantity input and action buttons.
 * The quote button links to the form page with product info pre-filled via
 * the `products_list` URL parameter (single field with all details).
 *
 * @package FS_Product_Catalog
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$settings           = \FSProductCatalog\Settings::get_all();
$inquiry_enabled    = ! empty( $settings['inquiry_enabled'] );
$quote_list_enabled = \FSProductCatalog\QuoteList::is_enabled();
$show_quote_list    = $quote_list_enabled && ( $settings['quote_list_show_on_single'] ?? true );

if ( ! $inquiry_enabled && ! $show_quote_list ) {
	return;
}

$show_quote_btn   = $inquiry_enabled && ( $settings['inquiry_show_quote_btn'] ?? true );
$show_contact_btn = $inquiry_enabled && ( $settings['inquiry_show_contact_btn'] ?? true );
$button_text      = ! empty( $settings['inquiry_button_text'] ) ? $settings['inquiry_button_text'] : __( 'Request a Quote', 'fs-product-catalog' );
$form_url         = ! empty( $settings['inquiry_form_url'] ) ? $settings['inquiry_form_url'] : '/custom-quote/';
$show_quantity    = ! empty( $settings['inquiry_show_quantity'] );
$contact_text     = ! empty( $settings['inquiry_contact_text'] ) ? $settings['inquiry_contact_text'] : __( 'Contact Us', 'fs-product-catalog' );
$contact_url      = ! empty( $settings['inquiry_contact_url'] ) ? $settings['inquiry_contact_url'] : '/contact/';

// Don't render if all buttons are off
if ( ! $show_quote_btn && ! $show_contact_btn && ! $show_quote_list ) {
	return;
}

$product_name = get_the_title();
$product_id   = get_the_ID();
$product_url  = get_the_permalink();

// Category info
$categories    = get_the_terms( $product_id, 'fs-product-category' );
$category_name = ( ! empty( $categories ) && ! is_wp_error( $categories ) ) ? $categories[0]->name : '';

// MOQ info
$id_data = \FSProductCatalog\Identification::get_all( $product_id );
$moq     = ! empty( $id_data['moq'] ) ? intval( $id_data['moq'] ) : 1;

?>

<div class="fs-product-inquiry" id="fs-product-inquiry">
	<div class="fs-product-inquiry__inner">
		<?php if ( $show_quantity || $show_quote_list ) : ?>
			<div class="fs-product-inquiry__quantity">
				<div class="fs-qty-control">
					<button type="button" class="fs-qty-control__btn" data-action="minus" aria-label="<?php esc_attr_e( 'Decrease quantity', 'fs-product-catalog' ); ?>">&minus;</button>
					<input type="number" id="fs-inquiry-qty" class="fs-qty-control__input" value="<?php echo esc_attr( $moq ); ?>" min="<?php echo esc_attr( $moq ); ?>" max="9999" step="1">
					<button type="button" class="fs-qty-control__btn" data-action="plus" aria-label="<?php esc_attr_e( 'Increase quantity', 'fs-product-catalog' ); ?>">&plus;</button>
				</div>
			</div>
		<?php endif; ?>

		<div class="fs-product-inquiry__actions">
			<?php if ( $show_quote_list ) : ?>
				<?php \FSProductCatalog\QuoteList::render_add_button(); ?>
			<?php endif; ?>

			<?php if ( $show_quote_btn ) : ?>
				<a href="<?php echo esc_url( $form_url ); ?>"
				   class="fs-inquiry-btn fs-inquiry-btn--primary"
				   id="fs-inquiry-quote-btn"
				   data-base-url="<?php echo esc_url( $form_url ); ?>"
				   data-product-name="<?php echo esc_attr( $product_name ); ?>"
				   data-product-url="<?php echo esc_url( $product_url ); ?>"
				   data-product-category="<?php echo esc_attr( $category_name ); ?>">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
					<?php echo esc_html( $button_text ); ?>
				</a>
			<?php endif; ?>

			<?php if ( $show_contact_btn ) : ?>
				<a href="<?php echo esc_url( $contact_url ); ?>" class="fs-inquiry-btn fs-inquiry-btn--secondary">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
					<?php echo esc_html( $contact_text ); ?>
				</a>
			<?php endif; ?>
		</div>
	</div>
</div>

<?php if ( $show_quantity || $show_quote_list ) : ?>
<script>
(function() {
	var qtyInput = document.getElementById('fs-inquiry-qty');
	if (!qtyInput) return;

	var minVal = parseInt(qtyInput.getAttribute('min'), 10) || 1;
	var quoteBtn = document.getElementById('fs-inquiry-quote-btn');

	function buildUrl() {
		if (!quoteBtn) return;
		var baseUrl = quoteBtn.getAttribute('data-base-url');
		var productName = quoteBtn.getAttribute('data-product-name');
		var productUrl = quoteBtn.getAttribute('data-product-url');
		var productCategory = quoteBtn.getAttribute('data-product-category');
		var qty = parseInt(qtyInput.value, 10) || minVal;

		var params = new URLSearchParams();
		params.set('product_name', productName);
		params.set('quantity', qty);
		params.set('product_url', productUrl);
		if (productCategory) params.set('product_category', productCategory);

		quoteBtn.href = baseUrl + '?' + params.toString();
	}

	// Bind quantity buttons
	document.querySelectorAll('#fs-product-inquiry .fs-qty-control__btn').forEach(function(btn) {
		btn.addEventListener('click', function() {
			var val = parseInt(qtyInput.value, 10) || minVal;
			if (this.dataset.action === 'minus' && val > minVal) {
				qtyInput.value = val - 1;
			}
			if (this.dataset.action === 'plus') {
				qtyInput.value = val + 1;
			}
			buildUrl();
		});
	});

	qtyInput.addEventListener('change', buildUrl);
	qtyInput.addEventListener('input', buildUrl);

	// Initial URL build
	buildUrl();
})();
</script>
<?php endif; ?>
