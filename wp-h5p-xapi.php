<?php

require_once __DIR__."/src/utils/Template.php";
require_once __DIR__."/plugin.php";

use h5pxapi\Template;

/*
Plugin Name: H5P xAPI
Plugin URI: http://github.com/tunapanda/wp-h5p-xapi
GitHub Plugin URI: https://github.com/tunapanda/wp-h5p-xapi
Description: Send H5P achievements to an xAPI repo.
Version: 0.1.6
*/

/**
 * Enqueue scripts and stylesheets.
 */
function h5pxapi_enqueue_scripts() {
	wp_register_script("wp-h5p-xapi",plugins_url()."/wp-h5p-xapi/wp-h5p-xapi.js",array("jquery"));
	wp_enqueue_script("wp-h5p-xapi");

	wp_register_style("wp-h5p-xapi",plugins_url()."/wp-h5p-xapi/wp-h5p-xapi.css");
	wp_enqueue_style("wp-h5p-xapi");

	$s="";
	$s.="<script>\n";

	$settings=h5pxapi_get_auth_settings();

	if ($settings && $settings["endpoint_url"])
		$s.="WP_H5P_XAPI_STATEMENT_URL='".plugins_url()."/wp-h5p-xapi/process-xapi-statement.php';";

	else
		$s.="WP_H5P_XAPI_STATEMENT_URL=null;";

	$s.="\n";

	// Permalink is not available in the admin interface.
	if (get_permalink()) {
		$s.="WP_H5P_XAPI_CONTEXTACTIVITY= {
			'id': '".get_permalink()."',
			'definition': {
				'type': 'http://activitystrea.ms/schema/1.0/page',
				'name': {
					'en': '".wp_title("|",FALSE)."'
				},
				'moreInfo': '".get_permalink()."'
			}
		};";
	} else {
		$s.="WP_H5P_XAPI_CONTEXTACTIVITY=null;";
	}
	$s.="\n";
	$s.="</script>";

	echo $s;
}

/**
 * Create the admin menu.
 */
function h5pxapi_admin_menu() {
	$settings=apply_filters("h5p-xapi-auth-settings",NULL);

	if (!$settings) {
		add_options_page(
			'H5P xAPI',
			'H5P xAPI',
			'manage_options',
			'h5pxapi_settings',
			'h5pxapi_create_settings_page'
		);
	}
}

/**
 * Admin init.
 */
function h5pxapi_admin_init() {
	register_setting("h5pxapi","h5pxapi_endpoint_url");
	register_setting("h5pxapi","h5pxapi_username");
	register_setting("h5pxapi","h5pxapi_password");
}

/**
 * Create settings page.
 */
function h5pxapi_create_settings_page() {
	wp_register_style("wp-h5p-xapi",plugins_url()."/wp-h5p-xapi/wp-h5p-xapi.css");
	wp_enqueue_style("wp-h5p-xapi");

	$template=new Template(__DIR__."/src/template/settings.tpl.php");
	$template->show();
}

add_action('wp_enqueue_scripts','h5pxapi_enqueue_scripts');
add_action('admin_enqueue_scripts','h5pxapi_enqueue_scripts');
add_action('admin_menu','h5pxapi_admin_menu');
add_action('admin_init','h5pxapi_admin_init');

function h5pxapi_response_message($message) {
	global $h5pxapi_response_message;

	$h5pxapi_response_message=$message;
}