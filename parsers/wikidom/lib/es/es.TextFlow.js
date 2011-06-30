/**
 * Renders and provides access to flowed text.
 * 
 * @param $container {jQuery Selection} Element to render into
 * @returns {TextFlow}
 */
function TextFlow( $container ) {
	this.$ = $container;
	this.lines = [];
	this.boundaries = [];
	this.words = [];
}

TextFlow.prototype.htmlEncode = function( text, trim ) {
	if ( trim ) {
		// Trailing whitespace
		text = text.replace( / $/, '' );
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
 * Gets an offset within from x and y coordinates.
 * 
 * @param x {Integer}
 * @param y {Integer}
 */
TextFlow.prototype.getOffset = function( x, y ) {
	var line = 0,
		lineCount = this.lines.length,
		offset = 0,
		top = 0,
		bottom = 0;
	
	/*
	 * Line finding
	 * 
	 * It's possible that a more efficient method could be used here, but the number of lines to be
	 * iterated through will rarely be over 100, so it's unlikely that any significant gains will be
	 * had. Plus, as long as we are iterating over each line, we can also sum up the top and bottom
	 * positions, which is a nice benefit of this method.
	 */
	while ( line < lineCount ) {
		bottom += lines[line].height;
		if ( y >= top && y < bottom ) {
			offset = lines[line].start;
			break;
		}
		top = bottom;
		line++;
	};
	
	return offset;
};

TextFlow.prototype.getPosition = function( offset ) {
	if ( offset < 0 ) {
		throw 'Out of range error. Offset is expected to be greater than or equal to 0.';
	}
	var line = 0,
		lineCount = this.lines.length,
		position = {
			'left': 0,
			'top': 0,
			'bottom': 0
		};
	
	/*
	 * Line finding
	 * 
	 * It's possible that a more efficient method could be used here, but the number of lines to be
	 * iterated through will rarely be over 100, so it's unlikely that any significant gains will be
	 * had. Plus, as long as we are iterating over each line, we can also sum up the top and bottom
	 * positions, which is a nice benefit of this method.
	 */
	while ( line < lineCount ) {
		if ( offset >= lines[line].start && offset < lines[line].end ) {
			position.bottom = position.top + lines[line].height;
			break;
		}
		position.top += lines[line].height;
		line++;
	};
	
	/*
	 * Virtual n+1 position
	 * 
	 * To allow access to position information of the right side of the last character on the last
	 * line, a virtual n+1 position is supported. Offsets beyond this virtual position will cause
	 * an exception to be thrown.
	 */
	if ( line === lineCount ) {
		if ( offset !== lines[line].end + 1 ) {
			line--;
			position.bottom = position.top;
			position.top -= lines[line].height;
		} else {
			throw 'Out of range error. Offset is expected to be less than or equal to text length.';
		}
	}
	
	/*
	 * Offset measuring
	 * 
	 * Since the left position will be zero for the first character in the line, so we can skip
	 * measuring for those cases.
	 */
	if ( lines[line].start < offset ) {
		var $ruler = $( '<div class="editSurface-line"></div>' ).appendTo( this.$ ),
			ruler = $ruler[0];
		ruler.innerHTML = this.htmlEncode( text.substring( lines[startLine].start, offset ) );
		position.left = ruler.clientWidth;
		$ruler.remove();
	} 
	
	return position;
};

/**
 * Renders text into a series of HTML elements, each a single line of wrapped text.
 * 
 * TODO: Allow re-flowing from a given offset on to make re-flow faster when modifying the text
 * 
 * @param text {String} Text to render
 */
TextFlow.prototype.render = function( text ) {
	/*
	 * Container measurement
	 * 
	 * To get an accurate measurement of the inside of the container, without having to deal with
	 * inconsistencies between browsers and box models, we can just create an element inside the
	 * container and measure it.
	 */
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
	 * build a list of HTML encoded strings for each gap between the offsets stored in the
	 * "boundaries" array. Slices of the "words" array can be joined, producing the encoded HTML of
	 * the words. In the final pass, each line will get encoded 1 more time, to allow for whitespace
	 * trimming.
	 * 
	 * Both "boundaries" and "words" data is kept around between renders.
	 */
	var boundaries = this.boundaries = [],
		words = this.words = [],
		boundary = /([ \.\,\;\:\-\t\r\n\f])/g,
		match,
		right,
		left = 0;
	while ( match = boundary.exec( text ) ) {
		// Include the boundary character in the range
		right = match.index + 1;
		// Store the boundary offset
		boundaries.push( right );
		// Store the word's encoded HTML
		words.push( this.htmlEncode( text.substring( left, right ) ) );
		// Remember the previous match
		left = right;
	}
	// Ensure the "boundaries" array ends in a boundary, which may automatically happen if the text
	// ends in a period, for instance, but may not in other cases
	if ( right < text.length ) {
		boundaries.push( text.length );
		words.push( this.htmlEncode( text.substring( right, text.length ) ) );
	}
	
	/*
	 * Line wrapping
	 * 
	 * Now that we have linear access to the offsets around non-breakable areas within the text, we
	 * can perform a binary-search for the best fit of words within a line. Line data is kept around
	 * between renders.
	 * 
	 * TODO: It may be possible to improve the efficiency of this code by making a best guess and
	 * working from there, rather than always starting with [i .. boundaries.length], which results
	 * in reducing the right position in all but the last line, and in most cases 2 or 3 times.
	 */
	this.$.empty();
	var lines = this.lines = [],
		offset = 0,
		subOffset,
		start = 0,
		end,
		fit,
		subFit,
		$ruler = $( '<div class="editSurface-line"></div>' ).appendTo( this.$ ),
		ruler = $ruler[0],
		lineText,
		$line;
	while ( offset < boundaries.length ) {
		fit = this.fitWords( words, offset, ruler, width );
		if ( fit.width > width ) {
			// The first word didn't fit, we need to split it up
			subOffset = start;
			offset++;
			end = boundaries[offset];
			do {
				subFit = this.fitCharacters( text, subOffset, end, ruler, width );
				// If we were able to get the rest of the characters on the line OK
				if (subFit.end === end) {
					// Try to fit more words on the line
					fit = this.fitWords( words, offset, ruler, width - subFit.width );
					if ( fit.end > offset ) {
						offset = fit.end - 1;
						subFit.end = end = boundaries[offset];
					}
				}
				this.appendLine( text, subOffset, subFit.end );
				// Move on to another line
				subOffset = subFit.end;
			} while (subOffset < end);
		} else {
			offset = fit.end - 1;
			end = boundaries[offset];
			this.appendLine( text, start, end );
		}
		start = end;
		offset++;
	}
	// Cleanup
	$ruler.remove();
};

TextFlow.prototype.appendLine = function( text, start, end ) {
	var lineText = text.substring( start, end );
	$line = $( '<div class="editSurface-line" line-index="'
			+ this.lines.length + '">' + this.htmlEncode( lineText, true ) + '</div>' )
		.appendTo( this.$ );
	// Collect line information
	this.lines.push({
		'text': lineText,
		'start': start,
		'end': end,
		'width': $line.outerWidth(),
		'height': $line.outerHeight()
	});
};

/**
 * Gets the index of the last word that fits inside the line
 * 
 * @param words {Array} List of HTML encoded strings, each a word to be fit
 * @param offset {Integer} Index within "words" to begin fitting from
 * @param line {HTMLElement} Element to take measurements with
 * @param width {Integer} Maximum width to allow the line to extend to
 * @return {Integer} Last index within "words" that contains a word that fits
 * @return {Null} If not even the first word can fit
 */
TextFlow.prototype.fitWords = function( words, offset, ruler, width ) {
	var start = offset,
		end = words.length,
		middle,
		finalWidth;
	do {
		// Place "middle" directly in the center of "start" and "end"
		middle = Math.ceil( ( start + end ) / 2 );
		// Prepare the line for measurement using pre-encoded HTML
		ruler.innerHTML = words.slice( offset, middle ).join( '' );
		// Test for over/under using width of the rendered line
		if ( ruler.clientWidth > width ) {
			// Detect impossible fit (the first word won't fit by itself)
			if (middle - offset === 1) {
				start = middle;
				break;
			}
			// Words after "middle" won't fit
			end = middle - 1;
		} else {
			// Words before "middle" will fit
			start = middle;
		}
	} while ( start < end );
	// Final measurment
	ruler.innerHTML = words.slice( offset, start ).join( '' );
	return { 'end': start, 'width': ruler.clientWidth };
};

TextFlow.prototype.fitCharacters = function( text, offset, limit, ruler, width ) {
	var start = offset,
		end = limit,
		middle,
		finalWidth;
	do {
		// Place "middle" directly in the center of "start" and "end"
		middle = Math.ceil( ( start + end ) / 2 );
		// Fill the line with a portion of the text, encoded as HTML
		ruler.innerHTML = this.htmlEncode( text.substring( offset, middle ) );
		// Test for over/under using width of the rendered line
		if ( ruler.clientWidth > width ) {
			// Detect impossible fit (the first character won't fit by itself)
			if (middle - offset === 1) {
				start = middle;
				break;
			}
			// Words after "middle" won't fit
			end = middle - 1;
		} else {
			// Words before "middle" will fit
			start = middle;
		}
	} while ( start < end );
	// Final measurement
	ruler.innerHTML = this.htmlEncode( text.substring( offset, start ) );
	return { 'end': start, 'width': ruler.clientWidth };
};
