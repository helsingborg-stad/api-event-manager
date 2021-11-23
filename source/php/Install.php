<?php

namespace HbgEventImporter;

global $eventDatabaseVersion;
$eventDatabaseVersion = '1.2';

/**
 * Creates/updates necessary database tables
 */
class Install
{
    public function __construct()
    {
        add_action('plugins_loaded', array($this, 'updateDbCheck'));
    }

    /**
     * Check if db needs to be updated
     * @return void
     */
    public function updateDbCheck()
    {
        global $eventDatabaseVersion;

        if (version_compare(get_option('event_manager_db_version'), $eventDatabaseVersion) < 0) {
            $this->createTables();
        }
    }

    /**
     * Creates the event occasions db table
     * @return void
     */
    public static function createTables()
    {
        global $wpdb;
        global $eventDatabaseVersion;

        $charsetCollate = $wpdb->get_charset_collate();

        $tableName = $wpdb->prefix . 'occasions';
        $sql = "
            CREATE TABLE $tableName (
                ID BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                event BIGINT(20) UNSIGNED NOT NULL,
                timestamp_start BIGINT(20) UNSIGNED NOT NULL,
                timestamp_end BIGINT(20) UNSIGNED NOT NULL,
                timestamp_door BIGINT(20) UNSIGNED DEFAULT NULL,
                location_mode VARCHAR(50) DEFAULT NULL,
                location LONGTEXT DEFAULT NULL,
                booking_link LONGTEXT DEFAULT NULL,
                PRIMARY KEY  (ID)
            ) $charsetCollate;
        ";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        update_option('event_manager_db_version', $eventDatabaseVersion);
    }
}
