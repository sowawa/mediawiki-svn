/*
 * Flow jQuery plugin
 */

$.flow = { 'widthCache': {} };

$.fn.flow = function( text ) {
	var lineLimit = $(this).innerWidth();
	
	// Wordify
	var words = [],
		word = { 'text': '', 'width': 0, 'metrics': [] };
	for ( var i = 0; i < text.length; i++ ) {
		var char = text[i];
		// Boundary detection
		var boundary = String( ' -\t\r\n\f' ).indexOf( char ) >= 0;
		// Encoding
		var charHtml = char
			.replace( '&', '&amp;' )
			.replace( ' ', '&nbsp;' )
			.replace( '<', '&lt;' )
			.replace( '>', '&gt;' )
			.replace( '\'', '&apos;' )
			.replace( '"', '&quot;' );
        // Measurement
		var charWidth;
		if ( typeof $.flow.widthCache[char] === 'undefined' ) {
			charWidth = $.flow.widthCache[char] =
				$( '<span>' + charHtml + '</span>' ).appendTo( $(this) ).width();
		} else {
			charWidth = $.flow.widthCache[char];
		}
		// Virtual boundary
		if ( word.width + charWidth >= lineLimit ) {
			words[words.length] = word;
			word = { 'text': '', 'width': 0, 'metrics': [] };
		}
		// Append
		if ( boundary ) {
			if ( word.text.length ) {
				words[words.length] = word;
				word = { 'text': '', 'width': 0, 'metrics': [] };
			}
			words[words.length] = { 'text': char, 'width': charWidth, 'metrics': [charWidth] };
		} else {
			word.text += char;
			word.width += charWidth;
			word.metrics[word.metrics.length] = charWidth;
		}
	}
	if ( word.text.length ) {
		words[words.length] = word;
	}
	
	// Lineify
	var lines = [],
		line = { 'text': '', 'width': 0, 'metrics': [] };
	for ( var i = 0; i < words.length; i++ ) {
		var hardReturn = String( '\r\n\f' ).indexOf( words[i].text ) >= 0;
		if ( line.width + words[i].width > lineLimit || hardReturn ) {
			lines[lines.length] = line;
			line = { 'text': '', 'width': 0, 'metrics': [] };
		}
		if ( !hardReturn && ( line.width > 0 || words[i].text !== ' ' ) ) {
			line.text += words[i].text;
			line.width += words[i].width;
			line.metrics = line.metrics.concat( words[i].metrics );
		}
	}
	if ( line.text.length ) {
		lines[lines.length] = line;
	}
	
	// Flow
	$(this).empty();
	for ( var i = 0; i < lines.length; i++ ) {
		var $line = $( '<div class="editSurface-line"></div>' )
			.data( 'metrics', lines[i].metrics )
			.data( 'text', lines[i].text )
			.data( 'line', i );
		if ( lines[i].text.length ) {
			$line.text( lines[i].text );
		} else {
			$line.html( '&nbsp;' );
			$line.addClass( 'empty' );
		}
		$(this).append( $line );
	}
	
	return $(this);
};