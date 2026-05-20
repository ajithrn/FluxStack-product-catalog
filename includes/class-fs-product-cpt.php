<?php
/**
 * Product Custom Post Type
 *
 * @package FS_Product_Catalog
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class FS_Product_CPT
 *
 * Handles the registration and management of the Products custom post type.
 */
class FS_Product_CPT {
	/**
	 * Initialize the class
	 */
	public static function init() {
		// Register post type.
		add_action( 'init', array( __CLASS__, 'register_post_type' ) );

		// Disable block editor for products.
		add_filter( 'use_block_editor_for_post_type', array( __CLASS__, 'disable_block_editor' ), 10, 2 );

		// Admin columns.
		add_filter( 'manage_fs-products_posts_columns', array( __CLASS__, 'add_admin_columns' ) );
		add_action( 'manage_fs-products_posts_custom_column', array( __CLASS__, 'render_admin_columns' ), 10, 2 );
		add_filter( 'manage_edit-fs-products_sortable_columns', array( __CLASS__, 'register_sortable_columns' ) );

		// Admin sorting.
		add_action( 'pre_get_posts', array( __CLASS__, 'sort_admin_columns' ) );

		// Admin messages.
		add_filter( 'post_updated_messages', array( __CLASS__, 'updated_messages' ) );
	}

	/**
	 * Register the products post type
	 */
	public static function register_post_type() {
		$labels = array(
			'name'                  => _x( 'Products', 'post type general name', 'fs-product-catalog' ),
			'singular_name'         => _x( 'Product', 'post type singular name', 'fs-product-catalog' ),
			'menu_name'             => _x( 'Products', 'admin menu', 'fs-product-catalog' ),
			'name_admin_bar'        => _x( 'Product', 'add new on admin bar', 'fs-product-catalog' ),
			'add_new'               => _x( 'Add New', 'product', 'fs-product-catalog' ),
			'add_new_item'          => __( 'Add New Product', 'fs-product-catalog' ),
			'new_item'              => __( 'New Product', 'fs-product-catalog' ),
			'edit_item'             => __( 'Edit Product', 'fs-product-catalog' ),
			'view_item'             => __( 'View Product', 'fs-product-catalog' ),
			'all_items'             => __( 'All Products', 'fs-product-catalog' ),
			'search_items'          => __( 'Search Products', 'fs-product-catalog' ),
			'parent_item_colon'     => __( 'Parent Products:', 'fs-product-catalog' ),
			'not_found'             => __( 'No products found.', 'fs-product-catalog' ),
			'not_found_in_trash'    => __( 'No products found in Trash.', 'fs-product-catalog' ),
			'featured_image'        => __( 'Product Image', 'fs-product-catalog' ),
			'set_featured_image'    => __( 'Set product image', 'fs-product-catalog' ),
			'remove_featured_image' => __( 'Remove product image', 'fs-product-catalog' ),
			'use_featured_image'    => __( 'Use as product image', 'fs-product-catalog' ),
			'archives'              => __( 'Product archives', 'fs-product-catalog' ),
			'insert_into_item'      => __( 'Insert into product', 'fs-product-catalog' ),
			'uploaded_to_this_item' => __( 'Uploaded to this product', 'fs-product-catalog' ),
			'filter_items_list'     => __( 'Filter products list', 'fs-product-catalog' ),
			'items_list_navigation' => __( 'Products list navigation', 'fs-product-catalog' ),
			'items_list'            => __( 'Products list', 'fs-product-catalog' ),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'product' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => 20,
			'menu_icon'          => 'dashicons-products',
			'supports'           => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
			'show_in_rest'       => false,
			'taxonomies'         => array( 'fs-product-category', 'fs-product-brand', 'fs-product-tag', 'fs-product-type' ),
		);

		register_post_type( 'fs-products', $args );
	}

	/**
	 * Disable block editor for products
	 *
	 * @param bool   $use_block_editor Whether to use block editor.
	 * @param string $post_type Post type.
	 * @return bool
	 */
	public static function disable_block_editor( $use_block_editor, $post_type ) {
		if ( 'fs-products' === $post_type ) {
			return false;
		}
		return $use_block_editor;
	}

	/**
	 * Add custom admin columns
	 *
	 * @param array $columns Existing columns.
	 * @return array
	 */
	public static function add_admin_columns( $columns ) {
		$new_columns = array();

		// Add checkbox.
		if ( isset( $columns['cb'] ) ) {
			$new_columns['cb'] = $columns['cb'];
		}

		// Add thumbnail.
		$new_columns['thumbnail'] = __( 'Image', 'fs-product-catalog' );

		// Add title.
		if ( isset( $columns['title'] ) ) {
			$new_columns['title'] = $columns['title'];
		}

		// Add taxonomies.
		$new_columns['product_categories'] = __( 'Categories', 'fs-product-catalog' );
		$new_columns['product_brands']     = __( 'Brands', 'fs-product-catalog' );
		$new_columns['product_types']      = __( 'Types', 'fs-product-catalog' );
		$new_columns['product_tags']       = __( 'Tags', 'fs-product-catalog' );

		// Add date.
		if ( isset( $columns['date'] ) ) {
			$new_columns['date'] = $columns['date'];
		}

		return $new_columns;
	}

	/**
	 * Render custom admin columns
	 *
	 * @param string $column Column name.
	 * @param int    $post_id Post ID.
	 */
	public static function render_admin_columns( $column, $post_id ) {
		switch ( $column ) {
			case 'thumbnail':
				if ( has_post_thumbnail( $post_id ) ) {
					echo '<a href="' . esc_url( get_edit_post_link( $post_id ) ) . '">';
					echo get_the_post_thumbnail( $post_id, array( 60, 60 ) );
					echo '</a>';
				} else {
					echo '<div style="width:60px;height:60px;background:#f0f0f0;display:flex;align-items:center;justify-content:center;"><span class="dashicons dashicons-products"></span></div>';
				}
				break;

			case 'product_categories':
				self::render_taxonomy_column( $post_id, 'fs-product-category' );
				break;

			case 'product_brands':
				self::render_taxonomy_column( $post_id, 'fs-product-brand' );
				break;

			case 'product_types':
				self::render_taxonomy_column( $post_id, 'fs-product-type' );
				break;

			case 'product_tags':
				self::render_taxonomy_column( $post_id, 'fs-product-tag' );
				break;
		}
	}

	/**
	 * Render taxonomy column content
	 *
	 * @param int    $post_id Post ID.
	 * @param string $taxonomy Taxonomy name.
	 */
	private static function render_taxonomy_column( $post_id, $taxonomy ) {
		$terms = get_the_terms( $post_id, $taxonomy );
		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			$term_links = array();
			foreach ( $terms as $term ) {
				$term_links[] = sprintf(
					'<a href="%s">%s</a>',
					esc_url( add_query_arg( array( 'post_type' => 'fs-products', $taxonomy => $term->slug ), 'edit.php' ) ),
					esc_html( $term->name )
				);
			}
			echo implode( ', ', $term_links );
		} else {
			echo '—';
		}
	}

	/**
	 * Register sortable columns
	 *
	 * @param array $columns Existing sortable columns.
	 * @return array
	 */
	public static function register_sortable_columns( $columns ) {
		$columns['product_categories'] = 'product_categories';
		$columns['product_brands']     = 'product_brands';
		$columns['product_types']      = 'product_types';
		return $columns;
	}

	/**
	 * Handle admin column sorting
	 *
	 * @param WP_Query $query The WordPress query object.
	 */
	public static function sort_admin_columns( $query ) {
		if ( ! is_admin() || ! $query->is_main_query() || 'fs-products' !== $query->get( 'post_type' ) ) {
			return;
		}

		$orderby = $query->get( 'orderby' );

		$taxonomy_map = array(
			'product_categories' => 'fs-product-category',
			'product_brands'     => 'fs-product-brand',
			'product_types'      => 'fs-product-type',
		);

		if ( isset( $taxonomy_map[ $orderby ] ) ) {
			$query->set( 'orderby', 'name' );
		}
	}

	/**
	 * Custom post updated messages
	 *
	 * @param array $messages Existing messages.
	 * @return array
	 */
	public static function updated_messages( $messages ) {
		$post             = get_post();
		$post_type        = get_post_type( $post );
		$post_type_object = get_post_type_object( $post_type );

		$messages['fs-products'] = array(
			0  => '', // Unused. Messages start at index 1.
			1  => __( 'Product updated.', 'fs-product-catalog' ),
			2  => __( 'Custom field updated.', 'fs-product-catalog' ),
			3  => __( 'Custom field deleted.', 'fs-product-catalog' ),
			4  => __( 'Product updated.', 'fs-product-catalog' ),
			/* translators: %s: date and time of the revision */
			5  => isset( $_GET['revision'] ) ? sprintf( __( 'Product restored to revision from %s', 'fs-product-catalog' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6  => __( 'Product published.', 'fs-product-catalog' ),
			7  => __( 'Product saved.', 'fs-product-catalog' ),
			8  => __( 'Product submitted.', 'fs-product-catalog' ),
			9  => sprintf(
				/* translators: %s: scheduled date */
				__( 'Product scheduled for: <strong>%s</strong>.', 'fs-product-catalog' ),
				date_i18n( __( 'M j, Y @ G:i', 'fs-product-catalog' ), strtotime( $post->post_date ) )
			),
			10 => __( 'Product draft updated.', 'fs-product-catalog' ),
		);

		if ( $post_type_object->publicly_queryable && 'fs-products' === $post_type ) {
			$permalink = get_permalink( $post->ID );

			$view_link = sprintf( ' <a href="%s">%s</a>', esc_url( $permalink ), __( 'View product', 'fs-product-catalog' ) );
			$messages[ $post_type ][1] .= $view_link;
			$messages[ $post_type ][6] .= $view_link;
			$messages[ $post_type ][9] .= $view_link;

			$preview_permalink = add_query_arg( 'preview', 'true', $permalink );
			$preview_link      = sprintf( ' <a target="_blank" href="%s">%s</a>', esc_url( $preview_permalink ), __( 'Preview product', 'fs-product-catalog' ) );
			$messages[ $post_type ][8]  .= $preview_link;
			$messages[ $post_type ][10] .= $preview_link;
		}

		return $messages;
	}

	/**
	 * Get products
	 *
	 * @param array $args Query arguments.
	 * @return WP_Query
	 */
	public static function get_products( $args = array() ) {
		$defaults = array(
			'post_type'      => 'fs-products',
			'posts_per_page' => -1,
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
		);

		$args = wp_parse_args( $args, $defaults );
		return new WP_Query( $args );
	}

	/**
	 * Get products by taxonomy
	 *
	 * @param string       $taxonomy Taxonomy name.
	 * @param string|array $terms Term slug(s).
	 * @param int          $limit Number of items to return.
	 * @return WP_Query
	 */
	public static function get_products_by_taxonomy( $taxonomy, $terms, $limit = -1 ) {
		return self::get_products(
			array(
				'tax_query'      => array(
					array(
						'taxonomy' => $taxonomy,
						'field'    => 'slug',
						'terms'    => $terms,
					),
				),
				'posts_per_page' => $limit,
			)
		);
	}
}
