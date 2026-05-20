<?php
/**
 * Pagination Template Part
 *
 * Traditional numbered pagination for product archives.
 * This template can be overridden by copying it to yourtheme/fs-product-catalog/parts/loop/pagination.php
 *
 * @package FS_Product_Catalog
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wp_query;

$total_pages  = (int) $wp_query->max_num_pages;
$current_page = max( 1, absint( get_query_var( 'paged' ) ) );

if ( $total_pages <= 1 ) {
	return;
}

$pagination_args = array(
	'base'      => str_replace( 999999999, '%#%', esc_url( get_pagenum_link( 999999999 ) ) ),
	'format'    => '?paged=%#%',
	'total'     => $total_pages,
	'current'   => $current_page,
	'mid_size'  => 2,
	'end_size'  => 1,
	'prev_text' => '<span aria-hidden="true">&laquo;</span><span class="screen-reader-text">' . esc_html__( 'Previous page', 'fs-product-catalog' ) . '</span>',
	'next_text' => '<span aria-hidden="true">&raquo;</span><span class="screen-reader-text">' . esc_html__( 'Next page', 'fs-product-catalog' ) . '</span>',
	'type'      => 'array',
);

$links = paginate_links( $pagination_args );

if ( empty( $links ) ) {
	return;
}
?>

<nav class="fs-product-pagination" aria-label="<?php esc_attr_e( 'Product pagination', 'fs-product-catalog' ); ?>">
	<ul class="fs-pagination-list">
		<?php foreach ( $links as $link ) : ?>
			<li class="fs-pagination-item">
				<?php echo $link; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- paginate_links output is safe. ?>
			</li>
		<?php endforeach; ?>
	</ul>
</nav>

<?php
// SEO: Output rel next/prev link tags.
if ( $current_page > 1 ) :
	$prev_url = get_pagenum_link( $current_page - 1 );
	?>
	<link rel="prev" href="<?php echo esc_url( $prev_url ); ?>" />
	<?php
endif;

if ( $current_page < $total_pages ) :
	$next_url = get_pagenum_link( $current_page + 1 );
	?>
	<link rel="next" href="<?php echo esc_url( $next_url ); ?>" />
<?php endif; ?>
