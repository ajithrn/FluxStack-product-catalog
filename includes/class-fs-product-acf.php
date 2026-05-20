<?php
/**
 * Product ACF Fields
 *
 * @package FS_Product_Catalog
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class FS_Product_ACF
 *
 * Handles ACF field registration and management for products.
 */
class FS_Product_ACF {
	/**
	 * Initialize the class
	 */
	public static function init() {
		// Register ACF fields.
		add_action( 'acf/init', array( __CLASS__, 'register_acf_fields' ) );

		// Set ACF JSON save/load points.
		add_filter( 'acf/settings/save_json', array( __CLASS__, 'set_acf_json_save_point' ) );
		add_filter( 'acf/settings/load_json', array( __CLASS__, 'set_acf_json_load_point' ) );

		// Hide unnecessary metaboxes.
		add_action( 'add_meta_boxes', array( __CLASS__, 'remove_metaboxes' ), 999 );
	}

	/**
	 * Register ACF fields
	 */
	public static function register_acf_fields() {
		// Fields are loaded from ACF JSON automatically.
		// No need for hardcoded registration as JSON is easier to manage.
	}

	/**
	 * Set ACF JSON save point
	 *
	 * @param string $path Default path.
	 * @return string
	 */
	public static function set_acf_json_save_point( $path ) {
		// Check if we're saving a product field group.
		if ( isset( $_POST['acf_field_group']['key'] ) && strpos( $_POST['acf_field_group']['key'], 'group_fs_product' ) === 0 ) {
			return FS_PRODUCT_CATALOG_PLUGIN_DIR . 'acf-json';
		}
		return $path;
	}

	/**
	 * Set ACF JSON load point
	 *
	 * @param array $paths Existing paths.
	 * @return array
	 */
	public static function set_acf_json_load_point( $paths ) {
		// Add plugin's acf-json directory to load paths.
		$paths[] = FS_PRODUCT_CATALOG_PLUGIN_DIR . 'acf-json';
		return $paths;
	}

	/**
	 * Remove unnecessary metaboxes
	 */
	public static function remove_metaboxes() {
		// We're using the default editor, so no need to remove it.
	}

	/**
	 * Auto-populate product name field with post title
	 *
	 * @param mixed  $value Field value.
	 * @param int    $post_id Post ID.
	 * @param array  $field Field array.
	 * @return mixed
	 */
	public static function populate_product_name( $value, $post_id, $field ) {
		if ( empty( $value ) ) {
			$value = get_the_title( $post_id );
		}
		return $value;
	}

	/**
	 * Get product info
	 *
	 * @param int $post_id Post ID.
	 * @return array
	 */
	public static function get_product_info( $post_id = null ) {
		if ( null === $post_id ) {
			$post_id = get_the_ID();
		}

		return array(
			'name'            => get_the_title( $post_id ),
			'description'     => get_field( 'product_description', $post_id ),
			'info'            => get_field( 'product_info', $post_id ),
			'specifications'  => get_field( 'product_specifications', $post_id ),
			'thumbnail'       => get_the_post_thumbnail_url( $post_id, 'full' ),
			'categories'      => get_the_terms( $post_id, 'fs-product-category' ),
			'brands'          => get_the_terms( $post_id, 'fs-product-brand' ),
			'tags'            => get_the_terms( $post_id, 'fs-product-tag' ),
			'types'           => get_the_terms( $post_id, 'fs-product-type' ),
		);
	}

	/**
	 * Get product specifications formatted for tabs
	 *
	 * @param int $post_id Post ID.
	 * @return array
	 */
	public static function get_product_specifications_tabs( $post_id = null ) {
		if ( null === $post_id ) {
			$post_id = get_the_ID();
		}

		$specifications = get_field( 'product_specifications', $post_id );

		if ( empty( $specifications ) ) {
			return array();
		}

		$tabs = array();
		foreach ( $specifications as $index => $spec ) {
			$tabs[] = array(
				'id'      => 'spec-tab-' . ( $index + 1 ),
				'title'   => $spec['tab_title'],
				'content' => $spec['content'],
			);
		}

		return $tabs;
	}
}
