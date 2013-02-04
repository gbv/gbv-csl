
function GBVCSL() {
    this.citeproc = null;
    this.locales  = { };
    this.language = null;
    this.styleXML = { };
    this.style    = null;
    this.items    = [ ];
}

GBVCSL.prototype.retrieveItem = function(id) { 
    return this.items[id]; 
};

GBVCSL.prototype.retrieveLocale = function(lang) { 
    return this.locales[lang]; 
};

GBVCSL.prototype.showBibliography = function() {
    if (!this.citeproc) return;

    var sru   = "http://sru.gbv.de/" + $('#dbkey').val() + '?' + $.param({
        recordSchema: 'mods',
        version: '1.1',
        operation: 'searchRetrieve',
        startRecord: 1,
        maximumRecords: 10,
        query: $('#cql').val()
    });

    $("#sru").attr('href',sru).show();

    var ids = [];
    for(var id in this.items) ids.push(id);
    this.citeproc.updateItems(ids);

    var output = this.citeproc.makeBibliography();
    if (output && output.length && output[1].length){
        output = output[0].bibstart + output[1].join("") + output[0].bibend;
        $('#references').html( output );
    }
};

GBVCSL.prototype.addLocales = function(locales) {
    for (var lang in locales) {
        this.locales[lang] = locales[lang];
    }
};

GBVCSL.prototype.addStyle = function(style, styleXML) {
    this.styleXML[style] = styleXML;
}

GBVCSL.prototype.setStyle = function(style) {
    this.style = style;
}

GBVCSL.prototype.setLanguage = function(language) {
    this.language = language;
}

GBVCSL.prototype.updateEngine = function() {
    this.citeproc = new CSL.Engine(this, this.styleXML[this.style], this.language );
};

GBVCSL.prototype.setItems = function(items) {
    this.items = { };
    for (var id in items) {
        this.items[id] = items[id];
    }

};

GBVCSL.prototype.performQuery = function(data) {
    var me = this;

    var oldhtml = $('#references').html();
    $.each(["style","locale","dbkey","cql"],function(k,v){
        if(data[v]) $('#'+v).parent().parent().removeClass('error');
    });

    $('#references').html("please wait...");

    $.ajax('./gbvcsl.php',{data:data}).done(function(response){

        $('#references').html("please wait a bit more...");

        if (response.locales) {
            me.addLocales( response.locales );
            me.setLanguage( data.locale );
        }
        if (response.style) {
            me.addStyle(data.style,response.style);
            me.setStyle(data.style);
        }
        if (response.locales || response.style) {
            me.updateEngine();
        }
        if (response.items) {
            me.setItems( response.items );
        }
        me.showBibliography();
    }).fail(function(response){
        $('#references').html(oldhtml);
        $.each(["style","locale","dbkey","cql"],function(k,v){
            if(data[v]) $('#'+v).parent().parent().addClass('error');
        });
    });
}


var gbvcsl = new GBVCSL();


function updateLocale() {
    gbvcsl.performQuery({ 
        locale: $('#locale').val() 
    });
}

function updateQuery() {
    gbvcsl.performQuery({
        dbkey: $('#dbkey').val(),
        cql:   $('#cql').val()
    });
}

function updateStyle() {
    gbvcsl.performQuery({
        style: $('#style').val(),
    });
}

$(document).ready(function() {
    gbvcsl.performQuery({ 
        cql:   $('#cql').val(), 
        dbkey: $('#dbkey').val(),
        style: $('#style').val(),
        locale: $('#locale').val(),
        abbreviations: 'default'
    });
});
