<?php
/**
 * Product Image Gallery Template Part
 *
 * This template can be overridden by copying it to yourtheme/fs-product-catalog/parts/product-image-gallery.php
 *
 * @package FS_Product_Catalog
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$product_id      = get_the_ID();
$thumbnail_id    = get_post_thumbnail_id( $product_id );
$gallery_images  = get_field( 'product_gallery', $product_id );
$thumbnail_size  = FS_Product_Frontend::get_thumbnail_size();
$gallery_thumb_size = FS_Product_Frontend::get_gallery_thumbnail_size();

// Combine featured image with gallery images.
$all_images = array();

if ( $thumbnail_id ) {
	$all_images[] = array(
		'id'  => $thumbnail_id,
		'url' => wp_get_attachment_image_url( $thumbnail_id, 'full' ),
	);
}

if ( ! empty( $gallery_images ) && is_array( $gallery_images ) ) {
	foreach ( $gallery_images as $image ) {
		if ( isset( $image['id'] ) && $image['id'] !== $thumbnail_id ) {
			$all_images[] = array(
				'id'  => $image['id'],
				'url' => $image['url'],
			);
		}
	}
}
?>

<div class="fs-product-images">
	<?php if ( ! empty( $all_images ) ) : ?>
		<div class="fs-product-gallery">
			<div class="fs-gallery-main">
				<?php
				$first_image = $all_images[0];
				echo wp_get_attachment_image(
					$first_image['id'],
					$thumbnail_size,
					false,
					array(
						'class'         => 'fs-gallery-main-image',
						'data-full-url' => esc_url( $first_image['url'] ),
					)
				);
				?>
			</div>
			
			<?php if ( count( $all_images ) > 1 ) : ?>
				<div class="fs-gallery-thumbnails">
					<?php foreach ( $all_images as $index => $image ) : ?>
						<button 
							type="button" 
							class="fs-gallery-thumbnail <?php echo 0 === $index ? 'active' : ''; ?>"
							data-index="<?php echo esc_attr( $index ); ?>"
							data-full-url="<?php echo esc_url( $image['url'] ); ?>"
							aria-label="<?php esc_attr_e( 'View image', 'fs-product-catalog' ); ?> <?php echo esc_attr( $index + 1 ); ?>"
						>
							<?php
							echo wp_get_attachment_image(
								$image['id'],
								$gallery_thumb_size,
								false,
								array( 'class' => 'fs-thumbnail-image' )
							);
							?>
						</button>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
		
		<!-- Lightbox Modal -->
		<div class="fs-lightbox" id="fs-lightbox" style="display: none;" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Image gallery', 'fs-product-catalog' ); ?>">
			<div class="fs-lightbox-overlay"></div>
			<div class="fs-lightbox-content">
				<button type="button" class="fs-lightbox-close" aria-label="<?php esc_attr_e( 'Close', 'fs-product-catalog' ); ?>">
					<span aria-hidden="true">&times;</span>
				</button>
				
				<?php if ( count( $all_images ) > 1 ) : ?>
					<button type="button" class="fs-lightbox-prev" aria-label="<?php esc_attr_e( 'Previous image', 'fs-product-catalog' ); ?>">
						<span aria-hidden="true">‹</span>
					</button>
					<button type="button" class="fs-lightbox-next" aria-label="<?php esc_attr_e( 'Next image', 'fs-product-catalog' ); ?>">
						<span aria-hidden="true">›</span>
					</button>
				<?php endif; ?>
				
				<div class="fs-lightbox-image-wrap">
					<img src="" alt="" class="fs-lightbox-image" />
				</div>
				
				<?php if ( count( $all_images ) > 1 ) : ?>
					<div class="fs-lightbox-counter">
						<span class="fs-lightbox-current">1</span> / <span class="fs-lightbox-total"><?php echo count( $all_images ); ?></span>
					</div>
				<?php endif; ?>
			</div>
			
			<!-- Hidden data for JavaScript -->
			<script type="application/json" class="fs-gallery-data">
				<?php echo wp_json_encode( $all_images ); ?>
			</script>
		</div>
		
	<?php else : ?>
		<div class="fs-product-no-image">
			<div class="fs-no-image-placeholder">
				<span class="dashicons dashicons-products"></span>
			</div>
		</div>
	<?php endif; ?>
</div>
