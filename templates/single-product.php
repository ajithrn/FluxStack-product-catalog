<?php
/**
 * Single Product Template
 *
 * This template can be overridden by copying it to yourtheme/fs-product-catalog/single-product.php
 *
 * @package FS_Product_Catalog
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

/**
 * Hook: fs_product_before_main_content
 */
do_action( 'fs_product_before_main_content' );
?>

<div class="fs-product-container">
	<?php
	while ( have_posts() ) :
		the_post();
		?>

		<?php
		// Breadcrumbs.
		if ( FS_Product_Frontend::show_breadcrumbs() ) {
			FS_Product_Template_Loader::get_template_part( 'breadcrumbs' );
		}
		?>

		<div class="fs-product-single-layout <?php echo esc_attr( 'sidebar-' . FS_Product_Frontend::get_single_sidebar_position() ); ?>">
			<?php if ( FS_Product_Frontend::show_single_sidebar() ) : ?>
				<?php FS_Product_Template_Loader::get_template_part( 'sidebar-single' ); ?>
			<?php endif; ?>

			<article id="product-<?php the_ID(); ?>" <?php post_class( 'fs-product-single' ); ?>>

				<?php
				/**
				 * Hook: fs_product_before_single_product
				 */
				do_action( 'fs_product_before_single_product' );
				?>

				<?php
				// Product header (title).
				FS_Product_Template_Loader::get_template_part( 'product-header' );
				?>

				<?php
				// Product content.
				FS_Product_Template_Loader::get_template_part( 'product-content' );
				?>

				<div class="fs-product-main">
					<div class="fs-product-main-inner">
						<?php
						// Product image gallery (left side).
						FS_Product_Template_Loader::get_template_part( 'product-image-gallery' );
						?>

						<?php
						// Product info items (right side).
						FS_Product_Template_Loader::get_template_part( 'product-info-items' );
						?>
					</div>
				</div>

				<?php
				// Product specifications.
				FS_Product_Template_Loader::get_template_part( 'product-specifications' );
				?>

				<?php
				/**
				 * Hook: fs_product_after_single_product
				 */
				do_action( 'fs_product_after_single_product' );
				?>

			</article>
		</div>

	<?php endwhile; ?>
</div>

<?php
/**
 * Hook: fs_product_after_main_content
 */
do_action( 'fs_product_after_main_content' );

get_footer();
