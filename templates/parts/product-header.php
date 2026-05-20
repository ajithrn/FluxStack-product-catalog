<?php
/**
 * Product Header Template Part
 *
 * This template can be overridden by copying it to yourtheme/fs-product-catalog/parts/product-header.php
 *
 * @package FS_Product_Catalog
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<header class="fs-product-header">
	<h1 class="fs-product-title"><?php the_title(); ?></h1>
	
	<?php
	// Display taxonomies.
	$categories = get_the_terms( get_the_ID(), 'fs-product-category' );
	$brands     = get_the_terms( get_the_ID(), 'fs-product-brand' );
	$types      = get_the_terms( get_the_ID(), 'fs-product-type' );
	?>
	
	<?php if ( ! empty( $categories ) || ! empty( $brands ) || ! empty( $types ) ) : ?>
		<div class="fs-product-meta">
			<?php if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) : ?>
				<div class="fs-product-meta-item fs-product-categories">
					<span class="fs-meta-label"><?php esc_html_e( 'Category:', 'fs-product-catalog' ); ?></span>
					<span class="fs-meta-value">
						<?php
						$category_links = array();
						foreach ( $categories as $category ) {
							$category_links[] = '<a href="' . esc_url( get_term_link( $category ) ) . '">' . esc_html( $category->name ) . '</a>';
						}
						echo implode( ', ', $category_links );
						?>
					</span>
				</div>
			<?php endif; ?>
			
			<?php if ( ! empty( $brands ) && ! is_wp_error( $brands ) ) : ?>
				<div class="fs-product-meta-item fs-product-brands">
					<span class="fs-meta-label"><?php esc_html_e( 'Brand:', 'fs-product-catalog' ); ?></span>
					<span class="fs-meta-value">
						<?php
						$brand_links = array();
						foreach ( $brands as $brand ) {
							$brand_links[] = '<a href="' . esc_url( get_term_link( $brand ) ) . '">' . esc_html( $brand->name ) . '</a>';
						}
						echo implode( ', ', $brand_links );
						?>
					</span>
				</div>
			<?php endif; ?>
			
			<?php if ( ! empty( $types ) && ! is_wp_error( $types ) ) : ?>
				<div class="fs-product-meta-item fs-product-types">
					<span class="fs-meta-label"><?php esc_html_e( 'Type:', 'fs-product-catalog' ); ?></span>
					<span class="fs-meta-value">
						<?php
						$type_links = array();
						foreach ( $types as $type ) {
							$type_links[] = '<a href="' . esc_url( get_term_link( $type ) ) . '">' . esc_html( $type->name ) . '</a>';
						}
						echo implode( ', ', $type_links );
						?>
					</span>
				</div>
			<?php endif; ?>
		</div>
	<?php endif; ?>
</header>
