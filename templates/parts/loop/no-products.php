<?php
/**
 * No Products Template Part
 *
 * This template can be overridden by copying it to yourtheme/fs-product-catalog/parts/loop/no-products.php
 *
 * @package FS_Product_Catalog
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="fs-no-products">
	<div class="fs-no-products-icon">
		<span class="dashicons dashicons-info"></span>
	</div>
	<h3 class="fs-no-products-title"><?php esc_html_e( 'No products found', 'fs-product-catalog' ); ?></h3>
	<p class="fs-no-products-message"><?php esc_html_e( 'Try adjusting your filters or search terms.', 'fs-product-catalog' ); ?></p>
</div>
