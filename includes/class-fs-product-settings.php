<?php
/**
 * Plugin Settings
 *
 * Handles the admin settings page and option management.
 *
 * @package FS_Product_Catalog
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class FS_Product_Settings
 *
 * Manages plugin settings with admin UI and AJAX save.
 */
class FS_Product_Settings {

	/**
	 * Option name in wp_options table.
	 */
	const OPTION_NAME = 'fs_product_catalog_settings';

	/**
	 * Settings defaults.
	 *
	 * @var array
	 */
	private static $defaults = array(
		// General.
		'products_per_page'    => 12,
		'archive_columns'      => 3,
		'default_orderby'      => 'menu_order',
		'show_breadcrumbs'     => true,

		// Single Product.
		'single_show_sidebar'  => true,
		'single_sidebar_position' => 'left',
		'single_sidebar_search'   => true,
		'single_sidebar_categories' => true,
		'single_sidebar_brands'    => true,
		'single_sidebar_types'     => true,
		'single_sidebar_tags'      => true,
		'sidebar_items_limit'      => 8,

		// Archive.
		'archive_show_sidebar'  => true,
		'archive_sidebar_position' => 'left',
		'archive_sidebar_search'   => true,
		'archive_sidebar_categories' => true,
		'archive_sidebar_brands'    => true,
		'archive_sidebar_types'     => true,
		'archive_sidebar_tags'      => true,

		// Product Card.
		'card_show_category'   => false,
		'card_show_excerpt'    => true,
		'card_show_more_link'  => true,
		'card_image_ratio'     => '1:1',
	);

	/**
	 * Initialize the class.
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_settings_page' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_assets' ) );
		add_action( 'wp_ajax_fs_product_save_settings', array( __CLASS__, 'ajax_save' ) );

		// Hook settings into existing filter system.
		add_action( 'init', array( __CLASS__, 'register_setting_filters' ), 5 );
	}

	/**
	 * Add settings page under Products menu.
	 */
	public static function add_settings_page() {
		add_submenu_page(
			'edit.php?post_type=fs-products',
			__( 'Catalog Settings', 'fs-product-catalog' ),
			__( 'Settings', 'fs-product-catalog' ),
			'manage_options',
			'fs-product-settings',
			array( __CLASS__, 'render_settings_page' )
		);
	}

	/**
	 * Enqueue admin assets for settings page.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public static function enqueue_admin_assets( $hook ) {
		if ( 'fs-products_page_fs-product-settings' !== $hook ) {
			return;
		}

		wp_enqueue_style(
			'fs-product-settings',
			FS_PRODUCT_CATALOG_PLUGIN_URL . 'assets/css/admin-settings.css',
			array(),
			FS_PRODUCT_CATALOG_VERSION
		);

		wp_enqueue_script(
			'fs-product-settings',
			FS_PRODUCT_CATALOG_PLUGIN_URL . 'assets/js/admin-settings.js',
			array(),
			FS_PRODUCT_CATALOG_VERSION,
			true
		);

		wp_localize_script(
			'fs-product-settings',
			'fsProductSettings',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'fs_product_settings_nonce' ),
				'i18n'    => array(
					'saving' => esc_html__( 'Saving...', 'fs-product-catalog' ),
					'saved'  => esc_html__( 'Settings saved!', 'fs-product-catalog' ),
					'error'  => esc_html__( 'Error saving settings.', 'fs-product-catalog' ),
				),
			)
		);
	}

	/**
	 * Render the settings page.
	 */
	public static function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$settings = self::get_all();
		include FS_PRODUCT_CATALOG_PLUGIN_DIR . 'templates/admin/settings-page.php';
	}

	/**
	 * AJAX save handler.
	 */
	public static function ajax_save() {
		check_ajax_referer( 'fs_product_settings_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Unauthorized' ) );
		}

		$settings = isset( $_POST['settings'] ) ? (array) $_POST['settings'] : array();
		$sanitized = self::sanitize_settings( $settings );

		update_option( self::OPTION_NAME, $sanitized );

		// Flush sidebar caches since settings may have changed visibility.
		$taxonomies = array( 'fs-product-category', 'fs-product-brand', 'fs-product-type', 'fs-product-tag' );
		foreach ( $taxonomies as $taxonomy ) {
			delete_transient( 'fs_sidebar_terms_' . sanitize_key( $taxonomy ) );
		}

		wp_send_json_success( array( 'message' => 'Settings saved' ) );
	}

	/**
	 * Sanitize settings input.
	 *
	 * @param array $input Raw input from form.
	 * @return array Sanitized settings.
	 */
	private static function sanitize_settings( $input ) {
		$sanitized = array();

		// Integers with bounds.
		$sanitized['products_per_page'] = isset( $input['products_per_page'] )
			? max( 1, min( absint( $input['products_per_page'] ), 100 ) ) : 12;

		$sanitized['archive_columns'] = isset( $input['archive_columns'] )
			? max( 2, min( absint( $input['archive_columns'] ), 4 ) ) : 3;

		$sanitized['sidebar_items_limit'] = isset( $input['sidebar_items_limit'] )
			? max( 3, min( absint( $input['sidebar_items_limit'] ), 50 ) ) : 8;

		// Select fields (whitelist values).
		$sanitized['default_orderby'] = isset( $input['default_orderby'] ) && in_array( $input['default_orderby'], array( 'menu_order', 'date', 'title' ), true )
			? $input['default_orderby'] : 'menu_order';

		$sanitized['single_sidebar_position'] = isset( $input['single_sidebar_position'] ) && in_array( $input['single_sidebar_position'], array( 'left', 'right' ), true )
			? $input['single_sidebar_position'] : 'left';

		$sanitized['archive_sidebar_position'] = isset( $input['archive_sidebar_position'] ) && in_array( $input['archive_sidebar_position'], array( 'left', 'right' ), true )
			? $input['archive_sidebar_position'] : 'left';

		$sanitized['card_image_ratio'] = isset( $input['card_image_ratio'] ) && in_array( $input['card_image_ratio'], array( '1:1', '3:4', '16:9' ), true )
			? $input['card_image_ratio'] : '1:1';

		// Toggles (booleans).
		$toggles = array(
			'show_breadcrumbs',
			'single_show_sidebar',
			'single_sidebar_search',
			'single_sidebar_categories',
			'single_sidebar_brands',
			'single_sidebar_types',
			'single_sidebar_tags',
			'archive_show_sidebar',
			'archive_sidebar_search',
			'archive_sidebar_categories',
			'archive_sidebar_brands',
			'archive_sidebar_types',
			'archive_sidebar_tags',
			'card_show_category',
			'card_show_excerpt',
			'card_show_more_link',
		);

		foreach ( $toggles as $toggle ) {
			$sanitized[ $toggle ] = ! empty( $input[ $toggle ] );
		}

		return $sanitized;
	}

	/**
	 * Get a single setting value.
	 *
	 * @param string $key Setting key.
	 * @param mixed  $default Default value (falls back to class defaults).
	 * @return mixed
	 */
	public static function get( $key, $default = null ) {
		$settings = get_option( self::OPTION_NAME, array() );

		if ( isset( $settings[ $key ] ) ) {
			return $settings[ $key ];
		}

		if ( null !== $default ) {
			return $default;
		}

		return isset( self::$defaults[ $key ] ) ? self::$defaults[ $key ] : null;
	}

	/**
	 * Get all settings merged with defaults.
	 *
	 * @return array
	 */
	public static function get_all() {
		$settings = get_option( self::OPTION_NAME, array() );
		return wp_parse_args( $settings, self::$defaults );
	}

	/**
	 * Register filters that feed settings into the existing filter system.
	 *
	 * Settings serve as defaults. Developers can still override via add_filter().
	 * We use priority 5 so developer filters (default priority 10) take precedence.
	 */
	public static function register_setting_filters() {
		// General.
		add_filter( 'fs_product_posts_per_page', function( $value ) {
			return FS_Product_Settings::get( 'products_per_page', $value );
		}, 5 );

		add_filter( 'fs_product_archive_columns', function( $value ) {
			return FS_Product_Settings::get( 'archive_columns', $value );
		}, 5 );

		add_filter( 'fs_product_show_breadcrumbs', function( $value ) {
			return (bool) FS_Product_Settings::get( 'show_breadcrumbs', $value );
		}, 5 );

		// Single product.
		add_filter( 'fs_product_show_single_sidebar', function( $value ) {
			return (bool) FS_Product_Settings::get( 'single_show_sidebar', $value );
		}, 5 );

		add_filter( 'fs_product_single_sidebar_position', function( $value ) {
			return FS_Product_Settings::get( 'single_sidebar_position', $value );
		}, 5 );

		add_filter( 'fs_single_sidebar_show_search', function( $value ) {
			return (bool) FS_Product_Settings::get( 'single_sidebar_search', $value );
		}, 5 );

		add_filter( 'fs_single_sidebar_show_categories', function( $value ) {
			return (bool) FS_Product_Settings::get( 'single_sidebar_categories', $value );
		}, 5 );

		add_filter( 'fs_single_sidebar_show_brands', function( $value ) {
			return (bool) FS_Product_Settings::get( 'single_sidebar_brands', $value );
		}, 5 );

		add_filter( 'fs_single_sidebar_show_types', function( $value ) {
			return (bool) FS_Product_Settings::get( 'single_sidebar_types', $value );
		}, 5 );

		add_filter( 'fs_single_sidebar_show_tags', function( $value ) {
			return (bool) FS_Product_Settings::get( 'single_sidebar_tags', $value );
		}, 5 );

		// Archive.
		add_filter( 'fs_product_show_sidebar', function( $value ) {
			return (bool) FS_Product_Settings::get( 'archive_show_sidebar', $value );
		}, 5 );

		add_filter( 'fs_product_sidebar_position', function( $value ) {
			return FS_Product_Settings::get( 'archive_sidebar_position', $value );
		}, 5 );

		add_filter( 'fs_archive_sidebar_show_search', function( $value ) {
			return (bool) FS_Product_Settings::get( 'archive_sidebar_search', $value );
		}, 5 );

		add_filter( 'fs_archive_sidebar_show_categories', function( $value ) {
			return (bool) FS_Product_Settings::get( 'archive_sidebar_categories', $value );
		}, 5 );

		add_filter( 'fs_archive_sidebar_show_brands', function( $value ) {
			return (bool) FS_Product_Settings::get( 'archive_sidebar_brands', $value );
		}, 5 );

		add_filter( 'fs_archive_sidebar_show_types', function( $value ) {
			return (bool) FS_Product_Settings::get( 'archive_sidebar_types', $value );
		}, 5 );

		add_filter( 'fs_archive_sidebar_show_tags', function( $value ) {
			return (bool) FS_Product_Settings::get( 'archive_sidebar_tags', $value );
		}, 5 );

		// Product card.
		add_filter( 'fs_product_card_show_category', function( $value ) {
			return (bool) FS_Product_Settings::get( 'card_show_category', $value );
		}, 5 );

		add_filter( 'fs_product_card_show_excerpt', function( $value ) {
			return (bool) FS_Product_Settings::get( 'card_show_excerpt', $value );
		}, 5 );

		add_filter( 'fs_product_card_show_more_link', function( $value ) {
			return (bool) FS_Product_Settings::get( 'card_show_more_link', $value );
		}, 5 );
	}
}
