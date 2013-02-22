<?php

/**
 * Utility class to extract values from MODS records.
 */
class MODS_Mapper {
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
 * Transforms MODS CSL-JSON.
 *
 * @see http://bibliographie-trac.ub.rub.de/wiki/CiteProc-JS
 * @see https://github.com/zotero/translators/blob/master/MODS.js 
 */
class MODS_Transformer {

	public static function transform( $records, $dbkey ) {
		$mapped = array();
		if (!$records or !count($records)) return $mapped;

		$mapper = new MODS_Mapper( $records );
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
}



?>
