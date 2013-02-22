<?php

/**
 * Query an SRU server and return a DOMNodeList with MODS XML elements.
 */
function get_mods_via_sru( $server, $cql, $args = array() ) {
    $args['recordSchema']   = 'mods';
    $args['version']        = '1.1';
    $args['operation']      = 'searchRetrieve';
    $args['startRecord']    = 1;
    $args['maximumRecords'] = isset($config['maximumRecords'])
                            ? $config['maximumRecords'] : 10;
    $args['query']          = $cql;
    
    $url = $server. '?'.  http_build_query($args);
    $xml = @file_get_contents($url);
    if (!$xml) return array();

    $records = array();
    $dom = new DOMDocument();
    $dom->loadXML($xml);
    $ns = "http://www.loc.gov/zing/srw/";
    $rec = $dom->documentElement; 

    $rec = $dom->documentElement->getElementsByTagNameNS($ns,"records")->item(0);
    if ($rec) $rec = $rec->getElementsByTagNameNS($ns,"record");
    if ($rec) {
        for($i=0; $i<$rec->length; $i++) {
            $r = $rec->item($i)->getElementsByTagNameNS($ns,"recordData")->item(0)->firstChild;
            $records[] = $r;
        }
    }

    return $records;
}

?>
