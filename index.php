<!DOCTYPE html>
<html lang="de">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>GBV CSL</title>
    <link rel="shortcut icon" type="image/x-icon" href="./img/favicon.ico">
	<link rel="stylesheet" type="text/css" href="./css/bootstrap.min.css" />
	<link rel="stylesheet" type="text/css" href="./css/gbv-bootstrap.css" />
	<link rel="stylesheet" type="text/css" href="./css/gbv-csl.css" />
    <script type="text/javascript; e4x=1" src="./js/xmle4x.js"></script> 
	<script type="text/javascript" src="./js/jquery-1.9.0.min.js"></script>
	<script type="text/javascript" src="./js/xmldom.js"></script>
	<script type="text/javascript" src="./js/citeproc.js"></script>
	<script type="text/javascript" src="./js/cslclient.js"></script>
    <script type="text/javascript" src="./js/bootstrap.min.js"></script>
  </head>
  <body>
    <div class="navbar navbar-static-top">
      <div class="navbar-inner">
        <div class="container-fluid">
          <a class="brand" href="https://www.gbv.de/" title="Gemeinsamer Bibliotheksverbund"></a>
          <div class="pull-left" >
            <h1>CSL&#xA0; <small>Formatierte Literaturlisten (BETA!)</small></h1>
          </div>
          <!--ul class="nav pull-right">
            <li class="active"><a href="#">Home</a></li>
            <li><a href="#">Link</a></li>
            <li><a href="#">Link</a></li>
          </ul-->
        </div>
      </div>
    </div>
    <div class="container-fluid">
      <p>
        Mit diesem Webservice können Sie Literaturlisten aus GBV-Katalogen in
        verschiedenen Zitationsstilen anzeigen lassen. Die Formatierung basiert
        auf der <a href="http://citationstyles.org/">Citation Style Language (CSL)</a>.
        Der Webservice befindet sich derzeit in einem frühen Entwicklungstadium und
        sollte daher nicht in Produktivsystemen verwendet werden.
      </p>
    </div>
    <div class="container-fluid">
      <div class="row-fluid">
        <div class="span3">
          <form>
            <div class="control-group">
              <label class="control-label" for="dbkey">Datenbank
                <span><a href="http://uri.gbv.de/database/"><i class="icon-question-sign"></i></a></span>
              </label>
              <div class="input-prepend">
                <span class="add-on"><i class="icon-folder-open"></i></span>
                <input type="text" id="dbkey" placeholder="dbkey" value="gvk">
              </div>
            </div>
            <div class="control-group">
              <label class="control-label" for="query">Anfrage (CQL-Syntax)</label>
              <div class="input-prepend">
                <span class="add-on"><i class="icon-search"></i></span>
                <input type="text" id="query" placeholder="CQL-Anfrage" value="pica.all=Liebe">
              </div>
              </div>
            <div class="control-group">
              <label class="control-label" for="style">Zitationsstil
                <span><a href="http://citationstyles.org/styles/"><i class="icon-question-sign"></i></a></span>
              </label>
              <div class="input-prepend">
                <span class="add-on"><i class="icon-pencil"></i></span>
                <input type="text" id="style" data-provide="typeahead" value="ieee"> 
              </div>
              </div>
            <div class="control-group">
              <label class="control-label" for="locale">Sprache</label>
              <div class="input-prepend">
                <span class="add-on"><i class="icon-flag"></i></span>
                <input type="text" id="locale" placeholder="ISO-Code" value="de-DE">
              </div>
            </div>
          </form>
          <p>
            Die Anzahl der Titel ist derzeit auf 10 beschränkt. Die Zitationsstile funktionieren momentan nicht
            alle. Einige Positivbeispiele: ieee, ieee-w-url, din-1505-2, din-1505-2-numeric, diplo, tgm-wien-diplom,
            tah-soz, cell-calcium, cell-numeric, hand, harvard-cardiff-university, harvard-european-archaeology...
          </p>
        </div>
        <div class="span9">
		  <h2>Literaturliste 
            <small><a href="#" id="sru" style="display:none">via SRU</a></small>
            <small><a href="#" id="api" style="display:none">via CSL-API</a></small>
          </h2>
          <div id="references">[Page generation failure. The bibliography processor requires a browser with Javascript enabled.]</div>
        </div>
      </div>
      <div class="row-fluid">
        <hr>
        <h2>API</h2>
        <p>
		  Für Datenbanken mit SRU-Schnittstelle unter sru.gbv.de gibt es jeweils
		  eine JSON-API unter <code>/{dbkey}</code>, z.B.
		  <a href="./gvk">/gvk</a> für den GVK. Folgende Parameter werden unterstützt:
        </p>
      </div>
      <div class="row-fluid">
        <div class="span6">
          <h3>Anfrage-Parameter</h3>
            <dl>
              <dt>query</dt>
              <dd>CQL-Abfrage</dd>
              <dt>style</dt>
              <dd>Zitationsstil</dd>
              <dt>locale</dt>
              <dd>Spracheinstellungen</dd>
              <dt>list=styles</dt>
              <dd>Verfügbare Zitationsstile</dd>
              <dd>items</dd>
              <dt>Ausgabeformat (Standard: <code>input</code>)
              <dt>callback</dt>
              <dd>JavaScript-Callback für JSONP</dd>
            </dl>
          </p>
         </div>
        <div class="span6">
          <h3>Antwort-Objekt</h3>
          <dl>
            <dt>items</dt>
            <dd>Liste von Titeldatensätzen als JSON-Objekte (Konvertiert von MODS)</dd>
            <dt>styles</dt>
            <dd>Objekt mit Mapping von Zitationsstilen auf CSL-Definition in XML</dd>
            <dt>locales</dt>
            <dd>Objekt mit Mapping von Sprachcode auf Spracheinstellungen in XML</dd>
            <dt>stylenames</dt>
            <dd>Liste von verfügbaren Zitationsstilen</dd>
          </dl>
        </div>
      </div>
      <div class="row-fluid">
        <h2>JavaScript-Client</h2>
        <p>
          Die Formatierung der Literaturangaben in dem gewählten Zitationsstil erfolgt Client-seitig
          in JavaScript mit dem CSL-Prozessor <a href="http://gsl-nagoya-u.net/http/pub/citeproc-doc.html">citeproc-js</a>.
          Zur Abfrage der GBC-CSL-API und zum Ansteuern von citeproc-js wird eine 
          <a href="./js/cslclient.js">eigene JavaScript-Bibliothek</a> verwendet.
        </p>
        <p>
          Der Quellcode für Server- und Client-Komponente ist unter
          <a href="https://github.com/gbv/gbv-csl">https://github.com/gbv/gbv-csl</a> verfügbar.
        </p>
      </div>
    </div>
    <footer class="footer">
       Umgesetzt mit <a href="http://gsl-nagoya-u.net/http/pub/citeproc-doc.html">citeproc-js</a>.
       Layout basierend auf <a href="http://twitter.github.com/bootstrap/">Bootstrap</a>.
    </footer>
  </body>
</html>
