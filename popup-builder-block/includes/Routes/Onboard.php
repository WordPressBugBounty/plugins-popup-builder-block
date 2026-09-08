<?php

namespace PopupBuilderBlock\Routes;

defined('ABSPATH') || exit;

use PopupBuilderBlock\Helpers\InstallTracker;

class Onboard extends Api {
	private const EMAIL = 'popupkit_onboard_email';
	private const EMAIL_ID = 'popupkit_onboard_email_id';
	private const STATUS = 'popupkit_onboard_status';
	private const URL = 'https://api.wpmet.com/public/plugin-subscribe/';
	private const SLUG = 'popup-builder-block';
	private const CRM_SLUG = 'popupkit';
	private const NOTICE = 'popupkit_onboard_notice';

	protected function get_routes(): array {
		return [
			[
				'endpoint'            => '/onboard',
				'methods'             => "GET",
				'callback'            => 'get_onboard',
			],
			[
				'endpoint'            => '/onboard',
				'methods'             => "POST",
				'callback'            => 'post_onboard',
			],
			[
				'endpoint'            => '/onboard/notice',
				'methods'             => "POST",
				'callback'            => 'post_onboard_notice',
			],
		];
	}

	public function get_onboard($request)
	{

		$status = get_option(Onboard::STATUS);
		$email = get_option(Onboard::EMAIL);

		return array(
			'status'    => 'success',
			'onboard'      => array(
				'status' => $status,
				'email' => $email,
			),
			'message'   => array(
				'Onboard data has been fetched successfully.',
			),
		);
	}

	public function post_onboard($request)
	{
		$data    = $request->get_params();
		update_option(Onboard::STATUS, 'onboarded');

		$email = !empty($data['userMail']) && is_email($data['userMail'])
			? sanitize_email($data['userMail'])
			: '';

		$permissions = !empty($data['pluginPermission']) && is_array($data['pluginPermission'])
			? $data['pluginPermission']
			: [];

		$tracked = $this->track_plugins($permissions);

		if (empty($email)) {
			return [
				'status'  => 'success',
				'tracked' => $tracked,
				'message' => __('Onboard data saved successfully.', 'popup-builder-block')
			];
		}

		$response_data = $this->subscribe_email($email, Onboard::CRM_SLUG);

		if (is_wp_error($response_data)) {
			return [
				'status'  => 'error',
				'tracked' => $tracked,
				'message' => __('Failed to send onboard data.', 'popup-builder-block')
			];
		}

		InstallTracker::mark_subscribed(Onboard::SLUG, $email);
		$this->store_subscription($email, $response_data);

		return [
			'status'  => 'success',
			'data'    => $response_data,
			'tracked' => $tracked,
			'message' => __('Onboard data saved successfully.', 'popup-builder-block')
		];
	}

	/**
	 * Handles the onboarding notice shown on the dashboard.
	 *
	 * Accepting subscribes the address collected by any Wpmet onboarding, or
	 * the site admin address when none was collected. Dismissing only records
	 * that the notice was answered so it stays hidden.
	 */
	public function post_onboard_notice($request)
	{
		$accepted = rest_sanitize_boolean($request->get_param('accepted'));

		if (!$accepted) {
			update_option(Onboard::NOTICE, 'dismissed', false);

			return [
				'status'  => 'success',
				'message' => __('Onboard notice dismissed.', 'popup-builder-block')
			];
		}

		$collected = get_option('wpmet_onboard_collected_email');
		$email     = is_email($collected) ? sanitize_email($collected) : sanitize_email(get_option('admin_email'));

		if (!is_email($email)) {
			return [
				'status'  => 'error',
				'message' => __('No email address available to subscribe.', 'popup-builder-block')
			];
		}

		$response_data = $this->subscribe_email($email, Onboard::CRM_SLUG);

		if (is_wp_error($response_data)) {
			return [
				'status'  => 'error',
				'message' => __('Failed to send onboard data.', 'popup-builder-block')
			];
		}

		$this->store_subscription($email, $response_data);
		update_option(Onboard::NOTICE, 'accepted', false);

		return [
			'status'  => 'success',
			'data'    => $response_data,
			'email'   => $email,
			'message' => __('Onboard data saved successfully.', 'popup-builder-block')
		];
	}

	/**
	 * Records every plugin the user opted into in the shared Wpmet registry.
	 *
	 * Only PopupKit subscribes the collected email; the other plugins are
	 * tracked and get their own onboarding status completed, nothing is sent
	 * to their CRM from here.
	 *
	 * @param array $permissions Permission map from the onboarding payload, slug => granted.
	 * @return array The tracked plugin files.
	 */
	private function track_plugins(array $permissions): array
	{
		$tracked = [];

		foreach ($permissions as $slug => $granted) {
			$slug = sanitize_key($slug);

			if (empty($slug) || !rest_sanitize_boolean($granted)) {
				continue;
			}

			$plugin_file = InstallTracker::mark($slug);

			if (!empty($plugin_file)) {
				$tracked[] = $plugin_file;
			}
		}

		return $tracked;
	}

	/**
	 * Stores the subscribed email and the contact id returned by the CRM.
	 *
	 * @param string $email         Sanitized email address that was subscribed.
	 * @param array  $response_data Decoded response of the subscribe endpoint.
	 * @return void
	 */
	private function store_subscription(string $email, array $response_data): void
	{
		update_option(Onboard::EMAIL, $email);
		update_option(Onboard::EMAIL_ID, $response_data['response']['data']['id'] ?? '');
	}

	/**
	 * Sends the collected email to the Wpmet subscribe endpoint for a plugin.
	 *
	 * @param string $email Sanitized email address.
	 * @param string $slug  Plugin slug known by the subscribe endpoint.
	 * @return array|\WP_Error Decoded response body, or the request error.
	 */
	private function subscribe_email(string $email, string $slug)
	{
		$response = wp_remote_post(
			Onboard::URL,
			[
				'method'      => 'POST',
				'data_format' => 'body',
				'headers'     => [
					'Content-Type' => 'application/json',
				],
				'body'        => wp_json_encode([
					'email' => $email,
					'slug'  => $slug,
				]),
			]
		);

		if (is_wp_error($response)) {
			return $response;
		}

		return (array) json_decode(wp_remote_retrieve_body($response), true);
	}
}
