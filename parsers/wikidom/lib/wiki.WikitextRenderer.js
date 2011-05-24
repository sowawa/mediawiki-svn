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
