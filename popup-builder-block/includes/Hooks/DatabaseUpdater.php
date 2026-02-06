<?php

namespace PopupBuilderBlock\Hooks;

defined( 'ABSPATH' ) || exit;

use PopupBuilderBlock\Helpers\DataBase;

class DatabaseUpdater {
    /**
     * class constructor.
     *
     * @return void
     * @since 2.1.2
     */
    public function __construct() {
        add_action( 'admin_init', array( $this, 'update_database' ) );
    }

    /**
     * Update database if required
     *
     * @return void
     * @since 2.1.2
     */
    public function update_database() {
        $installed_version = get_option( DataBase::$DATABASE_KEY );
        $current_version   = DataBase::$DATABASE_VERSION;

        if ( $installed_version === $current_version ) {
            return; // Already up to date
        }

        // New table added in 1.1.0
        if ( version_compare( $installed_version, '1.1.0', '<' ) ) {
            DataBase::createABTestTables();

            // Update the version in the database
            update_option( DataBase::$DATABASE_KEY, $current_version );
        }
    }
}