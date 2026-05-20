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
				<?php if ( FS_Product_Frontend::show_sorting() ) : ?>
					<div class="fs-product-sort">
						<label for="fs-sort-select" class="fs-sort-label"><?php esc_html_e( 'Sort by:', 'fs-product-catalog' ); ?></label>
						<select id="fs-sort-select" class="fs-sort-select">
							<option value="menu_order" <?php selected( FS_Product_Frontend::get_default_orderby(), 'menu_order' ); ?>><?php esc_html_e( 'Default', 'fs-product-catalog' ); ?></option>
							<option value="title_asc"><?php esc_html_e( 'Name A–Z', 'fs-product-catalog' ); ?></option>
							<option value="title_desc"><?php esc_html_e( 'Name Z–A', 'fs-product-catalog' ); ?></option>
							<option value="date_desc"><?php esc_html_e( 'Newest', 'fs-product-catalog' ); ?></option>
							<option value="date_asc"><?php esc_html_e( 'Oldest', 'fs-product-catalog' ); ?></option>
						</select>
					</div>
				<?php endif; ?>
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

				<div class="fs-product-load-more-wrap" data-pagination-mode="<?php echo esc_attr( FS_Product_Frontend::get_pagination_mode() ); ?>">
					<?php if ( $wp_query->max_num_pages > 1 ) : ?>
						<?php if ( 'pagination' === FS_Product_Frontend::get_pagination_mode() ) : ?>
							<?php FS_Product_Template_Loader::get_template_part( 'loop/pagination' ); ?>
						<?php else : ?>
							<button type="button" class="fs-product-load-more" data-page="1" data-max-pages="<?php echo esc_attr( $wp_query->max_num_pages ); ?>">
								<?php echo esc_html( FS_Product_Frontend::get_load_more_text() ); ?>
							</button>
							<div class="fs-product-loading" style="display: none;">
								<span class="fs-product-spinner"></span>
								<span class="fs-product-loading-text"><?php esc_html_e( 'Loading...', 'fs-product-catalog' ); ?></span>
							</div>
						<?php endif; ?>
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
