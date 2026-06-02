<?php
/**
 * GitHub Updater
 *
 * Checks the GitHub repository for new releases and integrates
 * with WordPress's native plugin update system.
 *
 * @package FS_Product_Catalog
 */

namespace FSProductCatalog;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class GitHubUpdater
 *
 * Handles version checking and update notifications from GitHub releases.
 */
class GitHubUpdater {

	/**
	 * Plugin file path.
	 *
	 * @var string
	 */
	private $plugin_file;

	/**
	 * GitHub repository (owner/repo).
	 *
	 * @var string
	 */
	private $github_repo;

	/**
	 * Plugin slug (directory name).
	 *
	 * @var string
	 */
	private $slug;

	/**
	 * Transient key for caching release data.
	 *
	 * @var string
	 */
	private $transient_key;

	/**
	 * Initialize the updater.
	 *
	 * @param string $plugin_file Path to the main plugin file.
	 * @param string $github_repo GitHub repo in "owner/repo" format.
	 */
	public function __construct( $plugin_file, $github_repo ) {
		$this->plugin_file   = $plugin_file;
		$this->github_repo   = $github_repo;
		$this->slug          = dirname( plugin_basename( $plugin_file ) );
		$this->transient_key = 'fs_catalog_github_update_' . md5( $this->slug );

		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_update' ) );
		add_filter( 'plugins_api', array( $this, 'plugin_info' ), 10, 3 );
		add_action( 'upgrader_process_complete', array( $this, 'clear_transient_after_update' ), 10, 2 );
	}

	/**
	 * Check for updates and inject into the update_plugins transient.
	 *
	 * @param object $transient The update_plugins transient.
	 * @return object
	 */
	public function check_update( $transient ) {
		if ( empty( $transient->checked ) ) {
			return $transient;
		}

		$remote = $this->get_remote_version();

		if ( $remote && version_compare( FS_PRODUCT_CATALOG_VERSION, $remote->new_version, '<' ) ) {
			$res              = new \stdClass();
			$res->slug        = $this->slug;
			$res->plugin      = plugin_basename( $this->plugin_file );
			$res->new_version = $remote->new_version;
			$res->tested      = $remote->tested;
			$res->package     = $remote->package;
			$res->url         = $remote->url;

			$transient->response[ $res->plugin ] = $res;
		}

		return $transient;
	}

	/**
	 * Provide plugin information for the update details modal.
	 *
	 * @param mixed  $res    Default result.
	 * @param string $action API action.
	 * @param object $args   API arguments.
	 * @return mixed
	 */
	public function plugin_info( $res, $action, $args ) {
		if ( 'plugin_information' !== $action ) {
			return $res;
		}

		if ( $this->slug !== ( $args->slug ?? '' ) ) {
			return $res;
		}

		$remote = $this->get_remote_version();
		if ( ! $remote ) {
			return $res;
		}

		$res                = new \stdClass();
		$res->name          = 'FluxStack Product Catalog';
		$res->slug          = $this->slug;
		$res->version       = $remote->new_version;
		$res->tested        = $remote->tested;
		$res->requires      = '5.8';
		$res->requires_php  = '7.4';
		$res->author        = '<a href="https://ajithrn.com">Ajith R N</a>';
		$res->download_link = $remote->package;
		$res->trunk         = $remote->package;
		$res->last_updated  = $remote->last_updated;
		$res->sections      = array(
			'description' => 'A custom product catalog system with categories, brands, types, AJAX filtering, quote list, and full admin settings.',
			'changelog'   => $remote->changelog ?? '',
		);

		return $res;
	}

	/**
	 * Clear the cached transient after this plugin is updated.
	 *
	 * @param \WP_Upgrader $upgrader WP_Upgrader instance.
	 * @param array        $options  Array of update data.
	 */
	public function clear_transient_after_update( $upgrader, $options ) {
		if ( 'update' !== ( $options['action'] ?? '' ) || 'plugin' !== ( $options['type'] ?? '' ) ) {
			return;
		}

		$this_plugin = plugin_basename( $this->plugin_file );
		$plugins     = $options['plugins'] ?? array();

		if ( in_array( $this_plugin, $plugins, true ) ) {
			delete_site_transient( $this->transient_key );
		}
	}

	/**
	 * Get the remote version data (cached for 12 hours).
	 *
	 * @return object|false
	 */
	private function get_remote_version() {
		$remote = get_site_transient( $this->transient_key );

		if ( false === $remote ) {
			$remote = $this->fetch_github_release();
			if ( $remote ) {
				set_site_transient( $this->transient_key, $remote, 12 * HOUR_IN_SECONDS );
			}
		}

		return $remote;
	}

	/**
	 * Fetch the latest release from GitHub API.
	 *
	 * @return object|false
	 */
	private function fetch_github_release() {
		$url      = "https://api.github.com/repos/{$this->github_repo}/releases/latest";
		$response = wp_remote_get( $url, array(
			'timeout' => 10,
			'headers' => array( 'Accept' => 'application/vnd.github.v3+json' ),
		) );

		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return false;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ) );
		if ( ! isset( $body->tag_name ) ) {
			return false;
		}

		$version = ltrim( $body->tag_name, 'v' );

		// Find the zip asset, fallback to zipball.
		$package = $body->zipball_url;
		if ( ! empty( $body->assets ) ) {
			foreach ( $body->assets as $asset ) {
				if ( 'fs-product-catalog.zip' === $asset->name ) {
					$package = $asset->browser_download_url;
					break;
				}
			}
		}

		$obj               = new \stdClass();
		$obj->new_version  = $version;
		$obj->url          = $body->html_url;
		$obj->package      = $package;
		$obj->changelog    = $this->parse_markdown( $body->body ?? '' );
		$obj->last_updated = $body->published_at ?? '';
		$obj->tested       = '6.7';

		return $obj;
	}

	/**
	 * Simple markdown to HTML for changelog display.
	 *
	 * @param string $text Markdown text.
	 * @return string HTML.
	 */
	private function parse_markdown( $text ) {
		$text = preg_replace( '/\*\*(.*?)\*\*/', '<strong>$1</strong>', $text );
		$text = preg_replace_callback(
			'/\[(.*?)\]\((.*?)\)/',
			function ( $m ) {
				return '<a href="' . esc_url( $m[2] ) . '">' . esc_html( $m[1] ) . '</a>';
			},
			$text
		);
		$text = preg_replace( '/^\s*-\s+(.*)/m', '<li>$1</li>', $text );
		$text = preg_replace( '/((<li>.*<\/li>\s*)+)/s', '<ul>$1</ul>', $text );
		return nl2br( $text );
	}
}
