<?php
/**
 * Product Card Template Part
 *
 * This template can be overridden by copying it to yourtheme/fs-product-catalog/parts/loop/product-card.php
 *
 * @package FS_Product_Catalog
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$product_id = get_the_ID();
?>

<article id="product-<?php echo esc_attr( $product_id ); ?>" <?php post_class( 'fs-product-card' ); ?>>
	<a href="<?php the_permalink(); ?>" class="fs-product-card-link">
		<div class="fs-product-card-image">
			<?php if ( has_post_thumbnail() ) : ?>
				<?php the_post_thumbnail( 'medium', array( 'class' => 'fs-card-thumbnail' ) ); ?>
			<?php else : ?>
				<div class="fs-card-no-image">
					<span class="dashicons dashicons-products"></span>
				</div>
			<?php endif; ?>
		</div>
		
		<div class="fs-product-card-content">
			<h3 class="fs-product-card-title"><?php the_title(); ?></h3>
			
			<?php
			$categories = get_the_terms( $product_id, 'fs-product-category' );
			if ( ! empty( $categories ) && ! is_wp_error( $categories ) && apply_filters( 'fs_product_card_show_category', true ) ) :
				?>
				<div class="fs-product-card-category">
					<?php echo esc_html( $categories[0]->name ); ?>
				</div>
			<?php endif; ?>
			
			<?php if ( has_excerpt() && apply_filters( 'fs_product_card_show_excerpt', true ) ) : ?>
				<div class="fs-product-card-excerpt">
					<?php echo wp_kses_post( get_the_excerpt() ); ?>
				</div>
			<?php endif; ?>
			
			<?php if ( apply_filters( 'fs_product_card_show_more_link', true ) ) : ?>
				<span class="fs-product-card-more">
					<?php esc_html_e( 'View Details', 'fs-product-catalog' ); ?>
					<span aria-hidden="true">→</span>
				</span>
			<?php endif; ?>
		</div>
	</a>
</article>
