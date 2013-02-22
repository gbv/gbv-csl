/**
 * CSLClient JavaScript library.
 * Requires jQuery and citeproc-js.
 * 
 * See <https://github.com/gbv/gbv-csl> for the latest version.
 *
 * @class CSLClient
 * @author Jakob Voss <voss@gbv.de>
 */
var CSLClient = function(args) {
    this.div   = args.div;
    this.api   = args.api;
    this.input = args.input;

    var client = this;

    // enable change handlers
    if (client.input.dbkey && client.input.query) {
        $.each(["dbkey","query"],function(index,name) {
            client.input[name].change(function(){
                client.performQuery({
                    dbkey: client.input.dbkey.val(),
                    query: client.input.query.val()
                });
            });
        });
    }

    $.each(["style","locale"],function(index,name) {
        client.input[name].change(function(){
            var args = { };
            args[name] = client.input[name].val()
            client.performQuery(args);
        });
    });
};

CSLClient.prototype = {
    citeproc: null,

    items: { },
    setItems: function(items) {
		this.items = items;
    },

    locales: { },
    currentLocale: null,
    addLocales: function(locales) {
        for (var lang in locales) {
            this.locales[lang] = locales[lang].replace(/<\?xml .*\?>/i, '');
        }
    },

    styles: { },
    currentStyle: null,
    addStyles: function(styles) {
        for (var style in styles) {
            this.styles[style] = styles[style].replace(/<\?xml .*\?>/i, '');
        }
    },

    retrieveItem: function(id) { 
		return this.items[id].input; 
	},
    retrieveLocale: function(lang) {
		return this.locales[lang]; 
	},

    updateEngine: function() {
        this.citeproc = new CSL.Engine(
            this, this.styles[this.currentStyle], this.currentLocale
        );
    }
};


CSLClient.prototype.showBibliography = function() {
    if (!this.citeproc) return;

    var sru = "http://sru.gbv.de/" + $('#dbkey').val() + '?' + $.param({
        recordSchema: 'mods',
        version: '1.1',
        operation: 'searchRetrieve',
        startRecord: 1,
        maximumRecords: 10,
        query: $('#query').val()
    });
    $("#sru").attr('href',sru).show();

	var api = this.api + $('#dbkey').val() + '?' + $.param({
        query: $('#query').val()
	});
    $("#api").attr('href',api).show();

    var ids = [];
    for(var id in this.items) ids.push(id);
    this.citeproc.updateItems(ids);

    var output = null;
	try { 
		output = this.citeproc.makeBibliography();
	} catch(e) { // ERROR
        var div = $('#'+this.div);
		div.html(""+e);
	}
    if (output && output.length && output[1].length){
        var html = output[0].bibstart + output[1].join("") + output[0].bibend;
        var div = $('#'+this.div);
        div.html(html).find('.csl-left-margin').each(function(i,v){
            $(v).wrapInner('<a href="'+output[0].entry_ids[i][0]+'" />');
        });
    }
};

CSLClient.prototype.performQuery = function(data) {
    var client = this;

    $.each(client.input,function(name,field){
        if(data[name]) field.parent().parent().removeClass('error');
    });

    var div = $('#'+client.div);
    div.css({ opacity: 0.2 });

	var url = client.api;
	if (data.dbkey) {
		url += "/" + data.dbkey;
		delete data.dbkey;
	}
    $.ajax(url,{data:data}).done(function(response){

        div.css({ opacity: 1 });

        if (response.stylenames) {
            $('#style').typeahead({source:response.stylenames});
        }

        if (response.locales) {
            client.addLocales( response.locales );
            client.currentLocale = data.locale;
        }
        if (response.styles) {
            client.addStyles(response.styles);
            client.currentStyle = data.style;
        }
        if (data.locale || data.style) {
            client.updateEngine();
        }
        if (response.items) {
            client.setItems( response.items );
        }
        client.showBibliography();

    }).fail(function(response){
        div.css({ opacity: 1 });
        $.each(client.input,function(name,field){
            if(data[name]) field.parent().parent().addClass('error');
        });
    });
}

//-----------------------------------------------------------------------------

$(document).ready(function() {
    (new CSLClient({
        div: 'references',
        api: './',
        input: {
            dbkey:  $('#dbkey'),
            query:  $('#query'),
            locale: $('#locale'),
            style:  $('#style')
        }
    })).performQuery({ 
        query:  $('#query').val(), 
        dbkey:  $('#dbkey').val(),
        style:  $('#style').val(),
        locale: $('#locale').val(),
        list:   'styles'
    });
});

