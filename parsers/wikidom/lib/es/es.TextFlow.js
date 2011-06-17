/**
 * TODO: Don't re-flow lines beyond a given offset, an optimization for insert/delete operations
 */
function TextFlow() {
	//
}

TextFlow.prototype.render = function( $container, text ) {
	//console.time( 'TextFlow.render' );
	var metrics = [],
		$ruler = $( '<div class="editSurface-line"></div>' ).appendTo( $(this) ),
		$line = $( '<div class="editSurface-line"></div>' ),
		lines = this.getLines( this.getWords( text, $ruler[0] ), $ruler.innerWidth() );
	$container.empty();
	for ( var i = 0; i < lines.length; i++ ) {
		metrics.push( lines[i] );
		if ( lines[i].text.length === 1 && lines[1].text.match( /[ \-\t\r\n\f]/ ) ) {
			$container.append( $line.clone().html( '&nbsp' ).addClass( 'empty' ) );
		} else {
			$container.append( $line.clone().html( lines[i].html ) );
		}
	}
	//console.timeEnd( 'TextFlow.render' );
	return metrics;
};

TextFlow.prototype.getWord = function( text, ruler ) {
	var word = {
		'text': text,
		'html': word.text
			.replace( /&/g, '&amp;' )
			.replace( / /g, '&nbsp;' )
			.replace( /</g, '&lt;' )
			.replace( />/g, '&gt;' )
			.replace( /'/g, '&apos;' )
			.replace( /"/g, '&quot;' )
			.replace( /\n/g, '<span class="editSurface-whitespace">\\n</span>' )
			.replace( /\t/g, '<span class="editSurface-whitespace">\\t</span>' );
	};
	ruler.innerHTML = word.html;
	word.width = ruler.clientWidth;
	return word;
};

TextFlow.prototype.getWords = function( text, ruler ) {
	var words = [],
		bounadry = /[ \-\t\r\n\f]/,
		left = 0,
		right = 0,
		search = 0;
	while ( ( search = text.substr( right ).search( bounadry ) ) >= 0 ) {
		right += search;
		words.push( this.getWord( text.substring( left, right ), ruler ) );
		if ( right < text.length ) {
			words.push( this.getWord( text.substring( right, ++right ), ruler ) );
		}
		left = right;
	}
	words.push( this.getWord( text.substring( right, text.length ), ruler ) );
	return words;
};

TextFlow.prototype.getLines = function( words, width ) {
	var lines = [],
		line = {
			'text': '',
			'html': '',
			'width': 0,
			'index': lines.length
		};
	for ( var i = 0; i < words.length; i++ ) {
		if ( line.width + words[i].width > width ) {
			lines.push( line );
			line = {
				'text': '',
				'html': '',
				'width': 0,
				'index': lines.length
			};
		}
		line.text += words[i].text;
		line.html += words[i].html;
		line.width += words[i].width;
	}
	if ( line.text.length ) {
		lines.push( line );
	}
	return lines;
};
