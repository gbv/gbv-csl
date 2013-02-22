<?php

class CSL_Registry {
	private $base;

	public function __construct( $base = NULL ) {
		$this->base = $base ? $base : ".";
	}

	public function get_style_xml( $name ) {
		$xml = @file_get_contents( $this->base . "/styles/$name.csl" );
		return $xml;
	}

	public function get_locale_xml( $locale ) {
		$xml = @file_get_contents( $this->base . "/locales/locales-$locale.xml" );
		return $xml;
	}

	public function list_styles() {
		$styles = array();
		$files = scandir( $this->base . "/styles" );
		foreach ($files as $file) {
			if (preg_match('/^(.+)\.csl$/',$file,$match)) {
				$styles[] = $match[1];
			}
		}
		return $styles;
	}
}

?>
