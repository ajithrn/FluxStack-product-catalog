<?php
/**
 * Plugin Name: FluxStack Product Catalog
 * Plugin URI: https://ajithrn.com
 * Description: A custom product catalog system without e-commerce functionality. Creates a custom post type for products with categories, brands, tags, and types.
 * Version: 2.0.0
 * Author: Ajith R N
 * Author URI: https://ajithrn.com
 * Text Domain: fs-product-catalog
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package FS_Product_Catalog
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants.
define( 'FS_PRODUCT_CATALOG_VERSION', '2.0.0' );
define( 'FS_PRODUCT_CATALOG_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'FS_PRODUCT_CATALOG_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'FS_PRODUCT_CATALOG_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

// Load Composer autoloader.
require_once FS_PRODUCT_CATALOG_PLUGIN_DIR . 'vendor/autoload.php';

use FSProductCatalog\CPT;
use FSProductCatalog\Taxonomies;
use FSProductCatalog\ACF;
use FSProductCatalog\TemplateLoader;
use FSProductCatalog\Frontend;
use FSProductCatalog\Ajax;
use FSProductCatalog\Settings;
use FSProductCatalog\RestAPI;
use FSProductCatalog\ImportExport;

/**
 * Main Plugin Class
 */
class FS_Product_Catalog {
	/**
	 * Single instance of the class
	 *
	 * @var FS_Product_Catalog
	 */
	private static $instance = null;

	/**
	 * Get single instance of the class
	 *
	 * @return FS_Product_Catalog
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	private function __construct() {
		$this->load_dependencies();
		$this->init_hooks();
	}

	/**
	 * Load required dependencies
	 */
	private function load_dependencies() {
		// Autoloaded via Composer — no manual requires needed.
	}

	/**
	 * Initialize hooks
	 */
	private function init_hooks() {
		// Check for ACF Pro dependency.
		add_action( 'admin_init', array( $this, 'check_dependencies' ) );
		add_action( 'admin_notices', array( $this, 'dependency_notice' ) );

		// Initialize components.
		add_action( 'plugins_loaded', array( $this, 'init_components' ) );

		// Activation and deactivation hooks.
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

		// Load plugin textdomain.
		add_action( 'init', array( $this, 'load_textdomain' ) );

		// Enqueue admin assets.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );

		// Add settings link on plugins page.
		add_filter( 'plugin_action_links_' . FS_PRODUCT_CATALOG_PLUGIN_BASENAME, array( $this, 'add_settings_link' ) );
	}

	/**
	 * Check for required dependencies
	 */
	public function check_dependencies() {
		if ( ! $this->is_acf_pro_active() ) {
			deactivate_plugins( FS_PRODUCT_CATALOG_PLUGIN_BASENAME );
		}
	}

	/**
	 * Check if ACF Pro is active
	 *
	 * @return bool
	 */
	private function is_acf_pro_active() {
		return class_exists( 'ACF' ) && defined( 'ACF_PRO' ) && ACF_PRO;
	}

	/**
	 * Display admin notice if dependencies are not met
	 */
	public function dependency_notice() {
		if ( ! $this->is_acf_pro_active() ) {
			?>
			<div class="notice notice-error">
				<p>
					<strong><?php esc_html_e( 'Product Catalog', 'fs-product-catalog' ); ?></strong>
					<?php
					printf(
						/* translators: %s: ACF Pro plugin link */
						esc_html__( 'requires Advanced Custom Fields PRO to be installed and activated. %s', 'fs-product-catalog' ),
						'<a href="https://www.advancedcustomfields.com/pro/" target="_blank">' . esc_html__( 'Get ACF Pro', 'fs-product-catalog' ) . '</a>'
					);
					?>
				</p>
			</div>
			<?php
		}
	}

	/**
	 * Initialize plugin components
	 */
	public function init_components() {
		// Only initialize if ACF Pro is active.
		if ( ! $this->is_acf_pro_active() ) {
			return;
		}

		CPT::init();
		Taxonomies::init();
		ACF::init();
		TemplateLoader::init();
		Frontend::init();
		Ajax::init();
		Settings::init();
		RestAPI::init();
		ImportExport::init();
	}

	/**
	 * Plugin activation
	 */
	public function activate() {
		// Initialize components to register post type and taxonomies.
		$this->init_components();

		// Flush rewrite rules.
		flush_rewrite_rules();
	}

	/**
	 * Plugin deactivation
	 */
	public function deactivate() {
		// Flush rewrite rules.
		flush_rewrite_rules();
	}

	/**
	 * Load plugin textdomain
	 */
	public function load_textdomain() {
		load_plugin_textdomain(
			'fs-product-catalog',
			false,
			dirname( FS_PRODUCT_CATALOG_PLUGIN_BASENAME ) . '/languages'
		);
	}

	/**
	 * Enqueue admin assets
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_admin_assets( $hook ) {
		// Only load on relevant admin pages.
		$allowed_hooks = array(
			'edit.php',
			'post.php',
			'post-new.php',
			'edit-tags.php',
			'term.php',
		);

		if ( ! in_array( $hook, $allowed_hooks, true ) ) {
			return;
		}

		// Check if we're on a product-related page.
		$screen = get_current_screen();
		if ( ! $screen || ( 'fs-products' !== $screen->post_type && ! in_array( $screen->taxonomy, array( 'fs-product-category', 'fs-product-brand', 'fs-product-type' ), true ) ) ) {
			return;
		}

		// Enqueue admin styles.
		wp_enqueue_style(
			'fs-product-catalog-admin',
			FS_PRODUCT_CATALOG_PLUGIN_URL . 'assets/dist/admin.min.css',
			array(),
			FS_PRODUCT_CATALOG_VERSION
		);
	}

	/**
	 * Add settings link on plugins page.
	 *
	 * @param array $links Existing plugin action links.
	 * @return array
	 */
	public function add_settings_link( $links ) {
		$settings_link = sprintf(
			'<a href="%s">%s</a>',
			esc_url( admin_url( 'edit.php?post_type=fs-products&page=fs-product-settings' ) ),
			esc_html__( 'Settings', 'fs-product-catalog' )
		);
		array_unshift( $links, $settings_link );
		return $links;
	}
}

/**
 * Initialize the plugin
 */
function fs_product_catalog_init() {
	return FS_Product_Catalog::get_instance();
}

// Start the plugin.
fs_product_catalog_init();
