<?php

if (php_sapi_name() == 'cli') { // for testing at command line
	array_shift($argv);
	foreach ($argv as $arg) {
		if (preg_match('/([^=]+)=(.*)/', $arg, $match)) {
			$_GET[$match[1]] = $match[2];
		}
	}
}

include_once 'cslapi.php';

$api = new CSL_API();

include_once 'cslregistry.php';
include_once 'modstransformer.php';

$api->registry    = new CSL_Registry();
$api->transformer = new MODS_Transformer();

$api->process();
$api->respond();

?>
