/**
 * GBVCSL client.
 * Requires jQuery and citeproc-js.
 * 
 * See <https://github.com/gbv/gbv-csl> for the latest version.
 *
 * @class GBVCSL client
 * @author Jakob Voss <voss@gbv.de>
 */
var GBVCSL = function(args) {
    this.div = args.div;
    this.api = args.api;
};

GBVCSL.prototype = {
    citeproc: null,

    items: [ ],
    setItems: function(items) {
        this.items = { };
        for (var id in items) {
            this.items[id] = items[id];
        }
    },

    locales: { },
    currentLocale: null,
    addLocales: function(locales) {
        for (var lang in locales) {
            this.locales[lang] = locales[lang];
        }
    },

    styles: { },
    currentStyle: null,
    addStyles: function(styles) {
        for (var style in styles) {
            this.styles[style] = styles[style];
        }
    },

    retrieveItem: function(id) { return this.items[id]; },
    retrieveLocale: function(lang) { return this.locales[lang]; },

    updateEngine: function() {
        this.citeproc = new CSL.Engine(
            this, this.styles[this.currentStyle], this.currentLocale
        );
    },

    html: function(html) {
        return $('#'+this.div).html(html);
    }
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
        var html = output[0].bibstart + output[1].join("") + output[0].bibend;
        this.html(html).find('.csl-left-margin').each(function(i,v){
            $(v).wrapInner('<a href="'+output[0].entry_ids[i][0]+'" />');
        });
    }
};

GBVCSL.prototype.performQuery = function(data) {
    var oldhtml = this.html();
    $.each(["style","locale","dbkey","cql"],function(k,v){
        if(data[v]) $('#'+v).parent().parent().removeClass('error');
    });

    this.html("please wait...");

    var me = this;
    $.ajax(this.api,{data:data}).done(function(response){

        me.html("please wait a bit more...");

        if (response.stylenames) {
            $('#style').typeahead({source:response.stylenames});
        }

        if (response.locales) {
            me.addLocales( response.locales );
            me.currentLocale = data.locale;
        }
        if (response.styles) {
            me.addStyles(response.styles);
            me.currentStyle = data.style;
        }
        if (data.locale || data.style) {
            me.updateEngine();
        }
        if (response.items) {
            me.setItems( response.items );
        }
        me.showBibliography();

    }).fail(function(response){
        me.html(oldhtml);
        $.each(["style","locale","dbkey","cql"],function(k,v){
            if(data[v]) $('#'+v).parent().parent().addClass('error');
        });
    });
}

//-----------------------------------------------------------------------------

var gbvcsl = new GBVCSL({
    div: 'references',
    api: './api'
});


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
        abbreviations: 'default',
        list: "styles"
    });
});
