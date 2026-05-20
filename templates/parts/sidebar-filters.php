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

// Get all taxonomies (cached).
$categories  = \FSProductCatalog\Frontend::get_cached_terms( 'fs-product-category' );
$brands      = \FSProductCatalog\Frontend::get_cached_terms( 'fs-product-brand' );
$types       = \FSProductCatalog\Frontend::get_cached_terms( 'fs-product-type' );
$tags        = \FSProductCatalog\Frontend::get_cached_terms( 'fs-product-tag' );
$items_limit = absint( apply_filters( 'fs_sidebar_items_limit', \FSProductCatalog\Settings::get( 'archive_sidebar_items_limit', 8 ) ) );
?>

<div class="fs-filters-wrap">
	<div class="fs-filters-header">
		<h3 class="fs-filters-title"><?php esc_html_e( 'Filter Products', 'fs-product-catalog' ); ?></h3>
		<button type="button" class="fs-filters-toggle" aria-label="<?php esc_attr_e( 'Toggle filters', 'fs-product-catalog' ); ?>">
			<span class="fs-toggle-icon"></span>
		</button>
	</div>
	
	<div class="fs-filters-content">
		<!-- Active Filters (shown at top when filters are applied) -->
		<div class="fs-active-filters" style="display: none;">
			<div class="fs-active-filters-header">
				<h4 class="fs-active-filters-title"><?php esc_html_e( 'Active Filters', 'fs-product-catalog' ); ?></h4>
				<button type="button" class="fs-clear-filters">
					<?php esc_html_e( 'Clear All', 'fs-product-catalog' ); ?>
				</button>
			</div>
			<div class="fs-active-filters-list"></div>
		</div>

		<!-- Search Filter -->
		<?php if ( \FSProductCatalog\Frontend::show_archive_sidebar_search() ) : ?>
			<div class="fs-filter-group fs-filter-search">
				<h4 class="fs-filter-title fs-filter-title--collapsible">
					<?php esc_html_e( 'Search', 'fs-product-catalog' ); ?>
					<span class="fs-filter-collapse-icon"></span>
				</h4>
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
		<?php
		$cat_limit = absint( apply_filters( 'fs_sidebar_categories_limit', $items_limit ) );
		if ( \FSProductCatalog\Frontend::show_archive_sidebar_categories() && ! empty( $categories ) && ! is_wp_error( $categories ) ) :
			$cat_count = count( $categories );
			?>
			<div class="fs-filter-group fs-filter-categories">
				<h4 class="fs-filter-title fs-filter-title--collapsible">
					<?php esc_html_e( 'Categories', 'fs-product-catalog' ); ?>
					<span class="fs-filter-collapse-icon"></span>
				</h4>
				<div class="fs-filter-content">
					<?php foreach ( $categories as $index => $category ) : ?>
						<label class="fs-filter-option <?php echo $index >= $cat_limit ? 'fs-filter-item--hidden' : ''; ?>">
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
					<?php if ( $cat_count > $cat_limit ) : ?>
						<button type="button" class="fs-filter-show-more" data-more="<?php echo esc_attr( $cat_count - $cat_limit ); ?>">
							<?php printf( esc_html__( 'Show more (%d)', 'fs-product-catalog' ), $cat_count - $cat_limit ); ?>
						</button>
					<?php endif; ?>
				</div>
			</div>
		<?php endif; ?>
		
		<!-- Brands Filter -->
		<?php
		$brand_limit = absint( apply_filters( 'fs_sidebar_brands_limit', $items_limit ) );
		if ( \FSProductCatalog\Frontend::show_archive_sidebar_brands() && ! empty( $brands ) && ! is_wp_error( $brands ) ) :
			$brand_count = count( $brands );
			?>
			<div class="fs-filter-group fs-filter-brands">
				<h4 class="fs-filter-title fs-filter-title--collapsible">
					<?php esc_html_e( 'Brands', 'fs-product-catalog' ); ?>
					<span class="fs-filter-collapse-icon"></span>
				</h4>
				<div class="fs-filter-content">
					<?php foreach ( $brands as $index => $brand ) : ?>
						<label class="fs-filter-option <?php echo $index >= $brand_limit ? 'fs-filter-item--hidden' : ''; ?>">
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
					<?php if ( $brand_count > $brand_limit ) : ?>
						<button type="button" class="fs-filter-show-more" data-more="<?php echo esc_attr( $brand_count - $brand_limit ); ?>">
							<?php printf( esc_html__( 'Show more (%d)', 'fs-product-catalog' ), $brand_count - $brand_limit ); ?>
						</button>
					<?php endif; ?>
				</div>
			</div>
		<?php endif; ?>
		
		<!-- Types Filter -->
		<?php
		$type_limit = absint( apply_filters( 'fs_sidebar_types_limit', $items_limit ) );
		if ( \FSProductCatalog\Frontend::show_archive_sidebar_types() && ! empty( $types ) && ! is_wp_error( $types ) ) :
			$type_count = count( $types );
			?>
			<div class="fs-filter-group fs-filter-types">
				<h4 class="fs-filter-title fs-filter-title--collapsible">
					<?php esc_html_e( 'Types', 'fs-product-catalog' ); ?>
					<span class="fs-filter-collapse-icon"></span>
				</h4>
				<div class="fs-filter-content">
					<?php foreach ( $types as $index => $type ) : ?>
						<label class="fs-filter-option <?php echo $index >= $type_limit ? 'fs-filter-item--hidden' : ''; ?>">
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
					<?php if ( $type_count > $type_limit ) : ?>
						<button type="button" class="fs-filter-show-more" data-more="<?php echo esc_attr( $type_count - $type_limit ); ?>">
							<?php printf( esc_html__( 'Show more (%d)', 'fs-product-catalog' ), $type_count - $type_limit ); ?>
						</button>
					<?php endif; ?>
				</div>
			</div>
		<?php endif; ?>
		
		<!-- Tags Filter -->
		<?php
		$tag_limit = absint( apply_filters( 'fs_sidebar_tags_limit', 15 ) );
		if ( \FSProductCatalog\Frontend::show_archive_sidebar_tags() && ! empty( $tags ) && ! is_wp_error( $tags ) ) :
			$tag_count = count( $tags );
			?>
			<div class="fs-filter-group fs-filter-tags">
				<h4 class="fs-filter-title fs-filter-title--collapsible">
					<?php esc_html_e( 'Tags', 'fs-product-catalog' ); ?>
					<span class="fs-filter-collapse-icon"></span>
				</h4>
				<div class="fs-filter-content">
					<div class="fs-filter-tags-list">
						<?php foreach ( $tags as $index => $tag ) : ?>
							<label class="fs-filter-tag <?php echo $index >= $tag_limit ? 'fs-filter-item--hidden' : ''; ?>">
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
					<?php if ( $tag_count > $tag_limit ) : ?>
						<button type="button" class="fs-filter-show-more" data-more="<?php echo esc_attr( $tag_count - $tag_limit ); ?>">
							<?php printf( esc_html__( 'Show more (%d)', 'fs-product-catalog' ), $tag_count - $tag_limit ); ?>
						</button>
					<?php endif; ?>
				</div>
			</div>
		<?php endif; ?>
		
	</div>
</div>
