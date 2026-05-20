<?php
/**
 * Product Info Items Template Part
 *
 * This template can be overridden by copying it to yourtheme/fs-product-catalog/parts/product-info-items.php
 *
 * @package FS_Product_Catalog
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$product_id   = get_the_ID();
$product_info = get_field( 'product_info', $product_id );

if ( empty( $product_info ) || ! is_array( $product_info ) ) {
	return;
}
?>

<div class="fs-product-info">
	<h2 class="fs-product-info-title"><?php esc_html_e( 'Product Information', 'fs-product-catalog' ); ?></h2>
	
	<div class="fs-product-info-items">
		<?php foreach ( $product_info as $item ) : ?>
			<?php if ( ! empty( $item['title'] ) || ! empty( $item['content'] ) ) : ?>
				<div class="fs-product-info-item">
					<?php if ( ! empty( $item['title'] ) ) : ?>
						<h3 class="fs-info-item-title"><?php echo esc_html( $item['title'] ); ?></h3>
					<?php endif; ?>
					
					<?php if ( ! empty( $item['content'] ) ) : ?>
						<div class="fs-info-item-content">
							<?php echo wp_kses_post( $item['content'] ); ?>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		<?php endforeach; ?>
	</div>
</div>
