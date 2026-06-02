<?php
/**
 * Product Identification Fields
 *
 * Registers native meta box for product identification fields (SKU, Part No., etc.)
 * These are stored as individual post meta keys — no ACF dependency.
 *
 * @package FS_Product_Catalog
 */

namespace FSProductCatalog;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Identification
 *
 * Handles product identification meta box and field management.
 */
class Identification {

	/**
	 * Meta key prefix.
	 */
	const META_PREFIX = '_fs_product_';

	/**
	 * Field definitions.
	 *
	 * @var array
	 */
	private static $fields = array(
		'sku' => array(
			'label'       => 'SKU',
			'type'        => 'text',
			'placeholder' => 'e.g. WRS-6x19-3/8',
			'description' => 'Internal stock-keeping unit identifier.',
		),
		'mfr_part' => array(
			'label'       => 'Mfr. Part No.',
			'type'        => 'text',
			'placeholder' => 'e.g. 6x19-IWRC-3/8',
			'description' => 'Manufacturer\'s part number.',
		),
		'uom' => array(
			'label'       => 'Unit of Measure',
			'type'        => 'text',
			'placeholder' => 'e.g. each, per ft, box of 10',
			'description' => 'How this product is measured/sold.',
		),
		'moq' => array(
			'label'       => 'Min. Order Qty',
			'type'        => 'number',
			'placeholder' => '1',
			'description' => 'Minimum quantity for orders. Used in quote list.',
		),
		'lead_time' => array(
			'label'       => 'Lead Time',
			'type'        => 'text',
			'placeholder' => 'e.g. 2-3 weeks, In stock',
			'description' => 'Estimated delivery/availability time.',
		),
		'weight' => array(
			'label'       => 'Weight',
			'type'        => 'text',
			'placeholder' => 'e.g. 2.5 lbs/ft',
			'description' => 'Product weight.',
		),
		'dimensions' => array(
			'label'       => 'Dimensions',
			'type'        => 'text',
			'placeholder' => 'e.g. 3/8" x 100ft',
			'description' => 'Product dimensions.',
		),
	);

	/**
	 * Initialize the class.
	 */
	public static function init() {
		add_action( 'add_meta_boxes', array( __CLASS__, 'register_meta_box' ) );
		add_action( 'save_post_fs-products', array( __CLASS__, 'save_meta' ), 10, 2 );
	}

	/**
	 * Register the identification meta box.
	 */
	public static function register_meta_box() {
		add_meta_box(
			'fs-product-identification',
			__( 'Product Identification', 'fs-product-catalog' ),
			array( __CLASS__, 'render_meta_box' ),
			'fs-products',
			'normal',
			'default'
		);
	}

	/**
	 * Render the meta box content.
	 *
	 * @param \WP_Post $post Current post object.
	 */
	public static function render_meta_box( $post ) {
		wp_nonce_field( 'fs_product_identification_save', 'fs_product_identification_nonce' );

		$settings = Settings::get_all();
		$sku_label = ! empty( $settings['sku_label'] ) ? $settings['sku_label'] : 'SKU';
		$mfr_part_label = ! empty( $settings['mfr_part_label'] ) ? $settings['mfr_part_label'] : 'Mfr. Part No.';

		echo '<div class="fs-identification-fields">';
		echo '<style>
			.fs-identification-fields { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; padding: 12px 0; }
			.fs-identification-field { display: flex; flex-direction: column; gap: 4px; }
			.fs-identification-field label { font-weight: 600; font-size: 13px; }
			.fs-identification-field input { width: 100%; padding: 6px 8px; }
			.fs-identification-field .description { font-size: 12px; color: #666; font-style: italic; }
			@media (max-width: 782px) { .fs-identification-fields { grid-template-columns: 1fr; } }
		</style>';

		foreach ( self::$fields as $key => $field ) {
			$meta_key = self::META_PREFIX . $key;
			$value = get_post_meta( $post->ID, $meta_key, true );

			// Use custom labels from settings for SKU and Part No.
			$label = $field['label'];
			if ( 'sku' === $key ) {
				$label = $sku_label;
			} elseif ( 'mfr_part' === $key ) {
				$label = $mfr_part_label;
			}

			$input_type = $field['type'];
			$input_attrs = '';
			if ( 'number' === $input_type ) {
				$input_attrs = ' min="1" step="1"';
			}

			printf(
				'<div class="fs-identification-field">
					<label for="fs_id_%1$s">%2$s</label>
					<input type="%3$s" id="fs_id_%1$s" name="fs_identification[%1$s]" value="%4$s" placeholder="%5$s"%6$s />
					<span class="description">%7$s</span>
				</div>',
				esc_attr( $key ),
				esc_html( $label ),
				esc_attr( $input_type ),
				esc_attr( $value ),
				esc_attr( $field['placeholder'] ),
				$input_attrs,
				esc_html( $field['description'] )
			);
		}

		echo '</div>';
	}

	/**
	 * Save meta box data.
	 *
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $post    Post object.
	 */
	public static function save_meta( $post_id, $post ) {
		// Verify nonce.
		if ( ! isset( $_POST['fs_product_identification_nonce'] ) ||
			! wp_verify_nonce( $_POST['fs_product_identification_nonce'], 'fs_product_identification_save' ) ) {
			return;
		}

		// Check autosave.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check permissions.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Check for our data.
		if ( ! isset( $_POST['fs_identification'] ) ) {
			return;
		}

		$data = (array) $_POST['fs_identification'];

		foreach ( self::$fields as $key => $field ) {
			$meta_key = self::META_PREFIX . $key;
			$value = isset( $data[ $key ] ) ? $data[ $key ] : '';

			if ( 'number' === $field['type'] ) {
				$value = absint( $value );
				if ( $value > 0 ) {
					update_post_meta( $post_id, $meta_key, $value );
				} else {
					delete_post_meta( $post_id, $meta_key );
				}
			} else {
				$value = sanitize_text_field( wp_unslash( $value ) );
				if ( '' !== $value ) {
					update_post_meta( $post_id, $meta_key, $value );
				} else {
					delete_post_meta( $post_id, $meta_key );
				}
			}
		}
	}

	/**
	 * Get a single identification field value.
	 *
	 * @param string $field Field key (e.g. 'sku', 'mfr_part').
	 * @param int    $post_id Post ID (defaults to current post).
	 * @return string
	 */
	public static function get( $field, $post_id = null ) {
		if ( null === $post_id ) {
			$post_id = get_the_ID();
		}
		return get_post_meta( $post_id, self::META_PREFIX . $field, true );
	}

	/**
	 * Get all identification fields for a product.
	 *
	 * @param int $post_id Post ID (defaults to current post).
	 * @return array Associative array of field values.
	 */
	public static function get_all( $post_id = null ) {
		if ( null === $post_id ) {
			$post_id = get_the_ID();
		}

		$data = array();
		foreach ( self::$fields as $key => $field ) {
			$value = get_post_meta( $post_id, self::META_PREFIX . $key, true );
			if ( '' !== $value && false !== $value ) {
				$data[ $key ] = $value;
			}
		}

		return $data;
	}

	/**
	 * Get field definitions (for REST API, export, etc.)
	 *
	 * @return array
	 */
	public static function get_field_definitions() {
		return self::$fields;
	}
}
