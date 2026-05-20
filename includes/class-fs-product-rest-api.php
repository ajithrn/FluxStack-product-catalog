<?php
/**
 * REST API
 *
 * Registers REST endpoints for the product catalog.
 *
 * @package FS_Product_Catalog
 */

namespace FSProductCatalog;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class RestAPI
 *
 * Provides REST API endpoints under fs-catalog/v1 namespace.
 */
class RestAPI {

	/**
	 * API namespace.
	 */
	const NAMESPACE = 'fs-catalog/v1';

	/**
	 * Initialize the class.
	 */
	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
	}

	/**
	 * Register REST routes.
	 */
	public static function register_routes() {
		// Check if REST API is enabled in settings.
		if ( ! (bool) Settings::get( 'enable_rest_api', false ) ) {
			return;
		}
		// Products list.
		register_rest_route(
			self::NAMESPACE,
			'/products',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'get_products' ),
				'permission_callback' => '__return_true',
				'args'                => self::get_products_args(),
			)
		);

		// Single product.
		register_rest_route(
			self::NAMESPACE,
			'/products/(?P<id>\d+)',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'get_product' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'id' => array(
						'validate_callback' => function( $param ) {
							return is_numeric( $param ) && $param > 0;
						},
					),
				),
			)
		);

		// Taxonomy terms.
		register_rest_route(
			self::NAMESPACE,
			'/terms/(?P<taxonomy>[a-z\-]+)',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'get_terms' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'taxonomy' => array(
						'validate_callback' => function( $param ) {
							$allowed = array( 'fs-product-category', 'fs-product-brand', 'fs-product-type', 'fs-product-tag' );
							return in_array( $param, $allowed, true );
						},
					),
					'hide_empty' => array(
						'default'           => true,
						'sanitize_callback' => 'rest_sanitize_boolean',
					),
				),
			)
		);
	}

	/**
	 * Get arguments schema for products endpoint.
	 *
	 * @return array
	 */
	private static function get_products_args() {
		return array(
			'page'     => array(
				'default'           => 1,
				'sanitize_callback' => 'absint',
			),
			'per_page' => array(
				'default'           => 12,
				'sanitize_callback' => function( $value ) {
					return max( 1, min( absint( $value ), 100 ) );
				},
			),
			'search'   => array(
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'orderby'  => array(
				'default'           => 'menu_order',
				'sanitize_callback' => function( $value ) {
					$allowed = array( 'menu_order', 'title', 'date' );
					return in_array( $value, $allowed, true ) ? $value : 'menu_order';
				},
			),
			'order'    => array(
				'default'           => 'ASC',
				'sanitize_callback' => function( $value ) {
					return in_array( strtoupper( $value ), array( 'ASC', 'DESC' ), true ) ? strtoupper( $value ) : 'ASC';
				},
			),
			'category' => array(
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'brand'    => array(
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'type'     => array(
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'tag'      => array(
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			),
		);
	}

	/**
	 * GET /products — List products with filters.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public static function get_products( $request ) {
		$args = array(
			'post_type'      => 'fs-products',
			'post_status'    => 'publish',
			'posts_per_page' => $request->get_param( 'per_page' ),
			'paged'          => $request->get_param( 'page' ),
			'orderby'        => $request->get_param( 'orderby' ),
			'order'          => $request->get_param( 'order' ),
		);

		// Search.
		$search = $request->get_param( 'search' );
		if ( ! empty( $search ) ) {
			$args['s'] = $search;
		}

		// Taxonomy filters.
		$tax_query = array();
		$tax_map   = array(
			'category' => 'fs-product-category',
			'brand'    => 'fs-product-brand',
			'type'     => 'fs-product-type',
			'tag'      => 'fs-product-tag',
		);

		foreach ( $tax_map as $param => $taxonomy ) {
			$value = $request->get_param( $param );
			if ( ! empty( $value ) ) {
				$tax_query[] = array(
					'taxonomy' => $taxonomy,
					'field'    => 'slug',
					'terms'    => explode( ',', $value ),
				);
			}
		}

		if ( ! empty( $tax_query ) ) {
			if ( count( $tax_query ) > 1 ) {
				$tax_query['relation'] = 'AND';
			}
			$args['tax_query'] = $tax_query;
		}

		$query = new \WP_Query( $args );

		$products = array();
		while ( $query->have_posts() ) {
			$query->the_post();
			$products[] = self::format_product( get_the_ID() );
		}
		wp_reset_postdata();

		$response = new \WP_REST_Response( $products, 200 );
		$response->header( 'X-WP-Total', $query->found_posts );
		$response->header( 'X-WP-TotalPages', $query->max_num_pages );
		$response->header( 'Cache-Control', 'public, max-age=300' );

		return $response;
	}

	/**
	 * GET /products/{id} — Single product.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function get_product( $request ) {
		$post_id = (int) $request->get_param( 'id' );
		$post    = get_post( $post_id );

		if ( ! $post || 'fs-products' !== $post->post_type || 'publish' !== $post->post_status ) {
			return new \WP_Error( 'not_found', __( 'Product not found.', 'fs-product-catalog' ), array( 'status' => 404 ) );
		}

		$response = new \WP_REST_Response( self::format_product( $post_id, true ), 200 );
		$response->header( 'Cache-Control', 'public, max-age=300' );

		return $response;
	}

	/**
	 * GET /terms/{taxonomy} — Taxonomy terms.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function get_terms( $request ) {
		$taxonomy   = $request->get_param( 'taxonomy' );
		$hide_empty = $request->get_param( 'hide_empty' );

		$terms = get_terms(
			array(
				'taxonomy'   => $taxonomy,
				'hide_empty' => $hide_empty,
			)
		);

		if ( is_wp_error( $terms ) ) {
			return new \WP_Error( 'invalid_taxonomy', __( 'Invalid taxonomy.', 'fs-product-catalog' ), array( 'status' => 400 ) );
		}

		$data = array();
		foreach ( $terms as $term ) {
			$term_data = array(
				'id'    => $term->term_id,
				'name'  => $term->name,
				'slug'  => $term->slug,
				'count' => $term->count,
				'link'  => get_term_link( $term ),
			);

			// Include parent for hierarchical taxonomies.
			if ( is_taxonomy_hierarchical( $taxonomy ) ) {
				$term_data['parent'] = $term->parent;
			}

			$data[] = $term_data;
		}

		$response = new \WP_REST_Response( $data, 200 );
		$response->header( 'Cache-Control', 'public, max-age=600' );

		return $response;
	}

	/**
	 * Format a product for API response.
	 *
	 * @param int  $post_id  Product post ID.
	 * @param bool $detailed Whether to include full content and ACF fields.
	 * @return array
	 */
	private static function format_product( $post_id, $detailed = false ) {
		$post = get_post( $post_id );

		$product = array(
			'id'        => $post_id,
			'title'     => get_the_title( $post_id ),
			'slug'      => $post->post_name,
			'excerpt'   => get_the_excerpt( $post_id ),
			'link'      => get_permalink( $post_id ),
			'date'      => $post->post_date,
			'modified'  => $post->post_modified,
			'menu_order' => $post->menu_order,
			'image'     => self::get_featured_image( $post_id ),
			'categories' => self::get_term_list( $post_id, 'fs-product-category' ),
			'brands'    => self::get_term_list( $post_id, 'fs-product-brand' ),
			'types'     => self::get_term_list( $post_id, 'fs-product-type' ),
			'tags'      => self::get_term_list( $post_id, 'fs-product-tag' ),
		);

		// Include full content and ACF fields for single product requests.
		if ( $detailed ) {
			$product['content'] = apply_filters( 'the_content', $post->post_content );

			// Gallery images.
			$gallery = get_field( 'product_gallery', $post_id );
			$product['gallery'] = array();
			if ( ! empty( $gallery ) && is_array( $gallery ) ) {
				foreach ( $gallery as $image ) {
					$product['gallery'][] = array(
						'id'        => $image['id'],
						'url'       => $image['url'],
						'thumbnail' => isset( $image['sizes']['thumbnail'] ) ? $image['sizes']['thumbnail'] : $image['url'],
					);
				}
			}

			// Product info (repeater).
			$info = get_field( 'product_info', $post_id );
			$product['info'] = array();
			if ( ! empty( $info ) && is_array( $info ) ) {
				foreach ( $info as $item ) {
					if ( ! empty( $item['title'] ) || ! empty( $item['content'] ) ) {
						$product['info'][] = array(
							'title'   => isset( $item['title'] ) ? $item['title'] : '',
							'content' => isset( $item['content'] ) ? $item['content'] : '',
						);
					}
				}
			}

			// Specifications (repeater).
			$specs = get_field( 'product_specifications', $post_id );
			$product['specifications'] = array();
			if ( ! empty( $specs ) && is_array( $specs ) ) {
				foreach ( $specs as $spec ) {
					if ( ! empty( $spec['tab_title'] ) ) {
						$product['specifications'][] = array(
							'title'   => $spec['tab_title'],
							'content' => isset( $spec['content'] ) ? $spec['content'] : '',
						);
					}
				}
			}
		}

		return $product;
	}

	/**
	 * Get featured image data.
	 *
	 * @param int $post_id Post ID.
	 * @return array|null
	 */
	private static function get_featured_image( $post_id ) {
		$thumbnail_id = get_post_thumbnail_id( $post_id );
		if ( ! $thumbnail_id ) {
			return null;
		}

		return array(
			'id'        => $thumbnail_id,
			'url'       => wp_get_attachment_image_url( $thumbnail_id, 'full' ),
			'thumbnail' => wp_get_attachment_image_url( $thumbnail_id, 'medium' ),
			'alt'       => get_post_meta( $thumbnail_id, '_wp_attachment_image_alt', true ),
		);
	}

	/**
	 * Get taxonomy terms for a product.
	 *
	 * @param int    $post_id  Post ID.
	 * @param string $taxonomy Taxonomy name.
	 * @return array
	 */
	private static function get_term_list( $post_id, $taxonomy ) {
		$terms = get_the_terms( $post_id, $taxonomy );
		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			return array();
		}

		$list = array();
		foreach ( $terms as $term ) {
			$list[] = array(
				'id'   => $term->term_id,
				'name' => $term->name,
				'slug' => $term->slug,
			);
		}

		return $list;
	}
}
