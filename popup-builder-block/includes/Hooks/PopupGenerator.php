<?php
namespace PopupBuilderBlock\Hooks;

defined( 'ABSPATH' ) || exit;

use PopupBuilderBlock\Helpers\Utils;
use PopupBuilderBlock\Helpers\UserAgent;
use PopupBuilderBlock\Helpers\PopupConditions;

class PopupGenerator {

	private static $post_type = 'popupkit-campaigns';

	private static $parsed_blocks = [];
	
	/**
	 * class constructor.
	 * private for singleton
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'wp', [ $this, 'prepare_popup_assets' ], 5 );
		add_action( 'wp_footer', array( $this, 'render_popup' ) );
		add_shortcode( 'popupkit', array( $this, 'render_inline_popup' ) );
	}

	/**
	 * Render the inline popup.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML for the inline popup or empty string if conditions are not met.
	 */
	public function render_inline_popup( $atts ){
		$atts = wp_parse_args( (array) $atts, array( 'id' => 0 ) );
		$id = (int) $atts['id'];
		if ( ! $id ) {
			return '';
		}

		$posts = self::get_popup_posts( $id );
		if ( empty( $posts ) ) {
			return ''; // not found or meta constraints not matched
		}

		$post = $posts[0];
		$current_post_id = get_the_ID();
		if ( self::should_skip_popup( $post, $current_post_id ) ) {
			return '';
		}

		$blocks = parse_blocks($post->post_content);
		if( empty( $blocks ) ) {
			return $post->post_content;
		}
		
		do_action( 'popup_builder_block/before_popup_render', $post->ID );
		$output = '';
		foreach ( $blocks as $block ) {
			$output .= render_block( $block );
		}
		
		return $output;
	}

	/**
	 * Prepare popup assets by checking conditions and loading necessary blocks.
	 *
	 * @return void
	 */
	public function prepare_popup_assets(): void {

		if ( is_singular( Utils::post_type() ) ) {
			return;
		}

		// Get the current post ID.
		$current_post_id = get_the_ID();
		$abtest_posts = [];
		$posts = self::get_popup_posts();

		foreach ( $posts as $post ) {
			if ( self::should_skip_popup( $post, $current_post_id, true, $abtest_posts ) ) {
				continue;
            }

			self::load_popup_assets( $post );
		}

		// Handle A/B test popups
		$selected_from_abtest = apply_filters('popup_builder_block/abtest/selected', array(), $abtest_posts);
		foreach($selected_from_abtest as $post_id) {
			$post = get_post($post_id);
			self::load_popup_assets( $post );
		}
	}

	/**
	 * Determine if a popup should be skipped based on various conditions.
	 *
	 * @param WP_Post $post The popup post object.
	 * @param int $current_post_id The current post ID.
	 * @param bool $check_abtest Whether to check for A/B test conditions.
	 * @param array|null $abtest_posts Reference to an array to collect A/B test posts.
	 * @return bool True if the popup should be skipped, false otherwise.
	 */
	private static function should_skip_popup( $post, $current_post_id = 0, $check_abtest = false, &$abtest_posts = null ): bool {
		$popup_conditions = new PopupConditions( $post->ID, $current_post_id );

		if (
			! $popup_conditions->display_conditions() ||
			! $popup_conditions->freequency_settings() ||
			$popup_conditions->ip_blocking() ||
			! $popup_conditions->geolocation_targeting() ||
			! $popup_conditions->scheduling() ||
			! $popup_conditions->cookie_targeting() ||
			! $popup_conditions->adblock_detection()
		) {
			return true;
		}

		if ( $check_abtest ) {
			return (bool) $popup_conditions->abtest_active( $abtest_posts );
		}

		return false;
	}

	/**
	 * Load popup assets by parsing blocks and registering them.
	 *
	 * @param WP_Post $post The popup post object.
	 * @return void
	 */
	private static function load_popup_assets( $post ): void {
		// Parse blocks once
		$blocks = parse_blocks( $post->post_content );
		
		self::$parsed_blocks[ $post->ID ] = $blocks;
		do_action( 'popup_builder_block/before_popup_render', $post->ID );

		// Register assets only (no output)
		foreach ( $blocks as $block ) {
			render_block( $block );
		}
	}

	/**
	 * Get popup posts based on specific meta conditions.
	 *
	 * @param int|null $id Optional popup ID to fetch a specific popup. Basically for inline popups.
	 * @return array Array of WP_Post objects matching the criteria.
	 */
	private static function get_popup_posts($id = null): array {
		$query_args = [
			'post_type'      => self::$post_type,
			'post_status'    => 'publish',
			'posts_per_page' => $id ? 1 : -1,
			'meta_query'     => [
				'relation' => 'AND',
				[
					'key'     => 'status',
					'value'   => true,
					'compare' => '=',
				],
				[
					'key'     => 'openTrigger',
					'value'   => 'none',
					'compare' => '!=',
				],
				[
					'key'     => 'displayDevice',
					'value'   => UserAgent::get_device(),
					'compare' => 'LIKE',
				],
				[
					'key'     => 'campaignType',
					'value'   => 'inline',
					'compare' => $id ? '=' : '!=',
				],
			],
		];
		if ( $id ) {
			$query_args['p'] = $id;
		}

		return get_posts( $query_args );
	}

	/**
	 * Renders the popups in the footer.
	 */
	public function render_popup(): void {

		if ( empty( self::$parsed_blocks ) ) {
			return;
		}

		foreach ( self::$parsed_blocks as $post_id => $blocks ) {
			foreach ( $blocks as $block ) {
				echo render_block( $block ); /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */
			}
		}
	}
}
