<?php

namespace PopupBuilderBlock\Helpers;

defined( 'ABSPATH' ) || exit;

/**
 * Tracks the plugins a user opted into during the PopupKit onboarding flow.
 *
 * The registry is shared with the other Wpmet plugins through the
 * `wpmet_onboarded_plugins` option, so a plugin that was already onboarded
 * from here does not ask the user to onboard again. Both the map and the
 * registry are keyed by plugin file, the way the other Wpmet plugins key them.
 *
 * @since 1.0.0
 */
class InstallTracker {

	/**
	 * Option name used to store the shared Wpmet onboarding registry.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	const OPTION_KEY = 'wpmet_onboarded_plugins';

	/**
	 * Onboarding data of the known Wpmet plugins, keyed by plugin file.
	 *
	 * `option` holds the plugin's own onboarding status option and `value`
	 * the value it is set to (both empty when the plugin does not have one).
	 *
	 * @since 1.0.0
	 * @var array<string, array{option: string, value: mixed}>
	 */
	const ONBOARD_STATUS_MAP = array(
		'popup-builder-block/popup-builder-block.php'          => array( 'option' => 'popupkit_onboard_status', 'value' => 'onboarded' ),
		'gutenkit-blocks-addon/gutenkit-blocks-addon.php'      => array( 'option' => 'gutenkit_onboard_status', 'value' => 'onboarded' ),
		'elementskit-lite/elementskit-lite.php'                => array( 'option' => 'elements_kit_onboard_status', 'value' => 'onboarded' ),
		'metform/metform.php'                                  => array( 'option' => 'met_form_onboard_status', 'value' => 'onboarded' ),
		'emailkit/EmailKit.php'                                => array( 'option' => 'emailkit_onboard_status', 'value' => 'onboarded' ),
		'getgenie/getgenie.php'                                => array( 'option' => 'getgenie_onboard_status', 'value' => 'onboarded' ),
		'shopengine/shopengine.php'                            => array( 'option' => 'shopengine_onboard_status', 'value' => 'onboarded' ),
		'blocks-for-shopengine/shopengine-gutenberg-addon.php' => array( 'option' => '', 'value' => '' ),
		'rox-appointment-booking/rox-appointment-booking.php'  => array( 'option' => '', 'value' => '' ),
		'dynamic-cpt-fields-engine/rox-dynamic-cpt-fields-engine.php'    => array( 'option' => '', 'value' => '' ),
	);

	/**
	 * Returns the filterable plugin onboarding map.
	 *
	 * @since 1.0.0
	 *
	 * @return array The onboarding map keyed by plugin file.
	 */
	public static function get_onboard_status_map(): array {
		return (array) apply_filters( 'wpmet_onboard_status_map', self::ONBOARD_STATUS_MAP );
	}

	/**
	 * Returns the shared onboarding registry.
	 *
	 * @since 1.0.0
	 *
	 * @return array The shared onboarding registry.
	 */
	public static function get_registry(): array {
		return (array) get_option( self::OPTION_KEY, array() );
	}

	/**
	 * Resolves a plugin file from a plugin file or a plugin slug.
	 *
	 * The onboarding payload identifies plugins by slug, while the shared
	 * registry keys them by plugin file.
	 *
	 * @since 1.0.0
	 *
	 * @param string $plugin Plugin file or plugin slug.
	 * @return string The plugin file, or an empty string when unknown.
	 */
	public static function resolve_plugin_file( string $plugin ): string {
		$map = self::get_onboard_status_map();

		if ( isset( $map[ $plugin ] ) ) {
			return $plugin;
		}

		foreach ( array_keys( $map ) as $plugin_file ) {
			if ( dirname( $plugin_file ) === $plugin ) {
				return $plugin_file;
			}
		}

		return '';
	}

	/**
	 * Records a plugin as onboarded and completes its own onboarding status.
	 *
	 * @since 1.0.0
	 *
	 * @param string $plugin       Plugin file or plugin slug.
	 * @param string $installed_by Plugin that initiated the onboarding.
	 * @return string The tracked plugin file, or an empty string when the plugin is unknown.
	 */
	public static function mark( string $plugin, string $installed_by = 'popupkit' ): string {
		$plugin_file = self::resolve_plugin_file( $plugin );

		if ( empty( $plugin_file ) ) {
			return '';
		}

		$registry = self::get_registry();

		if ( ! isset( $registry[ $plugin_file ] ) ) {
			$registry[ $plugin_file ] = array(
				'installed_by' => $installed_by,
				'installed_at' => time(),
			);
			self::save_registry( $registry );
		}

		$map = self::get_onboard_status_map();

		if ( ! empty( $map[ $plugin_file ]['option'] ) && ! get_option( $map[ $plugin_file ]['option'] ) ) {
			update_option( $map[ $plugin_file ]['option'], $map[ $plugin_file ]['value'], false );
		}

		return $plugin_file;
	}

	/**
	 * Checks whether a plugin was onboarded through a Wpmet onboarding flow.
	 *
	 * @since 1.0.0
	 *
	 * @param string $plugin Plugin file or plugin slug.
	 * @return bool True when the plugin is already tracked.
	 */
	public static function is_onboarded( string $plugin ): bool {
		$plugin_file = self::resolve_plugin_file( $plugin );

		return ! empty( $plugin_file ) && isset( self::get_registry()[ $plugin_file ] );
	}

	/**
	 * Returns the plugin files of every plugin tracked in the registry.
	 *
	 * @since 1.0.0
	 *
	 * @return array List of tracked plugin files.
	 */
	public static function get_onboarded_plugins(): array {
		return array_keys( self::get_registry() );
	}

	/**
	 * Flags a plugin as subscribed so the collected email is not sent twice.
	 *
	 * When an email is passed, it is also stored in the shared Wpmet options
	 * so the other plugins know an address was already collected.
	 *
	 * @since 1.0.0
	 *
	 * @param string $plugin Plugin file or plugin slug.
	 * @param string $email  Email address the plugin was subscribed with.
	 * @return void
	 */
	public static function mark_subscribed( string $plugin, string $email = '' ): void {
		$plugin_file = self::resolve_plugin_file( $plugin );

		if ( empty( $plugin_file ) ) {
			return;
		}

		$registry = self::get_registry();

		if ( ! isset( $registry[ $plugin_file ] ) || ! is_array( $registry[ $plugin_file ] ) ) {
			$registry[ $plugin_file ] = array();
		}

		$registry[ $plugin_file ]['subscribed']    = true;
		$registry[ $plugin_file ]['subscribed_at'] = time();
		self::save_registry( $registry );

		update_option( 'wpmet_onboard_email_collected', true, false );
		update_option( 'wpmet_onboard_collected_email', sanitize_email( $email ), false );
	}

	/**
	 * Checks whether the collected email was already sent for a plugin.
	 *
	 * @since 1.0.0
	 *
	 * @param string $plugin Plugin file or plugin slug.
	 * @return bool True when the plugin was already subscribed.
	 */
	public static function is_subscribed( string $plugin ): bool {
		$plugin_file = self::resolve_plugin_file( $plugin );

		if ( empty( $plugin_file ) ) {
			return false;
		}

		$registry = self::get_registry();

		return ! empty( $registry[ $plugin_file ]['subscribed'] );
	}

	/**
	 * Saves the shared onboarding registry.
	 *
	 * @since 1.0.0
	 *
	 * @param array $registry Shared onboarding registry.
	 * @return void
	 */
	private static function save_registry( array $registry ): void {
		update_option( self::OPTION_KEY, $registry, false );
	}
}
