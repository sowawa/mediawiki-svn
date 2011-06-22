/**
 * TODO: Don't re-flow lines beyond a given offset, an optimization for insert/delete operations
 */
function TextFlow( $container ) {
	this.$ = $container;
}

TextFlow.prototype.htmlEncode = function( text, trim ) {
	if ( trim ) {
		// Trailing whitespace
		text = text.replace( /\s+$/, '' );
	}
	return text
		// Tags
		.replace( /&/g, '&amp;' )
		.replace( /</g, '&lt;' )
		.replace( />/g, '&gt;' )
		// Quotes - probably not needed
		//.replace( /'/g, '&#039;' )
		//.replace( /"/g, '&quot;' )
		// Whitespace
		.replace( / /g, '&nbsp;' )
		.replace( /\n/g, '<span class="editSurface-whitespace">\\n</span>' )
		.replace( /\t/g, '<span class="editSurface-whitespace">\\t</span>' );
};

/**
 * Renders text into a series of div elements, each a single line of wrapped text.
 * 
 * TODO: Allow re-flowing from a given offset on to make re-flow faster when modifying the text
 * 
 * @param text {String} Text to render
 */
TextFlow.prototype.render = function( text ) {
	
	// Measure the container width
	var $ruler = $( '<div>&nbsp;</div>' ).appendTo( this.$ );
	var width = $ruler.innerWidth()
	$ruler.remove();
	
	/*
	 * Word boundary scan
	 * 
	 * To perform binary-search on words, rather than characters, we need to collect word boundary
	 * offsets into an array. This list of offsets always starts with 0 and ends with the length of
	 * the text, e.g. [0, ..., text.length]. The offset of the right side of the breaking character
	 * is stored, so the gaps between stored offsets always include the breaking character at the
	 * end.
	 * 
	 * To avoid encoding the same words as HTML over and over while fitting text to lines, we also
	 * build a list of HTML encoded strings for each gap between the offsets stored in the "words"
	 * array. Slices of the "html" array can be joined, producing the encoded HTML of the words. In
	 * the final pass, each line will get encoded 1 more time, to allow for whitespace trimming.
	 */
	var words = [0],
		html = [],
		boundary = /([ \.\,\;\:\-\t\r\n\f])/g,
		match,
		right,
		left = 0;
	while ( match = boundary.exec( text ) ) {
		// Include the boundary character in the range
		right = match.index + 1;
		// Store the boundary offset
		words.push( right );
		// Store the word's encoded HTML
		html.push( this.htmlEncode( text.substring( left, right ) ) );
		// Remember the previous match
		left = right;
	}
	// Ensure the words array ends in a boundary, which may automatically happen if the text ends
	// in a period, for instance, but may not in other cases
	if ( right !== text.length ) {
		words.push( text.length );
	}
	
	/*
	 * Line wrapping
	 * 
	 * Now that we have linear access to the offsets around non-breakable areas within the text, we
	 * can perform a binary-search for the best fit of words within a line.
	 * 
	 * TODO: It may be possible to improve the efficiency of this code by making a best guess and
	 * working from there, rather than always starting with [i .. words.length], which results in
	 * reducing the right position in all but the last line, and in most cases 2 or 3 times.
	 */
	var lineOffset = 0,
		lines = [],
		$lineRuler = $( '<div class="editSurface-line"></div>' ).appendTo( this.$ ),
		lineRuler = $lineRuler[0];	
	while ( lineOffset < words.length ) {
		var left = lineOffset,
			right = words.length,
			middle,
			clampedLeft;
		do {
			// Place "middle" directly in the center of "left" and "right"
			middle = Math.ceil( ( left + right ) / 2 );
			// Prepare the line for measurement using pre-encoded HTML
			lineRuler.innerHTML = html.slice( lineOffset, middle ).join( '' );
			// Test for over/under using width of the rendered line
			if ( lineRuler.clientWidth > width ) {
				// Words after "middle" won't fit
				right = middle - 1;
			} else {
				// Words before "middle" will fit
				left = middle;
			}
		} while ( left < right );
		
		// TODO: Check if it fits yet, if not, do binary search within the really long word
		
		// On the last line, l and r will both equal words.length, which is not a valid index
		clampedLeft = left === words.length ? left - 1 : left;
		
		// Collect line information
		lines.push({
			'text': text.substring( words[lineOffset], words[clampedLeft] ),
			'start': words[lineOffset],
			'end': words[clampedLeft],
			'width': lineRuler.clientWidth
		});
		
		// Step forward
		lineOffset = left;
	}
	// Cleanup - technically this will get removed by the empty() call below, but if that changes
	// we don't want to accidentally introduce any bugs, so explicit removal is preferred
	$lineRuler.remove();
	
	// TODO: It may be more efficient to re-use existing lines
	
	// Make way for the new lines
	this.$.empty();
	for ( var i = 0; i < lines.length; i++ ) {
		this.$.append(
			$( '<div class="editSurface-line"></div>' )
			.attr( 'line-index', i )
			.html( this.htmlEncode( text.substring( lines[i].start, lines[i].end ), true ) )
		);
	}
	
	return lines;
};
