<?php
    header('Cache-Control: no-cache, must-revalidate');
    
    // debug
    if (isset($_GET['debug']) && $_GET['debug'] == 1) {
        $debug = strip_tags($_GET['debug']);
    }
    else {
        $debug = 0;
    }
    
    // Content-Type depends on debug-Parameter
    if ($debug) {
        header('Content-Type: text/html; charset=utf8');
        echo '<pre>';
    }
    else {
        header('Content-Type: application/javascript; charset=utf8');
    }
       
    // -- Piwik Tracking API init --
    //require_once "PiwikTracker.php";
    
    require_once 'cslProvider.php';
 
    $cslProvider = new cslProvider();
    
    if ($cslProvider->init($_GET)) {
       // $cslProvider->trackPiwik();
        $result = $cslProvider->buildCitations();       
        echo $result;
    }
?>
