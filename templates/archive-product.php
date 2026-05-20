<?php
/**
 * Archive Product Template
 *
 * This template can be overridden by copying it to yourtheme/fs-product-catalog/archive-product.php
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

<div class="fs-product-archive-container">
	<?php
	// Breadcrumbs.
	if ( FS_Product_Frontend::show_breadcrumbs() ) {
		FS_Product_Template_Loader::get_template_part( 'breadcrumbs' );
	}
	?>

	<header class="fs-product-archive-header">
		<h1 class="fs-product-archive-title"><?php esc_html_e( 'Products', 'fs-product-catalog' ); ?></h1>
		<?php
		$description = get_the_archive_description();
		if ( $description ) {
			echo '<div class="fs-product-archive-description">' . wp_kses_post( $description ) . '</div>';
		}
		?>
	</header>

	<div class="fs-product-archive-layout <?php echo esc_attr( 'sidebar-' . FS_Product_Frontend::get_sidebar_position() ); ?>">
		<?php if ( FS_Product_Frontend::show_sidebar() ) : ?>
			<aside class="fs-product-sidebar">
				<?php FS_Product_Template_Loader::get_template_part( 'sidebar-filters' ); ?>
			</aside>
		<?php endif; ?>

		<div class="fs-product-archive-main">
			<div class="fs-product-results-bar">
				<div class="fs-product-results-count">
					<?php
					global $wp_query;
					$total = $wp_query->found_posts;
					printf(
						/* translators: %s: number of products */
						esc_html( _n( '%s product found', '%s products found', $total, 'fs-product-catalog' ) ),
						'<span class="count">' . esc_html( number_format_i18n( $total ) ) . '</span>'
					);
					?>
				</div>
			</div>

			<?php if ( have_posts() ) : ?>
				<div class="fs-product-grid" data-columns="<?php echo esc_attr( FS_Product_Frontend::get_archive_columns() ); ?>">
					<?php
					while ( have_posts() ) :
						the_post();
						FS_Product_Template_Loader::get_template_part( 'loop/product-card' );
					endwhile;
					?>
				</div>

				<div class="fs-product-load-more-wrap">
					<?php if ( $wp_query->max_num_pages > 1 ) : ?>
						<button type="button" class="fs-product-load-more" data-page="1" data-max-pages="<?php echo esc_attr( $wp_query->max_num_pages ); ?>">
							<?php esc_html_e( 'Load More Products', 'fs-product-catalog' ); ?>
						</button>
						<div class="fs-product-loading" style="display: none;">
							<span class="fs-product-spinner"></span>
							<span class="fs-product-loading-text"><?php esc_html_e( 'Loading...', 'fs-product-catalog' ); ?></span>
						</div>
					<?php endif; ?>
				</div>

			<?php else : ?>
				<?php FS_Product_Template_Loader::get_template_part( 'loop/no-products' ); ?>
			<?php endif; ?>
		</div>
	</div>
</div>

<?php
/**
 * Hook: fs_product_after_main_content
 */
do_action( 'fs_product_after_main_content' );

get_footer();
