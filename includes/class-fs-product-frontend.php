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

		// Flush term caches when terms change.
		add_action( 'created_term', array( __CLASS__, 'flush_term_cache' ), 10, 3 );
		add_action( 'edited_term', array( __CLASS__, 'flush_term_cache' ), 10, 3 );
		add_action( 'delete_term', array( __CLASS__, 'flush_term_cache' ), 10, 3 );

		// Schema.org structured data.
		add_action( 'wp_head', array( __CLASS__, 'output_product_schema' ) );

		// Product search results template.
		add_filter( 'template_include', array( __CLASS__, 'search_results_template' ), 99 );
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
					'ajaxUrl'        => admin_url( 'admin-ajax.php' ),
					'nonce'          => wp_create_nonce( 'fs_product_filter_nonce' ),
					'perPage'        => self::get_products_per_page(),
					'paginationMode' => self::get_pagination_mode(),
					'i18n'           => array(
						'loading'      => esc_html__( 'Loading...', 'fs-product-catalog' ),
						'loadMore'     => esc_html( self::get_load_more_text() ),
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
		if ( is_post_type_archive( 'fs-products' ) ) {
			return true;
		}

		if ( is_tax( array( 'fs-product-category', 'fs-product-brand', 'fs-product-type', 'fs-product-tag' ) ) ) {
			return true;
		}

		// Product search results.
		if ( is_search() && 'fs-products' === get_query_var( 'post_type' ) ) {
			return true;
		}

		return false;
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

	/**
	 * Get cached taxonomy terms for sidebar display.
	 *
	 * Uses transients to avoid repeated DB queries on every page load.
	 * Cache is automatically busted when terms are created, edited, or deleted.
	 *
	 * @param string $taxonomy Taxonomy name.
	 * @return array Array of term objects, or empty array.
	 */
	public static function get_cached_terms( $taxonomy ) {
		$transient_key = 'fs_sidebar_terms_' . sanitize_key( $taxonomy );
		$terms         = get_transient( $transient_key );

		if ( false === $terms ) {
			$terms = get_terms(
				array(
					'taxonomy'   => $taxonomy,
					'hide_empty' => true,
				)
			);

			if ( is_wp_error( $terms ) ) {
				return array();
			}

			// Cache for 1 hour.
			set_transient( $transient_key, $terms, HOUR_IN_SECONDS );
		}

		return $terms;
	}

	/**
	 * Flush sidebar term caches when terms change.
	 *
	 * @param int    $term_id Term ID.
	 * @param int    $tt_id Term taxonomy ID.
	 * @param string $taxonomy Taxonomy slug.
	 */
	public static function flush_term_cache( $term_id, $tt_id, $taxonomy ) {
		$product_taxonomies = array(
			'fs-product-category',
			'fs-product-brand',
			'fs-product-type',
			'fs-product-tag',
		);

		if ( in_array( $taxonomy, $product_taxonomies, true ) ) {
			delete_transient( 'fs_sidebar_terms_' . sanitize_key( $taxonomy ) );
		}
	}

	/**
	 * Output Schema.org Product JSON-LD on single product pages.
	 */
	public static function output_product_schema() {
		if ( ! is_singular( 'fs-products' ) ) {
			return;
		}

		$product_id  = get_the_ID();
		$schema      = array(
			'@context'    => 'https://schema.org',
			'@type'       => 'Product',
			'name'        => get_the_title( $product_id ),
			'description' => wp_strip_all_tags( get_the_excerpt( $product_id ) ),
			'url'         => get_permalink( $product_id ),
		);

		// Image.
		$thumbnail_id = get_post_thumbnail_id( $product_id );
		if ( $thumbnail_id ) {
			$image_url = wp_get_attachment_image_url( $thumbnail_id, 'full' );
			if ( $image_url ) {
				$schema['image'] = $image_url;
			}
		}

		// Brand.
		$brands = get_the_terms( $product_id, 'fs-product-brand' );
		if ( ! empty( $brands ) && ! is_wp_error( $brands ) ) {
			$schema['brand'] = array(
				'@type' => 'Brand',
				'name'  => $brands[0]->name,
			);
		}

		// Category.
		$categories = get_the_terms( $product_id, 'fs-product-category' );
		if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) {
			$schema['category'] = $categories[0]->name;
		}

		/**
		 * Filter the product schema data.
		 *
		 * @param array $schema     Schema.org data array.
		 * @param int   $product_id Product post ID.
		 */
		$schema = apply_filters( 'fs_product_schema', $schema, $product_id );

		echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
	}

	/**
	 * Override search template when searching for products.
	 *
	 * @param string $template Current template path.
	 * @return string
	 */
	public static function search_results_template( $template ) {
		if ( ! is_search() ) {
			return $template;
		}

		$post_type = get_query_var( 'post_type' );
		if ( 'fs-products' !== $post_type ) {
			return $template;
		}

		$search_template = FS_Product_Template_Loader::locate_template( 'search-products.php' );
		if ( $search_template ) {
			return $search_template;
		}

		return $template;
	}

	/**
	 * Get the default sort order setting.
	 *
	 * @return string
	 */
	public static function get_default_orderby() {
		return apply_filters( 'fs_product_default_orderby', 'menu_order' );
	}

	/**
	 * Check if sorting dropdown should be shown.
	 *
	 * @return bool
	 */
	public static function show_sorting() {
		return apply_filters( 'fs_product_show_sorting', true );
	}

	/**
	 * Get the pagination mode.
	 *
	 * @return string One of: load-more, pagination, infinite-scroll.
	 */
	public static function get_pagination_mode() {
		return apply_filters( 'fs_product_pagination_mode', 'load-more' );
	}

	/**
	 * Get custom load more button text.
	 *
	 * @return string
	 */
	public static function get_load_more_text() {
		$text = FS_Product_Settings::get( 'load_more_text', '' );
		if ( empty( $text ) ) {
			$text = __( 'Load More Products', 'fs-product-catalog' );
		}
		return apply_filters( 'fs_product_load_more_text', $text );
	}
}
