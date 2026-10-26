<?php

namespace PopupBuilderBlock\Helpers;

defined( 'ABSPATH' ) || exit;

class FrequencySettings {
	public static $is_frequency_matched = false;

	public function __construct( $post_meta, $popup_id ) {
		self::$is_frequency_matched = $this->frequency_settings( $post_meta, $popup_id );
	}

	private function getCookie( string $name ) {
		return isset( $_COOKIE[ $name ] ) ? sanitize_text_field( wp_unslash( $_COOKIE[ $name ] ) ) : null;
	}

	private function checkFrequency( string $displayFrequency, $popup_id ) {
		switch ( $displayFrequency ) {
			case 'once-a-day':
				if ( $this->getCookie( "pbb_viewed_{$popup_id}" ) !== 'true' ) {
					return true;
				}
				return false;
			case 'every-visit':
				return true;
			case 'every-session':
				if ( $this->getCookie( "pbb_viewed_session_{$popup_id}" ) !== 'true' ) {
					return true;
				}
				return false;
			case 'once-every-few-days':
				if ( $this->getCookie( "pbb_viewed_days_{$popup_id}" ) !== 'true' ) {
					return true;
				}
				return false;
			case 'once-every-few-visits':
				return true;
			default:
				break;
		}
	}

	private function frequency_settings( $post_meta, $popup_id ) {
		$displayFrequency         = $post_meta['displayFrequency'] ?? 'once-a-day';
		$displayVisitor           = $post_meta['displayVisitor'] ?? 'everyone';
		$displayVisitorConvertion = $post_meta['displayVisitorConvertion'] ?? 0;
		$closeCampaign			  = $post_meta['closeCampaign'] ?? false;
		$certainViews             = $post_meta['certainViews'] ?? false;
		$certainViewsCount        = $post_meta['certainViewsCount'] ?? 3;

		if ( 
			($displayVisitorConvertion && $this->getCookie( "pbb_conversion_{$popup_id}" ) == 'true')
			|| ($closeCampaign && $this->getCookie( "pbb_closed_{$popup_id}" ) == 'true')
			|| ($certainViews && intval( $this->getCookie( "pbb_viewed_{$popup_id}_count" ) ) >= intval( $certainViewsCount ))
		) {
			return false;
		}

		/**
		 * Whether the visitor type lets this popup show.
		 *
		 * New and Returning visitor targeting is a PopupKit Pro feature: Pro checks
		 * the visitor cookies here. Without it every visitor matches ("Everyone").
		 *
		 * @param bool   $matched        Whether the visitor matches.
		 * @param string $displayVisitor 'everyone', 'new' or 'return'.
		 * @param int    $popup_id       Popup campaign post ID.
		 */
		if ( ! apply_filters( 'popup_builder_block/frequency/visitor_type', true, $displayVisitor, $popup_id ) ) {
			return false;
		}

		return $this->checkFrequency( $displayFrequency, $popup_id );
	}
}
