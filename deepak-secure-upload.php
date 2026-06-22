<?php
/**
 * Plugin Name: Deepak Secure Upload 
 * Description: Secure File Upload Validator Plugin
 * Version: 1.0
 * Author: Deepak Kumar Singh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


add_action(
	'admin_menu',
	'dsu_register_menu'
);

function dsu_register_menu() {

	add_menu_page(
		'Secure Upload',
		'Secure Upload',
		'manage_options',
		'dsu-upload',
		'dsu_upload_page',
		'dashicons-upload'
	);
}



function dsu_upload_page() {

	?>

	<div class="wrap">

		<h1>Secure Upload</h1>

		<?php

		if (
			$_SERVER['REQUEST_METHOD'] === 'POST'
		) {

			dsu_handle_upload();
		}

		?>

		<form
			method="post"
			enctype="multipart/form-data"
		>

			<?php
			wp_nonce_field(
				'dsu_upload_action',
				'dsu_nonce'
			);
			?>

			<input
				type="file"
				name="secure_file"
				required
			>

			<?php
			submit_button(
				'Upload File'
			);
			?>

		</form>

	</div>

	<?php
}

/*
Upload Handler
*/

function dsu_handle_upload() {

	/*
	Nonce Verification
	*/

	if (
		! isset( $_POST['dsu_nonce'] ) ||
		! wp_verify_nonce(
			$_POST['dsu_nonce'],
			'dsu_upload_action'
		)
	) {

		wp_die(
			'Security check failed.'
		);
	}

	/*
	 Capability Check
	*/

	if (
		! current_user_can(
			'manage_options'
		)
	) {

		wp_die(
			'Unauthorized access.'
		);
	}

	/*
	File Exists
	*/

	if (
		empty(
			$_FILES['secure_file']['name']
		)
	) {

		wp_die(
			'No file selected.'
		);
	}

	$file = $_FILES['secure_file'];

	/*
	 File Size Validation
	*/

	$max_size =
		5 * 1024 * 1024;

	if (
		$file['size']
		>
		$max_size
	) {

		wp_die(
			'File exceeds 5MB limit.'
		);
	}

	/*
	Extension Validation
	*/

	$allowed_extensions = [
		'jpg',
		'jpeg',
		'png',
		'pdf'
	];

	$extension = strtolower(
		pathinfo(
			$file['name'],
			PATHINFO_EXTENSION
		)
	);

	if (
		! in_array(
			$extension,
			$allowed_extensions,
			true
		)
	) {

		wp_die(
			'Invalid file extension.'
		);
	}
    $finfo = finfo_open(
	FILEINFO_MIME_TYPE
);

$mime = finfo_file(
	$finfo,
	$file['tmp_name']
);

finfo_close(
	$finfo
);
$allowed_mimes = [

	'image/jpeg',

	'image/png',

	'application/pdf'
];
if (
	! in_array(
		$mime,
		$allowed_mimes,
		true
	)
) {

	wp_die(
		'Invalid MIME Type.'
	);
}


if ( $extension === 'png' ) {

	$handle = fopen(
		$file['tmp_name'],
		'rb'
	);

	$signature = bin2hex(
		fread(
			$handle,
			4
		)
	);

	fclose(
		$handle
	);

	if (
		$signature !== '89504e47'
	) {

		wp_die(
			'Invalid PNG file signature.'
		);
	}
}
if (
	in_array(
		$extension,
		['jpg', 'jpeg'],
		true
	)
) {

	$handle = fopen(
		$file['tmp_name'],
		'rb'
	);

	$signature = bin2hex(
		fread(
			$handle,
			3
		)
	);

	fclose(
		$handle
	);

	if (
		$signature !== 'ffd8ff'
	) {

		wp_die(
			'Invalid JPEG file signature.'
		);
	}
}
if (
	$extension === 'pdf'
) {

	$handle = fopen(
		$file['tmp_name'],
		'rb'
	);

	$signature = fread(
		$handle,
		4
	);

	fclose(
		$handle
	);

	if (
		$signature !== '%PDF'
	) {

		wp_die(
			'Invalid PDF file signature.'
		);
	}
}
	/*
	Upload File
	*/

	require_once(
		ABSPATH .
		'wp-admin/includes/file.php'
	);

	$uploaded_file =
		wp_handle_upload(
			$file,
			[
				'test_form' => false
			]
		);

	if (
		isset(
			$uploaded_file['error']
		)
	) {

		wp_die(
			$uploaded_file['error']
		);
	}

	/*
	Create Attachment
	*/

	require_once(
		ABSPATH .
		'wp-admin/includes/image.php'
	);

	$filetype =
		wp_check_filetype(
			basename(
				$uploaded_file['file']
			)
		);

	$attachment = [

		'post_mime_type' =>
			$filetype['type'],

		'post_title' =>
			sanitize_file_name(
				pathinfo(
					$uploaded_file['file'],
					PATHINFO_FILENAME
				)
			),

		'post_content' => '',

		'post_status' => 'inherit'
	];

	$attachment_id =
		wp_insert_attachment(
			$attachment,
			$uploaded_file['file']
		);

	/*
	Generate Metadata
	*/

	$attachment_data =
		wp_generate_attachment_metadata(
			$attachment_id,
			$uploaded_file['file']
		);

	wp_update_attachment_metadata(
		$attachment_id,
		$attachment_data
	);

	/*
	|--------------------------------------------------------------------------
	| Success
	|--------------------------------------------------------------------------
	*/

	echo '<pre>';
print_r($mime);
echo '</pre>';
}