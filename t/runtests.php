#!/usr/bin/php
<?php

# you might run this with
# $ prove -fn t/runtests.php

include_once 'cslregistry.php';
include_once 'cslapi.php';
include_once 'citeproc-php/CiteProc.php';

$n = 1;

// collect JSON files
$jsonfiles = array();
$dir = opendir('./t/');
while (FALSE !== ($file = readdir($dir))) {
	if (is_dir($file) || !preg_match('/^([a-z0-9-_]+)\.json$/',$file)) {
		continue;
	}
	$jsonfiles[substr($file,0,-5)] = "t/$file";
}

// test with each file
foreach ($jsonfiles as $name => $file) {
	$json = file_get_contents($file);
    $test = json_decode($json);

	if (!$test || json_last_error()) {
		echo "not ok $n - $name failed to load JSON\n"; 
		continue;
	}

	$diag = run_test($test);
	if ( $diag ) {
		print "not ok $n - $name\n";
		foreach ( explode("\n",$diag) as $line ) {
			print "  $line\n";
		}
		print "  ---\n";

	} else {
        print "ok $n - $name\n";
	}

	$n++;
}

echo "1..".($n-1)."\n";

function run_test($test) {

	if ( $test->style ) {
		$registry = new CSL_Registry("./");
		$csl = $registry->get_style_xml( $test->style );
		if (!$csl) {
			return "Failed to load style {$test->style}";
		}
    	$citeproc = new citeproc($csl);
	}

    $input_data  = (array)$test->input;
    $output = '';
    foreach($input_data as $data) {
		$html = $citeproc->render($data, 'bibliography');
		$html = preg_replace('/^<div class="csl-bib-body">(.*)<\/div>$/', '$1', $html);
		$output .= $html;
    }
	
	if ($output != $test->result) {
		return "got:    $output \nexpect: {$test->result}\n";
    }

	return;
}

?>
