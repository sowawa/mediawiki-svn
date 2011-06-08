/*
 * Flow jQuery plugin
 */

$.flow = { 'widthCache': {} };

$.fn.flow = function( text ) {
	
	function encodeHtml( c ) {
		return c.replace( /&/g, '&amp;' )
			.replace( / /g, '&nbsp;' )
			.replace( /</g, '&lt;' )
			.replace( />/g, '&gt;' )
			.replace( /\'/g, '&apos;' )
			.replace( /"/g, '&quot;' );
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
		var lineText = '';
		var lineMetrics = [];
		var lineWidth = 0;
		var lastLineWidth = 0;
		
		while ( pos < text.length && lineWidth < width ) {
			// Append text
			lineText += text.charAt( pos );
			// Apply to DOM
			$line.html( encodeHtml( lineText ) );
			// Get new line width from DOM
			lastLineWidth = lineWidth;
			lineWidth = $line.innerWidth();
			// Push difference (character width)
			lineMetrics.push( lineWidth - lastLineWidth );
			if ( breakableRe( text.charAt(pos) ) ) {
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
			lineText = text.substring( lineStartPos, breakPos );
			$line.html( encodeHtml( lineText ) );
			pos = breakPos;
		}
		
		$line
			.data( 'metrics', lineMetrics )
			.data( 'text', lineText )
			.data( 'line', line );
		
		if ( lineStartPos === pos ) {
			$line.html( '&nbsp;' )
		}
		
		line++;
	}

	return $this;
	
	// the end



};

