<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DSU_Logger {

	public static function log(
		$file_name,
		$mime_type,
		$status,
		$message = ''
	) {

		global $wpdb;

		$table_name =
			$wpdb->prefix .
			'dsu_upload_logs';

		$wpdb->insert(
			$table_name,
			[
				'user_id'   => get_current_user_id(),
				'file_name' => $file_name,
				'mime_type' => $mime_type,
				'status'    => $status,
				'message'   => $message,
			]
		);
	}
}