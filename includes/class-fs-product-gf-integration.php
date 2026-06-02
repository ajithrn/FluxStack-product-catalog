<?php
/**
 * Gravity Forms Integration (Optional)
 *
 * Provides enhanced quote list integration when Gravity Forms is active:
 * - Auto-targets GF field by form/field ID (no CSS selector guessing)
 * - Formats product list as HTML table in entry meta
 * - {fs_product_table} merge tag for notifications
 * - Auto-clears localStorage on successful submission
 *
 * Only loaded when GF is detected (class_exists('GFForms')).
 *
 * @package FS_Product_Catalog
 */

namespace FSProductCatalog;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class GFIntegration
 */
class GFIntegration {

	/**
	 * Initialize the class.
	 */
	public static function init() {
		// Only load if Gravity Forms is active.
		if ( ! class_exists( 'GFForms' ) ) {
			return;
		}

		// Add GF settings section to admin.
		add_action( 'wp_ajax_fs_gf_get_form_fields', array( __CLASS__, 'ajax_get_form_fields' ) );

		// Entry formatting after submission.
		add_action( 'gform_after_submission', array( __CLASS__, 'format_entry' ), 10, 2 );

		// Merge tag registration.
		add_filter( 'gform_custom_merge_tags', array( __CLASS__, 'register_merge_tag' ), 10, 4 );
		add_filter( 'gform_replace_merge_tags', array( __CLASS__, 'replace_merge_tag' ), 10, 7 );

		// Auto-clear localStorage on confirmation.
		add_filter( 'gform_confirmation', array( __CLASS__, 'add_clear_script' ), 10, 4 );

		// Add GF config to localized JS data.
		add_filter( 'fs_product_frontend_localize_data', array( __CLASS__, 'add_gf_localized_data' ) );
	}

	/**
	 * Check if GF integration is enabled in settings.
	 *
	 * @return bool
	 */
	private static function is_gf_enabled() {
		return (bool) Settings::get( 'quote_list_gf_enabled', false );
	}

	/**
	 * AJAX handler: get fields for a selected GF form.
	 */
	public static function ajax_get_form_fields() {
		check_ajax_referer( 'fs_product_settings_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error();
		}

		$form_id = absint( $_POST['form_id'] ?? 0 );
		if ( ! $form_id ) {
			wp_send_json_error();
		}

		$form = \GFAPI::get_form( $form_id );
		if ( ! $form ) {
			wp_send_json_error();
		}

		$fields = array();
		foreach ( $form['fields'] as $field ) {
			$fields[] = array(
				'id'    => $field->id,
				'label' => $field->label,
				'type'  => $field->type,
			);
		}

		wp_send_json_success( array( 'fields' => $fields ) );
	}

	/**
	 * Add GF-specific data to localized JS.
	 *
	 * @param array $data Existing localized data.
	 * @return array
	 */
	public static function add_gf_localized_data( $data ) {
		if ( ! self::is_gf_enabled() || ! isset( $data['quoteList'] ) ) {
			return $data;
		}

		$form_id  = absint( Settings::get( 'quote_list_gf_form_id', 0 ) );
		$field_id = absint( Settings::get( 'quote_list_gf_field_id', 0 ) );

		if ( $form_id && $field_id ) {
			// Override the generic field selector with GF's specific input ID.
			$data['quoteList']['fieldSelector'] = '#input_' . $form_id . '_' . $field_id;
			$data['quoteList']['gfEnabled'] = true;
			$data['quoteList']['gfFormId'] = $form_id;
		}

		return $data;
	}

	/**
	 * Format the entry after submission.
	 * Parses the product field text and stores a formatted HTML table in entry meta.
	 *
	 * @param array $entry The entry object.
	 * @param array $form  The form object.
	 */
	public static function format_entry( $entry, $form ) {
		if ( ! self::is_gf_enabled() ) {
			return;
		}

		$form_id  = absint( Settings::get( 'quote_list_gf_form_id', 0 ) );
		$field_id = absint( Settings::get( 'quote_list_gf_field_id', 0 ) );

		if ( absint( $form['id'] ) !== $form_id || ! $field_id ) {
			return;
		}

		$raw_value = rgar( $entry, $field_id );
		if ( empty( $raw_value ) ) {
			return;
		}

		// Parse the text lines into structured data.
		$html_table = self::build_html_table( $raw_value );

		// Store formatted table in entry meta for display.
		gform_update_meta( $entry['id'], 'fs_product_table_html', $html_table );
	}

	/**
	 * Register the {fs_product_table} merge tag.
	 *
	 * @param array $merge_tags Existing merge tags.
	 * @param int   $form_id   Form ID.
	 * @param array $fields    Form fields.
	 * @param int   $element_id Element ID.
	 * @return array
	 */
	public static function register_merge_tag( $merge_tags, $form_id, $fields, $element_id ) {
		$merge_tags[] = array(
			'label' => __( 'Product Quote Table', 'fs-product-catalog' ),
			'tag'   => '{fs_product_table}',
		);
		return $merge_tags;
	}

	/**
	 * Replace the {fs_product_table} merge tag with formatted HTML.
	 *
	 * @param string $text       The text to search/replace.
	 * @param array  $form       The form object.
	 * @param array  $entry      The entry object.
	 * @param bool   $url_encode Whether to URL encode.
	 * @param bool   $esc_html   Whether to escape HTML.
	 * @param bool   $nl2br      Whether to convert newlines to br.
	 * @param string $format     The format (html or text).
	 * @return string
	 */
	public static function replace_merge_tag( $text, $form, $entry, $url_encode, $esc_html, $nl2br, $format ) {
		if ( strpos( $text, '{fs_product_table}' ) === false ) {
			return $text;
		}

		if ( ! self::is_gf_enabled() ) {
			$text = str_replace( '{fs_product_table}', '', $text );
			return $text;
		}

		$field_id = absint( Settings::get( 'quote_list_gf_field_id', 0 ) );
		$raw_value = rgar( $entry, $field_id );

		if ( empty( $raw_value ) ) {
			$text = str_replace( '{fs_product_table}', '', $text );
			return $text;
		}

		$html_table = self::build_html_table( $raw_value );
		$text = str_replace( '{fs_product_table}', $html_table, $text );

		return $text;
	}

	/**
	 * Add localStorage clear script to GF confirmation.
	 *
	 * @param mixed $confirmation The confirmation message/redirect.
	 * @param array $form         The form object.
	 * @param array $entry        The entry object.
	 * @param bool  $ajax         Whether AJAX submission.
	 * @return mixed
	 */
	public static function add_clear_script( $confirmation, $form, $entry, $ajax ) {
		if ( ! self::is_gf_enabled() ) {
			return $confirmation;
		}

		$form_id = absint( Settings::get( 'quote_list_gf_form_id', 0 ) );
		$auto_clear = (bool) Settings::get( 'quote_list_gf_auto_clear', true );

		if ( absint( $form['id'] ) !== $form_id || ! $auto_clear ) {
			return $confirmation;
		}

		// Only add script to HTML confirmations (not redirects).
		if ( is_array( $confirmation ) ) {
			return $confirmation;
		}

		$clear_script = '<script>try{localStorage.removeItem("fs_quote_list")}catch(e){}</script>';
		$confirmation .= $clear_script;

		return $confirmation;
	}

	/**
	 * Build an HTML table from the raw product text field value.
	 *
	 * @param string $raw_value The raw text (one product per line).
	 * @return string HTML table.
	 */
	private static function build_html_table( $raw_value ) {
		$lines = array_filter( array_map( 'trim', explode( "\n", $raw_value ) ) );

		if ( empty( $lines ) ) {
			return '';
		}

		$html = '<table style="width:100%;border-collapse:collapse;font-size:14px;">';
		$html .= '<thead><tr style="background:#f5f5f5;">';
		$html .= '<th style="padding:8px 12px;text-align:left;border-bottom:2px solid #ddd;">Product</th>';
		$html .= '<th style="padding:8px 12px;text-align:left;border-bottom:2px solid #ddd;">Details</th>';
		$html .= '</tr></thead><tbody>';

		foreach ( $lines as $line ) {
			$parts = array_map( 'trim', explode( '|', $line ) );
			$name = isset( $parts[0] ) ? $parts[0] : '';
			
			// Strip leading list numbers/bullets (e.g. "1. Product Name" or "- Product Name" or "• Product Name")
			$name = preg_replace( '/^(\d+\.\s*|[\-\*•]\s*)/u', '', $name );

			$details = array_slice( $parts, 1 );

			// Check if last part is a URL.
			$url = '';
			if ( ! empty( $details ) ) {
				$last = end( $details );
				if ( filter_var( $last, FILTER_VALIDATE_URL ) ) {
					$url = array_pop( $details );
				}
			}

			$name_html = esc_html( $name );
			if ( $url ) {
				$name_html = '<a href="' . esc_url( $url ) . '" style="color:#007bff;text-decoration:none;">' . esc_html( $name ) . '</a>';
			}

			$details_html = esc_html( implode( ' | ', $details ) );

			$html .= '<tr style="border-bottom:1px solid #eee;">';
			$html .= '<td style="padding:8px 12px;">' . $name_html . '</td>';
			$html .= '<td style="padding:8px 12px;">' . $details_html . '</td>';
			$html .= '</tr>';
		}

		$html .= '</tbody></table>';

		return $html;
	}

	/**
	 * Get all GF forms for the settings dropdown.
	 *
	 * @return array Array of form data (id, title).
	 */
	public static function get_forms_list() {
		if ( ! class_exists( 'GFForms' ) || ! class_exists( 'GFAPI' ) ) {
			return array();
		}

		$forms = \GFAPI::get_forms();
		$list = array();

		foreach ( $forms as $form ) {
			$list[] = array(
				'id'    => $form['id'],
				'title' => $form['title'],
			);
		}

		return $list;
	}
}
