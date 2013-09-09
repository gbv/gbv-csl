<?php

class cslProvider {
    private $debug = '';
    private $database = '';
    private $query = '';
    private $count = '';
    private $callback = '';
    private $language = '';
    private $highlight = '';
    private $nohtml = '';
    private $citeproc = '';
    
    function __construct () {
    }
    
    // used for highlighing
    private function ext_str_ireplace($findme, $replacewith, $text) {
        // Replaces $findme in $subject with $replacewith
        // Ignores the case and do keep the original capitalization by using $1 in $replacewith         
        $rest = $text;         
        $result = '';         
        while (mb_stripos($rest, $findme) !== false) {
          $pos = mb_stripos($rest, $findme);         
          // Remove the wanted string from $rest and append it to $result
          $result .= mb_substr($rest, 0, $pos);
          $rest = mb_substr($rest, $pos, mb_strlen($rest)-$pos);         
          // Remove the wanted string from $rest and place it correctly into $result
          $result .= mb_ereg_replace('$1', mb_substr($rest, 0, mb_strlen($findme)), $replacewith);
          $rest = mb_substr($rest, mb_strlen($findme), mb_strlen($rest)-mb_strlen($findme));
        }         
        // After the last match, append the rest
        $result .= $rest;         
        return $result;
    }
    
    // parse $_GET-Parameters
    public function init($get) {
            
        $error = 0;
        $errorMessages = '';
            
        // if not $get
        if (!$get) {
                header('Content-Type: text/html; charset=utf8');
                include("doku.php");
                return 0;
        }
            
        // debug
        if (isset($get['debug']) && $get['debug'] == 1) {
            $this->debug = strip_tags($get['debug']);
        }
        else {
            $this->debug = 0;
        }        
            
        // database
        if (isset($get['database']) && $get['database'] != '') {
            $this->database = strip_tags($get['database']);
        }
        else {
            $this->database = 'gvk';
        }        

        // query
        if (isset($get['query']) && $get['query'] != '') {
            $this->query = urlencode(strip_tags($get['query']));
        }
        else {            
            $error = 1;
            $errorMessages .= 'Parameter "query" fehlt!<br />';
        }        
        
        // callback
        if (isset($get['callback']) && $get['callback'] != '') {
            $this->callback = strip_tags($get['callback']);
        }
        else {            
            $this->callback = '';
        }            
        
        // language
        if (isset($get['language']) && $get['language'] != '') {
            $this->language = strip_tags($get['language']);
        }
        else {            
            $error = 1;
            $errorMessages .= 'Parameter "language" fehlt!<br />';
        }           

        // citationstyle
        if (isset($get['citationstyle']) && $get['citationstyle'] != '') {
            $citationstyle = strip_tags($get['citationstyle']);
            $csl_file = 'styles/' . $citationstyle . '.csl';
            if (file_exists($csl_file)) {
                    $csl_data = file_get_contents($csl_file);                
                    $this->citeproc = new citeproc($csl_data, $this->language);                
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
        if (isset($get['count']) && $get['count'] != '') {
            $this->count = strip_tags($get['count']);
            if (! is_numeric($this->count)) {
                $error = 1;
                $errorMessages .= 'Ungültige Angabe für Parameter "count"!<br />';                
            }
        }
        else {
            $this->count = '10';
        }            
        
        // highlight?
        if (isset($get['highlight']) && $get['highlight'] != '') {
            if ($get['highlight'] == 1 || $get['highlight'] == 0) {
                $this->highlight = strip_tags($get['highlight']);
            }
            else {
                $this->highlight = '0';
            }                
        }
        else {
            $this->highlight = '0';
        }        

        // nohtml? pure string?
        if (isset($get['nohtml']) && $get['nohtml'] != '') {
            if ($get['nohtml'] == 1 || $get['nohtml'] == 0) {
                $this->nohtml = strip_tags($get['nohtml']);
            }
            else {
                $this->nohtml = 0;
            }                
        }
        else {
            $this->nohtml = 0;
        }                                                

        // error-Messages
        if ($error) {
                header('Content-Type: text/html; charset=utf8');
                include("doku.php");
            return 0;
        }        
        return 1;        
    }
    
    public function trackPiwik () {    
        // piwik-server
        PiwikTracker::$URL = 'http://piwik.gbv.de/';   
        // piwik-side-id
        $piwikTracker = new PiwikTracker( $idSite = 18 );            
        // send tracker request via http
        $piwikTracker->doTrackPageView('CSL-Webservice');                
    }
    
    public function buildCitations() {
        // start search and get mods-records as a first result    
        $sruPath = 'http://sru.gbv.de/' . $this->database . '?recordSchema=mods&version=1.1&operation=searchRetrieve&startRecord=1&maximumRecords=' . $this->count . '&query=' . $this->query;
        $sruData = file_get_contents($sruPath);
            
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
        $resultObj[0] = 'http://' . @$_SERVER['HTTP_HOST'] . @$_SERVER['REQUEST_URI'];
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
            $citation = $this->citeproc->render($cslRecord);
            // highlight result?
            if ($this->highlight == 1) {
                    $queryParts = substr($this->query, strpos($this->query, '%3D') + 3);
                    $queryParts = explode('+', $queryParts);                                    
                    
                    foreach ($queryParts as $part) {
                        $citation = preg_replace("/$part/i", "<span class=\"highlight\">\$0</span>", $citation);
                    }                        
            }                

            // nohtml?
            if ($this->nohtml == 1) {
                    $citation = strip_tags($citation);
            }
            
            // translate type
            $translator = new typeTranslator();
            $typeTranslation = $translator->translate($cslRecord->type);

            // append to result
            // citation
            array_push($resultObj[1], $citation);
            // type
            array_push($resultObj[2], $typeTranslation);
            // uri
            array_push($resultObj[3], 'http://uri.gbv.de/document/gvk:ppn:' . $ppn->nodeValue);        
            
            if ($debug) {
                echo "<br /><h3>--> Zitation:<br />" . $citation . "<hr /></h3>";
            }
        }
        
        // order by types    
        array_multisort($resultObj[2], $resultObj[1], $resultObj[3]);

        // result has to be json
        $result = json_encode($resultObj);
        
        // add callback-function?
        if ($this->callback != '') {
            $result = $this->callback . '(' . $result . ');';
        }                
        return $result;
    }
}

?>
