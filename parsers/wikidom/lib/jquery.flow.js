/*
 * Flow jQuery plugin
 * 
 * Each line has data in the following structure, embedded as $line.data( 'flow' )
 * {
 *     index: 0,
 *     text: 'abc 123',
 *     metrics: [9,4,9]
 *     width: 22,
 *     words: [
 *         {
 *             text: 'abc',
 *             html: 'abc',
 *             metrics: [3,3,3],
 *             width: 9,
 *             index: 0,
 *             offset: 0
 *         },
 *         {
 *             text: ' ',
 *             html: '&nbsp;',
 *             metrics: [4],
 *             width: 4,
 *             index: 1,
 *             offset: 3
 *         },
 *         {
 *             text: '123',
 *             html: '123',
 *             metrics: [3,3,3]
 *             width: 9,
 *             index: 2,
 *             offset: 4
 *         }
 *     ]
 * }
 */
function copy( from, to ) {
	if ( typeof to === 'undefined' ) {
		to = {};
	}
	if ( from == null || typeof from != 'object' ) {
		return from;
	}
	if ( from.constructor != Object && from.constructor != Array ) {
		return from;
	}
	if ( from.constructor == Date
			|| from.constructor == RegExp
			|| from.constructor == Function
			|| from.constructor == String
			|| from.constructor == Number
			|| from.constructor == Boolean ) {
		return new from.constructor( from );
	}
	to = to || new from.constructor();
	for ( var name in from ) {
		to[name] = typeof to[name] == 'undefined' ? copy( from[name], null ) : to[name];
	}
	return to;
}

$.flow = {
	'charCache': {},
	'wordCache': {},
	'measureWord': function( text, ruler ) {
		if ( typeof $.flow.wordCache[text] === 'undefined' ) {
			// Cache miss
			var word = { 'text': text, 'html': '', 'metrics': [] };
			for ( var i = 0; i < text.length; i++ ) {
				var char = text[i],
					charHtml = char
						.replace( '&', '&amp;' )
						.replace( ' ', '&nbsp;' )
						.replace( '<', '&lt;' )
						.replace( '>', '&gt;' )
						.replace( '\'', '&apos;' )
						.replace( '"', '&quot;' )
						.replace( '\n', '<span class="editSurface-whitespace">\\n</span>' )
						.replace( '\t', '<span class="editSurface-whitespace">\\t</span>' );
				word.html += charHtml;
				if ( typeof $.flow.charCache[char] === 'undefined' ) {
					// Cache miss
					ruler.innerHTML = charHtml;
					word.metrics.push( $.flow.charCache[char] = ruler.clientWidth );
					continue;
				}
				// Cache hit
				word.metrics.push( $.flow.charCache[char] );
			}
			ruler.innerHTML = word.html;
			word.width = ruler.clientWidth;
			$.flow.wordCache[text] = copy( word );
			return word;
		}
		// Cache hit
		return copy( $.flow.wordCache[text] );
	},
	'getWords': function( text, ruler ) {
		var words = [],
			bounadry = /[ \-\t\r\n\f]/,
			left = 0,
			right = 0,
			search = 0;
		while ( ( search = text.substr( right ).search( bounadry ) ) >= 0 ) {
			right += search;
			words.push( $.flow.measureWord( text.substring( left, right ), ruler ) );
			if ( right < text.length ) {
				words.push( $.flow.measureWord( text.substring( right, ++right ), ruler ) );
			}
			left = right;
		}
		words.push( $.flow.measureWord( text.substring( right, text.length ), ruler ) );
		return words;
	},
	'getLines': function( words, width ) {
		// Lineify
		var lineCount = 0,
			charCount = 0,
			wordCount = 0,
			lines = [],
			line = {
				'text': '',
				'html': '',
				'width': 0,
				'metrics': [],
				'words': [],
				'index': lineCount
			};
		for ( var i = 0; i < words.length; i++ ) {
			if ( line.width + words[i].width > width ) {
				lines.push( line );
				charCount = 0;
				wordCount = 0;
				lineCount++;
				line = {
					'text': '',
					'html': '',
					'width': 0,
					'metrics': [],
					'words': [],
					'index': lineCount
				};
			}
			words[i].index = wordCount;
			wordCount++;
			words[i].offset = charCount;
			charCount += words[i].text.length;
			line.words.push( words[i] );
			line.text += words[i].text;
			line.html += words[i].html;
			line.width += words[i].width;
			line.metrics.push( words[i].width );
		}
		if ( line.text.length ) {
			lines.push( line );
		}
		return lines;
	}
};

$.fn.flow = function( text ) {
	//console.time( 'flow' );
	
	var $this = $(this),
		$ruler = $( '<div></div>' ).appendTo( $(this) ),
		lines = $.flow.getLines(
			$.flow.getWords( text, $( '<div class="editSurface-line"></div>' ).appendTo( $this )[0] ),
			$ruler.innerWidth()
		);
	
	// Flow
	$this.empty();
	for ( var i = 0; i < lines.length; i++ ) {
		var $line = $( '<div class="editSurface-line"></div>' ).data( 'flow', lines[i] );
		if ( lines[i].text.length === 1 && lines[1].text.match( /[ \-\t\r\n\f]/ ) ) {
			$line.html( '&nbsp;' );
			$line.addClass( 'empty' );
		} else {
			$line.html( lines[i].html );
		}
		$this.append( $line );
	}
	
	//console.timeEnd( 'flow' );
	return $this;
};
