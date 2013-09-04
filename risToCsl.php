<?php
    class risToCsl_Mapper {
    	function __construct () {
	}
	
	public function risToCsl ( $risArr, $debug ) {
		
	    // csl-data-json-schema
	    
	    /// https://github.com/citation-style-language/schema/blob/master/csl-data.json		
		
	    // examples for valid csl
	    //
	    // https://bitbucket.org/fbennett/citeproc-js/src/9f26d1e1972084addbc678e0475cf11bf1b48259/demo/sampledata.html?at=default
	    //
	    if ($debug) {
		echo "<h3>Komplettes RIS: </h3>";
		var_dump($risArr);
	    }
	    
	    
	    // map the ris to csl
	    $cslRecord = array();

	    /*
		++ TY  -	`DICT`
		++ A2  -	Editor (Secondary Author)
		++ A3  -       Tertiary Author
		++ A4  -	Translator (Subsidiary Author)
		++ AB  -	Abstract
		-- AD  -	Author Address (not available in gso)
	    AN  -	Accession Number
		++ AU  -	Author
	    C1  - 	Term
	    CA  -	Caption
		--	CN  -	Call Number (not available in gso)
		++ CY  -	City
	    DB  -	Name of Database
	    DO  -	DOI
		++ DP  -	Database Provider (not available in gso)
	    ET  -	Edition
		++ EP -	Ending page number
	    J2  -	Abbreviation
		++ KW  -	Keywords
		++ JF - 	Periodical full name
		++ IS - 	Issue number
	    L1  -	File Attachments
	    L4  -	Figure
	    LA  -	Language
	    LB  -	Label
	    M1  -	Number
	    M3  - 	Type of Work
		++ N1  -	Notes
	    NV  -	Number of Volumes
	    OP  -	Original Publication
		++ PB  -	Publisher
		++ PY  -	Year
	    RI  -	Reviewed Item
	    RN  -	Research Notes
	    RP  -	Reprint Edition
	    SE  - 	Version
		++ SN  -	ISBN
		++ SP  -	Pages
	    ST  -	Short Title
		++ TI  -	Title
		++ T1  - Title
		++ T2  - Dictionary Title
		++ T3  - Title Series
		-- TA  -	Translated Author (nicht in gso)		
	    TT  -	Translated Title
		++ UR  -	URL
		++ VL  -	Volume
	    Y2  -	Access Date
		++ ER  -	{IGNORE}
	    
		++ U1 - type of resource (f.e. "Online Ressource (PDF, 339 KB, 4 S.)")
		-- S1 - origin of record (f.e. "Gemeinsamer Bibliotheksverbund (GBV) / Verbundzentrale des GBV (VZG)")

	    */	

	    // VL --> volume
	    $cslRecord['volume'] = $risArr['VL'][0];	
	    unset($risArr['VL']);

	    // IS --> issue-number
	    $cslRecord['issue'] = $risArr['IS'][0];	
	    unset($risArr['IS']);						    			    		    
					    		    
	    // AU/A1/A2/A3/A4 --> author
	    $cslRecord['author'] = array();
	    foreach($risArr['A1'] as $author) {
		    $arr = explode(', ', $author);
		    $nameSet = array();
		    $nameSet['family'] = $arr[0];
		    $nameSet['given'] = $arr[1];
		    $nameSet['static-ordering'] = true;
		    $nameSet = (object) $nameSet;
		    array_push($cslRecord['author'], $nameSet);
		    unset($risArr['A1']);
	    }  
	    foreach($risArr['A2'] as $author) {
		    $arr = explode(', ', $author);
		    $nameSet = array();
		    $nameSet['family'] = $arr[0];
		    $nameSet['given'] = $arr[1];
		    $nameSet['static-ordering'] = true;
		    $nameSet = (object) $nameSet;
		    array_push($cslRecord['author'], $nameSet);
		    unset($risArr['A2']);
	    } 
	    foreach($risArr['A3'] as $author) {
		    $arr = explode(', ', $author);
		    $nameSet = array();
		    $nameSet['family'] = $arr[0];
		    $nameSet['given'] = $arr[1];
		    $nameSet['static-ordering'] = true;
		    $nameSet = (object) $nameSet;
		    array_push($cslRecord['author'], $nameSet);
		    unset($risArr['A3']);
	    }  	
	    foreach($risArr['A4'] as $author) {
		    $arr = explode(', ', $author);
		    $nameSet = array();
		    $nameSet['family'] = $arr[0];
		    $nameSet['given'] = $arr[1];
		    $nameSet['static-ordering'] = true;
		    $nameSet = (object) $nameSet;
		    array_push($cslRecord['author'], $nameSet);
		    unset($risArr['A4']);
	    }  		    	     		
	    foreach($risArr['AU'] as $author) {
		    $arr = explode(', ', $author);
		    $nameSet = array();
		    $nameSet['family'] = $arr[0];
		    $nameSet['given'] = $arr[1];
		    $nameSet['static-ordering'] = true;
		    $nameSet = (object) $nameSet;
		    array_push($cslRecord['author'], $nameSet);
		    unset($risArr['AU']);
	    }  
	    
	    // TI --> Titel
	    $titles = array();
	    array_push($titles, $risArr['TI'][0]);
	    unset($risArr['TI']);
	    
	    // T1 --> Titel
	    array_push($titles, $risArr['T1'][0]);
	    unset($risArr['T1']);	  
	    
	    // T2 --> Titel
	    array_push($titles, $risArr['T2'][0]);
	    unset($risArr['T2']);	
	    
	    // T3 --> Titel
	    array_push($titles, $risArr['T3'][0]);
	    
	    $titles = array_filter($titles);
	    $cslRecord['title'] = implode('. ', $titles);	    
	    
	    // T3 (Series-Title) --> collection-title 
	    $cslRecord['collection-title'] = $risArr['T3'][0];	
	    unset($risArr['T3']);								
	    
	    // JF (Periodical full name) --> container-title
	    $cslRecord['container-title'] = $risArr['JF'][0];	
	    unset($risArr['JF']);		    
	    
	    // TY --> Type
	    $cslRecord['type'] = $risArr['TY'][0];
	    unset($risArr['TY']);
	    
	    // PB --> Publisher
	    $cslRecord['publisher'] = $risArr['PB'][0];
	    unset($risArr['PB']);

	    // CY --> Publisher-Place
	    $cslRecord['publisher-place'] = $risArr['CY'][0];	
	    unset($risArr['CY']);
		    
	    // N1 --> note
	    $cslRecord['note'] = $risArr['N1'][0];	
	    unset($risArr['N1']);			    
		    
	    // N2 --> Abstract (depricated? not in ris-doku)
	    $cslRecord['abstract'] = $risArr['N2'][0];	    
	    unset($risArr['N2']);
	    	
	    // AB --> Abstract
	    $cslRecord['abstract'] = $risArr['AB'][0];	
	    unset($risArr['AB']);		    
		    
	    // SN --> ISSN/ISBN
	    $cslRecord['ISBN'] = $risArr['SN'][0];	
	    unset($risArr['SN']);		

	    // PY --> publication-year
	    $year['date-parts'][0][0] = intval(preg_replace('/[^0-9]/','',$risArr['PY'][0]));
	    $cslRecord['issued'] = (object)$year;		    
	    unset($risArr['PY']);			

	    // ID --> id
	    $cslRecord['id'] = 'http://uri.gbv.de/document/gvk:ppn:' . $risArr['ID'][0];	
	    unset($risArr['ID']);	

	    // UR --> URL
	    // multible urls available, but we chose only the first one. more don´t make sense. First url = most important
	    $cslRecord['URL'] = $risArr['UR'][0];	
	    unset($risArr['UR']);		    					    

	    // U1 --> medium
	    $cslRecord['medium'] = $risArr['U1'][0];	
	    unset($risArr['U1']);

	    // SP --> startpage
	    $startpage = $risArr['SP'][0];	
	    unset($risArr['SP']);		

	    // EP --> endpage
	    $endpage = $risArr['EP'][0];	
	    unset($risArr['EP']);		
	    
	    // set pages
	    if ($startpage != '' && $endpage != '') {
	    	$pages = $startpage . '-' . $endpage;
	    }
	    else {
	    	$pages = $startpage;
	    }
	    $cslRecord['page'] = $pages;					

	    // KW --> keywords
	    // until now not used in any style. doku unclear
	    $cslRecord['keyword'] = array();
	    foreach($risArr['KW'] as $KW) {
		    array_push($cslRecord['keyword'], $KW);		    
	    }  	    	
	    unset($risArr['KW']);				    
						    		    	    
	    // delete "recordEnd"-tags
	    unset($risArr['S1']);
	    unset($risArr['0']);			
	    unset($risArr['ER']);			
									    
	    $cslRecord = (object) $cslRecord;
	    
	    if ($debug) {
		// delete...
		echo "<h3>Noch nicht gemappte RIS-Teile: </h3>";
		var_dump($risArr);

		echo '<hr /><h3>cslRecord als JSON:</h3>';	
		echo $this->prettyPrintJSON(json_encode($cslRecord));		    	    
	    }
	    return $cslRecord;
	}
	
	public function prettyPrintJSON( $json ) {
	    $result = '';
	    $level = 0;
	    $prev_char = '';
	    $in_quotes = false;
	    $ends_line_level = NULL;
	    $json_length = strlen( $json );

	    for( $i = 0; $i < $json_length; $i++ ) {
		$char = $json[$i];
		$new_line_level = NULL;
		$post = "";
		if( $ends_line_level !== NULL ) {
		    $new_line_level = $ends_line_level;
		    $ends_line_level = NULL;
		}
		if( $char === '"' && $prev_char != '\\' ) {
		    $in_quotes = !$in_quotes;
		} else if( ! $in_quotes ) {
		    switch( $char ) {
			case '}': case ']':
			    $level--;
			    $ends_line_level = NULL;
			    $new_line_level = $level;
			    break;

			case '{': case '[':
			    $level++;
			case ',':
			    $ends_line_level = $level;
			    break;

			case ':':
			    $post = " ";
			    break;

			case " ": case "\t": case "\n": case "\r":
			    $char = "";
			    $ends_line_level = $new_line_level;
			    $new_line_level = NULL;
			    break;
		    }
		}
		if( $new_line_level !== NULL ) {
		    $result .= "\n".str_repeat( "\t", $new_line_level );
		}
		$result .= $char.$post;
		$prev_char = $char;
	    }

	    return $result;
	}
	
    }
?>