<?php
/**
 * Uninstall handler
 *
 * Cleans up plugin-specific options and transients when the plugin is deleted.
 * Does NOT delete product posts, terms, or ACF field data (that's user content).
 *
 * @package FS_Product_Catalog
 */

// Exit if not called by WordPress uninstall.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Delete plugin options.
delete_option( 'fs_product_catalog_settings' );

// Delete sidebar term cache transients.
$taxonomies = array(
	'fs-product-category',
	'fs-product-brand',
	'fs-product-type',
	'fs-product-tag',
);

foreach ( $taxonomies as $taxonomy ) {
	delete_transient( 'fs_sidebar_terms_' . sanitize_key( $taxonomy ) );
}

// Flush rewrite rules to clean up product URL rules.
flush_rewrite_rules();
