<?php
/**
 * Single Product Sidebar Template Part
 *
 * This template can be overridden by copying it to yourtheme/fs-product-catalog/parts/sidebar-single.php
 *
 * @package FS_Product_Catalog
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$product_id  = get_the_ID();
$items_limit = absint( apply_filters( 'fs_sidebar_items_limit', \FSProductCatalog\Settings::get( 'sidebar_items_limit', 8 ) ) );
?>

<aside class="fs-product-single-sidebar">
	<div class="fs-filters-wrap">
		<div class="fs-filters-header">
			<h3 class="fs-filters-title"><?php esc_html_e( 'Browse Products', 'fs-product-catalog' ); ?></h3>
			<button type="button" class="fs-filters-toggle" aria-label="<?php esc_attr_e( 'Toggle filters', 'fs-product-catalog' ); ?>">
				<span class="fs-toggle-icon"></span>
			</button>
		</div>

		<div class="fs-filters-content">
			<!-- Search -->
			<?php if ( apply_filters( 'fs_single_sidebar_show_search', true ) ) : ?>
				<div class="fs-filter-group fs-filter-search">
					<h4 class="fs-filter-title fs-filter-title--collapsible">
						<?php esc_html_e( 'Search', 'fs-product-catalog' ); ?>
						<span class="fs-filter-collapse-icon"></span>
					</h4>
					<div class="fs-filter-content">
						<form role="search" method="get" class="fs-sidebar-search-form" action="<?php echo esc_url( get_post_type_archive_link( 'fs-products' ) ); ?>">
							<input type="search" class="fs-search-input" placeholder="<?php esc_attr_e( 'Search products...', 'fs-product-catalog' ); ?>" value="<?php echo get_search_query(); ?>" name="s" />
							<input type="hidden" name="post_type" value="fs-products" />
						</form>
					</div>
				</div>
			<?php endif; ?>

			<!-- Categories -->
			<?php
			$categories    = \FSProductCatalog\Frontend::get_cached_terms( 'fs-product-category' );
			$cat_limit     = absint( apply_filters( 'fs_sidebar_categories_limit', $items_limit ) );
			if ( ! empty( $categories ) && apply_filters( 'fs_single_sidebar_show_categories', true ) ) :
				$cat_count = count( $categories );
				?>
				<div class="fs-filter-group fs-filter-categories">
					<h4 class="fs-filter-title fs-filter-title--collapsible">
						<?php esc_html_e( 'Categories', 'fs-product-catalog' ); ?>
						<span class="fs-filter-collapse-icon"></span>
					</h4>
					<div class="fs-filter-content">
						<?php foreach ( $categories as $index => $category ) : ?>
							<a href="<?php echo esc_url( get_term_link( $category ) ); ?>" class="fs-filter-option <?php echo $index >= $cat_limit ? 'fs-filter-item--hidden' : ''; ?>">
								<span class="fs-filter-label">
									<?php echo esc_html( $category->name ); ?>
									<span class="fs-filter-count">(<?php echo esc_html( $category->count ); ?>)</span>
								</span>
							</a>
						<?php endforeach; ?>
						<?php if ( $cat_count > $cat_limit ) : ?>
							<button type="button" class="fs-filter-show-more" data-more="<?php echo esc_attr( $cat_count - $cat_limit ); ?>">
								<?php printf( esc_html__( 'Show more (%d)', 'fs-product-catalog' ), $cat_count - $cat_limit ); ?>
							</button>
						<?php endif; ?>
					</div>
				</div>
			<?php endif; ?>

			<!-- Brands -->
			<?php
			$brands      = \FSProductCatalog\Frontend::get_cached_terms( 'fs-product-brand' );
			$brand_limit = absint( apply_filters( 'fs_sidebar_brands_limit', $items_limit ) );
			if ( ! empty( $brands ) && apply_filters( 'fs_single_sidebar_show_brands', true ) ) :
				$brand_count = count( $brands );
				?>
				<div class="fs-filter-group fs-filter-brands">
					<h4 class="fs-filter-title fs-filter-title--collapsible">
						<?php esc_html_e( 'Brands', 'fs-product-catalog' ); ?>
						<span class="fs-filter-collapse-icon"></span>
					</h4>
					<div class="fs-filter-content">
						<?php foreach ( $brands as $index => $brand ) : ?>
							<a href="<?php echo esc_url( get_term_link( $brand ) ); ?>" class="fs-filter-option <?php echo $index >= $brand_limit ? 'fs-filter-item--hidden' : ''; ?>">
								<span class="fs-filter-label">
									<?php echo esc_html( $brand->name ); ?>
									<span class="fs-filter-count">(<?php echo esc_html( $brand->count ); ?>)</span>
								</span>
							</a>
						<?php endforeach; ?>
						<?php if ( $brand_count > $brand_limit ) : ?>
							<button type="button" class="fs-filter-show-more" data-more="<?php echo esc_attr( $brand_count - $brand_limit ); ?>">
								<?php printf( esc_html__( 'Show more (%d)', 'fs-product-catalog' ), $brand_count - $brand_limit ); ?>
							</button>
						<?php endif; ?>
					</div>
				</div>
			<?php endif; ?>

			<!-- Types -->
			<?php
			$types      = \FSProductCatalog\Frontend::get_cached_terms( 'fs-product-type' );
			$type_limit = absint( apply_filters( 'fs_sidebar_types_limit', $items_limit ) );
			if ( ! empty( $types ) && apply_filters( 'fs_single_sidebar_show_types', true ) ) :
				$type_count = count( $types );
				?>
				<div class="fs-filter-group fs-filter-types">
					<h4 class="fs-filter-title fs-filter-title--collapsible">
						<?php esc_html_e( 'Types', 'fs-product-catalog' ); ?>
						<span class="fs-filter-collapse-icon"></span>
					</h4>
					<div class="fs-filter-content">
						<?php foreach ( $types as $index => $type ) : ?>
							<a href="<?php echo esc_url( get_term_link( $type ) ); ?>" class="fs-filter-option <?php echo $index >= $type_limit ? 'fs-filter-item--hidden' : ''; ?>">
								<span class="fs-filter-label">
									<?php echo esc_html( $type->name ); ?>
									<span class="fs-filter-count">(<?php echo esc_html( $type->count ); ?>)</span>
								</span>
							</a>
						<?php endforeach; ?>
						<?php if ( $type_count > $type_limit ) : ?>
							<button type="button" class="fs-filter-show-more" data-more="<?php echo esc_attr( $type_count - $type_limit ); ?>">
								<?php printf( esc_html__( 'Show more (%d)', 'fs-product-catalog' ), $type_count - $type_limit ); ?>
							</button>
						<?php endif; ?>
					</div>
				</div>
			<?php endif; ?>

			<!-- Tags -->
			<?php
			$tags      = \FSProductCatalog\Frontend::get_cached_terms( 'fs-product-tag' );
			$tag_limit = absint( apply_filters( 'fs_sidebar_tags_limit', 15 ) );
			if ( ! empty( $tags ) && apply_filters( 'fs_single_sidebar_show_tags', true ) ) :
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
								<a href="<?php echo esc_url( get_term_link( $tag ) ); ?>" class="fs-sidebar-tag <?php echo $index >= $tag_limit ? 'fs-filter-item--hidden' : ''; ?>">
									<?php echo esc_html( $tag->name ); ?>
								</a>
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

			<?php
			/**
			 * Hook: fs_single_sidebar_after
			 */
			do_action( 'fs_single_sidebar_after', $product_id );
			?>
		</div>
	</div>
</aside>
