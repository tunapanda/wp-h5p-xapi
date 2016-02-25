<?php

/**
 * This file contains functionality for the rest of the files.
 */

/**
 * The authentication settings can be provided either by another
 * plugin, if that plugin implements the h5p-xapi-auth-settings filter.
 * If there is no plugin implementing the filter, the settings 
 * will come from the settings page.
 */
function h5pxapi_get_auth_settings() {
	$settings=apply_filters("h5p-xapi-auth-settings",NULL);

	if (!$settings) {
		$settings=array(
			"endpoint_url"=>get_option("h5pxapi_endpoint_url"),
			"username"=>get_option("h5pxapi_username"),
			"password"=>get_option("h5pxapi_password")
		);
	}

	return $settings;
}