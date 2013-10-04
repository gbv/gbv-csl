<?php

error_reporting(E_ERROR | E_PARSE);
set_include_path(get_include_path() . PATH_SEPARATOR .__DIR__.'/..');

include_once 'cslProvider.php';

class BasicTest extends PHPUnit_Framework_TestCase {

    public function testOk() {
        $this->assertTrue(TRUE);
    }

    public function testExample() {
        $get = array(
            "query"         => "pica.isb=3-11-023209-X",
            "citationstyle" => "ieee",
            "language"      => "de"
        );
        $cslProvider = new cslProvider();
        $this->assertTrue( !! $cslProvider->init($get) );
        $citations = $cslProvider->buildCitations();       

        $this->assertEquals( $citations, '["http:\/\/",["J.  Bergmann und Danowski, P., \u201eHandbuch Bibliothek 2.0. Bibliothekspraxis ; 41\u201c. de Gruyter Saur, Berlin [u.a.], 2010.","P.  Danowski und Bergmann, J., \u201eHandbuch Bibliothek 2.0\u201c. Walter de Gruyter GmbH  Co.KG, [s.l.], 2010."],["Buch","Elektronisches Ressource"],["http:\/\/uri.gbv.de\/document\/gvk:ppn:629400288","http:\/\/uri.gbv.de\/document\/gvk:ppn:658709062"]]');
    }
}

?>
