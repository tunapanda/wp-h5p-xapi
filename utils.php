<?php

	/**
	 * Check connection to LRS...
	 */
	function checkConnection($endpoint, $username, $password) {
		$url=$endpoint;
		if (substr($url,-1)!="/")
			$url.="/";
		$url.="statements?limit=1";

		$userpwd=$username.":".$password;

		$headers=array(
			"Content-Type: application/json",
			"X-Experience-API-Version: 1.0.1",
		);

		$curl=curl_init();
		curl_setopt($curl,CURLOPT_RETURNTRANSFER,TRUE);
		curl_setopt($curl,CURLOPT_HTTPHEADER,$headers);
		curl_setopt($curl,CURLOPT_USERPWD,$userpwd);
		curl_setopt($curl,CURLOPT_URL,$url);

		$res=curl_exec($curl);
		$decoded=json_decode($res,TRUE);
		$code=curl_getinfo($curl,CURLINFO_HTTP_CODE);

		print_r($decoded);

		if ($code!=200 || !array_key_exists("statements",$decoded)) {
			$message="Unknown error, $code.";

			if ($decoded["message"])
				$message=$decoded["message"];

			return array(
				"success"=>FALSE,
				"message"=>$message." (".$code.")"
			);
		}

		return array(
			"success"=>TRUE,
			"message"=>"The connection seems to be working."
		);
	}
