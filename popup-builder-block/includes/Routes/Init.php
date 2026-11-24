<?php
namespace PopupBuilderBlock\Routes;

defined( 'ABSPATH' ) || exit;

/**
 * Enqueue registrar.
 *
 * @since 1.0.0
 * @access public
 */
class Init {
	/**
	 * class constructor.
	 * private for singleton
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function __construct() {
		new Subscribers();
		new FetchDemo();
		new Popup();
		new SettingsData();
		new ProcessDownload();
		new Onboard();
		new Templates();
		new ABTest();
		new DeactivationFeedback();

		// Core only answers this for logged-in users, so apiFetch can't refresh the expired nonce of a
		// cached page for guests, and front-end form submits and popup logs fail with 403.
		add_action( 'wp_ajax_nopriv_rest-nonce', 'wp_ajax_rest_nonce' );
	}
}
