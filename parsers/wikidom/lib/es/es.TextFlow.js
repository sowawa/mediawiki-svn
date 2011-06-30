/**
 * Renders and provides access to flowed text.
 * 
 * @param $container {jQuery Selection} Element to render into
 * @returns {TextFlow}
 */
function TextFlow( $container, text ) {
	this.$ = $container;
	this.boundaries = [];
	this.words = [];
	this.lines = [];
	this.width = null;
	this.text = '';
	if ( text !== undefined ) {
		this.setText( text );
	}
}

/**
 * Encodes text as an HTML string.
 * 
 * @param text {String} Text to escape
 * @return {String} HTML escaped text
 */
TextFlow.prototype.escape = function( text ) {
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
 * Gets offset within content closest to of a given position.
 * 
 * @param x {Integer} Horizontal position in pixels
 * @param y {Integer} Vertical position in pixels
 * @return {Integer} Offset within content nearest the given coordinates
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

/**
 * Gets position coordinates of a given offset.
 * 
 * Offsets are boundaries between content. Results are given in left, top and bottom positions,
 * which could be used to draw a cursor, highlighting painting, etc.
 * 
 * @param offset {Integer} Offset within content
 * @return {Object} Object containing left, top and bottom properties, each positions in pixels
 */
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
		ruler.innerHTML = this.escape( text.substring( lines[startLine].start, offset ) );
		position.left = ruler.clientWidth;
		$ruler.remove();
	} 
	
	return position;
};

TextFlow.prototype.setText = function( text ) {
	// Don't reevaluate boundaries if the text hasn't actually changed
	if ( text === this.text ) {
		return;
	}
	this.text = text;
	
	/*
	 * Word boundary scan
	 * 
	 * To perform binary-search on words, rather than characters, we need to collect word boundary
	 * offsets into an array. The offset of the right side of the breaking character is stored, so
	 * the gaps between stored offsets always include the breaking character at the end.
	 * 
	 * To avoid encoding the same words as HTML over and over while fitting text to lines, we also
	 * build a list of HTML escaped strings for each gap between the offsets stored in the
	 * "boundaries" array. Slices of the "words" array can be joined, producing the escaped HTML of
	 * the words.
	 */
	// Purge "boundaries" and "words" arrays
	this.boundaries = [];
	this.words = [];
	// Iterate over each word+boundary sequence, capturing offsets and encoding text as we go
	var boundary = /([ \.\,\;\:\-\t\r\n\f])/g,
		match,
		start = 0,
		end;
	while ( match = boundary.exec( text ) ) {
		// Include the boundary character in the range
		end = match.index + 1;
		// Store the boundary offset
		this.boundaries.push( end );
		// Store the word's escaped HTML
		this.words.push( this.escape( text.substring( start, end ) ) );
		// Remember the previous match
		start = end;
	}
	// If the last character is not a boundary character, we need to append the final range to the
	// "boundaries" and "words" arrays
	if ( end < text.length ) {
		this.boundaries.push( text.length );
		this.words.push( this.escape( text.substring( end, text.length ) ) );
	}
	// Force re-flow
	this.width = null;
};

/**
 * Renders text into a series of HTML elements, each a single line of wrapped text.
 * 
 * In cases where a single word is too long to fit on a line, the word will be "virtually" wrapped,
 * causing them to be fragmented. Word fragments are rendered on their own lines, except for their
 * remainder, which is combined with whatever proceeding words can fit on the same line.
 * 
 * The offset parameter can be used to reduce the amount of work involved in re-rendering the same
 * text, but will be automatically ignored if the text or width of the container has changed.
 * 
 * @param offset {Integer} Offset to re-render from, if possible (not yet implemented)
 */
TextFlow.prototype.render = function( offset ) {
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
	
	// Ignore offset optimization if the width has changed or the text has never been flowed before
	if (this.width !== width) {
		offset = undefined;
	}
	
	// TODO: Take offset into account and only work from there
	
	// Reset lines in the DOM and the "lines" array
	this.$.empty();
	this.lines = [];
	// Iterate over each word that will fit in a line, appending them to the DOM as we go
	var wordOffset = 0,
		lineStart = 0,
		lineEnd,
		wordFit,
		charOffset,
		charFit,
		$ruler = $( '<div class="editSurface-line"></div>' ).appendTo( this.$ ),
		ruler = $ruler[0];
	while ( wordOffset < this.boundaries.length ) {
		wordFit = this.fitWords( wordOffset, this.words.length, ruler, width );
		if ( wordFit.width > width ) {
			// The first word didn't fit, we need to split it up
			charOffset = lineStart;
			wordOffset++;
			lineEnd = this.boundaries[wordOffset];
			do {
				charFit = this.fitCharacters( charOffset, lineEnd, ruler, width );
				// If we were able to get the rest of the characters on the line OK
				if (charFit.end === lineEnd) {
					// Try to fit more words on the line
					wordFit = this.fitWords(
						wordOffset, this.words.length, ruler, width - charFit.width
					);
					if ( wordFit.end > wordOffset ) {
						wordOffset = wordFit.end - 1;
						charFit.end = lineEnd = this.boundaries[wordOffset];
					}
				}
				this.appendLine( charOffset, charFit.end );
				// Move on to another line
				charOffset = charFit.end;
			} while ( charOffset < lineEnd );
		} else {
			wordOffset = wordFit.end - 1;
			lineEnd = this.boundaries[wordOffset];
			this.appendLine( lineStart, lineEnd );
		}
		lineStart = lineEnd;
		wordOffset++;
	}
	// Cleanup
	$ruler.remove();
};

/**
 * Adds a line containing a given range of text to the end of the DOM and the "lines" array.
 * 
 * @param start {Integer} Beginning of text range for line
 * @param end {Integer} Ending of text range for line
 */
TextFlow.prototype.appendLine = function( start, end ) {
	var lineText = this.text.substring( start, end );
	$line = $( '<div class="editSurface-line" line-index="'
			+ this.lines.length + '">' + this.escape( lineText ) + '</div>' )
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
 * Gets the index of the boundary of last word that fits inside the line
 * 
 * The "words" and "boundaries" arrays provide linear access to the offsets around non-breakable
 * areas within the text. Using these, we can perform a binary-search for the best fit of words
 * within a line, just as we would with characters.
 * 
 * Results are given as an object containing both an index and a width, the later of which can be
 * used to detect when the first word was too long to fit on a line. In such cases the result will
 * contain the index of the boundary of the first word and it's width.
 * 
 * TODO: Because limit is most likely given as "words.length", it may be possible to improve the
 * efficiency of this code by making a best guess and working from there, rather than always
 * starting with [offset .. limit], which usually results in reducing the end position in all but
 * the last line, and in most cases more than 3 times, before changing directions.
 * 
 * @param start {Integer} Index within "words" to begin fitting from
 * @param end {Integer} Index within "words" to stop fitting to
 * @param ruler {HTMLElement} Element to take measurements with
 * @param width {Integer} Maximum width to allow the line to extend to
 * @return {Integer} Last index within "words" that contains a word that fits
 */
TextFlow.prototype.fitWords = function( start, end, ruler, width ) {
	var offset = start,
		middle;
	do {
		// Place "middle" directly in the center of "start" and "end"
		middle = Math.ceil( ( start + end ) / 2 );
		// Prepare the line for measurement using pre-escaped HTML
		ruler.innerHTML = this.words.slice( offset, middle ).join( '' );
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
	ruler.innerHTML = this.words.slice( offset, start ).join( '' );
	return { 'end': start, 'width': ruler.clientWidth };
};

/**
 * Gets the index of the boundary of the last character that fits inside the line
 * 
 * Results are given as an object containing both an index and a width, the later of which can be
 * used to detect when the first character was too long to fit on a line. In such cases the result
 * will contain the index of the first character and it's width.
 * 
 * @param start {Integer} Index within "text" to begin fitting from
 * @param end {Integer} Index within "text" to stop fitting to
 * @param ruler {HTMLElement} Element to take measurements with
 * @param width {Integer} Maximum width to allow the line to extend to
 * @return {Integer} Last index within "text" that contains a character that fits
 */
TextFlow.prototype.fitCharacters = function( start, end, ruler, width ) {
	var offset = start,
		middle;
	do {
		// Place "middle" directly in the center of "start" and "end"
		middle = Math.ceil( ( start + end ) / 2 );
		// Fill the line with a portion of the text, escaped as HTML
		ruler.innerHTML = this.escape( this.text.substring( offset, middle ) );
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
	ruler.innerHTML = this.escape( this.text.substring( offset, start ) );
	return { 'end': start, 'width': ruler.clientWidth };
};
