/* Utilities */

var Xml = {
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
		return '<' + tag + Xml.attr( attributes ) + '>';
	},
	'close': function( tag ) {
		return '</' + tag + '>';
	},
	'tag': function( tag, attributes, value, escape ) {
		if ( value === false ) {
			return '<' + tag + Xml.attr( attributes ) + ' />';
		} else {
			if ( escape ) {
				value = Xml.esc( value );
			}
			return '<' + tag + Xml.attr( attributes ) + '>' + value + '</' + tag + '>';
		}
	}
};

function repeat( pattern, count ) {
	if ( count < 1 ) return '';
	var result = '';
	while ( count > 0 ) {
		if ( count & 1 ) result += pattern;
		count >>= 1, pattern += pattern;
	};
	return result;
};

function AnnotationSerialization() {
	this.insertions = {};
}

AnnotationSerialization.prototype.wrapWithText = function( range, pre, post ) {
	var start = range.offset;
	if ( !( start in this.insertions ) ) {
		this.insertions[start] = [pre];
	} else {
		this.insertions[start].push( pre );
	}
	var end = range.offset + range.length;
	if ( !( end in this.insertions ) ) {
		this.insertions[end] = [post];
	} else {
		this.insertions[end].unshift( post );
	}
};

AnnotationSerialization.prototype.wrapWithXml = function( range, tag, attributes ) {
	this.wrapWithText( range, Xml.open( tag, attributes ), Xml.close( tag ) );
};

AnnotationSerialization.prototype.apply = function( text ) {
	var out = '';
	for ( var i = 0, iMax = text.length; i <= iMax; i++ ) {
		if ( i in this.insertions ) {
			out += this.insertions[i].join( '' );
		}
		if ( i < iMax ) {
			out += text[i];
		}
	}
	return out;
};

/* Renderers */

function HtmlRenderer() { }

HtmlRenderer.prototype.render = function( doc ) {
	return HtmlRenderer.renderers.document( doc );
};

HtmlRenderer.renderers = {
	'document': function( doc, rawFirstParagraph ) {
		var out = [];
		for ( var b = 0, bMax = doc.blocks.length; b < bMax; b++ ) {
			var block = doc.blocks[b];
			if ( block.type in HtmlRenderer.renderers ) {
				if ( block.type === 'paragraph' ) {
					out.push(
						HtmlRenderer.renderers.paragraph( block, rawFirstParagraph && b === 0 )
					);
				} else {
					out.push( HtmlRenderer.renderers[block.type]( block ) );
				}
			}
		}
		return out.join( '\n' );
	},
	'comment': function( comment ) {
		return '<!--' + comment.text + '-->';
	},
	'rule': function( rule ) {
		return Xml.tag( 'hr', {}, false );
	},
	'heading': function( heading ) {
		return Xml.tag( 'h' + heading.level, {}, HtmlRenderer.renderers.line( heading.line ) );
	},
	'paragraph': function( paragraph, raw ) {
		var out = [];
		for ( var l = 0, lMax = paragraph.lines.length; l < lMax; l++ ) {
			out.push( HtmlRenderer.renderers.line( paragraph.lines[l] ) );
		}
		if ( raw ) {
			return out.join( '\n' );
		} else {
			return Xml.tag( 'p', {}, out.join( '\n' ) );
		}
	},
	'list': function( list ) {
		var tags = {
			'bullet': 'ul',
			'number': 'ol'
		};
		var out = [];
		out.push( Xml.open( tags[list.style] ) );
		for ( var i = 0, iMax = list.items.length; i < iMax; i++ ) {
			out.push( HtmlRenderer.renderers.item( list.items[i] ) );
		}
		out.push( Xml.close( tags[list.style] ) );
		return out.join( '\n' );
	},
	'table': function( table ) {
		var out = [];
		var types = {
			'heading': 'th',
			'data': 'td'
		};
		out.push( Xml.open( 'table', table.attributes ) );
		for ( var r = 0, rMax = table.rows.length; r < rMax; r++ ) {
			out.push( Xml.open( 'tr' ) );
			var row = table.rows[r];
			for ( var c = 0, cMax = row.length; c < cMax; c++ ) {
				var type = types[row[c].type || 'data'];
				out.push( Xml.tag(
					type,
					row[c].attributes,
					HtmlRenderer.renderers.document( row[c].document, true )
				) );
			}
			out.push( Xml.close( 'tr' ) );
		}
		out.push( Xml.close( 'table' ) );
		return out.join( '\n' );
	},
	'item': function( item ) {
		if ( 'lists' in item && item.lists.length ) {
			var out = [];
			out.push( Xml.open( 'li' ) + HtmlRenderer.renderers.line( item.line ) );
			for ( var l = 0, lMax = item.lists.length; l < lMax; l++ ) {
				out.push( HtmlRenderer.renderers.list( item.lists[l] ) );
			}
			out.push( Xml.close( 'li' ) )
			return out.join( '\n' );
		} else {
			return Xml.tag( 'li', {}, HtmlRenderer.renderers.line( item.line ) );
		}
	},
	'line': function( line ) {
		if ( 'annotations' in line && line.annotations.length ) {
			var as = new AnnotationSerialization();
			for ( var a = 0, aMax = line.annotations.length; a < aMax; a++ ) {
				var an = line.annotations[a];
				switch ( an.type ) {
					case 'bold':
						as.wrapWithXml( an.range, 'strong' );
						break;
					case 'italic':
						as.wrapWithXml( an.range, 'em' );
						break;
					case 'xlink':
						as.wrapWithXml( an.range, 'a', { 'href': an.data.url } );
						break;
					case 'ilink':
						as.wrapWithXml( an.range, 'a', { 'href': '/wiki/' + an.data.title } );
						break;
				}
			}
			return as.apply( line.text );
		} else {
			return line.text;
		}
	}
};

function WikitextRenderer() { }

WikitextRenderer.prototype.render = function( doc ) {
	return WikitextRenderer.renderers.document( doc );
};

WikitextRenderer.renderers = {
	'document': function( doc, rawFirstParagraph ) {
		var out = [];
		for ( var b = 0, bMax = doc.blocks.length; b < bMax; b++ ) {
			var block = doc.blocks[b];
			if ( block.type in WikitextRenderer.renderers ) {
				if ( block.type === 'paragraph' ) {
					out.push(
						WikitextRenderer.renderers.paragraph( block, rawFirstParagraph && b === 0 )
					);
					if ( b + 1 < bMax /* && doc.blocks[b + 1].type === 'paragraph' */ ) {
						out.push( '' );
					}
				} else {
					out.push( WikitextRenderer.renderers[block.type]( block ) );
				}
			}
		}
		return out.join( '\n' );
	},
	'comment': HtmlRenderer.renderers.comment,
	'rule': function( rule ) {
		return '----';
	},
	'heading': function( heading ) {
		var symbols = repeat( '=', heading.level );
		return symbols + WikitextRenderer.renderers.line( heading.line ) + symbols;
	},
	'paragraph': function( paragraph ) {
		var out = [];
		for ( var l = 0, lMax = paragraph.lines.length; l < lMax; l++ ) {
			out.push( WikitextRenderer.renderers.line( paragraph.lines[l] ) );
		}
		return out.join( '\n' );
	},
	'list': function( list, path ) {
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
			out.push( WikitextRenderer.renderers.item( list.items[i], path ) );
		}
		return out.join( '\n' );
	},
	'table': function( table ) {
		var out = [];
		var types = {
			'heading': '!',
			'data': '|'
		};
		out.push( '{|' + Xml.attr( table.attributes ) );
		for ( var r = 0, rMax = table.rows.length; r < rMax; r++ ) {
			var row = table.rows[r];
			if ( r ) {
				out.push( '|-' );
			}
			for ( var c = 0, cMax = row.length; c < cMax; c++ ) {
				var type = types[row[c].type || 'data'];
				out.push(
					type
					+ ( row[c].attributes ? Xml.attr( row[c].attributes ) + '|' : '' )
					+ WikitextRenderer.renderers.document( row[c].document, true )
				);
			}
		}
		out.push( '|}' );
		return out.join( '\n' );
	},
	'item': function( item, path ) {
		if ( 'lists' in item && item.lists.length ) {
			var out = [];
			out.push( path + ' ' + WikitextRenderer.renderers.line( item.line ) );
			for ( var l = 0, lMax = item.lists.length; l < lMax; l++ ) {
				out.push( WikitextRenderer.renderers.list( item.lists[l], path ) );
			}
			return out.join( '\n' );
		} else {
			return path + ' ' + WikitextRenderer.renderers.line( item.line );
		}
	},
	'line': function( line ) {
		if ( 'annotations' in line && line.annotations.length ) {
			var as = new AnnotationSerialization();
			for ( var a = 0, aMax = line.annotations.length; a < aMax; a++ ) {
				var an = line.annotations[a];
				switch ( an.type ) {
					case 'bold':
						as.wrapWithText( an.range, '\'\'\'', '\'\'\'' );
						break;
					case 'italic':
						as.wrapWithText( an.range, '\'\'', '\'\'' );
						break;
					case 'xlink':
						as.wrapWithText( an.range, '[' + an.data.url + ' ', ']' );
						break;
					case 'ilink':
						as.wrapWithText( an.range, '[[' + an.data.title + '|', ']]' );
						break;
				}
			}
			return as.apply( line.text );
		} else {
			return line.text;
		}
	}
};

/* Parsers */

function WikitextParser() { }

WikitextParser.prototype.parse = function( text ) {
	//
};
