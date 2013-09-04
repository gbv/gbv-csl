<?php
    class typeTranslator {
    	
    	protected $xml;
	
    	function __construct () {
	    $this->xml = simplexml_load_file('typeTranslation.xml');
	}
	
	public function translate ( $notation, $language = 'de' ) {
	    $translation = '';
	    $translation = $this->xml->xpath('/types/type[@notation="' . $notation . '"]/term[@lang="' . $language . '"]');
	    $translation = strval($translation[0]);
	    return $translation;
	}
    }
?>