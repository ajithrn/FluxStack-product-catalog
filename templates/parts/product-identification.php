<?php
/**
 * Product Identification Display
 *
 * Shows SKU, Part No., Unit, MOQ, Lead Time, Weight, Dimensions
 * on the single product page. Only renders fields that have values.
 *
 * @package FS_Product_Catalog
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$product_id = get_the_ID();
$id_data    = \FSProductCatalog\Identification::get_all( $product_id );

// Don't render if no identification data exists.
if ( empty( $id_data ) ) {
	return;
}

$settings       = \FSProductCatalog\Settings::get_all();
$sku_label      = ! empty( $settings['sku_label'] ) ? $settings['sku_label'] : __( 'SKU', 'fs-product-catalog' );
$mfr_part_label = ! empty( $settings['mfr_part_label'] ) ? $settings['mfr_part_label'] : __( 'Mfr. Part No.', 'fs-product-catalog' );

// Map field keys to display labels.
$labels = array(
	'sku'       => $sku_label,
	'mfr_part'  => $mfr_part_label,
	'uom'       => __( 'Unit', 'fs-product-catalog' ),
	'moq'       => __( 'Min. Order', 'fs-product-catalog' ),
	'lead_time' => __( 'Lead Time', 'fs-product-catalog' ),
	'weight'    => __( 'Weight', 'fs-product-catalog' ),
	'dimensions' => __( 'Dimensions', 'fs-product-catalog' ),
);
?>

<div class="fs-product-identification">
	<dl class="fs-product-identification__list">
		<?php foreach ( $id_data as $key => $value ) : ?>
			<?php if ( isset( $labels[ $key ] ) ) : ?>
				<div class="fs-product-identification__item">
					<dt class="fs-product-identification__label"><?php echo esc_html( $labels[ $key ] ); ?></dt>
					<dd class="fs-product-identification__value"><?php echo esc_html( $value ); ?></dd>
				</div>
			<?php endif; ?>
		<?php endforeach; ?>
	</dl>
</div>
