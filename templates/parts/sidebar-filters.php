<?php
/**
 * Sidebar Filters Template Part
 *
 * This template can be overridden by copying it to yourtheme/fs-product-catalog/parts/sidebar-filters.php
 *
 * @package FS_Product_Catalog
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get all taxonomies.
$categories = get_terms(
	array(
		'taxonomy'   => 'fs-product-category',
		'hide_empty' => true,
	)
);

$brands = get_terms(
	array(
		'taxonomy'   => 'fs-product-brand',
		'hide_empty' => true,
	)
);

$types = get_terms(
	array(
		'taxonomy'   => 'fs-product-type',
		'hide_empty' => true,
	)
);

$tags = get_terms(
	array(
		'taxonomy'   => 'fs-product-tag',
		'hide_empty' => true,
	)
);
?>

<div class="fs-filters-wrap">
	<div class="fs-filters-header">
		<h3 class="fs-filters-title"><?php esc_html_e( 'Filter Products', 'fs-product-catalog' ); ?></h3>
		<button type="button" class="fs-filters-toggle" aria-label="<?php esc_attr_e( 'Toggle filters', 'fs-product-catalog' ); ?>">
			<span class="fs-toggle-icon"></span>
		</button>
	</div>
	
	<div class="fs-filters-content">
		<!-- Search Filter -->
		<?php if ( FS_Product_Frontend::show_archive_sidebar_search() ) : ?>
			<div class="fs-filter-group fs-filter-search">
				<h4 class="fs-filter-title"><?php esc_html_e( 'Search', 'fs-product-catalog' ); ?></h4>
				<div class="fs-filter-content">
					<input 
						type="search" 
						class="fs-search-input" 
						placeholder="<?php esc_attr_e( 'Search products...', 'fs-product-catalog' ); ?>"
						aria-label="<?php esc_attr_e( 'Search products', 'fs-product-catalog' ); ?>"
					/>
				</div>
			</div>
		<?php endif; ?>
		
		<!-- Categories Filter -->
		<?php if ( FS_Product_Frontend::show_archive_sidebar_categories() && ! empty( $categories ) && ! is_wp_error( $categories ) ) : ?>
			<div class="fs-filter-group fs-filter-categories">
				<h4 class="fs-filter-title">
					<?php esc_html_e( 'Categories', 'fs-product-catalog' ); ?>
					<button type="button" class="fs-filter-toggle" aria-label="<?php esc_attr_e( 'Toggle categories', 'fs-product-catalog' ); ?>">
						<span class="fs-filter-toggle-icon"></span>
					</button>
				</h4>
				<div class="fs-filter-content">
					<?php foreach ( $categories as $category ) : ?>
						<label class="fs-filter-option">
							<input 
								type="checkbox" 
								name="fs_category[]" 
								value="<?php echo esc_attr( $category->term_id ); ?>"
								data-taxonomy="fs-product-category"
							/>
							<span class="fs-filter-label">
								<?php echo esc_html( $category->name ); ?>
								<span class="fs-filter-count">(<?php echo esc_html( $category->count ); ?>)</span>
							</span>
						</label>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>
		
		<!-- Brands Filter -->
		<?php if ( FS_Product_Frontend::show_archive_sidebar_brands() && ! empty( $brands ) && ! is_wp_error( $brands ) ) : ?>
			<div class="fs-filter-group fs-filter-brands">
				<h4 class="fs-filter-title">
					<?php esc_html_e( 'Brands', 'fs-product-catalog' ); ?>
					<button type="button" class="fs-filter-toggle" aria-label="<?php esc_attr_e( 'Toggle brands', 'fs-product-catalog' ); ?>">
						<span class="fs-filter-toggle-icon"></span>
					</button>
				</h4>
				<div class="fs-filter-content">
					<?php foreach ( $brands as $brand ) : ?>
						<label class="fs-filter-option">
							<input 
								type="checkbox" 
								name="fs_brand[]" 
								value="<?php echo esc_attr( $brand->term_id ); ?>"
								data-taxonomy="fs-product-brand"
							/>
							<span class="fs-filter-label">
								<?php echo esc_html( $brand->name ); ?>
								<span class="fs-filter-count">(<?php echo esc_html( $brand->count ); ?>)</span>
							</span>
						</label>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>
		
		<!-- Types Filter -->
		<?php if ( FS_Product_Frontend::show_archive_sidebar_types() && ! empty( $types ) && ! is_wp_error( $types ) ) : ?>
			<div class="fs-filter-group fs-filter-types">
				<h4 class="fs-filter-title">
					<?php esc_html_e( 'Types', 'fs-product-catalog' ); ?>
					<button type="button" class="fs-filter-toggle" aria-label="<?php esc_attr_e( 'Toggle types', 'fs-product-catalog' ); ?>">
						<span class="fs-filter-toggle-icon"></span>
					</button>
				</h4>
				<div class="fs-filter-content">
					<?php foreach ( $types as $type ) : ?>
						<label class="fs-filter-option">
							<input 
								type="checkbox" 
								name="fs_type[]" 
								value="<?php echo esc_attr( $type->term_id ); ?>"
								data-taxonomy="fs-product-type"
							/>
							<span class="fs-filter-label">
								<?php echo esc_html( $type->name ); ?>
								<span class="fs-filter-count">(<?php echo esc_html( $type->count ); ?>)</span>
							</span>
						</label>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>
		
		<!-- Tags Filter -->
		<?php if ( FS_Product_Frontend::show_archive_sidebar_tags() && ! empty( $tags ) && ! is_wp_error( $tags ) ) : ?>
			<div class="fs-filter-group fs-filter-tags">
				<h4 class="fs-filter-title">
					<?php esc_html_e( 'Tags', 'fs-product-catalog' ); ?>
					<button type="button" class="fs-filter-toggle" aria-label="<?php esc_attr_e( 'Toggle tags', 'fs-product-catalog' ); ?>">
						<span class="fs-filter-toggle-icon"></span>
					</button>
				</h4>
				<div class="fs-filter-content">
					<div class="fs-filter-tags-list">
						<?php foreach ( $tags as $tag ) : ?>
							<label class="fs-filter-tag">
								<input 
									type="checkbox" 
									name="fs_tag[]" 
									value="<?php echo esc_attr( $tag->term_id ); ?>"
									data-taxonomy="fs-product-tag"
								/>
								<span class="fs-tag-label"><?php echo esc_html( $tag->name ); ?></span>
							</label>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		<?php endif; ?>
		
		<!-- Active Filters -->
		<div class="fs-active-filters" style="display: none;">
			<h4 class="fs-active-filters-title"><?php esc_html_e( 'Active Filters', 'fs-product-catalog' ); ?></h4>
			<div class="fs-active-filters-list"></div>
			<button type="button" class="fs-clear-filters">
				<?php esc_html_e( 'Clear All Filters', 'fs-product-catalog' ); ?>
			</button>
		</div>
	</div>
</div>
