/*
 * Wikitext document object models
 * 
 * document
 *   blocks: Array
 *   attributes: Plain object
 * 
 * // Blocks
 * 
 * comment
 *   text: String
 * horizontal-rule
 * heading
 *   level: Integer (1..6)
 *   line: Line object
 * paragraph
 *   lines: Array of line objects
 * list
 *   style: String ("bullet" or "number")
 *   items: Array of item objects
 * table
 *   rows: Array of arrays of cell objects
 *   attributes: Plain object
 * 
 * // Components
 * 
 * line
 *   text: String
 * item
 *   line: Line object
 *   lists: Array of list objects
 * range
 *   offset: Integer
 *   length: Integer
 * annotation
 *   type: String
 *   range: Range object
 *   data: Plain object
 * cell
 *   document: Object
 *   attributes: Plain object
 */

var wiki = {};

/**
 * Serializes offset-based annotations.
 */
wiki.AnnotationRenderer = function() {

	/* Private Members */

	var that = this;
	var insertions = {};

	/* Methods */

	this.wrapWithText = function( range, pre, post ) {
		var start = range.offset;
		if ( !( start in insertions ) ) {
			insertions[start] = [pre];
		} else {
			insertions[start].push( pre );
		}
		var end = range.offset + range.length;
		if ( !( end in insertions ) ) {
			insertions[end] = [post];
		} else {
			insertions[end].unshift( post );
		}
	};

	this.wrapWithXml = function( range, tag, attributes ) {
		that.wrapWithText(
			range, wiki.util.xml.open( tag, attributes ), wiki.util.xml.close( tag )
		);
	};

	this.apply = function( text ) {
		var out = '';
		for ( var i = 0, iMax = text.length; i <= iMax; i++ ) {
			if ( i in insertions ) {
				out += insertions[i].join( '' );
			}
			if ( i < iMax ) {
				out += text[i];
			}
		}
		return out;
	};
};

/**
 * Serializes a WikiDom into HTML.
 */
wiki.HtmlRenderer = function() {

	/* Private Members */

	var that = this;
	var blockRenderers = {
		'comment': renderComment,
		'horizontal-rule': renderHorizontalRule,
		'heading': renderHeading,
		'paragraph': renderParagraph,
		'list': renderList,
		'table': renderTable
	};

	/* Private Methods */

	function renderDocument( doc, rawFirstParagraph ) {
		var out = [];
		for ( var b = 0, bMax = doc.blocks.length; b < bMax; b++ ) {
			var block = doc.blocks[b];
			if ( block.type in blockRenderers ) {
				if ( block.type === 'paragraph' ) {
					out.push(
						renderParagraph( block, rawFirstParagraph && b === 0 )
					);
				} else {
					out.push( blockRenderers[block.type]( block ) );
				}
			}
		}
		return out.join( '\n' );
	}

	function renderComment( comment ) {
		return '<!--' + comment.text + '-->';
	}

	function renderHorizontalRule( rule ) {
		return wiki.util.xml.tag( 'hr', {}, false );
	}

	function renderHeading( heading ) {
		return wiki.util.xml.tag( 'h' + heading.level, {}, renderLine( heading.line ) );
	}

	function renderParagraph( paragraph, raw ) {
		var out = [];
		for ( var l = 0, lMax = paragraph.lines.length; l < lMax; l++ ) {
			out.push( renderLine( paragraph.lines[l] ) );
		}
		if ( raw ) {
			return out.join( '\n' );
		} else {
			return wiki.util.xml.tag( 'p', {}, out.join( '\n' ) );
		}
	}

	function renderList( list ) {
		var tags = {
			'bullet': 'ul',
			'number': 'ol'
		};
		var out = [];
		out.push( wiki.util.xml.open( tags[list.style] ) );
		for ( var i = 0, iMax = list.items.length; i < iMax; i++ ) {
			out.push( renderItem( list.items[i] ) );
		}
		out.push( wiki.util.xml.close( tags[list.style] ) );
		return out.join( '\n' );
	}

	function renderTable( table ) {
		var out = [];
		var types = {
			'heading': 'th',
			'data': 'td'
		};
		out.push( wiki.util.xml.open( 'table', table.attributes ) );
		for ( var r = 0, rMax = table.rows.length; r < rMax; r++ ) {
			out.push( wiki.util.xml.open( 'tr' ) );
			var row = table.rows[r];
			for ( var c = 0, cMax = row.length; c < cMax; c++ ) {
				var type = types[row[c].type || 'data'];
				out.push( wiki.util.xml.tag(
					type,
					row[c].attributes,
					renderDocument( row[c].document, true )
				) );
			}
			out.push( wiki.util.xml.close( 'tr' ) );
		}
		out.push( wiki.util.xml.close( 'table' ) );
		return out.join( '\n' );
	}

	function renderItem( item ) {
		if ( 'lists' in item && item.lists.length ) {
			var out = [];
			out.push( wiki.util.xml.open( 'li' ) + renderLine( item.line ) );
			for ( var l = 0, lMax = item.lists.length; l < lMax; l++ ) {
				out.push( renderList( item.lists[l] ) );
			}
			out.push( wiki.util.xml.close( 'li' ) )
			return out.join( '\n' );
		} else {
			return wiki.util.xml.tag( 'li', {}, renderLine( item.line ) );
		}
	}

	function renderLine( line ) {
		if ( 'annotations' in line && line.annotations.length ) {
			var ar = new wiki.AnnotationRenderer();
			for ( var a = 0, aMax = line.annotations.length; a < aMax; a++ ) {
				var an = line.annotations[a];
				switch ( an.type ) {
					case 'bold':
						ar.wrapWithXml( an.range, 'strong' );
						break;
					case 'italic':
						ar.wrapWithXml( an.range, 'em' );
						break;
					case 'xlink':
						ar.wrapWithXml( an.range, 'a', { 'href': an.data.url } );
						break;
					case 'ilink':
						ar.wrapWithXml( an.range, 'a', { 'href': '/wiki/' + an.data.title } );
						break;
				}
			}
			return ar.apply( line.text );
		} else {
			return line.text;
		}
	}

	/* Methods */

	this.render = function( doc ) {
		return renderDocument( doc );
	};
};

/**
 * Serializes a WikiDom into Wikitext.
 */
wiki.WikitextRenderer = function() {

	/* Private Members */

	var that = this;
	var blockRenderers = {
		'comment': renderComment,
		'horizontal-rule': renderHorizontalRule,
		'heading': renderHeading,
		'paragraph': renderParagraph,
		'list': renderList,
		'table': renderTable
	};

	/* Private Methods */

	function renderDocument( doc, rawFirstParagraph ) {
		var out = [];
		for ( var b = 0, bMax = doc.blocks.length; b < bMax; b++ ) {
			var block = doc.blocks[b];
			if ( block.type in blockRenderers ) {
				if ( block.type === 'paragraph' ) {
					out.push(
						renderParagraph( block, rawFirstParagraph && b === 0 )
					);
					if ( b + 1 < bMax /* && doc.blocks[b + 1].type === 'paragraph' */ ) {
						out.push( '' );
					}
				} else {
					out.push( blockRenderers[block.type]( block ) );
				}
			}
		}
		return out.join( '\n' );
	}

	function renderComment( comment ) {
		return '<!--' + comment.text + '-->';
	}

	function renderHorizontalRule( rule ) {
		return '----';
	}

	function renderHeading( heading ) {
		var symbols = wiki.util.str.repeat( '=', heading.level );
		return symbols + renderLine( heading.line ) + symbols;
	}

	function renderParagraph( paragraph ) {
		var out = [];
		for ( var l = 0, lMax = paragraph.lines.length; l < lMax; l++ ) {
			out.push( renderLine( paragraph.lines[l] ) );
		}
		return out.join( '\n' );
	}

	function renderList( list, path ) {
		if ( typeof path === 'undefined' ) {
			path = '';
		}
		var symbols = {
			'bullet': '*',
			'number': '#'
		};
		path += symbols[list.style];
		var out = [];
		for ( var i = 0, iMax = list.items.length; i < iMax; i++ ) {
			out.push( renderItem( list.items[i], path ) );
		}
		return out.join( '\n' );
	}

	function renderTable( table ) {
		var out = [];
		var types = {
			'heading': '!',
			'data': '|'
		};
		out.push( '{|' + wiki.util.xml.attr( table.attributes ) );
		for ( var r = 0, rMax = table.rows.length; r < rMax; r++ ) {
			var row = table.rows[r];
			if ( r ) {
				out.push( '|-' );
			}
			for ( var c = 0, cMax = row.length; c < cMax; c++ ) {
				var type = types[row[c].type || 'data'];
				out.push(
					type
					+ ( row[c].attributes ? wiki.util.xml.attr( row[c].attributes ) + '|' : '' )
					+ renderDocument( row[c].document, true )
				);
			}
		}
		out.push( '|}' );
		return out.join( '\n' );
	}

	function renderItem( item, path ) {
		if ( 'lists' in item && item.lists.length ) {
			var out = [];
			out.push( path + ' ' + renderLine( item.line ) );
			for ( var l = 0, lMax = item.lists.length; l < lMax; l++ ) {
				out.push( renderList( item.lists[l], path ) );
			}
			return out.join( '\n' );
		} else {
			return path + ' ' + renderLine( item.line );
		}
	}

	function renderLine( line ) {
		if ( 'annotations' in line && line.annotations.length ) {
			var ar = new wiki.AnnotationRenderer();
			for ( var a = 0, aMax = line.annotations.length; a < aMax; a++ ) {
				var an = line.annotations[a];
				switch ( an.type ) {
					case 'bold':
						ar.wrapWithText( an.range, '\'\'\'', '\'\'\'' );
						break;
					case 'italic':
						ar.wrapWithText( an.range, '\'\'', '\'\'' );
						break;
					case 'xlink':
						ar.wrapWithText( an.range, '[' + an.data.url + ' ', ']' );
						break;
					case 'ilink':
						ar.wrapWithText( an.range, '[[' + an.data.title + '|', ']]' );
						break;
				}
			}
			return ar.apply( line.text );
		} else {
			return line.text;
		}
	}

	/* Methods */

	this.render = function( doc ) {
		return renderDocument( doc );
	};
};

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
		'attr': function( attributes ) {
			var attr = '';
			if ( attributes ) {
				for ( var name in attributes ) {
					attr += ' ' + name + '="' + attributes[name] + '"';
				}
			}
			return attr;
		},
		'open': function( tag, attributes ) {
			return '<' + tag + wiki.util.xml.attr( attributes ) + '>';
		},
		'close': function( tag ) {
			return '</' + tag + '>';
		},
		'tag': function( tag, attributes, value, escape ) {
			if ( value === false ) {
				return '<' + tag + wiki.util.xml.attr( attributes ) + ' />';
			} else {
				if ( escape ) {
					value = wiki.util.xml.esc( value );
				}
				return '<' + tag + wiki.util.xml.attr( attributes ) + '>' + value
					+ '</' + tag + '>';
			}
		}
	}
};
