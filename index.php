<?php
    header('Cache-Control: no-cache, must-revalidate');
    
    // debug
    if (isset($_GET['debug']) && $_GET['debug'] == 1) {
	$debug = strip_tags($_GET['debug']);
    }
    else {
    	$debug = 0;
    }
    
    if (!$debug) {
	header('Content-Type: application/javascript; charset=utf8');
    }
    
    if ($debug) {
	header('charset=utf8');
	echo '<pre>';
    }
    
    error_reporting(-1);
    ini_set('error_reporting', E_ALL);
    
    // include citationstyle-processor
    include ('citeproc/CiteProc.php');	    
	
    // include risToCsl-Mapper
    include ('risToCsl.php');
    
    // -- Piwik Tracking API init --
    require_once "PiwikTracker.php";
    PiwikTracker::$URL = 'http://piwik.gbv.de/';   
    
    $piwikTracker = new PiwikTracker( $idSite = 18 );
    // Sends Tracker request via http
    $piwikTracker->doTrackPageView('CSL-Webservice');
    
    ///////////////////////////////////////////////////////////////////////////
    // parse $_GET-Parameters
    ///////////////////////////////////////////////////////////////////////////
    
    $error = 0;
    $errorMessages = '';
        
    // debug
    if (isset($_GET['debug']) && $_GET['debug'] == 1) {
	$debug = strip_tags($_GET['debug']);
    }
    else {
    	$debug = 0;
    }	
	
    // database
    if (isset($_GET['database']) && $_GET['database'] != '') {
	$database = strip_tags($_GET['database']);
    }
    else {
    	$database = 'gvk';
    }	

    // query
    if (isset($_GET['query']) && $_GET['query'] != '') {
	$query = strip_tags($_GET['query']);
    }
    else {    	
	$error = 1;
	$errorMessages .= 'Parameter "query" fehlt!<br />';
    }	
    
    // callback
    if (isset($_GET['callback']) && $_GET['callback'] != '') {
	$callback = strip_tags($_GET['callback']);
    }
    else {    	
	$callback = '';
    }	    
    
    // language
    if (isset($_GET['language']) && $_GET['language'] != '') {
	$language = strip_tags($_GET['language']);
    }
    else {    	
	$error = 1;
	$errorMessages .= 'Parameter "language" fehlt!<br />';
    }	   

    // citationstyle
    if (isset($_GET['citationstyle']) && $_GET['citationstyle'] != '') {
	$citationstyle = strip_tags($_GET['citationstyle']);
	$csl_file = 'styles/' . $citationstyle . '.csl';
	if (file_exists($csl_file)) {
		$csl_data = file_get_contents($csl_file);
		$csl_doc = new DOMDocument();	
		$csl_doc->loadXML($csl_data);			
		$citeproc = new citeproc($csl_data, $language);		
	}
	else {
	    $error = 1;
	    $errorMessages .= 'Gewählten Zitationsstil nicht gefunden!<br />';	    
	}	
    }
    else {
    	$error = 1;
	$errorMessages .= 'Parameter "citationstyle" fehlt!<br />';
    }	

    // count
    if (isset($_GET['count']) && $_GET['count'] != '') {
	$count = strip_tags($_GET['count']);
	if (! is_numeric($count)) {
	    $error = 1;
	    $errorMessages .= 'Ungültige Angabe für Parameter "count"!<br />';		
	}
    }
    else {
    	$count = '10';
    }	    
    
    // highlight?
    if (isset($_GET['highlight']) && $_GET['highlight'] != '') {
    	if ($_GET['highlight'] == 1 || $_GET['highlight'] == 0) {
	    $highlight = strip_tags($_GET['highlight']);
	}
	else {
	    $highlight = '0';
	}		
    }
    else {
    	$highlight = '0';
    }	       

    // error-Messages
    if ($error) {
    	echo "Ungültige Anfrage: <br />" . $errorMessages;
	return;
    }	
    
    ///////////////////////////////////////////////////////////////////////////
    // SRU-Search with given parameters
    ///////////////////////////////////////////////////////////////////////////
	
    // start search and get mods-records as a first result
    $sruData = file_get_contents('http://sru.gbv.de/' . $database . '?recordSchema=mods&version=1.1&operation=searchRetrieve&startRecord=1&maximumRecords=' . $count . '&query=' . $query);    
    
    // get rid of namespace
    $sruData = str_replace('<zs:', '<zs_', $sruData);
    $sruData = str_replace('</zs:', '</zs_', $sruData);
    
    // get the recordIDs
    $sruXML = new DOMDocument();	
    $sruXML->loadXML($sruData);    
    
    // get list of all ppns
    $ppnNodeList = $sruXML->getElementsByTagName('recordIdentifier');

    // prepare result (follows open search standard: http://www.opensearch.org/Specifications/OpenSearch/Extensions/Suggestions/1.1#Example)
    $resultObj = array();
    $resultObj[0] = 'http://' . $_SERVER[HTTP_HOST] . $_SERVER[REQUEST_URI];
    $resultObj[1] = array();
    $resultObj[2] = array();			
    $resultObj[3] = array();	    

    // for each found ppn do 
    foreach ($ppnNodeList as $ppn) {
    	
	// get the RIS-data 
    	$ris = file_get_contents('http://unapi.gbv.de/?id=gvk:ppn:' . $ppn->nodeValue . '&format=ris-legacy');
	
	// ris to array
	$risArr = array();
	foreach(preg_split("/((\r?\n)|(\r\n?))/", $ris) as $line){	    
	    $notation = substr($line,0,2);	    
	    if (!$risArr[$notation]) {
	    	$risArr[$notation] = array();
	    }
	    $content = substr($line, strpos($line, '  - ') + 4);
	    array_push($risArr[$notation], $content);
	} 		
	
	// do mapping
	$risToCslTransformer = new risToCsl_Mapper();	
	$cslRecord = $risToCslTransformer->risToCsl($risArr, $debug);
	
	// render citation-string
	$citation = $citeproc->render($cslRecord);
	
	// append to result
	array_push($resultObj[1], $citation);
	array_push($resultObj[3], 'http://uri.gbv.de/document/gvk:ppn:' . $ppn->nodeValue);	
	
	if ($debug) {
	    echo "<br /><h3>--> Zitation:<br />" . $citation . "<hr /></h3>";
	}
    }
    
    // result has to be json
    $result = json_encode($resultObj);
    
    // add callback-function?
    if ($callback != '') {
    	$result = $callback . '(' . $result . ');';
    }
    
    // output result
    echo $result;
?>