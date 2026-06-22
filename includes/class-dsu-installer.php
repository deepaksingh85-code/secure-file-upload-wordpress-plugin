<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DSU_Installer {

	public static function activate() {

		global $wpdb;

		$table_name =
			$wpdb->prefix .
			'dsu_upload_logs';

		$charset_collate =
			$wpdb->get_charset_collate();

		$sql = "
		CREATE TABLE $table_name (

			id BIGINT NOT NULL AUTO_INCREMENT,

			user_id BIGINT NOT NULL,

			file_name VARCHAR(255) NOT NULL,

			mime_type VARCHAR(255) NOT NULL,

			status VARCHAR(50) NOT NULL,

			message TEXT NULL,

			created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

			PRIMARY KEY (id)

		) $charset_collate;
		";

		require_once(
			ABSPATH .
			'wp-admin/includes/upgrade.php'
		);

		dbDelta(
			$sql
		);
	}
}