<?php

namespace PopupBuilderBlock\Helpers;

defined( 'ABSPATH' ) || exit;

class GeoLocation {

	private static $geolocation;

	private static function init() {
		if(! isset(self::$geolocation)) {
			self::$geolocation = new GeoLocation();
		}
	}

	private static function get_cookie_location() {
		$pbb_location = isset( $_COOKIE['pbb_location'] ) ? sanitize_text_field( wp_unslash( $_COOKIE['pbb_location'] ) ) : '';
		$location = json_decode( $pbb_location );
		// check if the user has set a cookie for the location
		if ( ! empty( $location ) ) {
			return self::get_allowed_data($location);
		} else {
			return self::get_server_location();
		}
	}

	private static function get_allowed_data($data) {
		$allowed = ['city', 'region', 'country', 'timezone'];
		$result = new \stdClass();

		foreach ($allowed as $key) {
			$result->$key = $data->$key ?? '';
		}

		return $result;
	}

	private static function get_server_location() {
		$ip = IPBlocking::get_visitor_ip();
		
		$response = wp_remote_get( "https://ipinfo.io/{$ip}/json" );

		if ( is_wp_error( $response ) ) {
			return '';
		}

		$data = json_decode( wp_remote_retrieve_body( $response ) );

		if ( ! empty( $data ) ) {
			return self::get_allowed_data($data);
		}
	}

	public static function get_location() {
		self::init();

		return self::get_cookie_location();
	}
}
