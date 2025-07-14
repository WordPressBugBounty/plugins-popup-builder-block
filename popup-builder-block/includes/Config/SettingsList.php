<?php

namespace PopupBuilderBlock\Config;

defined( 'ABSPATH' ) || exit;

class SettingsList {

	public static function pbb_settings_list() {
		$list = apply_filters(
			'popup-builder-block/pbb-settings-tabs/list',
			array(
				'unfiltered_upload' => array(
					'_id'         => uniqid(),
					'slug'        => 'unfiltered_upload',
					'title'       => 'Unfiltered File Upload',
					'description' => 'To be able to upload any SVG and JSON file from Media and PopupKit Icon Picker. PopupKit will remove any potentially harmful scripts and code by sanitizing the unfiltered files. We recommend enabling this feature only if you understand the security risks involved.',
					'package'     => 'free',
					'status'      => 'inactive',
					'category'    => 'general',
				),
				'remote_image'      => array(
					'_id'         => uniqid(),
					'slug'        => 'remote_image',
					'title'       => 'Download Remote Image',
					'description' => 'To download remote images from Popupkit Template Library, enable the "Download Remote Image" option.',
					'package'     => 'free',
					'status'      => 'active',
					'category'    => 'general',
				),
				'uninstall-data'    => array(
					'_id'         => uniqid(),
					'slug'        => 'uninstall-data',
					'title'       => 'Uninstall All Data',
					'description' => 'Enable this feature to remove all the data related to Popup Builder Block when you uninstall the plugin.',
					'package'     => 'free',
					'status'      => 'inactive',
					'category'    => 'data',
				),
				'analytics'         => array(
					'_id'         => uniqid(),
					'slug'        => 'analytics',
					'title'       => 'Analytics Data Maximum Storage Period',
					'description' => 'Log the data of your campaigns to the database. Automatically delete old campaign data after a period. Note that deleted data, will not appear in the Analytics page.',
					'package'     => 'free',
					'value'       => '2',
					'status'      => 'active',
					'category'    => 'advanced',
				),
				'user_consent'      => array(
					'_id'         => uniqid(),
					'slug'        => 'user_consent',
					'title'       => 'User Consent',
					'description' => 'Show update & fix related important messages, essential tutorials and promotional images on WP Dashboard',
					'package'     => 'free',
					'status'      => 'active',
					'category'    => 'general',
				),
				// 'version_control' => array(
				// '_id'    => uniqid(),
				// 'slug'    => 'version_control',
				// 'title'   => 'Version Control',
				// 'description' => 'Enable this feature to manage the version of your popups. You can create version of a popup and restore them at any time.',
				// 'package' => 'free',
				// 'value'   => '1.0.0',
				// 'status'  => 'active',
				// 'category' => 'version',
				// ),

			)
		);
		return $list;
	}
}
