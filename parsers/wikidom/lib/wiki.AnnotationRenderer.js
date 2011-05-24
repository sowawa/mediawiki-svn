/**
 * Serializes offset-based annotations.
 */
wiki.AnnotationRenderer = function() {

	/* Private Members */

	var that = this;
	var insertions = {};

	/* Methods */

	this.wrapWithText = function( range, pre, post ) {
		var start = range.offset;
		if ( !( start in insertions ) ) {
			insertions[start] = [pre];
		} else {
			insertions[start].push( pre );
		}
		var end = range.offset + range.length;
		if ( !( end in insertions ) ) {
			insertions[end] = [post];
		} else {
			insertions[end].unshift( post );
		}
	};

	this.wrapWithXml = function( range, tag, attributes ) {
		that.wrapWithText(
			range, wiki.util.xml.open( tag, attributes ), wiki.util.xml.close( tag )
		);
	};

	this.apply = function( text ) {
		var out = '';
		for ( var i = 0, iMax = text.length; i <= iMax; i++ ) {
			if ( i in insertions ) {
				out += insertions[i].join( '' );
			}
			if ( i < iMax ) {
				out += text[i];
			}
		}
		return out;
	};
};
