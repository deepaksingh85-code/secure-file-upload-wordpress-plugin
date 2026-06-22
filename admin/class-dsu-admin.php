<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DSU_Admin {

	public function __construct() {

		add_action(
			'admin_menu',
			[
				$this,
				'register_menu'
			]
		);
	}

	public function register_menu() {

		add_menu_page(
			'Secure Upload Logs',
			'Secure Upload',
			'manage_options',
			'dsu-logs',
			[
				$this,
				'logs_page'
			],
			'dashicons-shield'
		);
	}

	public function logs_page() {

		global $wpdb;

		$table_name =
			$wpdb->prefix .
			'dsu_upload_logs';

		$logs =
			$wpdb->get_results(
				"SELECT * FROM $table_name
				ORDER BY id DESC"
			);

		?>

		<div class="wrap">

			<h1>Secure Upload Logs</h1>

			<table class="widefat striped">

				<thead>

					<tr>
						<th>ID</th>
						<th>File</th>
						<th>MIME</th>
						<th>Status</th>
						<th>Message</th>
						<th>Date</th>
					</tr>

				</thead>

				<tbody>

				<?php foreach ( $logs as $log ) : ?>

					<tr>

						<td><?php echo esc_html( $log->id ); ?></td>

						<td><?php echo esc_html( $log->file_name ); ?></td>

						<td><?php echo esc_html( $log->mime_type ); ?></td>

						<td><?php echo esc_html( $log->status ); ?></td>

						<td><?php echo esc_html( $log->message ); ?></td>

						<td><?php echo esc_html( $log->created_at ); ?></td>

					</tr>

				<?php endforeach; ?>

				</tbody>

			</table>

		</div>

		<?php
	}
}