<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DSU_Validator {

	public function __construct() {

		add_filter(
			'wp_handle_upload_prefilter',
			[
				$this,
				'validate_upload',
			]
		);
	}

	public function validate_upload( $file ) {

		/*
		|--------------------------------------------------------------------------
		| File Size Validation
		|--------------------------------------------------------------------------
		*/

		$max_size = 5 * 1024 * 1024;

		if ( $file['size'] > $max_size ) {

			DSU_Logger::log(
				$file['name'],
				'application/octet-stream',
				'failed',
				'File exceeds size limit'
			);

			$file['error'] =
				'File exceeds 5MB limit.';

			return $file;
		}

		/*
		|--------------------------------------------------------------------------
		| Extension Validation
		|--------------------------------------------------------------------------
		*/

		$allowed_extensions = [
			'jpg',
			'jpeg',
			'png',
			'pdf',
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

			DSU_Logger::log(
				$file['name'],
				'application/octet-stream',
				'failed',
				'Invalid file extension'
			);

			$file['error'] =
				'Invalid file extension.';

			return $file;
		}

		/*
		|--------------------------------------------------------------------------
		| MIME Validation
		|--------------------------------------------------------------------------
		*/

		if ( ! function_exists( 'finfo_open' ) ) {

			DSU_Logger::log(
				$file['name'],
				'unknown',
				'failed',
				'Server MIME validation unavailable'
			);

			$file['error'] =
				'Server MIME validation unavailable.';

			return $file;
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
			'application/pdf',
		];

		if (
			! in_array(
				$mime,
				$allowed_mimes,
				true
			)
		) {

			DSU_Logger::log(
				$file['name'],
				$mime,
				'failed',
				'Invalid MIME type'
			);

			$file['error'] =
				'Invalid MIME type.';

			return $file;
		}

		/*
		|--------------------------------------------------------------------------
		| Magic Number Validation
		|--------------------------------------------------------------------------
		*/

		if (
			! $this->validate_magic_number(
				$file,
				$extension
			)
		) {

			DSU_Logger::log(
				$file['name'],
				$mime,
				'failed',
				'Invalid file signature'
			);

			$file['error'] =
				'Invalid file signature.';

			return $file;
		}

		/*
		|--------------------------------------------------------------------------
		| Success Log
		|--------------------------------------------------------------------------
		*/

		DSU_Logger::log(
			$file['name'],
			$mime,
			'success',
			'Validation passed'
		);

		return $file;
	}

	private function validate_magic_number(
		$file,
		$extension
	) {

		if (
			empty( $file['tmp_name'] ) ||
			! file_exists(
				$file['tmp_name']
			)
		) {

			return false;
		}

		$handle = fopen(
			$file['tmp_name'],
			'rb'
		);

		if ( ! $handle ) {
			return false;
		}

		switch ( $extension ) {

			case 'png':

				$signature =
					bin2hex(
						fread(
							$handle,
							4
						)
					);

				fclose(
					$handle
				);

				return (
					$signature ===
					'89504e47'
				);

			case 'jpg':

			case 'jpeg':

				$signature =
					bin2hex(
						fread(
							$handle,
							3
						)
					);

				fclose(
					$handle
				);

				return (
					$signature ===
					'ffd8ff'
				);

			case 'pdf':

				$signature =
					fread(
						$handle,
						4
					);

				fclose(
					$handle
				);

				return (
					$signature ===
					'%PDF'
				);
		}

		fclose(
			$handle
		);

		return false;
	}
}