<?php
/**
 * Related Products Template Part
 *
 * Shows products from the same category, excluding the current product.
 * This template can be overridden by copying it to yourtheme/fs-product-catalog/parts/related-products.php
 *
 * @package FS_Product_Catalog
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$product_id = get_the_ID();

// Check if related products should be shown.
if ( ! apply_filters( 'fs_product_show_related', true, $product_id ) ) {
	return;
}

$related_count = apply_filters( 'fs_product_related_count', 4, $product_id );
$related_count = max( 1, min( absint( $related_count ), 8 ) );

// Get categories for the current product.
$categories = get_the_terms( $product_id, 'fs-product-category' );

if ( empty( $categories ) || is_wp_error( $categories ) ) {
	return;
}

$category_ids = wp_list_pluck( $categories, 'term_id' );

// Query related products.
$related_args = array(
	'post_type'      => 'fs-products',
	'posts_per_page' => $related_count,
	'post_status'    => 'publish',
	'post__not_in'   => array( $product_id ),
	'orderby'        => 'rand',
	'tax_query'      => array(
		array(
			'taxonomy' => 'fs-product-category',
			'field'    => 'term_id',
			'terms'    => $category_ids,
		),
	),
);

/**
 * Filter the related products query args.
 *
 * @param array $related_args WP_Query arguments.
 * @param int   $product_id   Current product ID.
 */
$related_args = apply_filters( 'fs_product_related_query_args', $related_args, $product_id );

$related_query = new WP_Query( $related_args );

if ( ! $related_query->have_posts() ) {
	wp_reset_postdata();
	return;
}

$columns = min( $related_count, 4 );
?>

<section class="fs-related-products" aria-labelledby="fs-related-title">
	<h2 class="fs-related-products-title" id="fs-related-title">
		<?php esc_html_e( 'Related Products', 'fs-product-catalog' ); ?>
	</h2>

	<div class="fs-product-grid fs-related-grid" data-columns="<?php echo esc_attr( $columns ); ?>">
		<?php
		while ( $related_query->have_posts() ) :
			$related_query->the_post();
			FS_Product_Template_Loader::get_template_part( 'loop/product-card' );
		endwhile;
		?>
	</div>
</section>

<?php
wp_reset_postdata();
