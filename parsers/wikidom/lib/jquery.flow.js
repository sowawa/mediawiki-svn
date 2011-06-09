/*
 * Flow jQuery plugin
 */

$.flow = { 'widthCache': {} };

$.fn.flow = function( text ) {
	console.time( 'flow' );
	
	function encodeHtml( c ) {
		return c.replace( /\&/g, '&amp;' )
			.replace( /</g, '&lt;' )
			.replace( />/g, '&gt;' );
	}
	
	var breakableRe = /[\s\r\n\f]/;
	
	var $this = $(this);

	$this.empty();
	
	var width = $this.innerWidth();
	var pos = 0;
	var line = 0;
	
	while( pos < text.length ) {
		var lineStartPos = pos;
		var breakPos = pos;
		
		var $line = $( '<div class="editSurface-line"></div>' ).appendTo( $this );
		var lineElem = $line.get(0);
		var lineText = '';
		var lineMetrics = [];
		var lineWidth = 0;
		var lastLineWidth = 0;
		
		while ( pos < text.length && lineWidth < width ) {
			// Append text
			var c = text.charAt( pos );
			lineText += c;
			lineElem.innerHTML = encodeHtml( lineText );
		
			// Get new line width from DOM
			lastLineWidth = lineWidth;
			// $.innerWidth call is expensive. Assume padding and border = 0 and this should be okay
			lineWidth = lineElem.offsetWidth; 
			// Push difference (character width)
			lineMetrics.push( lineWidth - lastLineWidth );

			if ( breakableRe( c ) ) {
				breakPos = pos;
			}

			pos++;
		}
	
		if ( lineWidth >= width ) {
			if ( breakPos === lineStartPos ) {
				// There was no breakable position between the start of the line and here, so we
				// have some kind of long word. Or, the line width is very small. Break at the
				// previous character.
				pos -= 1;
				breakPos = pos;
			} else {
				// Include the breaking character in the previous line
				// TODO: How does this work with hyphens? Won't they be to far right?
				breakPos++;
			}
			// Move the position back to the last safe location
			pos = breakPos;
			// Truncate characters that won't fit
			lineText = text.substring( lineStartPos, breakPos );
			lineElem.innerHTML = encodeHtml( lineText );
			// Don't leave metrics from truncated characters around
			lineMetrics = lineMetrics.slice( 0, pos - lineStartPos );
		}

		$line
			.data( 'metrics', lineMetrics )
			.data( 'text', lineText )
			.data( 'line', line );
		
		if ( lineStartPos === pos ) {
			lineElem.innerHtml = '&nbsp;';
		}
		
		line++;
	}
	
	console.timeEnd( 'flow' );
	
	return $this;
};

