/**
 * Serializes offset-based annotations.
 */
wiki.AnnotationRenderer = function() {

	/* Private Members */

	var that = this;
	var insertions = {};

	/* Methods */

	/**
	 * Adds a set of insertions around a range of text.
	 * 
	 * Insertions for the same range will be nested in order of declaration.
	 * @example
	 *     ar = new wiki.AnnotationRenderer();
	 *     ar.wrapWithText( { 'offset': 1, 'length': 1 }, '[', ']' );
	 *     ar.wrapWithText( { 'offset': 1, 'length': 1 }, '{', '}' );
	 *     // Outputs: "a[{b}]c"
	 *     console.log( ar.apply( 'abc' ) );
	 * 
	 * @param range Object: Range to insert text around
	 * @param pre String: Text to insert before range
	 * @param post String: Text to insert after range
	 */
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

	/**
	 * Adds a set of opening and closing XML tags around a range of text.
	 * 
	 * This is a convenience function, and has the same nesting behavior as wrapWithText.
	 * 
	 * @param range Object: Range to insert XML tags around
	 * @param tag String: XML tag name
	 * @param attributes Object: XML tag attributes (optional)
	 */
	this.wrapWithXml = function( range, tag, attributes ) {
		that.wrapWithText(
			range, wiki.util.xml.open( tag, attributes ), wiki.util.xml.close( tag )
		);
	};

	/**
	 * Applies insertions to text.
	 * 
	 * @param text String: Text to apply insertions to
	 * @return String: Wrapped text
	 */
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
