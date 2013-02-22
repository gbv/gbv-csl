<?php

include_once 'jsonapi.php';
include_once 'gbvsru.php';

include_once 'modstransformer.php';
include_once 'citeproc-php/CiteProc.php';

function arrayToObject($array) {
	if (!is_array($array)) return $array;

	$is_obj = 0;
	$obj = new stdClass();
	foreach( $array as $name => $value ) {
		if (!preg_match('/^\d+$/',$name)) {
			$is_obj = 1;
		}
		$obj->$name = arrayToObject($value);
	}
	return $is_obj ? $obj : $array;
}


class CSL_API extends JSON_API {

	protected static $parameters = array(
		'callback' => '^[a-zA-Z0-9_]+$',
		'style'    => '^[a-zA-Z0-9-]+$',
		'locale'   => '^[a-z][a-z](-[A-Z][A-Z])?$',
		'dbkey'    => '^[a-z][a-z0-9]*(-[a-z0-9]+)*$',
		'query'    => '.*',
		'list'     => '^([a-z]+(,[a-z]+)*)?$',
		'items'    => '^([a-z]+(,[a-z]+)*)?$'
	);

	protected function process_args() {
		$req = $this->request;

		$req->list  = isset($req->list) ? explode(',',$req->list) : array();
		$req->items = isset($req->items) ? explode(',',$req->items) : array('input');

		if ( isset($req->query) || isset($req->dbkey) ) {
			if ( !isset($req->query) ) {
				$this->error('missing query parameter');
				unset($req->query);
			} else if ( !isset($req->dbkey) ) {
				$this->error('missing dbkey parameter');
				unset($req->dbkey);
			}
		}
	}

	public $registry;
	public $transformer;

	public function process( ) {
		$req = $this->request;

		if ( isset($req->style) ) {
			if ( $xml = $this->registry->get_style_xml( $req->style ) ) {
				$this->response->styles = (object) array( $req->style => $xml );
			} else {
				$this->error("style not found",404);
			}
		}

		if ( isset($req->locale) ) {
			if ( $xml = $this->registry->get_locale_xml( $req->locale ) ) {
				$this->response->locales = (object) array( ($req->locale) => $xml );
			} else {
				$this->error('locale not found',404);
			}
		}

		if ( in_array('styles',$req->list) ) {
			$this->response->stylenames = $this->registry->list_styles();
		}

		if ( isset( $req->query ) ) {
			$this->process_items();
		}
	}

	private function process_items() {
		$req = $this->request;

		$items = array();

		$dbkey = $req->dbkey;
		$query = $req->query;

		$mods_records = get_mods_via_sru("http://sru.gbv.de/$dbkey",$query);

		if (in_array('mods',$req->items)) {
			$mapper = new MODS_Mapper( $mods_records );
			while( $mapper->valid() ) {
				$ppn = $mapper->value('m:recordInfo/m:recordIdentifier[@source="DE-601"]');
				$id  = "http://uri.gbv.de/document/$dbkey:ppn:$ppn";
				$xml = $mapper->current();
				if (!isset($items[$id])) $items[$id] = new stdClass();
				$items[$id]->mods = $xml->ownerDocument->saveXML($xml);
				$mapper->next();
			}
		}	

		if (in_array('input',$req->items) || in_array('html',$req->items)) {
			$input_records = $this->transformer->transform($mods_records, $dbkey);
	
			foreach ($input_records as $id => $input) {
				$input_records[$id] = arrayToObject($input);
			}

			if (in_array('input',$req->items)) {
				foreach ($input_records as $id => $input) {
					if (!isset($items[$id])) $items[$id] = new stdClass();
					$items[$id]->input = $input;
				}
			}
		}

		if (in_array('html',$req->items)) {

			$csl = null; 
			if ( isset($req->style) ) {
				$style = $req->style;
				$csl = $this->response->styles->$style;
			} else {
				$this->error('missing parameter style');
			}

			if ($csl) {
				if (!in_array('input',$req->items)) {
					unset($this->response->styles);
				} 

				$citeproc = new citeproc($csl);

				foreach($input_records as $id => $input) {
					if (!isset($items[$id])) $items[$id] = new stdClass();
					$html = $citeproc->render($input, 'bibliography');
					$html = preg_replace('/^<div class="csl-bib-body">(.*)<\/div>$/', '$1', $html);
					$items[$id]->html = $html;
				}

			}
		}

		$this->response->items = $items;
	}
}

?>
