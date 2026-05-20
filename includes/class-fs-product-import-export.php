<?php
/**
 * Import/Export
 *
 * Handles CSV import and export of products.
 *
 * @package FS_Product_Catalog
 */

namespace FSProductCatalog;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ImportExport
 *
 * Provides CSV import/export functionality for products.
 */
class ImportExport {

	/**
	 * CSV columns for export/import.
	 */
	const CSV_COLUMNS = array(
		'id',
		'title',
		'content',
		'excerpt',
		'status',
		'menu_order',
		'featured_image',
		'categories',
		'brands',
		'types',
		'tags',
	);

	/**
	 * Initialize the class.
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_admin_page' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
		add_action( 'wp_ajax_fs_product_export_csv', array( __CLASS__, 'ajax_export' ) );
		add_action( 'wp_ajax_fs_product_import_csv', array( __CLASS__, 'ajax_import' ) );
	}

	/**
	 * Add submenu page under Products.
	 */
	public static function add_admin_page() {
		add_submenu_page(
			'edit.php?post_type=fs-products',
			__( 'Import / Export', 'fs-product-catalog' ),
			__( 'Import / Export', 'fs-product-catalog' ),
			'manage_options',
			'fs-product-import-export',
			array( __CLASS__, 'render_page' )
		);
	}

	/**
	 * Enqueue assets for the import/export page.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public static function enqueue_assets( $hook ) {
		if ( 'fs-products_page_fs-product-import-export' !== $hook ) {
			return;
		}

		wp_enqueue_style(
			'fs-product-settings',
			FS_PRODUCT_CATALOG_PLUGIN_URL . 'assets/css/admin-settings.css',
			array(),
			FS_PRODUCT_CATALOG_VERSION
		);
	}

	/**
	 * Render the import/export page.
	 */
	public static function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		include FS_PRODUCT_CATALOG_PLUGIN_DIR . 'templates/admin/import-export-page.php';
	}

	/**
	 * AJAX handler: Export products to CSV.
	 */
	public static function ajax_export() {
		check_ajax_referer( 'fs_product_import_export_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Unauthorized' );
		}

		// Get filter params.
		$category = isset( $_POST['category'] ) ? sanitize_text_field( wp_unslash( $_POST['category'] ) ) : '';
		$brand    = isset( $_POST['brand'] ) ? sanitize_text_field( wp_unslash( $_POST['brand'] ) ) : '';
		$type     = isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : '';

		$args = array(
			'post_type'      => 'fs-products',
			'post_status'    => array( 'publish', 'draft', 'pending' ),
			'posts_per_page' => -1,
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
		);

		// Apply taxonomy filters.
		$tax_query = array();
		if ( ! empty( $category ) ) {
			$tax_query[] = array(
				'taxonomy' => 'fs-product-category',
				'field'    => 'slug',
				'terms'    => $category,
			);
		}
		if ( ! empty( $brand ) ) {
			$tax_query[] = array(
				'taxonomy' => 'fs-product-brand',
				'field'    => 'slug',
				'terms'    => $brand,
			);
		}
		if ( ! empty( $type ) ) {
			$tax_query[] = array(
				'taxonomy' => 'fs-product-type',
				'field'    => 'slug',
				'terms'    => $type,
			);
		}
		if ( ! empty( $tax_query ) ) {
			if ( count( $tax_query ) > 1 ) {
				$tax_query['relation'] = 'AND';
			}
			$args['tax_query'] = $tax_query;
		}

		$query = new \WP_Query( $args );

		// Build CSV.
		$output = fopen( 'php://output', 'w' );

		// Headers.
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="products-export-' . gmdate( 'Y-m-d' ) . '.csv"' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		// Column headers.
		fputcsv( $output, self::CSV_COLUMNS );

		// Rows.
		while ( $query->have_posts() ) {
			$query->the_post();
			$post_id = get_the_ID();

			$row = array(
				$post_id,
				get_the_title(),
				get_post_field( 'post_content', $post_id ),
				get_the_excerpt(),
				get_post_status(),
				get_post_field( 'menu_order', $post_id ),
				wp_get_attachment_url( get_post_thumbnail_id( $post_id ) ) ?: '',
				self::get_term_slugs( $post_id, 'fs-product-category' ),
				self::get_term_slugs( $post_id, 'fs-product-brand' ),
				self::get_term_slugs( $post_id, 'fs-product-type' ),
				self::get_term_slugs( $post_id, 'fs-product-tag' ),
			);

			fputcsv( $output, $row );
		}

		wp_reset_postdata();
		fclose( $output );
		exit;
	}

	/**
	 * AJAX handler: Import products from CSV.
	 */
	public static function ajax_import() {
		check_ajax_referer( 'fs_product_import_export_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Unauthorized' ) );
		}

		if ( empty( $_FILES['csv_file'] ) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK ) {
			wp_send_json_error( array( 'message' => __( 'No file uploaded or upload error.', 'fs-product-catalog' ) ) );
		}

		$file = $_FILES['csv_file']['tmp_name'];
		$mime = mime_content_type( $file );

		if ( ! in_array( $mime, array( 'text/csv', 'text/plain', 'application/csv', 'application/vnd.ms-excel' ), true ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid file type. Please upload a CSV file.', 'fs-product-catalog' ) ) );
		}

		$handle = fopen( $file, 'r' );
		if ( ! $handle ) {
			wp_send_json_error( array( 'message' => __( 'Could not read file.', 'fs-product-catalog' ) ) );
		}

		// Read header row.
		$headers = fgetcsv( $handle );
		if ( ! $headers || ! in_array( 'title', $headers, true ) ) {
			fclose( $handle );
			wp_send_json_error( array( 'message' => __( 'Invalid CSV format. Missing "title" column.', 'fs-product-catalog' ) ) );
		}

		$results = array(
			'created' => 0,
			'updated' => 0,
			'skipped' => 0,
			'errors'  => array(),
		);

		$row_num = 1;
		while ( ( $row = fgetcsv( $handle ) ) !== false ) {
			$row_num++;

			// Map columns to values.
			$data = array();
			foreach ( $headers as $index => $col ) {
				$data[ $col ] = isset( $row[ $index ] ) ? $row[ $index ] : '';
			}

			// Title is required.
			if ( empty( $data['title'] ) ) {
				$results['skipped']++;
				continue;
			}

			$result = self::import_single_product( $data );

			if ( is_wp_error( $result ) ) {
				$results['errors'][] = sprintf(
					/* translators: 1: row number, 2: error message */
					__( 'Row %1$d: %2$s', 'fs-product-catalog' ),
					$row_num,
					$result->get_error_message()
				);
			} elseif ( 'created' === $result ) {
				$results['created']++;
			} elseif ( 'updated' === $result ) {
				$results['updated']++;
			}
		}

		fclose( $handle );

		wp_send_json_success( $results );
	}

	/**
	 * Import a single product from CSV row data.
	 *
	 * @param array $data Associative array of column => value.
	 * @return string|WP_Error 'created', 'updated', or WP_Error.
	 */
	private static function import_single_product( $data ) {
		$post_id = 0;
		$action  = 'created';

		// Check if updating existing product by ID.
		if ( ! empty( $data['id'] ) ) {
			$existing = get_post( absint( $data['id'] ) );
			if ( $existing && 'fs-products' === $existing->post_type ) {
				$post_id = $existing->ID;
				$action  = 'updated';
			}
		}

		// Prepare post data.
		$post_data = array(
			'post_type'   => 'fs-products',
			'post_title'  => sanitize_text_field( $data['title'] ),
			'post_status' => ! empty( $data['status'] ) && in_array( $data['status'], array( 'publish', 'draft', 'pending' ), true )
				? $data['status'] : 'draft',
		);

		if ( ! empty( $data['content'] ) ) {
			$post_data['post_content'] = wp_kses_post( $data['content'] );
		}

		if ( ! empty( $data['excerpt'] ) ) {
			$post_data['post_excerpt'] = sanitize_textarea_field( $data['excerpt'] );
		}

		if ( isset( $data['menu_order'] ) && '' !== $data['menu_order'] ) {
			$post_data['menu_order'] = absint( $data['menu_order'] );
		}

		// Insert or update.
		if ( $post_id > 0 ) {
			$post_data['ID'] = $post_id;
			$result = wp_update_post( $post_data, true );
		} else {
			$result = wp_insert_post( $post_data, true );
		}

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		$post_id = (int) $result;

		// Assign taxonomies.
		$tax_map = array(
			'categories' => 'fs-product-category',
			'brands'     => 'fs-product-brand',
			'types'      => 'fs-product-type',
			'tags'       => 'fs-product-tag',
		);

		foreach ( $tax_map as $col => $taxonomy ) {
			if ( ! empty( $data[ $col ] ) ) {
				$slugs = array_map( 'trim', explode( '|', $data[ $col ] ) );
				$term_ids = array();

				foreach ( $slugs as $slug ) {
					$term = get_term_by( 'slug', $slug, $taxonomy );
					if ( ! $term ) {
						// Create term if it doesn't exist.
						$new_term = wp_insert_term( $slug, $taxonomy, array( 'slug' => sanitize_title( $slug ) ) );
						if ( ! is_wp_error( $new_term ) ) {
							$term_ids[] = $new_term['term_id'];
						}
					} else {
						$term_ids[] = $term->term_id;
					}
				}

				if ( ! empty( $term_ids ) ) {
					wp_set_object_terms( $post_id, $term_ids, $taxonomy );
				}
			}
		}

		return $action;
	}

	/**
	 * Get pipe-separated term slugs for a product.
	 *
	 * @param int    $post_id  Post ID.
	 * @param string $taxonomy Taxonomy name.
	 * @return string
	 */
	private static function get_term_slugs( $post_id, $taxonomy ) {
		$terms = get_the_terms( $post_id, $taxonomy );
		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			return '';
		}

		$slugs = wp_list_pluck( $terms, 'slug' );
		return implode( '|', $slugs );
	}
}
