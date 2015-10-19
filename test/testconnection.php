<?php

require_once __DIR__."/../utils.php";

$endpoint="http://localhost/repo/learninglocker/public/data/xAPI/";
$username="7b880fc1f371715ce24309b90e051fcd24d700c3";
$password="c089ce76ca667862e615995b909f2ddf9acc1795";

$res=checkConnection($endpoint, $username, $password);

print_r($res);

// testing
/*if (array_key_exists("statements",$decoded))
	echo "works...";

else
	echo "fails..";*/
