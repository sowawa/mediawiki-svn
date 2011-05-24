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
