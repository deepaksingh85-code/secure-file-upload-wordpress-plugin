<?php
/**
 * Plugin Name: Deepak Secure Upload
 * Description: Protects WordPress uploads using file size, extension, MIME type and magic number validation.
 * Version: 3.0
 * Author: Deepak Kumar Singh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define(
	'DSU_PLUGIN_PATH',
	plugin_dir_path( __FILE__ )
);

require_once DSU_PLUGIN_PATH . 'includes/class-dsu-installer.php';
require_once DSU_PLUGIN_PATH . 'includes/class-dsu-logger.php';
require_once DSU_PLUGIN_PATH . 'includes/class-dsu-validator.php';
require_once DSU_PLUGIN_PATH . 'admin/class-dsu-admin.php';

register_activation_hook(
	__FILE__,
	[ 'DSU_Installer', 'activate' ]
);

new DSU_Validator();
new DSU_Admin();