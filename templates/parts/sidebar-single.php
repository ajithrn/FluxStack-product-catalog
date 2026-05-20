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

$product_id = get_the_ID();
?>

<aside class="fs-product-single-sidebar">
	<div class="fs-filters-wrap">
		<div class="fs-filters-header">
			<h3 class="fs-filters-title"><?php esc_html_e( 'Browse Products', 'fs-product-catalog' ); ?></h3>
		</div>

		<div class="fs-filters-content">
			<!-- Search -->
			<?php if ( apply_filters( 'fs_single_sidebar_show_search', true ) ) : ?>
				<div class="fs-filter-group fs-filter-search">
					<h4 class="fs-filter-title"><?php esc_html_e( 'Search', 'fs-product-catalog' ); ?></h4>
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
			$categories = FS_Product_Frontend::get_cached_terms( 'fs-product-category' );
			if ( ! empty( $categories ) && apply_filters( 'fs_single_sidebar_show_categories', true ) ) :
				?>
				<div class="fs-filter-group fs-filter-categories">
					<h4 class="fs-filter-title"><?php esc_html_e( 'Categories', 'fs-product-catalog' ); ?></h4>
					<div class="fs-filter-content">
						<?php foreach ( $categories as $category ) : ?>
							<a href="<?php echo esc_url( get_term_link( $category ) ); ?>" class="fs-filter-option">
								<span class="fs-filter-label">
									<?php echo esc_html( $category->name ); ?>
									<span class="fs-filter-count">(<?php echo esc_html( $category->count ); ?>)</span>
								</span>
							</a>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>

			<!-- Brands -->
			<?php
			$brands = FS_Product_Frontend::get_cached_terms( 'fs-product-brand' );
			if ( ! empty( $brands ) && apply_filters( 'fs_single_sidebar_show_brands', true ) ) :
				?>
				<div class="fs-filter-group fs-filter-brands">
					<h4 class="fs-filter-title"><?php esc_html_e( 'Brands', 'fs-product-catalog' ); ?></h4>
					<div class="fs-filter-content">
						<?php foreach ( $brands as $brand ) : ?>
							<a href="<?php echo esc_url( get_term_link( $brand ) ); ?>" class="fs-filter-option">
								<span class="fs-filter-label">
									<?php echo esc_html( $brand->name ); ?>
									<span class="fs-filter-count">(<?php echo esc_html( $brand->count ); ?>)</span>
								</span>
							</a>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>

			<!-- Types -->
			<?php
			$types = FS_Product_Frontend::get_cached_terms( 'fs-product-type' );
			if ( ! empty( $types ) && apply_filters( 'fs_single_sidebar_show_types', true ) ) :
				?>
				<div class="fs-filter-group fs-filter-types">
					<h4 class="fs-filter-title"><?php esc_html_e( 'Types', 'fs-product-catalog' ); ?></h4>
					<div class="fs-filter-content">
						<?php foreach ( $types as $type ) : ?>
							<a href="<?php echo esc_url( get_term_link( $type ) ); ?>" class="fs-filter-option">
								<span class="fs-filter-label">
									<?php echo esc_html( $type->name ); ?>
									<span class="fs-filter-count">(<?php echo esc_html( $type->count ); ?>)</span>
								</span>
							</a>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>

			<!-- Tags -->
			<?php
			$tags = FS_Product_Frontend::get_cached_terms( 'fs-product-tag' );
			if ( ! empty( $tags ) && apply_filters( 'fs_single_sidebar_show_tags', true ) ) :
				?>
				<div class="fs-filter-group fs-filter-tags">
					<h4 class="fs-filter-title"><?php esc_html_e( 'Tags', 'fs-product-catalog' ); ?></h4>
					<div class="fs-filter-content">
						<div class="fs-filter-tags-list">
							<?php foreach ( $tags as $tag ) : ?>
								<a href="<?php echo esc_url( get_term_link( $tag ) ); ?>" class="fs-sidebar-tag">
									<?php echo esc_html( $tag->name ); ?>
								</a>
							<?php endforeach; ?>
						</div>
					</div>
				</div>
			<?php endif; ?>

			<?php
			/**
			 * Hook: fs_single_sidebar_after
			 *
			 * Allows adding custom content to the single product sidebar
			 */
			do_action( 'fs_single_sidebar_after', $product_id );
			?>
		</div>
	</div>
</aside>
