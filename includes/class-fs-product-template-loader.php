<?php
/**
 * Template Loader
 *
 * Handles loading of frontend templates with theme override support.
 *
 * @package FS_Product_Catalog
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class FS_Product_Template_Loader
 *
 * Loads templates from plugin or theme directory.
 */
class FS_Product_Template_Loader {
	/**
	 * Initialize the class
	 */
	public static function init() {
		add_filter( 'template_include', array( __CLASS__, 'template_loader' ) );
	}

	/**
	 * Load template
	 *
	 * @param string $template Template path.
	 * @return string
	 */
	public static function template_loader( $template ) {
		if ( is_singular( 'fs-products' ) ) {
			$template = self::locate_template( 'single-product.php' );
		} elseif ( is_post_type_archive( 'fs-products' ) ) {
			$template = self::locate_template( 'archive-product.php' );
		} elseif ( is_tax( 'fs-product-category' ) ) {
			$template = self::locate_template( 'taxonomy-category.php' );
		} elseif ( is_tax( 'fs-product-brand' ) ) {
			$template = self::locate_template( 'taxonomy-brand.php' );
		} elseif ( is_tax( 'fs-product-type' ) ) {
			$template = self::locate_template( 'taxonomy-type.php' );
		} elseif ( is_tax( 'fs-product-tag' ) ) {
			$template = self::locate_template( 'taxonomy-tag.php' );
		}

		return $template;
	}

	/**
	 * Locate template
	 *
	 * Checks theme directory first, then plugin directory.
	 *
	 * @param string $template_name Template name.
	 * @return string
	 */
	public static function locate_template( $template_name ) {
		// Check theme directory first.
		$theme_template = locate_template(
			array(
				'fs-product-catalog/' . $template_name,
			)
		);

		if ( $theme_template ) {
			return $theme_template;
		}

		// Fallback to plugin directory.
		$plugin_template = FS_PRODUCT_CATALOG_PLUGIN_DIR . 'templates/' . $template_name;

		if ( file_exists( $plugin_template ) ) {
			return $plugin_template;
		}

		return '';
	}

	/**
	 * Get template part
	 *
	 * @param string $slug Template slug.
	 * @param string $name Template name (optional).
	 * @param array  $args Arguments to pass to template (optional).
	 */
	public static function get_template_part( $slug, $name = '', $args = array() ) {
		$templates = array();
		$name      = (string) $name;

		if ( '' !== $name ) {
			$templates[] = "{$slug}-{$name}.php";
		}

		$templates[] = "{$slug}.php";

		// Allow filtering of template parts.
		$templates = apply_filters( 'fs_product_get_template_part', $templates, $slug, $name );

		// Extract args to make them available in template.
		if ( ! empty( $args ) && is_array( $args ) ) {
			extract( $args ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
		}

		foreach ( $templates as $template ) {
			// Check theme directory.
			$theme_template = locate_template( 'fs-product-catalog/parts/' . $template );

			if ( $theme_template ) {
				include $theme_template;
				return;
			}

			// Check plugin directory.
			$plugin_template = FS_PRODUCT_CATALOG_PLUGIN_DIR . 'templates/parts/' . $template;

			if ( file_exists( $plugin_template ) ) {
				include $plugin_template;
				return;
			}
		}
	}

	/**
	 * Get template
	 *
	 * @param string $template_name Template name.
	 * @param array  $args Arguments to pass to template (optional).
	 * @param string $template_path Template path (optional).
	 * @param string $default_path Default path (optional).
	 */
	public static function get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
		if ( ! empty( $args ) && is_array( $args ) ) {
			extract( $args ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
		}

		$located = self::locate_template( $template_name );

		if ( ! file_exists( $located ) ) {
			return;
		}

		// Allow filtering before including template.
		do_action( 'fs_product_before_template_part', $template_name, $located, $args );

		include $located;

		// Allow filtering after including template.
		do_action( 'fs_product_after_template_part', $template_name, $located, $args );
	}
}
