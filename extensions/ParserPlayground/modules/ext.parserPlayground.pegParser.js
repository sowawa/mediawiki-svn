/**
 * Wrap a parser generated with pegjs into the FakeParser class,
 * so will use FakeParser's HTML output and round-tripping functions.
 *
 * If installed as a user script or to customize, set parserPlaygroundPegPage
 * to point at the MW page name containing the parser peg definition; default
 * is 'MediaWiki:Gadget-ParserPlayground-PegParser.pegjs'.
 */
function PegParser(options) {
	FakeParser.call(this, options);
}

$.extend(PegParser.prototype, FakeParser.prototype);

PegParser.prototype.parseToTree = function(text, callback) {
	this.initField(function() {
		var $src = $('#pegparser-source');
		if ($src.length) {
			var parserSource = $src.val();
		} else {
			var parserSource = '';
		}
		var out, err;
		try {
			var parser = PEG.buildParser(parserSource);
			out = parser.parse(text);
		} catch (e) {
			err = e;
		} finally {
			callback(out, err);
		}
	});
}

PegParser.prototype.initField = function(callback) {
	var src = $('#pegparser-source');
	if (src.length) {
		src.show();
		callback();
	} else {
		var area = $('<textarea id="pegparser-source" rows=25></textarea>').insertBefore('#wpTextbox1');
		if ( typeof parserPlaygroundPegPage !== 'undefined' ) {
			$.get(wgScriptPath + '/api' + wgScriptExtension, {
				format: 'json',
				action: 'query',
				prop: 'revisions',
				rvprop: 'content',
				titles: parserPlaygroundPegPage
			}, function(data, xhr) {
				$.each(data.query.pages, function(i, page) {
					if (page.revisions && page.revisions.length) {
						$('#pegparser-source').val(page.revisions[0]['*']);
					}
				});
				callback();
			}, 'json');
		} else {
			$.get(wgExtensionAssetsPath + '/ParserPlayground/modules/pegParser.pegjs.txt', function(data) {
				$('#pegparser-source').val(data);
				callback();
			}, 'text' );
		}
	}
};
