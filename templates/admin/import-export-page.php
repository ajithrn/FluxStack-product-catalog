<?php
/**
 * Import/Export Admin Page Template
 *
 * @package FS_Product_Catalog
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$nonce      = wp_create_nonce( 'fs_product_import_export_nonce' );
$categories = get_terms( array( 'taxonomy' => 'fs-product-category', 'hide_empty' => false ) );
$brands     = get_terms( array( 'taxonomy' => 'fs-product-brand', 'hide_empty' => false ) );
$types      = get_terms( array( 'taxonomy' => 'fs-product-type', 'hide_empty' => false ) );
?>

<div class="fs-settings-app" id="fs-import-export">
	<div class="fs-settings-header">
		<div class="fs-settings-header__left">
			<h1 class="fs-settings-header__title"><?php esc_html_e( 'Import / Export', 'fs-product-catalog' ); ?></h1>
		</div>
	</div>

	<div class="fs-ie-grid">
		<!-- Export Section -->
		<section class="fs-settings-section">
			<h2 class="fs-settings-section__title"><?php esc_html_e( 'Export Products', 'fs-product-catalog' ); ?></h2>
			<p class="fs-settings-section__desc"><?php esc_html_e( 'Download all products as a CSV file. Optionally filter by taxonomy before exporting.', 'fs-product-catalog' ); ?></p>

			<form id="fs-export-form" class="fs-settings-form">
				<input type="hidden" name="action" value="fs_product_export_csv" />
				<input type="hidden" name="nonce" value="<?php echo esc_attr( $nonce ); ?>" />

				<div class="fs-settings-field">
					<label class="fs-settings-field__label"><?php esc_html_e( 'Filter by Category', 'fs-product-catalog' ); ?></label>
					<select class="fs-settings-field__select" name="category">
						<option value=""><?php esc_html_e( 'All Categories', 'fs-product-catalog' ); ?></option>
						<?php if ( ! is_wp_error( $categories ) ) : ?>
							<?php foreach ( $categories as $cat ) : ?>
								<option value="<?php echo esc_attr( $cat->slug ); ?>"><?php echo esc_html( $cat->name ); ?> (<?php echo esc_html( $cat->count ); ?>)</option>
							<?php endforeach; ?>
						<?php endif; ?>
					</select>
				</div>

				<div class="fs-settings-field">
					<label class="fs-settings-field__label"><?php esc_html_e( 'Filter by Brand', 'fs-product-catalog' ); ?></label>
					<select class="fs-settings-field__select" name="brand">
						<option value=""><?php esc_html_e( 'All Brands', 'fs-product-catalog' ); ?></option>
						<?php if ( ! is_wp_error( $brands ) ) : ?>
							<?php foreach ( $brands as $brand ) : ?>
								<option value="<?php echo esc_attr( $brand->slug ); ?>"><?php echo esc_html( $brand->name ); ?> (<?php echo esc_html( $brand->count ); ?>)</option>
							<?php endforeach; ?>
						<?php endif; ?>
					</select>
				</div>

				<div class="fs-settings-field">
					<label class="fs-settings-field__label"><?php esc_html_e( 'Filter by Type', 'fs-product-catalog' ); ?></label>
					<select class="fs-settings-field__select" name="type">
						<option value=""><?php esc_html_e( 'All Types', 'fs-product-catalog' ); ?></option>
						<?php if ( ! is_wp_error( $types ) ) : ?>
							<?php foreach ( $types as $type ) : ?>
								<option value="<?php echo esc_attr( $type->slug ); ?>"><?php echo esc_html( $type->name ); ?> (<?php echo esc_html( $type->count ); ?>)</option>
							<?php endforeach; ?>
						<?php endif; ?>
					</select>
				</div>

				<div class="fs-settings-field">
					<button type="submit" class="button button-primary">
						<span class="dashicons dashicons-download" style="margin-top:3px;margin-right:4px;"></span>
						<?php esc_html_e( 'Export CSV', 'fs-product-catalog' ); ?>
					</button>
				</div>
			</form>
		</section>

		<!-- Import Section -->
		<section class="fs-settings-section">
			<h2 class="fs-settings-section__title"><?php esc_html_e( 'Import Products', 'fs-product-catalog' ); ?></h2>
			<p class="fs-settings-section__desc"><?php esc_html_e( 'Upload a CSV file to create or update products. Products with an existing ID will be updated; new rows will be created as drafts.', 'fs-product-catalog' ); ?></p>

			<form id="fs-import-form" class="fs-settings-form" enctype="multipart/form-data">
				<input type="hidden" name="action" value="fs_product_import_csv" />
				<input type="hidden" name="nonce" value="<?php echo esc_attr( $nonce ); ?>" />

				<div class="fs-settings-field">
					<label class="fs-settings-field__label"><?php esc_html_e( 'CSV File', 'fs-product-catalog' ); ?></label>
					<input type="file" name="csv_file" accept=".csv,text/csv" required class="fs-settings-field__input" />
					<span class="fs-settings-field__help">
						<?php esc_html_e( 'Required columns: title. Optional: id, content, excerpt, status, menu_order, featured_image, categories, brands, types, tags.', 'fs-product-catalog' ); ?>
						<br>
						<?php esc_html_e( 'Taxonomy values use pipe (|) separator for multiple terms. E.g.: "wire-rope|chain"', 'fs-product-catalog' ); ?>
					</span>
				</div>

				<div class="fs-settings-field">
					<button type="submit" class="button button-primary" id="fs-import-btn">
						<span class="dashicons dashicons-upload" style="margin-top:3px;margin-right:4px;"></span>
						<?php esc_html_e( 'Import CSV', 'fs-product-catalog' ); ?>
					</button>
				</div>

				<div id="fs-import-results" style="display:none;" class="fs-ie-results"></div>
			</form>
		</section>
	</div>
</div>

<script>
(function() {
	'use strict';

	// Export form — submit as regular form POST to get file download.
	var exportForm = document.getElementById('fs-export-form');
	if (exportForm) {
		exportForm.addEventListener('submit', function(e) {
			e.preventDefault();
			var formData = new FormData(this);
			var params = new URLSearchParams(formData).toString();
			window.location.href = '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>?' + params;
		});
	}

	// Import form — AJAX upload.
	var importForm = document.getElementById('fs-import-form');
	if (importForm) {
		importForm.addEventListener('submit', function(e) {
			e.preventDefault();

			var btn = document.getElementById('fs-import-btn');
			var results = document.getElementById('fs-import-results');
			btn.disabled = true;
			btn.textContent = '<?php echo esc_js( __( 'Importing...', 'fs-product-catalog' ) ); ?>';
			results.style.display = 'none';

			var formData = new FormData(this);

			fetch('<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>', {
				method: 'POST',
				body: formData
			})
			.then(function(r) { return r.json(); })
			.then(function(response) {
				results.style.display = 'block';

				if (response.success) {
					var d = response.data;
					var html = '<strong><?php echo esc_js( __( 'Import complete:', 'fs-product-catalog' ) ); ?></strong><br>';
					html += '<?php echo esc_js( __( 'Created:', 'fs-product-catalog' ) ); ?> ' + d.created + '<br>';
					html += '<?php echo esc_js( __( 'Updated:', 'fs-product-catalog' ) ); ?> ' + d.updated + '<br>';
					html += '<?php echo esc_js( __( 'Skipped:', 'fs-product-catalog' ) ); ?> ' + d.skipped;

					if (d.errors && d.errors.length > 0) {
						html += '<br><br><strong><?php echo esc_js( __( 'Errors:', 'fs-product-catalog' ) ); ?></strong><br>';
						html += d.errors.join('<br>');
					}

					results.innerHTML = html;
					results.className = 'fs-ie-results fs-ie-results--success';
				} else {
					results.innerHTML = '<strong><?php echo esc_js( __( 'Error:', 'fs-product-catalog' ) ); ?></strong> ' + (response.data.message || 'Unknown error');
					results.className = 'fs-ie-results fs-ie-results--error';
				}
			})
			.catch(function() {
				results.style.display = 'block';
				results.innerHTML = '<strong><?php echo esc_js( __( 'Error:', 'fs-product-catalog' ) ); ?></strong> Network error';
				results.className = 'fs-ie-results fs-ie-results--error';
			})
			.finally(function() {
				btn.disabled = false;
				btn.innerHTML = '<span class="dashicons dashicons-upload" style="margin-top:3px;margin-right:4px;"></span> <?php echo esc_js( __( 'Import CSV', 'fs-product-catalog' ) ); ?>';
			});
		});
	}
})();
</script>

<style>
.fs-ie-grid {
	display: grid;
	grid-template-columns: 1fr 1fr;
	gap: 24px;
	margin-top: 20px;
}
@media (max-width: 960px) {
	.fs-ie-grid { grid-template-columns: 1fr; }
}
.fs-ie-results {
	margin-top: 16px;
	padding: 12px 16px;
	border-radius: 4px;
	font-size: 13px;
	line-height: 1.6;
}
.fs-ie-results--success {
	background: #edfaef;
	border: 1px solid #46b450;
	color: #1e4620;
}
.fs-ie-results--error {
	background: #fef1f1;
	border: 1px solid #d63638;
	color: #8a0f0f;
}
</style>
