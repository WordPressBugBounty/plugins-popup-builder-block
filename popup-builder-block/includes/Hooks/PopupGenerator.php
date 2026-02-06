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
	}

	public function prepare_popup_assets(): void {

		if ( is_singular( Utils::post_type() ) ) {
			return;
		}

		$current_post_id = get_the_ID();

		$args = [
			'post_type'      => self::$post_type,
			'post_status'    => 'publish',
			'posts_per_page' => -1,
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
			],
		];

		$abtest_posts = [];
		$posts = get_posts( $args );

		foreach ( $posts as $post ) {

			$popup_conditions = new PopupConditions( $post->ID, $current_post_id );

			if (
				! $popup_conditions->display_conditions() ||
				! $popup_conditions->freequency_settings() ||
				$popup_conditions->ip_blocking() ||
				! $popup_conditions->geolocation_targeting() ||
				! $popup_conditions->scheduling() ||
				! $popup_conditions->cookie_targeting() ||
				! $popup_conditions->adblock_detection() ||
				$popup_conditions->abtest_active( $abtest_posts )
			) {
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
