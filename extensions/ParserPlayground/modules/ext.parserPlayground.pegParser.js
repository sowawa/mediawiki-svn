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

PegParser.src = false;

PegParser.prototype.parseToTree = function(text, callback) {
	this.initSource(function() {
		var out, err;
		try {
			var parser = PEG.buildParser(PegParser.src);
			out = parser.parse(text);
		} catch (e) {
			err = e;
		} finally {
			callback(out, err);
		}
	});
}

PegParser.prototype.initSource = function(callback) {
	if (PegParser.src) {
		callback();
	} else {
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
						PegParser.src = page.revisions[0]['*'];
					}
				});
				callback();
			}, 'json');
		} else {
			$.get(wgExtensionAssetsPath + '/ParserPlayground/modules/pegParser.pegjs.txt', function(data) {
				PegParser.src = data;
				callback();
			}, 'text' );
		}
	}
};
