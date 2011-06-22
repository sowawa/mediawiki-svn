/**
 * TODO: Don't re-flow lines beyond a given offset, an optimization for insert/delete operations
 */
function TextFlow( $container ) {
	this.$ = $container;
}

TextFlow.encodeHtml = function( text ) {
	return text
		.replace( /&/g, '&amp;' )
		.replace( / /g, '&nbsp;' )
		.replace( /</g, '&lt;' )
		.replace( />/g, '&gt;' )
		.replace( /'/g, '&apos;' )
		.replace( /"/g, '&quot;' )
		.replace( /\n/g, '<span class="editSurface-whitespace">\\n</span>' )
		.replace( /\t/g, '<span class="editSurface-whitespace">\\t</span>' );
};

TextFlow.prototype.render = function( text ) {
	//console.time( 'TextFlow.render' );
	
	// Clear all lines -- FIXME: This should adaptively re-use/cleanup existing lines
	this.$.empty();
	
	// Measure the container width
	var $ruler = $( '<div>&nbsp;</div>' ).appendTo( this.$ );
	var width = $ruler.innerWidth()
	$ruler.remove();
	
	// Build list of line break offsets
	var words = [0],
		boundary = /[ \-\t\r\n\f]/,
		left = 0,
		right = 0,
		search = 0;
	while ( ( search = text.substr( right ).search( boundary ) ) >= 0 ) {
		right += search;
		words.push( ++right );
		left = right;
	}
	words.push( right );
	words.push( text.length );
	
	// Create lines from text
	var pos = 0,
		index = 0,
		metrics = [];
	while ( pos < words.length - 1 ) {
		// Create line
		var $line = $( '<div class="editSurface-line"></div>' )
				.attr( 'line-index', index )
				.appendTo( this.$ ),
			line = $line[0];
		
		// Use binary search-like technique for efficiency
		var l = pos,
			r = words.length,
			m;
		do {
			m = Math.ceil( ( l + r ) / 2 );
			line.innerHTML = TextFlow.encodeHtml( text.substring( words[pos], words[m] ) );
			if ( line.clientWidth > width ) {
				// Text is too long
				r = m - 1;
			} else {
				l = m;
			}
		} while ( l < r );
		line.innerHTML = TextFlow.encodeHtml( text.substring( words[pos], words[l] ) );
		
		// TODO: Check if it fits yet, if not, do binary search within the really long word
		
		metrics.push({
			'text': text.substring( words[pos], words[l] ),
			'offset': words[pos],
			'length': words[l] - words[pos],
			'width': line.clientWidth,
			'index': index
		});
		
		// Step forward
		index++;
		pos = m;
	}
	
	//console.timeEnd( 'TextFlow.render' );
	
	return metrics;
};
