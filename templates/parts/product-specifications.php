<?php
/**
 * Product Specifications Template Part
 *
 * This template can be overridden by copying it to yourtheme/fs-product-catalog/parts/product-specifications.php
 *
 * @package FS_Product_Catalog
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$product_id      = get_the_ID();
$specifications  = get_field( 'product_specifications', $product_id );

if ( empty( $specifications ) || ! is_array( $specifications ) ) {
	return;
}
?>

<div class="fs-product-specifications">
	<h2 class="fs-product-specs-title"><?php esc_html_e( 'Specifications', 'fs-product-catalog' ); ?></h2>
	
	<?php
	// Filter out specs with no content at all.
	$valid_specs = array();
	foreach ( $specifications as $index => $spec ) {
		if ( ! empty( $spec['content'] ) || ! empty( $spec['tab_title'] ) ) {
			$valid_specs[] = $spec;
		}
	}

	if ( empty( $valid_specs ) ) {
		return;
	}

	// If only one spec, show without tabs.
	if ( 1 === count( $valid_specs ) ) : ?>
		<div class="fs-spec-content">
			<?php echo wp_kses_post( $valid_specs[0]['content'] ); ?>
		</div>
	<?php else : ?>
		<div class="fs-specs-tabs">
			<div class="fs-specs-tabs-nav" role="tablist">
				<?php foreach ( $valid_specs as $index => $spec ) :
					$tab_title = ! empty( $spec['tab_title'] )
						? $spec['tab_title']
						: sprintf( __( 'Tab %d', 'fs-product-catalog' ), $index + 1 );
					?>
					<button 
						type="button"
						class="fs-specs-tab-button <?php echo 0 === $index ? 'active' : ''; ?>"
						role="tab"
						aria-selected="<?php echo 0 === $index ? 'true' : 'false'; ?>"
						aria-controls="fs-spec-panel-<?php echo esc_attr( $index ); ?>"
						id="fs-spec-tab-<?php echo esc_attr( $index ); ?>"
						data-tab="<?php echo esc_attr( $index ); ?>"
					>
						<?php echo esc_html( $tab_title ); ?>
					</button>
				<?php endforeach; ?>
			</div>
			
			<div class="fs-specs-tabs-content">
				<?php foreach ( $valid_specs as $index => $spec ) : ?>
					<?php if ( ! empty( $spec['content'] ) ) : ?>
						<div 
							class="fs-specs-tab-panel <?php echo 0 === $index ? 'active' : ''; ?>"
							role="tabpanel"
							aria-labelledby="fs-spec-tab-<?php echo esc_attr( $index ); ?>"
							id="fs-spec-panel-<?php echo esc_attr( $index ); ?>"
							<?php echo 0 !== $index ? 'hidden' : ''; ?>
						>
							<div class="fs-spec-content">
								<?php echo wp_kses_post( $spec['content'] ); ?>
							</div>
						</div>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endif; ?>
</div>
