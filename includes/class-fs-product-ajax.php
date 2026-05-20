<?php
/**
 * AJAX Handler
 *
 * Handles AJAX requests for filtering and loading products.
 *
 * @package FS_Product_Catalog
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class FS_Product_Ajax
 *
 * Manages AJAX functionality for product filtering and loading.
 */
class FS_Product_Ajax {
	/**
	 * Initialize the class
	 */
	public static function init() {
		// AJAX actions for logged in and non-logged in users.
		add_action( 'wp_ajax_fs_filter_products', array( __CLASS__, 'filter_products' ) );
		add_action( 'wp_ajax_nopriv_fs_filter_products', array( __CLASS__, 'filter_products' ) );

		add_action( 'wp_ajax_fs_load_more_products', array( __CLASS__, 'load_more_products' ) );
		add_action( 'wp_ajax_nopriv_fs_load_more_products', array( __CLASS__, 'load_more_products' ) );
	}

	/**
	 * Filter products via AJAX
	 */
	public static function filter_products() {
		// Verify nonce.
		check_ajax_referer( 'fs_product_filter_nonce', 'nonce' );

		// Get filter parameters.
		$search     = isset( $_POST['search'] ) ? sanitize_text_field( wp_unslash( $_POST['search'] ) ) : '';
		$categories = isset( $_POST['categories'] ) ? array_map( 'absint', (array) $_POST['categories'] ) : array();
		$brands     = isset( $_POST['brands'] ) ? array_map( 'absint', (array) $_POST['brands'] ) : array();
		$types      = isset( $_POST['types'] ) ? array_map( 'absint', (array) $_POST['types'] ) : array();
		$tags       = isset( $_POST['tags'] ) ? array_map( 'absint', (array) $_POST['tags'] ) : array();
		$paged      = isset( $_POST['paged'] ) ? absint( $_POST['paged'] ) : 1;
		$per_page   = isset( $_POST['per_page'] ) ? absint( $_POST['per_page'] ) : 12;

		// Build query args.
		$args = array(
			'post_type'      => 'fs-products',
			'posts_per_page' => $per_page,
			'paged'          => $paged,
			'post_status'    => 'publish',
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
		);

		// Add search.
		if ( ! empty( $search ) ) {
			$args['s'] = $search;
		}

		// Build tax query.
		$tax_query = array();

		if ( ! empty( $categories ) ) {
			$tax_query[] = array(
				'taxonomy' => 'fs-product-category',
				'field'    => 'term_id',
				'terms'    => $categories,
			);
		}

		if ( ! empty( $brands ) ) {
			$tax_query[] = array(
				'taxonomy' => 'fs-product-brand',
				'field'    => 'term_id',
				'terms'    => $brands,
			);
		}

		if ( ! empty( $types ) ) {
			$tax_query[] = array(
				'taxonomy' => 'fs-product-type',
				'field'    => 'term_id',
				'terms'    => $types,
			);
		}

		if ( ! empty( $tags ) ) {
			$tax_query[] = array(
				'taxonomy' => 'fs-product-tag',
				'field'    => 'term_id',
				'terms'    => $tags,
			);
		}

		// Add tax query if we have filters.
		if ( ! empty( $tax_query ) ) {
			// Add relation if multiple taxonomies.
			if ( count( $tax_query ) > 1 ) {
				$tax_query['relation'] = 'AND';
			}
			$args['tax_query'] = $tax_query;
		}

		// Allow filtering of query args.
		$args = apply_filters( 'fs_product_ajax_query_args', $args );

		// Execute query.
		$query = new WP_Query( $args );

		// Prepare response.
		$response = array(
			'success'   => true,
			'html'      => '',
			'found'     => $query->found_posts,
			'max_pages' => $query->max_num_pages,
			'current'   => $paged,
		);

		if ( $query->have_posts() ) {
			ob_start();
			while ( $query->have_posts() ) {
				$query->the_post();
				FS_Product_Template_Loader::get_template_part( 'loop/product-card' );
			}
			$response['html'] = ob_get_clean();
			wp_reset_postdata();
		} else {
			ob_start();
			FS_Product_Template_Loader::get_template_part( 'loop/no-products' );
			$response['html'] = ob_get_clean();
		}

		wp_send_json( $response );
	}

	/**
	 * Load more products via AJAX
	 */
	public static function load_more_products() {
		// Verify nonce.
		check_ajax_referer( 'fs_product_filter_nonce', 'nonce' );

		// Get parameters.
		$paged    = isset( $_POST['paged'] ) ? absint( $_POST['paged'] ) : 1;
		$per_page = isset( $_POST['per_page'] ) ? absint( $_POST['per_page'] ) : 12;
		$query_vars = isset( $_POST['query_vars'] ) ? json_decode( wp_unslash( $_POST['query_vars'] ), true ) : array();

		// Sanitize query vars.
		if ( ! empty( $query_vars ) && is_array( $query_vars ) ) {
			$query_vars = array_map( 'sanitize_text_field', $query_vars );
		}

		// Build query args.
		$args = array(
			'post_type'      => 'fs-products',
			'posts_per_page' => $per_page,
			'paged'          => $paged,
			'post_status'    => 'publish',
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
		);

		// Merge with query vars if available.
		if ( ! empty( $query_vars ) ) {
			$args = array_merge( $args, $query_vars );
		}

		// Allow filtering of query args.
		$args = apply_filters( 'fs_product_load_more_query_args', $args );

		// Execute query.
		$query = new WP_Query( $args );

		// Prepare response.
		$response = array(
			'success'   => true,
			'html'      => '',
			'found'     => $query->found_posts,
			'max_pages' => $query->max_num_pages,
			'current'   => $paged,
		);

		if ( $query->have_posts() ) {
			ob_start();
			while ( $query->have_posts() ) {
				$query->the_post();
				FS_Product_Template_Loader::get_template_part( 'loop/product-card' );
			}
			$response['html'] = ob_get_clean();
			wp_reset_postdata();
		}

		wp_send_json( $response );
	}

	/**
	 * Get filter counts
	 *
	 * @param string $taxonomy Taxonomy name.
	 * @param array  $args Query arguments.
	 * @return array
	 */
	public static function get_filter_counts( $taxonomy, $args = array() ) {
		$terms = get_terms(
			array(
				'taxonomy'   => $taxonomy,
				'hide_empty' => true,
			)
		);

		$counts = array();

		if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
			foreach ( $terms as $term ) {
				$counts[ $term->term_id ] = $term->count;
			}
		}

		return $counts;
	}
}
