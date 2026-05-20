<?php
/**
 * Product Search Results Template
 *
 * Displays product search results using the archive layout.
 * This template can be overridden by copying it to yourtheme/fs-product-catalog/search-products.php
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
	if ( \FSProductCatalog\Frontend::show_breadcrumbs() ) {
		\FSProductCatalog\TemplateLoader::get_template_part( 'breadcrumbs' );
	}
	?>

	<header class="fs-product-archive-header">
		<h1 class="fs-product-archive-title">
			<?php
			printf(
				/* translators: %s: search query */
				esc_html__( 'Search results for: %s', 'fs-product-catalog' ),
				'<span class="fs-search-term">' . esc_html( get_search_query() ) . '</span>'
			);
			?>
		</h1>
	</header>

	<div class="fs-product-archive-layout <?php echo esc_attr( 'sidebar-' . \FSProductCatalog\Frontend::get_sidebar_position() ); ?>">
		<?php if ( \FSProductCatalog\Frontend::show_sidebar() ) : ?>
			<aside class="fs-product-sidebar">
				<?php \FSProductCatalog\TemplateLoader::get_template_part( 'sidebar-filters' ); ?>
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
				<div class="fs-product-grid" data-columns="<?php echo esc_attr( \FSProductCatalog\Frontend::get_archive_columns() ); ?>">
					<?php
					while ( have_posts() ) :
						the_post();
						\FSProductCatalog\TemplateLoader::get_template_part( 'loop/product-card' );
					endwhile;
					?>
				</div>

				<?php if ( $wp_query->max_num_pages > 1 ) : ?>
					<div class="fs-product-load-more-wrap">
						<?php \FSProductCatalog\TemplateLoader::get_template_part( 'loop/pagination' ); ?>
					</div>
				<?php endif; ?>

			<?php else : ?>
				<?php \FSProductCatalog\TemplateLoader::get_template_part( 'loop/no-products' ); ?>
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
