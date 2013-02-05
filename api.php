<?php

/************************************
 * gbv-csl/api.php - Citation server
 *
 * @author Jakob Voss
 * @date   2013-02-05
 */

/**
 * Utility class to extract values from MODS records.
 */
class MODSMapper {
    private $records;
    private $xpath;
    private $position = 0;

    public function __construct( $records ) {
        $this->records = $records;
        if (count($records)) {
            $this->xpath = new DOMXpath($this->records[0]->ownerDocument);
            $this->xpath->registerNamespace("m","http://www.loc.gov/mods/v3");
        }
    }

    public function next() {
        return ++$this->position;
    }

    public function current() {
        return $this->records[$this->position];
    }

    public function valid() {
        return isset($this->records[$this->position]);
    }

    public function value($query,$context = null) {
        if (!$this->valid()) return;
        $query = "normalize-space($query)";
        return $this->xpath->evaluate($query, $context ? $context : $this->current());
    }

    public function nodes($query) {
        if (!$this->valid()) return;
        return $this->xpath->query($query,$this->current());
    }
}

/**
 * Map MODS to JSON for CSL.
 *
 * @see http://bibliographie-trac.ub.rub.de/wiki/CiteProc-JS
 * @see https://github.com/zotero/translators/blob/master/MODS.js 
 */
function map_mods_records( $records, $dbkey ) {
    $mapped = array();
    if (!$records or !count($records)) return $mapped;

    $mapper = new MODSMapper( $records );
    while( $mapper->valid() ) {

        $title = $mapper->value('m:titleInfo/m:title');
        // TODO: prepend nonSort

        $year  = $mapper->value("m:originInfo/m:dateIssued");
        // TODO: if $year != \d\d\d\d        
        $year  = (int)$year;

        $ppn   = $mapper->value('m:recordInfo/m:recordIdentifier[@source="DE-601"]');
        $id    = "http://uri.gbv.de/document/$dbkey:ppn:$ppn";

        $record = array(
            "type" => "book",
            "title" => $title,
    		"issued" => array(
	    		"date-parts" => array(
			    	array( $year )
			    )
            )
        );

        $authors = array();
        $anodes = $mapper->nodes('m:name[@type="personal"]');
        foreach( $anodes as $node ) {
            $person = array(
                "family" => $mapper->value('m:namePart[@type="family"]',$node),
                "given"  => $mapper->value('m:namePart[@type="given"]',$node)
            );
            $authors[] = $person;
        }
        $record['author'] = $authors;

        $edition = $mapper->value('m:originInfo/m:edition');
        if ($edition) {
            // TODO: make number
            $record['edition'] = $edition;
        }

        $isbn = $mapper->value('m:identifier[@type="isbn"]');
        if ($isbn) {
            $record['ISBN'] = $isbn;
        }

        $publisher = $mapper->value('m:originInfo/m:publisher');
        if ($publisher) {
            $record['publisher'] = $publisher;
        }
        $place = $mapper->value('m:originInfo/m:place/m:placeTerm[@type="text"]');
        if ($place) {
            $record['publisher-place'] = $place;
        }

        $record['id'] = $id;
        $mapped[$id] = $record;

        $mapper->next();
    }

    return $mapped;
}

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

/**
 * Serialize and send JSON or JSONP.
 */
function send_json( $data ) {
    if (!$data) $data = new stdClass();

    if (isset($_GET['callback'])) {
        $callback = $_GET['callback'];
        if (preg_match('/^[a-zA-Z0-9_]+$/', $callback)) {
            header('Content-Type: text/javascript; charset=utf-8');
            header('access-control-allow-origin: *');
            echo $callback . '(' . json_encode($data) . ')';
            return;
        } else {
            $data = array("error" => 400);
            header(':', true, 400);
        }
    }

    header('Content-type: application/json; charset=utf-8');
    header('access-control-allow-origin: *');
    echo json_encode($data);
}

////////////

$data = null;

if (isset($_GET['list'])) {
    $list = $_GET['list'];
    if ($list == "styles") {
        $files = scandir('./styles');
        $data["stylenames"] = array();
        foreach ($files as $file) {
            if (preg_match('/^(.+)\.csl$/',$file,$match)) {
                $data["stylenames"][] = $match[1];
            }
        }
        // HACK:
//        $data["stylenames"] = explode(',',"ieee,ieee-w-url,din-1505-2,din-1505-2-numeric,diplo,tgm-wien-diplom,tah-soz,cell-calcium,cell-numeric,hand,harvard-cardiff-university,harvard-european-archaeology");
    }
}


////////////////////////////////////////////
// GET CSL style from CSL style repository
if (isset($_GET['style'])) {
    $style = $_GET['style'];

    if (!preg_match('/^[a-zA-Z0-9-]+$/', $style)) {
        response_code(400);
    } else if ($xml = @file_get_contents("./styles/$style.csl")) {
        $xml = preg_replace('/^<\?xml.+\n/i','',$xml);
        $data['styles'] = array( $style => $xml );
    } else {
        $data['error']['style'] = 'not found';
        header(':', true, 404);
    }
}

/////////////////////////////////////////////////////////
// GET citeproc-js locales (from citeproc-js repository)

if(isset($_GET['locale'])) {
    $locale = $_GET['locale'];
    if (!preg_match('/^[a-z][a-z](-[A-Z][A-Z])?$/', $locale)) {
        response_code(400);
    } else if ($xml = @file_get_contents("./locales/locales-$locale.xml")) {
        $xml = preg_replace('/^<\?xml.+\n/i','',$xml);
        $data['locales'] = array( $locale => $xml );
    } else {
//        $data['locales'] = null;
        $data['error']['locale'] = 'not found';
        header(':', true, 404);
    }
}

if(isset($_GET['abbrev'])) {
    // TODO
}

if(isset($_GET['cql'])) {
    $cql   = $_GET['cql'];
    $dbkey = isset($_GET['dbkey']) ? $_GET['dbkey'] : 'gvk';
    $records = get_mods_via_sru("http://sru.gbv.de/$dbkey",$cql);
    $data['items'] = map_mods_records($records, $dbkey);
}

send_json($data);

?>
