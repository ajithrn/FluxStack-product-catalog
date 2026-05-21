<?php
/**
 * Admin Settings Page Template
 *
 * @package FS_Product_Catalog
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="fs-settings-app" id="fs-product-settings">
	<div class="fs-settings-header">
		<div class="fs-settings-header__left">
			<h1 class="fs-settings-header__title"><?php esc_html_e( 'Catalog Settings', 'fs-product-catalog' ); ?></h1>
			<span class="fs-settings-header__version">v<?php echo esc_html( FS_PRODUCT_CATALOG_VERSION ); ?></span>
		</div>
		<div class="fs-settings-header__right">
			<button type="button" class="fs-settings-btn fs-settings-btn--primary" id="fs-settings-save">
				<span class="fs-settings-btn__text"><?php esc_html_e( 'Save Changes', 'fs-product-catalog' ); ?></span>
				<span class="dashicons dashicons-saved"></span>
			</button>
		</div>
	</div>

	<div class="fs-settings-toast" id="fs-settings-toast" hidden></div>

	<nav class="fs-settings-tabs">
		<button class="fs-settings-tabs__tab is-active" data-tab="general">
			<span class="dashicons dashicons-admin-generic"></span>
			<?php esc_html_e( 'General', 'fs-product-catalog' ); ?>
		</button>
		<button class="fs-settings-tabs__tab" data-tab="single">
			<span class="dashicons dashicons-media-default"></span>
			<?php esc_html_e( 'Single Product', 'fs-product-catalog' ); ?>
		</button>
		<button class="fs-settings-tabs__tab" data-tab="archive">
			<span class="dashicons dashicons-grid-view"></span>
			<?php esc_html_e( 'Archive', 'fs-product-catalog' ); ?>
		</button>
		<button class="fs-settings-tabs__tab" data-tab="card">
			<span class="dashicons dashicons-format-image"></span>
			<?php esc_html_e( 'Product Card', 'fs-product-catalog' ); ?>
		</button>
		<button class="fs-settings-tabs__tab" data-tab="inquiry">
			<span class="dashicons dashicons-email-alt"></span>
			<?php esc_html_e( 'Inquiry', 'fs-product-catalog' ); ?>
		</button>
		<button class="fs-settings-tabs__tab" data-tab="advanced">
			<span class="dashicons dashicons-admin-tools"></span>
			<?php esc_html_e( 'Advanced', 'fs-product-catalog' ); ?>
		</button>
	</nav>

	<!-- General Tab -->
	<div class="fs-settings-panel is-active" data-panel="general">
		<section class="fs-settings-section">
			<h2 class="fs-settings-section__title"><?php esc_html_e( 'Catalog Display', 'fs-product-catalog' ); ?></h2>
			<p class="fs-settings-section__desc"><?php esc_html_e( 'Controls how products appear on the main archive page and taxonomy pages (categories, brands, types, tags).', 'fs-product-catalog' ); ?></p>

			<div class="fs-settings-form">
				<div class="fs-settings-field">
					<label class="fs-settings-field__label"><?php esc_html_e( 'Products Per Page', 'fs-product-catalog' ); ?></label>
					<input type="number" class="fs-settings-field__input fs-settings-field__input--small" name="fs_settings[products_per_page]" value="<?php echo esc_attr( $settings['products_per_page'] ); ?>" min="1" max="100">
					<span class="fs-settings-field__help"><?php esc_html_e( 'Products shown before "Load More" appears. Higher values = longer initial load.', 'fs-product-catalog' ); ?></span>
				</div>

				<div class="fs-settings-field">
					<label class="fs-settings-field__label"><?php esc_html_e( 'Grid Columns', 'fs-product-catalog' ); ?></label>
					<select class="fs-settings-field__select" name="fs_settings[archive_columns]">
						<option value="2" <?php selected( $settings['archive_columns'], 2 ); ?>><?php esc_html_e( '2 — larger cards, image-focused', 'fs-product-catalog' ); ?></option>
						<option value="3" <?php selected( $settings['archive_columns'], 3 ); ?>><?php esc_html_e( '3 — balanced (recommended)', 'fs-product-catalog' ); ?></option>
						<option value="4" <?php selected( $settings['archive_columns'], 4 ); ?>><?php esc_html_e( '4 — compact, more visible at once', 'fs-product-catalog' ); ?></option>
					</select>
					<span class="fs-settings-field__help"><?php esc_html_e( 'Desktop columns. Automatically reduces on tablet (2) and mobile (1).', 'fs-product-catalog' ); ?></span>
				</div>

				<div class="fs-settings-field">
					<label class="fs-settings-field__label"><?php esc_html_e( 'Default Sort Order', 'fs-product-catalog' ); ?></label>
					<select class="fs-settings-field__select" name="fs_settings[default_orderby]">
						<option value="menu_order" <?php selected( $settings['default_orderby'], 'menu_order' ); ?>><?php esc_html_e( 'Manual (page attributes order)', 'fs-product-catalog' ); ?></option>
						<option value="date" <?php selected( $settings['default_orderby'], 'date' ); ?>><?php esc_html_e( 'Newest first', 'fs-product-catalog' ); ?></option>
						<option value="title" <?php selected( $settings['default_orderby'], 'title' ); ?>><?php esc_html_e( 'Alphabetical (A → Z)', 'fs-product-catalog' ); ?></option>
					</select>
					<span class="fs-settings-field__help"><?php esc_html_e( 'Initial sort when visitors land on the page. "Manual" uses the Order field in product editor.', 'fs-product-catalog' ); ?></span>
				</div>
			</div>
		</section>

		<section class="fs-settings-section">
			<h2 class="fs-settings-section__title"><?php esc_html_e( 'Navigation', 'fs-product-catalog' ); ?></h2>
			<p class="fs-settings-section__desc"><?php esc_html_e( 'Breadcrumbs show the path: Home → Products → Category → Product Name. Helps visitors navigate back and improves SEO.', 'fs-product-catalog' ); ?></p>

			<div class="fs-settings-form">
				<div class="fs-settings-field fs-settings-field--wide">
					<label class="fs-settings-field__toggle">
						<input type="checkbox" name="fs_settings[show_breadcrumbs]" value="1" <?php checked( $settings['show_breadcrumbs'] ); ?>>
						<span class="fs-settings-field__toggle-slider"></span>
						<span class="fs-settings-field__toggle-label"><?php esc_html_e( 'Show Breadcrumbs', 'fs-product-catalog' ); ?></span>
					</label>
					<span class="fs-settings-field__help"><?php esc_html_e( 'Disable if your theme already provides breadcrumbs to avoid duplicates.', 'fs-product-catalog' ); ?></span>
				</div>
			</div>
		</section>

		<section class="fs-settings-section">
			<h2 class="fs-settings-section__title"><?php esc_html_e( 'Pagination', 'fs-product-catalog' ); ?></h2>
			<p class="fs-settings-section__desc"><?php esc_html_e( 'Controls how additional products are loaded on archive pages.', 'fs-product-catalog' ); ?></p>

			<div class="fs-settings-form">
				<div class="fs-settings-field">
					<label class="fs-settings-field__label"><?php esc_html_e( 'Pagination Mode', 'fs-product-catalog' ); ?></label>
					<select class="fs-settings-field__select" name="fs_settings[pagination_mode]" id="fs-pagination-mode">
						<option value="load-more" <?php selected( $settings['pagination_mode'], 'load-more' ); ?>><?php esc_html_e( 'Load More button — click to append more products', 'fs-product-catalog' ); ?></option>
						<option value="infinite-scroll" <?php selected( $settings['pagination_mode'], 'infinite-scroll' ); ?>><?php esc_html_e( 'Infinite scroll — auto-loads when scrolling near bottom', 'fs-product-catalog' ); ?></option>
						<option value="pagination" <?php selected( $settings['pagination_mode'], 'pagination' ); ?>><?php esc_html_e( 'Numbered pages — traditional pagination with page numbers', 'fs-product-catalog' ); ?></option>
					</select>
					<span class="fs-settings-field__help"><?php esc_html_e( '"Load More" gives users control. "Infinite scroll" loads automatically. "Numbered pages" is best for SEO and bookmarking.', 'fs-product-catalog' ); ?></span>
				</div>

				<div class="fs-settings-field fs-settings-field--conditional" data-show-when="load-more,infinite-scroll" data-depends-on="fs-pagination-mode">
					<label class="fs-settings-field__label"><?php esc_html_e( 'Load More Button Text', 'fs-product-catalog' ); ?></label>
					<input type="text" class="fs-settings-field__input" name="fs_settings[load_more_text]" value="<?php echo esc_attr( $settings['load_more_text'] ); ?>" placeholder="<?php esc_attr_e( 'Load More Products', 'fs-product-catalog' ); ?>">
					<span class="fs-settings-field__help"><?php esc_html_e( 'Custom text for the Load More button. Leave empty to use the default.', 'fs-product-catalog' ); ?></span>
				</div>
			</div>
		</section>
	</div>

	<!-- Single Product Tab -->
	<div class="fs-settings-panel" data-panel="single">
		<section class="fs-settings-section">
			<h2 class="fs-settings-section__title"><?php esc_html_e( 'Sidebar', 'fs-product-catalog' ); ?></h2>
			<p class="fs-settings-section__desc"><?php esc_html_e( 'The sidebar shows a browse panel next to the product content — with search, categories, brands, types, and tags. Helps visitors discover related products without leaving the page.', 'fs-product-catalog' ); ?></p>

			<div class="fs-settings-form">
				<div class="fs-settings-field">
					<label class="fs-settings-field__toggle">
						<input type="checkbox" name="fs_settings[single_show_sidebar]" value="1" <?php checked( $settings['single_show_sidebar'] ); ?>>
						<span class="fs-settings-field__toggle-slider"></span>
						<span class="fs-settings-field__toggle-label"><?php esc_html_e( 'Enable Sidebar', 'fs-product-catalog' ); ?></span>
					</label>
					<span class="fs-settings-field__help"><?php esc_html_e( 'When off, product content uses the full page width.', 'fs-product-catalog' ); ?></span>
				</div>

				<div class="fs-settings-field">
					<label class="fs-settings-field__label"><?php esc_html_e( 'Position', 'fs-product-catalog' ); ?></label>
					<select class="fs-settings-field__select" name="fs_settings[single_sidebar_position]">
						<option value="left" <?php selected( $settings['single_sidebar_position'], 'left' ); ?>><?php esc_html_e( 'Left', 'fs-product-catalog' ); ?></option>
						<option value="right" <?php selected( $settings['single_sidebar_position'], 'right' ); ?>><?php esc_html_e( 'Right', 'fs-product-catalog' ); ?></option>
					</select>
				</div>

				<div class="fs-settings-field">
					<label class="fs-settings-field__label"><?php esc_html_e( 'Items Per Section', 'fs-product-catalog' ); ?></label>
					<input type="number" class="fs-settings-field__input fs-settings-field__input--small" name="fs_settings[sidebar_items_limit]" value="<?php echo esc_attr( $settings['sidebar_items_limit'] ); ?>" min="3" max="50">
					<span class="fs-settings-field__help"><?php esc_html_e( 'Items shown before "Show more" appears. Set to 50 to show all.', 'fs-product-catalog' ); ?></span>
				</div>

				<div class="fs-settings-field fs-settings-field--wide">
					<label class="fs-settings-field__label"><?php esc_html_e( 'Visible Sections', 'fs-product-catalog' ); ?></label>
					<div class="fs-settings-checkbox-group">
						<label class="fs-settings-checkbox">
							<input type="checkbox" name="fs_settings[single_sidebar_search]" value="1" <?php checked( $settings['single_sidebar_search'] ); ?>>
							<?php esc_html_e( 'Search box', 'fs-product-catalog' ); ?>
						</label>
						<label class="fs-settings-checkbox">
							<input type="checkbox" name="fs_settings[single_sidebar_categories]" value="1" <?php checked( $settings['single_sidebar_categories'] ); ?>>
							<?php esc_html_e( 'Categories', 'fs-product-catalog' ); ?>
						</label>
						<label class="fs-settings-checkbox">
							<input type="checkbox" name="fs_settings[single_sidebar_brands]" value="1" <?php checked( $settings['single_sidebar_brands'] ); ?>>
							<?php esc_html_e( 'Brands', 'fs-product-catalog' ); ?>
						</label>
						<label class="fs-settings-checkbox">
							<input type="checkbox" name="fs_settings[single_sidebar_types]" value="1" <?php checked( $settings['single_sidebar_types'] ); ?>>
							<?php esc_html_e( 'Types', 'fs-product-catalog' ); ?>
						</label>
						<label class="fs-settings-checkbox">
							<input type="checkbox" name="fs_settings[single_sidebar_tags]" value="1" <?php checked( $settings['single_sidebar_tags'] ); ?>>
							<?php esc_html_e( 'Tags', 'fs-product-catalog' ); ?>
						</label>
					</div>
					<span class="fs-settings-field__help"><?php esc_html_e( 'Unchecked sections are hidden. Sections with no content are automatically hidden regardless.', 'fs-product-catalog' ); ?></span>
				</div>
			</div>
		</section>

		<section class="fs-settings-section">
			<h2 class="fs-settings-section__title"><?php esc_html_e( 'Related Products', 'fs-product-catalog' ); ?></h2>
			<p class="fs-settings-section__desc"><?php esc_html_e( 'Shows a grid of products from the same category at the bottom of the product page. Encourages visitors to explore more.', 'fs-product-catalog' ); ?></p>

			<div class="fs-settings-form">
				<div class="fs-settings-field">
					<label class="fs-settings-field__toggle">
						<input type="checkbox" name="fs_settings[show_related_products]" value="1" <?php checked( $settings['show_related_products'] ); ?>>
						<span class="fs-settings-field__toggle-slider"></span>
						<span class="fs-settings-field__toggle-label"><?php esc_html_e( 'Show Related Products', 'fs-product-catalog' ); ?></span>
					</label>
					<span class="fs-settings-field__help"><?php esc_html_e( 'Displays products from the same category below the product content.', 'fs-product-catalog' ); ?></span>
				</div>

				<div class="fs-settings-field">
					<label class="fs-settings-field__label"><?php esc_html_e( 'Number of Products', 'fs-product-catalog' ); ?></label>
					<input type="number" class="fs-settings-field__input fs-settings-field__input--small" name="fs_settings[related_products_count]" value="<?php echo esc_attr( $settings['related_products_count'] ); ?>" min="1" max="8">
					<span class="fs-settings-field__help"><?php esc_html_e( 'How many related products to show (1–8). Uses the same card layout as the archive grid.', 'fs-product-catalog' ); ?></span>
				</div>
			</div>
		</section>
	</div>

	<!-- Archive Tab -->
	<div class="fs-settings-panel" data-panel="archive">
		<section class="fs-settings-section">
			<h2 class="fs-settings-section__title"><?php esc_html_e( 'Sorting & Display', 'fs-product-catalog' ); ?></h2>
			<p class="fs-settings-section__desc"><?php esc_html_e( 'Controls the sorting dropdown and results bar on archive pages. The dropdown lets visitors re-order products without reloading the page.', 'fs-product-catalog' ); ?></p>

			<div class="fs-settings-form">
				<div class="fs-settings-field">
					<label class="fs-settings-field__toggle">
						<input type="checkbox" name="fs_settings[show_sorting]" value="1" <?php checked( $settings['show_sorting'] ); ?>>
						<span class="fs-settings-field__toggle-slider"></span>
						<span class="fs-settings-field__toggle-label"><?php esc_html_e( 'Show Sorting Dropdown', 'fs-product-catalog' ); ?></span>
					</label>
					<span class="fs-settings-field__help"><?php esc_html_e( 'Displays a "Sort by" dropdown in the results bar. Options: Default, Name A–Z, Name Z–A, Newest, Oldest.', 'fs-product-catalog' ); ?></span>
				</div>
			</div>
		</section>

		<section class="fs-settings-section">
			<h2 class="fs-settings-section__title"><?php esc_html_e( 'Filter Sidebar', 'fs-product-catalog' ); ?></h2>
			<p class="fs-settings-section__desc"><?php esc_html_e( 'The filter sidebar lets visitors narrow down products using checkboxes. Filters apply instantly via AJAX — no page reload. Appears on the main products page and all taxonomy archives.', 'fs-product-catalog' ); ?></p>

			<div class="fs-settings-form">
				<div class="fs-settings-field">
					<label class="fs-settings-field__toggle">
						<input type="checkbox" name="fs_settings[archive_show_sidebar]" value="1" <?php checked( $settings['archive_show_sidebar'] ); ?>>
						<span class="fs-settings-field__toggle-slider"></span>
						<span class="fs-settings-field__toggle-label"><?php esc_html_e( 'Enable Filter Sidebar', 'fs-product-catalog' ); ?></span>
					</label>
					<span class="fs-settings-field__help"><?php esc_html_e( 'When off, the product grid takes the full width with no filtering.', 'fs-product-catalog' ); ?></span>
				</div>

				<div class="fs-settings-field">
					<label class="fs-settings-field__label"><?php esc_html_e( 'Position', 'fs-product-catalog' ); ?></label>
					<select class="fs-settings-field__select" name="fs_settings[archive_sidebar_position]">
						<option value="left" <?php selected( $settings['archive_sidebar_position'], 'left' ); ?>><?php esc_html_e( 'Left (standard for filters)', 'fs-product-catalog' ); ?></option>
						<option value="right" <?php selected( $settings['archive_sidebar_position'], 'right' ); ?>><?php esc_html_e( 'Right', 'fs-product-catalog' ); ?></option>
					</select>
				</div>

				<div class="fs-settings-field">
					<label class="fs-settings-field__label"><?php esc_html_e( 'Items Per Section', 'fs-product-catalog' ); ?></label>
					<input type="number" class="fs-settings-field__input fs-settings-field__input--small" name="fs_settings[archive_sidebar_items_limit]" value="<?php echo esc_attr( $settings['archive_sidebar_items_limit'] ); ?>" min="3" max="50">
					<span class="fs-settings-field__help"><?php esc_html_e( 'Max filter options visible per section before "Show more" appears. Set to 50 to show all.', 'fs-product-catalog' ); ?></span>
				</div>

				<div class="fs-settings-field fs-settings-field--wide">
					<label class="fs-settings-field__label"><?php esc_html_e( 'Filter Sections', 'fs-product-catalog' ); ?></label>
					<div class="fs-settings-checkbox-group">
						<label class="fs-settings-checkbox">
							<input type="checkbox" name="fs_settings[archive_sidebar_search]" value="1" <?php checked( $settings['archive_sidebar_search'] ); ?>>
							<?php esc_html_e( 'Live search', 'fs-product-catalog' ); ?>
						</label>
						<label class="fs-settings-checkbox">
							<input type="checkbox" name="fs_settings[archive_sidebar_categories]" value="1" <?php checked( $settings['archive_sidebar_categories'] ); ?>>
							<?php esc_html_e( 'Categories', 'fs-product-catalog' ); ?>
						</label>
						<label class="fs-settings-checkbox">
							<input type="checkbox" name="fs_settings[archive_sidebar_brands]" value="1" <?php checked( $settings['archive_sidebar_brands'] ); ?>>
							<?php esc_html_e( 'Brands', 'fs-product-catalog' ); ?>
						</label>
						<label class="fs-settings-checkbox">
							<input type="checkbox" name="fs_settings[archive_sidebar_types]" value="1" <?php checked( $settings['archive_sidebar_types'] ); ?>>
							<?php esc_html_e( 'Types', 'fs-product-catalog' ); ?>
						</label>
						<label class="fs-settings-checkbox">
							<input type="checkbox" name="fs_settings[archive_sidebar_tags]" value="1" <?php checked( $settings['archive_sidebar_tags'] ); ?>>
							<?php esc_html_e( 'Tags', 'fs-product-catalog' ); ?>
						</label>
					</div>
					<span class="fs-settings-field__help"><?php esc_html_e( 'Each section shows checkboxes for that taxonomy. Sections with no terms are auto-hidden.', 'fs-product-catalog' ); ?></span>
				</div>
			</div>
		</section>
	</div>

	<!-- Product Card Tab -->
	<div class="fs-settings-panel" data-panel="card">
		<section class="fs-settings-section">
			<h2 class="fs-settings-section__title"><?php esc_html_e( 'Card Elements', 'fs-product-catalog' ); ?></h2>
			<p class="fs-settings-section__desc"><?php esc_html_e( 'Each product in the grid is shown as a card with an image, title, and optional elements. The entire card is clickable — these settings control what extra info is visible.', 'fs-product-catalog' ); ?></p>

			<div class="fs-settings-form">
				<div class="fs-settings-field fs-settings-field--wide">
					<label class="fs-settings-field__label"><?php esc_html_e( 'Show on Cards', 'fs-product-catalog' ); ?></label>
					<div class="fs-settings-checkbox-group">
						<label class="fs-settings-checkbox">
							<input type="checkbox" name="fs_settings[card_show_category]" value="1" <?php checked( $settings['card_show_category'] ); ?>>
							<?php esc_html_e( 'Category label — shows primary category below title', 'fs-product-catalog' ); ?>
						</label>
						<label class="fs-settings-checkbox">
							<input type="checkbox" name="fs_settings[card_show_excerpt]" value="1" <?php checked( $settings['card_show_excerpt'] ); ?>>
							<?php esc_html_e( 'Excerpt — short text preview (2 lines max)', 'fs-product-catalog' ); ?>
						</label>
						<label class="fs-settings-checkbox">
							<input type="checkbox" name="fs_settings[card_show_more_link]" value="1" <?php checked( $settings['card_show_more_link'] ); ?>>
							<?php esc_html_e( '"View Details" link — visual cue at bottom of card', 'fs-product-catalog' ); ?>
						</label>
					</div>
					<span class="fs-settings-field__help"><?php esc_html_e( 'Fewer elements = cleaner grid. Image and title are always shown.', 'fs-product-catalog' ); ?></span>
				</div>
			</div>
		</section>

		<section class="fs-settings-section">
			<h2 class="fs-settings-section__title"><?php esc_html_e( 'Card Image', 'fs-product-catalog' ); ?></h2>
			<p class="fs-settings-section__desc"><?php esc_html_e( 'The featured image fills the top of each card. Choose an aspect ratio — images are cropped to fit using CSS object-fit (no actual file cropping).', 'fs-product-catalog' ); ?></p>

			<div class="fs-settings-form">
				<div class="fs-settings-field">
					<label class="fs-settings-field__label"><?php esc_html_e( 'Aspect Ratio', 'fs-product-catalog' ); ?></label>
					<select class="fs-settings-field__select" name="fs_settings[card_image_ratio]">
						<option value="1:1" <?php selected( $settings['card_image_ratio'], '1:1' ); ?>><?php esc_html_e( 'Square (1:1) — most versatile', 'fs-product-catalog' ); ?></option>
						<option value="3:4" <?php selected( $settings['card_image_ratio'], '3:4' ); ?>><?php esc_html_e( 'Portrait (3:4) — taller, good for vertical products', 'fs-product-catalog' ); ?></option>
						<option value="16:9" <?php selected( $settings['card_image_ratio'], '16:9' ); ?>><?php esc_html_e( 'Landscape (16:9) — wider, good for horizontal products', 'fs-product-catalog' ); ?></option>
					</select>
					<span class="fs-settings-field__help"><?php esc_html_e( 'Upload images at or above this ratio for best results. Smaller images will be upscaled to fill.', 'fs-product-catalog' ); ?></span>
				</div>
			</div>
		</section>
	</div>

	<!-- Inquiry Tab -->
	<div class="fs-settings-panel" data-panel="inquiry">
		<section class="fs-settings-section">
			<h2 class="fs-settings-section__title"><?php esc_html_e( 'Product Inquiry', 'fs-product-catalog' ); ?></h2>
			<p class="fs-settings-section__desc"><?php esc_html_e( 'Adds a "Request a Quote" section on single product pages with quantity input and action buttons. Clicking the quote button takes the visitor to your quote form with the product name and quantity pre-filled.', 'fs-product-catalog' ); ?></p>

			<div class="fs-settings-form">
				<div class="fs-settings-field">
					<label class="fs-settings-field__toggle">
						<input type="checkbox" name="fs_settings[inquiry_enabled]" value="1" <?php checked( $settings['inquiry_enabled'] ); ?>>
						<span class="fs-settings-field__toggle-slider"></span>
						<span class="fs-settings-field__toggle-label"><?php esc_html_e( 'Enable Inquiry Section', 'fs-product-catalog' ); ?></span>
					</label>
					<span class="fs-settings-field__help"><?php esc_html_e( 'Shows a quote/inquiry box on single product pages.', 'fs-product-catalog' ); ?></span>
				</div>

				<div class="fs-settings-field">
					<label class="fs-settings-field__label"><?php esc_html_e( 'Placement', 'fs-product-catalog' ); ?></label>
					<select class="fs-settings-field__select" name="fs_settings[inquiry_placement]">
						<option value="after-info" <?php selected( $settings['inquiry_placement'] ?? 'after-info', 'after-info' ); ?>><?php esc_html_e( 'Below Product Info (right column)', 'fs-product-catalog' ); ?></option>
						<option value="full-width" <?php selected( $settings['inquiry_placement'] ?? 'after-info', 'full-width' ); ?>><?php esc_html_e( 'Full width (above specifications)', 'fs-product-catalog' ); ?></option>
					</select>
					<span class="fs-settings-field__help"><?php esc_html_e( '"Below Product Info" places it under the info box in the image/info grid. "Full width" spans the entire content area above specs.', 'fs-product-catalog' ); ?></span>
				</div>

				<div class="fs-settings-field">
					<label class="fs-settings-field__toggle">
						<input type="checkbox" name="fs_settings[inquiry_show_quantity]" value="1" <?php checked( $settings['inquiry_show_quantity'] ); ?>>
						<span class="fs-settings-field__toggle-slider"></span>
						<span class="fs-settings-field__toggle-label"><?php esc_html_e( 'Show Quantity Field', 'fs-product-catalog' ); ?></span>
					</label>
					<span class="fs-settings-field__help"><?php esc_html_e( 'Lets visitors specify how many they need. The quantity is passed to the quote form.', 'fs-product-catalog' ); ?></span>
				</div>
			</div>
		</section>

		<section class="fs-settings-section">
			<h2 class="fs-settings-section__title"><?php esc_html_e( 'Quote Button', 'fs-product-catalog' ); ?></h2>
			<p class="fs-settings-section__desc"><?php esc_html_e( 'The primary action button. Links to your quote/inquiry form page with product details pre-filled via URL parameters.', 'fs-product-catalog' ); ?></p>

			<div class="fs-settings-form">
				<div class="fs-settings-field">
					<label class="fs-settings-field__toggle">
						<input type="checkbox" name="fs_settings[inquiry_show_quote_btn]" value="1" <?php checked( $settings['inquiry_show_quote_btn'] ?? true ); ?>>
						<span class="fs-settings-field__toggle-slider"></span>
						<span class="fs-settings-field__toggle-label"><?php esc_html_e( 'Show Quote Button', 'fs-product-catalog' ); ?></span>
					</label>
				</div>

				<div class="fs-settings-field">
					<label class="fs-settings-field__label"><?php esc_html_e( 'Button Text', 'fs-product-catalog' ); ?></label>
					<input type="text" class="fs-settings-field__input" name="fs_settings[inquiry_button_text]" value="<?php echo esc_attr( $settings['inquiry_button_text'] ); ?>" placeholder="<?php esc_attr_e( 'Request a Quote', 'fs-product-catalog' ); ?>">
				</div>

				<div class="fs-settings-field fs-settings-field--wide">
					<label class="fs-settings-field__label"><?php esc_html_e( 'Quote Form URL', 'fs-product-catalog' ); ?></label>
					<input type="text" class="fs-settings-field__input" name="fs_settings[inquiry_form_url]" value="<?php echo esc_attr( $settings['inquiry_form_url'] ); ?>" placeholder="/custom-quote/">
					<span class="fs-settings-field__help"><?php esc_html_e( 'URL of your quote form page. Parameters appended automatically: ?product_name=...&quantity=...', 'fs-product-catalog' ); ?></span>
				</div>
			</div>
		</section>

		<section class="fs-settings-section">
			<h2 class="fs-settings-section__title"><?php esc_html_e( 'Contact Button', 'fs-product-catalog' ); ?></h2>
			<p class="fs-settings-section__desc"><?php esc_html_e( 'A secondary action button for general inquiries.', 'fs-product-catalog' ); ?></p>

			<div class="fs-settings-form">
				<div class="fs-settings-field">
					<label class="fs-settings-field__toggle">
						<input type="checkbox" name="fs_settings[inquiry_show_contact_btn]" value="1" <?php checked( $settings['inquiry_show_contact_btn'] ?? true ); ?>>
						<span class="fs-settings-field__toggle-slider"></span>
						<span class="fs-settings-field__toggle-label"><?php esc_html_e( 'Show Contact Button', 'fs-product-catalog' ); ?></span>
					</label>
				</div>

				<div class="fs-settings-field">
					<label class="fs-settings-field__label"><?php esc_html_e( 'Button Text', 'fs-product-catalog' ); ?></label>
					<input type="text" class="fs-settings-field__input" name="fs_settings[inquiry_contact_text]" value="<?php echo esc_attr( $settings['inquiry_contact_text'] ); ?>" placeholder="<?php esc_attr_e( 'Contact Us', 'fs-product-catalog' ); ?>">
				</div>

				<div class="fs-settings-field">
					<label class="fs-settings-field__label"><?php esc_html_e( 'Contact Page URL', 'fs-product-catalog' ); ?></label>
					<input type="text" class="fs-settings-field__input" name="fs_settings[inquiry_contact_url]" value="<?php echo esc_attr( $settings['inquiry_contact_url'] ); ?>" placeholder="/contact/">
				</div>
			</div>
		</section>
	</div>

	<!-- Advanced Tab -->
	<div class="fs-settings-panel" data-panel="advanced">
		<section class="fs-settings-section">
			<h2 class="fs-settings-section__title"><?php esc_html_e( 'REST API', 'fs-product-catalog' ); ?></h2>
			<p class="fs-settings-section__desc"><?php esc_html_e( 'The REST API exposes product data as JSON endpoints for headless frontends, mobile apps, or third-party integrations.', 'fs-product-catalog' ); ?></p>

			<div class="fs-settings-form">
				<div class="fs-settings-field">
					<label class="fs-settings-field__toggle">
						<input type="checkbox" name="fs_settings[enable_rest_api]" value="1" <?php checked( $settings['enable_rest_api'] ); ?>>
						<span class="fs-settings-field__toggle-slider"></span>
						<span class="fs-settings-field__toggle-label"><?php esc_html_e( 'Enable REST API', 'fs-product-catalog' ); ?></span>
					</label>
					<span class="fs-settings-field__help">
						<?php
						printf(
							/* translators: %s: API base URL */
							esc_html__( 'When enabled, product data is available at %s. Public read access, no authentication required.', 'fs-product-catalog' ),
							'<code>' . esc_html( rest_url( 'fs-catalog/v1/products' ) ) . '</code>'
						);
						?>
					</span>
				</div>
			</div>
		</section>
	</div>
</div>
