<?php
/**
 * Frontend Handler
 *
 * Handles frontend assets, hooks, and template functionality.
 *
 * @package FS_Product_Catalog
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class FS_Product_Frontend
 *
 * Manages frontend functionality and asset loading.
 */
class FS_Product_Frontend {
	/**
	 * Initialize the class
	 */
	public static function init() {
		// Enqueue frontend assets.
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_frontend_assets' ) );

		// Add body classes.
		add_filter( 'body_class', array( __CLASS__, 'body_classes' ) );

		// Modify main query.
		add_action( 'pre_get_posts', array( __CLASS__, 'modify_main_query' ) );
	}

	/**
	 * Enqueue frontend assets
	 */
	public static function enqueue_frontend_assets() {
		// Check if we're on a product page.
		if ( ! self::is_product_page() ) {
			return;
		}

		// Common styles (loaded on all product pages).
		wp_enqueue_style(
			'fs-product-catalog-common',
			FS_PRODUCT_CATALOG_PLUGIN_URL . 'assets/css/frontend-common.css',
			array(),
			FS_PRODUCT_CATALOG_VERSION
		);

		// Single product page.
		if ( is_singular( 'fs-products' ) ) {
			wp_enqueue_style(
				'fs-product-catalog-single',
				FS_PRODUCT_CATALOG_PLUGIN_URL . 'assets/css/frontend-single.css',
				array( 'fs-product-catalog-common' ),
				FS_PRODUCT_CATALOG_VERSION
			);

			wp_enqueue_script(
				'fs-product-catalog-single',
				FS_PRODUCT_CATALOG_PLUGIN_URL . 'assets/js/frontend-single.js',
				array(),
				FS_PRODUCT_CATALOG_VERSION,
				true
			);

			// Localize script for single product.
			wp_localize_script(
				'fs-product-catalog-single',
				'fsProductSingle',
				array(
					'i18n' => array(
						'prev'  => esc_html__( 'Previous', 'fs-product-catalog' ),
						'next'  => esc_html__( 'Next', 'fs-product-catalog' ),
						'close' => esc_html__( 'Close', 'fs-product-catalog' ),
					),
				)
			);
		}

		// Archive/taxonomy pages.
		if ( self::is_product_archive() ) {
			wp_enqueue_style(
				'fs-product-catalog-archive',
				FS_PRODUCT_CATALOG_PLUGIN_URL . 'assets/css/frontend-archive.css',
				array( 'fs-product-catalog-common' ),
				FS_PRODUCT_CATALOG_VERSION
			);

			wp_enqueue_script(
				'fs-product-catalog-archive',
				FS_PRODUCT_CATALOG_PLUGIN_URL . 'assets/js/frontend-archive.js',
				array(),
				FS_PRODUCT_CATALOG_VERSION,
				true
			);

			// Localize script for AJAX.
			wp_localize_script(
				'fs-product-catalog-archive',
				'fsProductCatalog',
				array(
					'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
					'nonce'      => wp_create_nonce( 'fs_product_filter_nonce' ),
					'perPage'    => self::get_products_per_page(),
					'i18n'       => array(
						'loading'      => esc_html__( 'Loading...', 'fs-product-catalog' ),
						'loadMore'     => esc_html__( 'Load More Products', 'fs-product-catalog' ),
						'noMore'       => esc_html__( 'No more products to load', 'fs-product-catalog' ),
						'noResults'    => esc_html__( 'No products found', 'fs-product-catalog' ),
						'clearFilters' => esc_html__( 'Clear All Filters', 'fs-product-catalog' ),
						'filterBy'     => esc_html__( 'Filter By', 'fs-product-catalog' ),
						'showing'      => esc_html__( 'Showing', 'fs-product-catalog' ),
						'of'           => esc_html__( 'of', 'fs-product-catalog' ),
						'products'     => esc_html__( 'products', 'fs-product-catalog' ),
					),
				)
			);
		}
	}

	/**
	 * Check if current page is a product page
	 *
	 * @return bool
	 */
	public static function is_product_page() {
		return is_singular( 'fs-products' ) || self::is_product_archive();
	}

	/**
	 * Check if current page is a product archive
	 *
	 * @return bool
	 */
	public static function is_product_archive() {
		return is_post_type_archive( 'fs-products' ) || is_tax(
			array(
				'fs-product-category',
				'fs-product-brand',
				'fs-product-type',
				'fs-product-tag',
			)
		);
	}

	/**
	 * Add body classes
	 *
	 * @param array $classes Existing body classes.
	 * @return array
	 */
	public static function body_classes( $classes ) {
		if ( is_singular( 'fs-products' ) ) {
			$classes[] = 'fs-product-single';
		}

		if ( self::is_product_archive() ) {
			$classes[] = 'fs-product-archive';
		}

		return $classes;
	}

	/**
	 * Modify main query
	 *
	 * @param WP_Query $query The WordPress query object.
	 */
	public static function modify_main_query( $query ) {
		if ( is_admin() || ! $query->is_main_query() ) {
			return;
		}

		// Set posts per page for product archives.
		if ( self::is_product_archive() ) {
			$posts_per_page = self::get_products_per_page();
			$query->set( 'posts_per_page', $posts_per_page );

			// Set default orderby.
			if ( ! $query->get( 'orderby' ) ) {
				$query->set( 'orderby', 'menu_order' );
				$query->set( 'order', 'ASC' );
			}
		}
	}

	/**
	 * Get products per page
	 *
	 * @return int
	 */
	public static function get_products_per_page() {
		$per_page = apply_filters( 'fs_product_posts_per_page', 12 );
		return absint( $per_page );
	}

	/**
	 * Get archive columns
	 *
	 * @return int
	 */
	public static function get_archive_columns() {
		$columns = apply_filters( 'fs_product_archive_columns', 3 );
		return absint( $columns );
	}

	/**
	 * Check if breadcrumbs should be shown
	 *
	 * @return bool
	 */
	public static function show_breadcrumbs() {
		return apply_filters( 'fs_product_show_breadcrumbs', true );
	}

	/**
	 * Check if sidebar should be shown
	 *
	 * @return bool
	 */
	public static function show_sidebar() {
		if ( ! self::is_product_archive() ) {
			return false;
		}
		return apply_filters( 'fs_product_show_sidebar', true );
	}

	/**
	 * Get sidebar position
	 *
	 * @return string
	 */
	public static function get_sidebar_position() {
		$position = apply_filters( 'fs_product_sidebar_position', 'left' );
		return in_array( $position, array( 'left', 'right' ), true ) ? $position : 'left';
	}

	/**
	 * Check if single product sidebar should be shown
	 *
	 * @return bool
	 */
	public static function show_single_sidebar() {
		if ( ! is_singular( 'fs-products' ) ) {
			return false;
		}
		return apply_filters( 'fs_product_show_single_sidebar', true );
	}

	/**
	 * Get single product sidebar position
	 *
	 * @return string
	 */
	public static function get_single_sidebar_position() {
		$position = apply_filters( 'fs_product_single_sidebar_position', 'left' );
		return in_array( $position, array( 'left', 'right' ), true ) ? $position : 'left';
	}

	/**
	 * Check if single sidebar search should be shown
	 *
	 * @return bool
	 */
	public static function show_single_sidebar_search() {
		return apply_filters( 'fs_single_sidebar_show_search', true );
	}

	/**
	 * Check if single sidebar categories should be shown
	 *
	 * @return bool
	 */
	public static function show_single_sidebar_categories() {
		return apply_filters( 'fs_single_sidebar_show_categories', true );
	}

	/**
	 * Check if single sidebar brands should be shown
	 *
	 * @return bool
	 */
	public static function show_single_sidebar_brands() {
		return apply_filters( 'fs_single_sidebar_show_brands', true );
	}

	/**
	 * Check if single sidebar types should be shown
	 *
	 * @return bool
	 */
	public static function show_single_sidebar_types() {
		return apply_filters( 'fs_single_sidebar_show_types', true );
	}

	/**
	 * Check if single sidebar tags should be shown
	 *
	 * @return bool
	 */
	public static function show_single_sidebar_tags() {
		return apply_filters( 'fs_single_sidebar_show_tags', true );
	}

	/**
	 * Check if archive sidebar search should be shown
	 *
	 * @return bool
	 */
	public static function show_archive_sidebar_search() {
		return apply_filters( 'fs_archive_sidebar_show_search', true );
	}

	/**
	 * Check if archive sidebar categories should be shown
	 *
	 * @return bool
	 */
	public static function show_archive_sidebar_categories() {
		return apply_filters( 'fs_archive_sidebar_show_categories', true );
	}

	/**
	 * Check if archive sidebar brands should be shown
	 *
	 * @return bool
	 */
	public static function show_archive_sidebar_brands() {
		return apply_filters( 'fs_archive_sidebar_show_brands', true );
	}

	/**
	 * Check if archive sidebar types should be shown
	 *
	 * @return bool
	 */
	public static function show_archive_sidebar_types() {
		return apply_filters( 'fs_archive_sidebar_show_types', true );
	}

	/**
	 * Check if archive sidebar tags should be shown
	 *
	 * @return bool
	 */
	public static function show_archive_sidebar_tags() {
		return apply_filters( 'fs_archive_sidebar_show_tags', true );
	}

	/**
	 * Get product thumbnail size
	 *
	 * @return string|array
	 */
	public static function get_thumbnail_size() {
		return apply_filters( 'fs_product_thumbnail_size', 'large' );
	}

	/**
	 * Get gallery thumbnail size
	 *
	 * @return string|array
	 */
	public static function get_gallery_thumbnail_size() {
		return apply_filters( 'fs_product_gallery_thumbnail_size', 'thumbnail' );
	}
}
