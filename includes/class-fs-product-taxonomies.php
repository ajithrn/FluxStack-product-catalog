<?php
/**
 * Product Taxonomies
 *
 * @package FS_Product_Catalog
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class FS_Product_Taxonomies
 *
 * Handles the registration and management of product taxonomies.
 */
class FS_Product_Taxonomies {
	/**
	 * Initialize the class
	 */
	public static function init() {
		// Register taxonomies.
		add_action( 'init', array( __CLASS__, 'register_taxonomies' ) );

		// Add image fields to taxonomies.
		add_action( 'acf/init', array( __CLASS__, 'register_taxonomy_image_fields' ) );

		// Admin columns for taxonomies with images.
		add_filter( 'manage_edit-fs-product-category_columns', array( __CLASS__, 'add_taxonomy_columns' ) );
		add_filter( 'manage_edit-fs-product-brand_columns', array( __CLASS__, 'add_taxonomy_columns' ) );
		add_filter( 'manage_edit-fs-product-type_columns', array( __CLASS__, 'add_taxonomy_columns' ) );

		add_filter( 'manage_fs-product-category_custom_column', array( __CLASS__, 'render_taxonomy_columns' ), 10, 3 );
		add_filter( 'manage_fs-product-brand_custom_column', array( __CLASS__, 'render_taxonomy_columns' ), 10, 3 );
		add_filter( 'manage_fs-product-type_custom_column', array( __CLASS__, 'render_taxonomy_columns' ), 10, 3 );
	}

	/**
	 * Register product taxonomies
	 */
	public static function register_taxonomies() {
		// Register Product Categories.
		self::register_product_category();

		// Register Product Brands.
		self::register_product_brand();

		// Register Product Tags.
		self::register_product_tag();

		// Register Product Types.
		self::register_product_type();
	}

	/**
	 * Register Product Category taxonomy
	 */
	private static function register_product_category() {
		$labels = array(
			'name'                       => _x( 'Product Categories', 'taxonomy general name', 'fs-product-catalog' ),
			'singular_name'              => _x( 'Product Category', 'taxonomy singular name', 'fs-product-catalog' ),
			'search_items'               => __( 'Search Product Categories', 'fs-product-catalog' ),
			'popular_items'              => __( 'Popular Product Categories', 'fs-product-catalog' ),
			'all_items'                  => __( 'All Product Categories', 'fs-product-catalog' ),
			'parent_item'                => __( 'Parent Product Category', 'fs-product-catalog' ),
			'parent_item_colon'          => __( 'Parent Product Category:', 'fs-product-catalog' ),
			'edit_item'                  => __( 'Edit Product Category', 'fs-product-catalog' ),
			'update_item'                => __( 'Update Product Category', 'fs-product-catalog' ),
			'add_new_item'               => __( 'Add New Product Category', 'fs-product-catalog' ),
			'new_item_name'              => __( 'New Product Category Name', 'fs-product-catalog' ),
			'separate_items_with_commas' => __( 'Separate product categories with commas', 'fs-product-catalog' ),
			'add_or_remove_items'        => __( 'Add or remove product categories', 'fs-product-catalog' ),
			'choose_from_most_used'      => __( 'Choose from the most used product categories', 'fs-product-catalog' ),
			'not_found'                  => __( 'No product categories found.', 'fs-product-catalog' ),
			'menu_name'                  => __( 'Categories', 'fs-product-catalog' ),
		);

		$args = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'product-category' ),
			'show_in_rest'      => true,
		);

		register_taxonomy( 'fs-product-category', array( 'fs-products' ), $args );
	}

	/**
	 * Register Product Brand taxonomy
	 */
	private static function register_product_brand() {
		$labels = array(
			'name'                       => _x( 'Product Brands', 'taxonomy general name', 'fs-product-catalog' ),
			'singular_name'              => _x( 'Product Brand', 'taxonomy singular name', 'fs-product-catalog' ),
			'search_items'               => __( 'Search Product Brands', 'fs-product-catalog' ),
			'popular_items'              => __( 'Popular Product Brands', 'fs-product-catalog' ),
			'all_items'                  => __( 'All Product Brands', 'fs-product-catalog' ),
			'parent_item'                => null,
			'parent_item_colon'          => null,
			'edit_item'                  => __( 'Edit Product Brand', 'fs-product-catalog' ),
			'update_item'                => __( 'Update Product Brand', 'fs-product-catalog' ),
			'add_new_item'               => __( 'Add New Product Brand', 'fs-product-catalog' ),
			'new_item_name'              => __( 'New Product Brand Name', 'fs-product-catalog' ),
			'separate_items_with_commas' => __( 'Separate product brands with commas', 'fs-product-catalog' ),
			'add_or_remove_items'        => __( 'Add or remove product brands', 'fs-product-catalog' ),
			'choose_from_most_used'      => __( 'Choose from the most used product brands', 'fs-product-catalog' ),
			'not_found'                  => __( 'No product brands found.', 'fs-product-catalog' ),
			'menu_name'                  => __( 'Brands', 'fs-product-catalog' ),
		);

		$args = array(
			'hierarchical'      => false,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'product-brand' ),
			'show_in_rest'      => true,
		);

		register_taxonomy( 'fs-product-brand', array( 'fs-products' ), $args );
	}

	/**
	 * Register Product Tag taxonomy
	 */
	private static function register_product_tag() {
		$labels = array(
			'name'                       => _x( 'Product Tags', 'taxonomy general name', 'fs-product-catalog' ),
			'singular_name'              => _x( 'Product Tag', 'taxonomy singular name', 'fs-product-catalog' ),
			'search_items'               => __( 'Search Product Tags', 'fs-product-catalog' ),
			'popular_items'              => __( 'Popular Product Tags', 'fs-product-catalog' ),
			'all_items'                  => __( 'All Product Tags', 'fs-product-catalog' ),
			'parent_item'                => null,
			'parent_item_colon'          => null,
			'edit_item'                  => __( 'Edit Product Tag', 'fs-product-catalog' ),
			'update_item'                => __( 'Update Product Tag', 'fs-product-catalog' ),
			'add_new_item'               => __( 'Add New Product Tag', 'fs-product-catalog' ),
			'new_item_name'              => __( 'New Product Tag Name', 'fs-product-catalog' ),
			'separate_items_with_commas' => __( 'Separate product tags with commas', 'fs-product-catalog' ),
			'add_or_remove_items'        => __( 'Add or remove product tags', 'fs-product-catalog' ),
			'choose_from_most_used'      => __( 'Choose from the most used product tags', 'fs-product-catalog' ),
			'not_found'                  => __( 'No product tags found.', 'fs-product-catalog' ),
			'menu_name'                  => __( 'Tags', 'fs-product-catalog' ),
		);

		$args = array(
			'hierarchical'      => false,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'product-tag' ),
			'show_in_rest'      => true,
		);

		register_taxonomy( 'fs-product-tag', array( 'fs-products' ), $args );
	}

	/**
	 * Register Product Type taxonomy
	 */
	private static function register_product_type() {
		$labels = array(
			'name'                       => _x( 'Product Types', 'taxonomy general name', 'fs-product-catalog' ),
			'singular_name'              => _x( 'Product Type', 'taxonomy singular name', 'fs-product-catalog' ),
			'search_items'               => __( 'Search Product Types', 'fs-product-catalog' ),
			'popular_items'              => __( 'Popular Product Types', 'fs-product-catalog' ),
			'all_items'                  => __( 'All Product Types', 'fs-product-catalog' ),
			'parent_item'                => __( 'Parent Product Type', 'fs-product-catalog' ),
			'parent_item_colon'          => __( 'Parent Product Type:', 'fs-product-catalog' ),
			'edit_item'                  => __( 'Edit Product Type', 'fs-product-catalog' ),
			'update_item'                => __( 'Update Product Type', 'fs-product-catalog' ),
			'add_new_item'               => __( 'Add New Product Type', 'fs-product-catalog' ),
			'new_item_name'              => __( 'New Product Type Name', 'fs-product-catalog' ),
			'separate_items_with_commas' => __( 'Separate product types with commas', 'fs-product-catalog' ),
			'add_or_remove_items'        => __( 'Add or remove product types', 'fs-product-catalog' ),
			'choose_from_most_used'      => __( 'Choose from the most used product types', 'fs-product-catalog' ),
			'not_found'                  => __( 'No product types found.', 'fs-product-catalog' ),
			'menu_name'                  => __( 'Types', 'fs-product-catalog' ),
		);

		$args = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'product-type' ),
			'show_in_rest'      => true,
		);

		register_taxonomy( 'fs-product-type', array( 'fs-products' ), $args );
	}

	/**
	 * Register taxonomy image fields using ACF
	 */
	public static function register_taxonomy_image_fields() {
		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}

		// Category Image Field.
		acf_add_local_field_group(
			array(
				'key'                   => 'group_fs_product_category_image',
				'title'                 => __( 'Category Image', 'fs-product-catalog' ),
				'fields'                => array(
					array(
						'key'           => 'field_fs_product_category_image',
						'label'         => __( 'Category Image', 'fs-product-catalog' ),
						'name'          => 'category_image',
						'type'          => 'image',
						'instructions'  => __( 'Upload an image for this category', 'fs-product-catalog' ),
						'required'      => 0,
						'return_format' => 'array',
						'preview_size'  => 'thumbnail',
						'library'       => 'all',
					),
				),
				'location'              => array(
					array(
						array(
							'param'    => 'taxonomy',
							'operator' => '==',
							'value'    => 'fs-product-category',
						),
					),
				),
				'menu_order'            => 0,
				'position'              => 'normal',
				'style'                 => 'default',
				'label_placement'       => 'top',
				'instruction_placement' => 'label',
			)
		);

		// Brand Image Field.
		acf_add_local_field_group(
			array(
				'key'                   => 'group_fs_product_brand_image',
				'title'                 => __( 'Brand Image', 'fs-product-catalog' ),
				'fields'                => array(
					array(
						'key'           => 'field_fs_product_brand_image',
						'label'         => __( 'Brand Image', 'fs-product-catalog' ),
						'name'          => 'brand_image',
						'type'          => 'image',
						'instructions'  => __( 'Upload an image for this brand', 'fs-product-catalog' ),
						'required'      => 0,
						'return_format' => 'array',
						'preview_size'  => 'thumbnail',
						'library'       => 'all',
					),
				),
				'location'              => array(
					array(
						array(
							'param'    => 'taxonomy',
							'operator' => '==',
							'value'    => 'fs-product-brand',
						),
					),
				),
				'menu_order'            => 0,
				'position'              => 'normal',
				'style'                 => 'default',
				'label_placement'       => 'top',
				'instruction_placement' => 'label',
			)
		);

		// Type Image Field.
		acf_add_local_field_group(
			array(
				'key'                   => 'group_fs_product_type_image',
				'title'                 => __( 'Type Image', 'fs-product-catalog' ),
				'fields'                => array(
					array(
						'key'           => 'field_fs_product_type_image',
						'label'         => __( 'Type Image', 'fs-product-catalog' ),
						'name'          => 'type_image',
						'type'          => 'image',
						'instructions'  => __( 'Upload an image for this type', 'fs-product-catalog' ),
						'required'      => 0,
						'return_format' => 'array',
						'preview_size'  => 'thumbnail',
						'library'       => 'all',
					),
				),
				'location'              => array(
					array(
						array(
							'param'    => 'taxonomy',
							'operator' => '==',
							'value'    => 'fs-product-type',
						),
					),
				),
				'menu_order'            => 0,
				'position'              => 'normal',
				'style'                 => 'default',
				'label_placement'       => 'top',
				'instruction_placement' => 'label',
			)
		);
	}

	/**
	 * Add image column to taxonomy admin
	 *
	 * @param array $columns Existing columns.
	 * @return array
	 */
	public static function add_taxonomy_columns( $columns ) {
		$new_columns = array();

		// Add checkbox.
		if ( isset( $columns['cb'] ) ) {
			$new_columns['cb'] = $columns['cb'];
		}

		// Add image column.
		$new_columns['image'] = __( 'Image', 'fs-product-catalog' );

		// Add remaining columns.
		foreach ( $columns as $key => $value ) {
			if ( 'cb' !== $key ) {
				$new_columns[ $key ] = $value;
			}
		}

		return $new_columns;
	}

	/**
	 * Render taxonomy image column
	 *
	 * @param string $content Column content.
	 * @param string $column_name Column name.
	 * @param int    $term_id Term ID.
	 * @return string
	 */
	public static function render_taxonomy_columns( $content, $column_name, $term_id ) {
		if ( 'image' !== $column_name ) {
			return $content;
		}

		$term     = get_term( $term_id );
		$taxonomy = $term->taxonomy;

		// Determine the field name based on taxonomy.
		$field_map = array(
			'fs-product-category' => 'category_image',
			'fs-product-brand'    => 'brand_image',
			'fs-product-type'     => 'type_image',
		);

		if ( ! isset( $field_map[ $taxonomy ] ) ) {
			return $content;
		}

		$image = get_field( $field_map[ $taxonomy ], $taxonomy . '_' . $term_id );

		if ( $image && is_array( $image ) && isset( $image['sizes']['thumbnail'] ) ) {
			return '<img src="' . esc_url( $image['sizes']['thumbnail'] ) . '" alt="' . esc_attr( $term->name ) . '" style="width:50px;height:50px;object-fit:cover;border-radius:3px;" />';
		}

		return '<div style="width:50px;height:50px;background:#f0f0f0;display:flex;align-items:center;justify-content:center;border-radius:3px;"><span class="dashicons dashicons-format-image" style="color:#ccc;"></span></div>';
	}

	/**
	 * Get terms with images
	 *
	 * @param string $taxonomy Taxonomy name.
	 * @param array  $args Query arguments.
	 * @return array
	 */
	public static function get_terms_with_images( $taxonomy, $args = array() ) {
		$defaults = array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => false,
		);

		$args  = wp_parse_args( $args, $defaults );
		$terms = get_terms( $args );

		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			return array();
		}

		$field_map = array(
			'fs-product-category' => 'category_image',
			'fs-product-brand'    => 'brand_image',
			'fs-product-type'     => 'type_image',
		);

		$field_name = isset( $field_map[ $taxonomy ] ) ? $field_map[ $taxonomy ] : '';

		foreach ( $terms as $term ) {
			if ( $field_name ) {
				$term->image = get_field( $field_name, $taxonomy . '_' . $term->term_id );
			}
		}

		return $terms;
	}
}
