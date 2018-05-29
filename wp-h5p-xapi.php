<?php

require_once __DIR__ . "/src/utils/Template.php";
require_once __DIR__ . "/plugin.php";

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

function h5pxapi_enqueue_scripts()
{
    wp_register_script("wp-h5p-xapi", plugins_url() . "/wp-h5p-xapi/wp-h5p-xapi.js", array("jquery"), filemtime(__FILE__ ), true);
    wp_enqueue_script("wp-h5p-xapi");

    wp_register_style("wp-h5p-xapi", plugins_url() . "/wp-h5p-xapi/wp-h5p-xapi.css", array(), filemtime(__FILE__ ), 'all');
    wp_enqueue_style("wp-h5p-xapi");

    $xapi_js_settings = array();

    $settings = h5pxapi_get_auth_settings();

    $xapi_js_settings['ajax_url'] = admin_url('admin-ajax.php');

    // Permalink is not available in the admin interface.
    if (get_permalink()) {
        $xapi_js_settings['context_activity'] = array(
            'id' => get_permalink(),
            'definition' => array(
                'type' => 'http://activitystrea.ms/schema/1.0/page',
                'name' => array(
                    'en' => wp_title("|", false),
                ),
                'moreInfo' => get_permalink(),
            ),
        );
    }

    wp_localize_script('wp-h5p-xapi', 'xapi_settings', $xapi_js_settings);

}

/**
 * Create the admin menu.
 */
function h5pxapi_admin_menu()
{
    $settings = apply_filters("h5p-xapi-auth-settings", null);

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
  register_setting("h5pxapi", "h5pxapi_alerts");

}

/**
 * Create settings page.
 */
function h5pxapi_create_settings_page()
{
    wp_register_style("wp-h5p-xapi", plugins_url() . "/wp-h5p-xapi/wp-h5p-xapi.css");
    wp_enqueue_style("wp-h5p-xapi");

    $template = new Template(__DIR__ . "/src/template/settings.tpl.php");
    $template->show();
}

function h5pxapi_event_handler()
{
    require_once __DIR__.'/process-xapi-statement.php';
    wp_die();
}

add_action('wp_enqueue_scripts', 'h5pxapi_enqueue_scripts');
add_action('admin_enqueue_scripts', 'h5pxapi_enqueue_scripts');
add_action('admin_menu', 'h5pxapi_admin_menu');
add_action('admin_init', 'h5pxapi_admin_init');
add_action('wp_ajax_xapi_event', 'h5pxapi_event_handler');
add_action('wp_ajax_nopriv_xapi_event', 'h5pxapi_event_handler');

function h5pxapi_response_message($message)
{
    global $h5pxapi_response_message;

    $h5pxapi_response_message = $message;
}
