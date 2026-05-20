<?php
/**
 * Breadcrumbs Template Part
 *
 * This template can be overridden by copying it to yourtheme/fs-product-catalog/parts/breadcrumbs.php
 *
 * @package FS_Product_Catalog
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$breadcrumbs = array();

// Home link.
$breadcrumbs[] = array(
	'url'  => home_url( '/' ),
	'text' => __( 'Home', 'fs-product-catalog' ),
);

// Products archive link.
$post_type_object = get_post_type_object( 'fs-products' );
if ( $post_type_object ) {
	$breadcrumbs[] = array(
		'url'  => get_post_type_archive_link( 'fs-products' ),
		'text' => $post_type_object->labels->name,
	);
}

// Current page.
if ( is_singular( 'fs-products' ) ) {
	// Single product - add categories if available.
	$categories = get_the_terms( get_the_ID(), 'fs-product-category' );
	if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) {
		$category = array_shift( $categories );
		$breadcrumbs[] = array(
			'url'  => get_term_link( $category ),
			'text' => $category->name,
		);
	}
	$breadcrumbs[] = array(
		'url'  => '',
		'text' => get_the_title(),
	);
} elseif ( is_tax() ) {
	// Taxonomy archive.
	$term = get_queried_object();
	
	// Add parent terms if hierarchical.
	if ( is_taxonomy_hierarchical( $term->taxonomy ) && $term->parent ) {
		$parent_terms = array();
		$parent_id    = $term->parent;
		
		while ( $parent_id ) {
			$parent = get_term( $parent_id, $term->taxonomy );
			if ( ! is_wp_error( $parent ) ) {
				$parent_terms[] = $parent;
				$parent_id      = $parent->parent;
			} else {
				break;
			}
		}
		
		$parent_terms = array_reverse( $parent_terms );
		foreach ( $parent_terms as $parent_term ) {
			$breadcrumbs[] = array(
				'url'  => get_term_link( $parent_term ),
				'text' => $parent_term->name,
			);
		}
	}
	
	$breadcrumbs[] = array(
		'url'  => '',
		'text' => $term->name,
	);
}

// Allow filtering of breadcrumbs.
$breadcrumbs = apply_filters( 'fs_product_breadcrumbs', $breadcrumbs );

if ( empty( $breadcrumbs ) ) {
	return;
}
?>

<nav class="fs-product-breadcrumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'fs-product-catalog' ); ?>">
	<ol class="fs-breadcrumb-list">
		<?php foreach ( $breadcrumbs as $index => $crumb ) : ?>
			<li class="fs-breadcrumb-item <?php echo empty( $crumb['url'] ) ? 'active' : ''; ?>">
				<?php if ( ! empty( $crumb['url'] ) ) : ?>
					<a href="<?php echo esc_url( $crumb['url'] ); ?>"><?php echo esc_html( $crumb['text'] ); ?></a>
				<?php else : ?>
					<span><?php echo esc_html( $crumb['text'] ); ?></span>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ol>
</nav>
