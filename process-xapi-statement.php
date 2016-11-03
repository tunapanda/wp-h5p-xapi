<?php

$h5pxapi_response_message=NULL;

/**
 * This file receives the xAPI statement as a http post.
 */

require_once __DIR__."/src/utils/Template.php";
require_once __DIR__."/src/utils/WpUtil.php";
require_once __DIR__."/plugin.php";

use h5pxapi\Template;
use h5pxapi\WpUtil;

require_once WpUtil::getWpLoadPath();

$statementObject=json_decode(stripslashes($_REQUEST["statement"]),TRUE);

if (isset($statementObject["context"]["extensions"]) 
		&& !$statementObject["context"]["extensions"])
	unset($statementObject["context"]["extensions"]);

if (has_filter("h5p-xapi-pre-save")) {
	$statementObject=apply_filters("h5p-xapi-pre-save",$statementObject);

	if (!$statementObject) {
		echo json_encode(array(
			"ok"=>1,
			"message"=>$h5pxapi_response_message
		));
		exit;
	}
}

$settings=h5pxapi_get_auth_settings();
$content=json_encode($statementObject);

//error_log($content);

$url=$settings["endpoint_url"];
if (!trim($url)) {
	echo json_encode(array(
		"ok"=>1,
		"message"=>$h5pxapi_response_message
	));
	exit;
}

if (substr($url,-1)!="/")
	$url.="/";
$url.="statements";

$userpwd=$settings["username"].":".$settings["password"];

$headers=array(
	"Content-Type: application/json",
	"X-Experience-API-Version: 1.0.1",
);

$curl=curl_init();
curl_setopt($curl,CURLOPT_RETURNTRANSFER,TRUE);
curl_setopt($curl,CURLOPT_HTTPHEADER,$headers);
curl_setopt($curl,CURLOPT_USERPWD,$userpwd);
curl_setopt($curl,CURLOPT_URL,$url);
curl_setopt($curl,CURLOPT_POST,1);
curl_setopt($curl,CURLOPT_POSTFIELDS,$content);

$res=curl_exec($curl);
$decoded=json_decode($res,TRUE);
$code=curl_getinfo($curl,CURLINFO_HTTP_CODE);

// We rely on the response to be an array with a single entry
// constituting a uuid for the inserted statement, something like
// ["70de9692-2a4e-4f66-8441-c15ef534b690"].
// Is this learninglocker specific?
if ($code!=200 || sizeof($decoded)!=1 || strlen($decoded[0])!=36) {
	$response=array(
		"ok"=>0,
		"message"=>"Unknown error",
		"code"=>$code
	);

	if ($decoded["message"])
		$response["message"]=$decoded["message"];
		
	if (is_string($res))
		$response["message"] = $res;
	
	if ($res == FALSE) {
		$response["message"] = curl_error($curl);
	}

	echo json_encode($response);
	exit;
}

do_action("h5p-xapi-post-save",$statementObject);

$response=array(
	"ok"=>1,
	"message"=>$h5pxapi_response_message
);

echo json_encode($response);
