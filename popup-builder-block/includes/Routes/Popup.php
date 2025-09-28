<?php

namespace PopupBuilderBlock\Routes;

defined( 'ABSPATH' ) || exit;

use PopupBuilderBlock\Helpers\DataBase;
use PopupBuilderBlock\Helpers\UserAgent;
use PopupBuilderBlock\Helpers\GeoLocation;

class Popup extends Api {

	protected function get_routes(): array {
        return [
            [
                'endpoint'            => '/popup/campaigns',
                'methods'             => 'GET',
                'callback'            => 'get_campaigns',
            ],
            [
                'endpoint'            => '/popup/date',
                'methods'             => 'GET',
                'callback'            => 'get_date',
            ],
            [
                'endpoint'            => '/popup/logs',
                'methods'             => 'GET',
                'callback'            => 'get_logs',
            ],
            [
                'endpoint'            => '/popup/logs',
                'methods'             => 'POST',
                'callback'            => 'insert_logs',
                'permission_callback' => [$this, 'permission_callback'],
            ],
            [
                'endpoint'            => '/popup/logs',
                'methods'             => 'DELETE',
                'callback'            => 'delete_logs',
            ],
            [
                'endpoint'            => '/popup/logs',
                'methods'             => 'PUT',
                'callback'            => 'update_logs',
                'permission_callback' => [$this, 'permission_callback'],
            ],
        ];
    }

	public function permission_callback(): bool {
		// check for nonce
		return isset( $_SERVER['HTTP_X_WP_NONCE'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_WP_NONCE'] ) ), 'wp_rest' );
	}

	public function get_campaigns($request) {
		$params = $request->get_params();
		$is_subscribers = isset( $params['subscribers'] ) ? (bool) $params['subscribers'] : false;

		if(!$is_subscribers) {
			$logs  = DataBase::getDB(
				'campaign_id, SUM(views) AS total_views, SUM(converted) AS total_converted',
				'pbb_logs',
				'1 GROUP BY campaign_id'
			);
		}
		
		// Fetch all campaigns from the database
		$args = array(
			'post_type'      => 'popupkit-campaigns',
			'posts_per_page' => -1,
			'post_status'    => $params['status'] ?? 'any', // Default to 'any' to include all statuses
		);
		$posts = get_posts( $args );

		if ( empty( $posts ) ) {
			return array(
				'status'  => 'error',
				'message' => 'No campaigns found',
			);
		}

		$campaigns = array();
		foreach ( $posts as $post ) {
			$campaign = array(
				'id'     => $post->ID,
				'title'  => $post->post_title,
				'date'   => $post->post_date,
				'status' => $post->post_status,
				'guid'   => $post->guid,
			);

			if ( !$is_subscribers ) {
				$campaign['meta'] = array(
					'type'             => get_post_meta( $post->ID, 'campaignType', true ),
					'status'             => get_post_meta( $post->ID, 'status', true ) == '1', // Explicitly cast to boolean
					'scheduleDateTime'   => get_post_meta( $post->ID, 'scheduleDateTime', true ) == '1', // Explicitly cast to boolean
					'scheduleOnDateValue'=> get_post_meta( $post->ID, 'scheduleOnDateValue', true ),
					'scheduleOffDateValue'=> get_post_meta( $post->ID, 'scheduleOffDateValue', true ),
					'scheduleTimeZone'   => get_post_meta( $post->ID, 'scheduleTimeZone', true ),
				);
				$campaign['author'] = get_the_author_meta( 'display_name', $post->post_author );

				$campaign['views'] = $logs ? array_reduce( $logs, function( $carry, $item ) use ( $post ) {
					return $carry + ( $item->campaign_id == $post->ID ? $item->total_views : 0 );
				}, 0 ) : 0;
				$campaign['converted'] = $logs ? array_reduce( $logs, function( $carry, $item ) use ( $post ) {
					return $carry + ( $item->campaign_id == $post->ID ? $item->total_converted : 0 );
				}, 0 ) : 0;
			}

			$campaigns[] = $campaign;
		}


		return array(
			'status'     => 'success',
			'data'  => $campaigns,
			'message'    => 'Campaigns fetched successfully',
		);
	}

	public function get_date( $request ) {
		global $wpdb;
		$table = $request['cat'] ?? '';

		if ( empty( $table ) ) {
			return array(
				'status'  => 'error',
				'message' => 'Invalid table name',
			);
		};

		$table_name = $wpdb->prefix . "pbb_$table";
		$data	= $wpdb->get_results(
			$wpdb->prepare("SELECT 
				date
				FROM $table_name LIMIT 1;" // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			)
		);

		if ( empty( $data ) ) {
			return array(
				'status'  => 'error',
				'message' => 'No data found',
			);
		}

		return array(
			'status'  => 'success',
			'data'    => $data,
			'message' => 'Data fetched successfully',
		);
	}

	public function get_logs( $request ) {
		// Validate the request parameters
		$campaign_id = $request['campaignId'] ?? '';
		$start_date  = $request['startDate'] ?? '';
		$end_date    = $request['endDate'] ?? '';
		$method	  = isset($request['type']) ? 'get_' . $request['type'] : '';

		if ( empty( $start_date ) || empty( $end_date ) || empty( $method ) ) {
			return array(
				'status'  => 'error',
				'message' => 'Invalid parameters',
			);
		}

		if ( ! method_exists( DataBase::class, $method ) ) {
			return array(
				'status'  => 'error',
				'message' => 'Invalid type provided',
			);
		}

		// Call the appropriate method dynamically
		$data = DataBase::$method( $campaign_id, $start_date, $end_date );

		if ( empty( $data ) ) {
			return array(
				'status'  => 'error',
				'message' => 'Data not found',
			);
		}

		return array(
			'status'  => 'success',
			'data'    => $data,
			'message' => 'Data fetched successfully',
		);
	}

	public function insert_logs( $request ) {
		$campaign_id = $request['postId'];
		$location = GeoLocation::get_location();
		$country = $location->country ?? '';
		$browser = UserAgent::get_browser() ?? '';
		$device = UserAgent::get_device();

		$user_details = array(
			'browser' => $browser,
			'device'  => $device,
			'country' => $country,
		);


		$current_date = gmdate( 'Y-m-d' );
		$log_id = DataBase::insertOrUpdateLog($campaign_id, $current_date, $device);

		if(!empty($browser)) DataBase::insertOrUpdateBrowser($log_id, $browser);
		if(!empty($country)) DataBase::insertOrUpdateCountry($log_id, $country);

		return array(
			'status'  => 'success',
			'data'    => array(
				'logId' => $log_id,
				'userDetails'	=> $user_details,
				'location' => $location
			),
			'message' => 'Logs inserted successfully',
		);
	}

	public function update_logs( $request ) {
		if ( ! isset( $request['id'] ) ) {
			return rest_ensure_response(
				array(
					'status'  => 'error',
					'message' => 'Invalid log ID',
				)
			);
		}

		$id   = absint( $request['id'] );

		$logs = DataBase::getDB( "*", 'pbb_logs', 'id = ' . $id );

		if ( empty( $logs ) ) {
			return rest_ensure_response(
				array(
					'status'  => 'error',
					'message' => 'Log not found',
				)
			);
		}

		$data = array(
			'converted' => isset( $request['converted'] ) ? $logs[0]->converted + 1 : $logs[0]->converted,
		);

		$updated = DataBase::updateDB( 'pbb_logs', $data, array( 'id' => $id ) );
		DataBase::insertOrUpdateReferrer($id, $request['refferer'] ?? '');

		if ( ! $updated ) {
			return array(
				'status'  => 'error',
				'message' => 'Failed to update logs',
			);
		}

		return array(
			'status'  => 'success',
			'data'    => $updated,
			'message' => 'Logs updated successfully',
		);
	}

	public function delete_logs( $request ) {
		$start_date = $request['startDate'] ?? '';
		$end_date   = $request['endDate'] ?? '';

		if ( empty( $start_date ) || empty( $end_date ) ) {
			return array(
				'status'  => 'error',
				'message' => 'Invalid date range',
			);
		}

		$deleted = DataBase::deleteExpiredData( array( 'start_date' => $start_date, 'end_date' => $end_date ) );

		if ( empty( $deleted ) ) {
			return array(
				'status'  => 'error',
				'message' => 'No data found to delete',
			);
		}

		return array(
			'status'  => 'success',
			'data'    => $deleted,
			'message' => 'Data deleted successfully!',
		);
	}
}
