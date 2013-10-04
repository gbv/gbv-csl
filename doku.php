<!DOCTYPE html>
<html lang="de">
    <head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<link rel="shortcut icon" type="image/x-icon" href="http://www.gbv.de/favicon.ico">
	<title>GVK - CSL</title>
	<style type="text/css">
	    body {
		margin: 0px;
		padding: 0px;
		font-family: "HelveticaNeue-Light", "Helvetica Neue Light", "Helvetica Neue", Helvetica, Arial, "Lucida Grande", sans-serif;
		font-size: 13px; 
	    }
	    
	    header {
		background-color: #557FBB;
		color: #FFFFFF;
		padding: 5px 10px 5px 10px;
	    }
	    
	    #errors {
	    	margin-top: 15px;
	    	padding: 10px 20px 10px 20px;
		background-color: #C20000;
		color: white;
		font-size: 14px;
		line-height: 20px;
	    }	    
	    
	    #stage {
	    	padding: 10px 20px 10px 20px;
	    }
	    
	    #stage table {
	    	border-spacing: 10px;
		margin-bottom: 40px;
	    }
	    
	    #stage table tr td:first-child {
	    	font-weight: bold;
		min-width: 120px;
		padding-right: 20px;
	    }
	 </style>
    </head>
    <body>
	    <header><h1>Autocomplete --> CSL</h1></header>
	    <?php
		if ($errorMessages != '' ) {
		    echo '<div id="errors">';
		    
		    echo "<h3>Ungültige Anfrage: <i>http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'] . '</i></h3>';
	    
		    echo $errorMessages;
		    
		    echo '</div>';
		}
	    ?>	    
	    <div id="stage">
		<h2>Obligatorische Anfrage-Parameter:</h2>
		<table>
		    <tr>
			<td>query</td>
			<td>CQL-Abfrage</td>
		    </tr>
		    <tr>
			<td>citationstyle</td>
			<td>Name eines dieser Zitationsstile: https://www.zotero.org/styles</td>
		    </tr>			    
		    <tr>
			<td>language</td>
			<td>de / en</td>
		    </tr>    		    		    		    		    		    
		</table>
		
		<h2>Optionale Anfrage-Parameter:</h2>
		<table>	
		    <tr>
			<td>database</td>
			<td>(Standard "gvk")</td>
		    </tr>

		    <tr>
			<td>callback</td>
			<td>für Nutzung von jsonp</td>
		    </tr>
		    <tr>
			<td>count</td>
			<td>Anzahl Ergebnisse (Standard "10")</td>
		    </tr>
		    <tr>
			<td>highlight</td>
			<td>Ergebnisse "highlighten"? ("0" oder "1". Standard "0")</td>
		    </tr>
		    <tr>
			<td>nohtml</td>
			<td>Ergebnisse ohne HTML-Formatierung? ("0" oder "1". Standard "0")</td>
		    </tr>   
		    <tr>
			<td>caching</td>
			<td>den Cache nutzen (1-tägige Lebensdauer, "0" oder "1". Standard "0"))</td>
		    </tr>                        		    		    		    		    		    
		</table>	
		
		<h2>Beispielanfragen:</h2>
		<table>
		    <tr>
			<td>Einfach:</td>
            <td><?php 
        function url($path){
          return sprintf( "%s://%s%s$path",
          isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
              $_SERVER['HTTP_HOST'], $_SERVER['REQUEST_URI']);
        }
                $url = url("?query=pica.all=Waldfee&citationstyle=ieee&language=de"); 
                echo "<a href='$url'>$url</a>"; ?>
            </td>
		    </tr>
		    <tr>
			<td>Ohne HTML als Formatierung:</td>
            <td><?php $url = url("?query=pica.all=Waldfee&citationstyle=padagogische-hochschule-heidelberg&count=15&nohtml=1&language=de");
                echo "<a href='$url'>$url</a>"; ?>
            </td>
		    </tr>
		    <tr>
			<td>Mit Formatierung und Highlighting:</td>
            <td><?php $url = url("?query=pica.all=Waldfee&citationstyle=ieee&count=5&highlight=1&language=de");
                echo "<a href='$url'>$url</a>"; ?>
		    </tr>	    		    		    		    		    		    
		</table>				
	    </div>
    </body>
</html>
