/**
 * Utilities used by WikiDom renderers and parsers.
 */
wiki.util = {
	'str': {
		'repeat': function( pattern, count ) {
			if ( count < 1 ) return '';
			var result = '';
			while ( count > 0 ) {
				if ( count & 1 ) result += pattern;
				count >>= 1, pattern += pattern;
			};
			return result;
		}
	},
	'xml': {
		'esc': function( text ) {
			return text
				.replace( /&/g, '&amp;' )
				.replace( /</g, '&lt;' )
				.replace( />/g, '&gt;' )
				.replace( /"/g, '&quot;' )
				.replace( /'/g, '&#039;' );
		},
		'attr': function( attributes, prespace ) {
			var attr = [];
			if ( attributes ) {
				for ( var name in attributes ) {
					attr.push( name + '="' + attributes[name] + '"' );
				}
			}
			return ( prespace && attr.length ? ' ' : '' ) + attr.join( ' ' );
		},
		'open': function( tag, attributes ) {
			return '<' + tag + wiki.util.xml.attr( attributes, true ) + '>';
		},
		'close': function( tag ) {
			return '</' + tag + '>';
		},
		'tag': function( tag, attributes, value, escape ) {
			if ( value === false ) {
				return '<' + tag + wiki.util.xml.attr( attributes, true ) + ' />';
			} else {
				if ( escape ) {
					value = wiki.util.xml.esc( value );
				}
				return '<' + tag + wiki.util.xml.attr( attributes, true ) + '>' + value
					+ '</' + tag + '>';
			}
		}
	}
};
