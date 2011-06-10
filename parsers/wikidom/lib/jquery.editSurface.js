$.fn.editSurface = function( options ) {
	var $this = $(this);
	sel = {
		'active': false
	};
	
	options = $.extend( {
		// Defaults
		'document': null
	}, options );
	
	// Initialization
	$this
		.addClass( 'editSurface-container' )
		.append( '<div class="editSurface-document"></div>' )
		.after( '<div class="editSurface-cursor"></div>' )
		.before( '<div class="editSurface-range"></div>'
				+ '<div class="editSurface-range"></div>'
				+ '<div class="editSurface-range"></div>');
	
	// Shortcuts
	var $document = $this.find( '.editSurface-document' );
	var ranges = {
		'$all': $( '.editSurface-range' ),
		'$first': $( '.editSurface-range:eq(0)' ),
		'$fill': $( '.editSurface-range:eq(1)' ),
		'$last': $( '.editSurface-range:eq(2)' )
	};
	
	// Events
	$(document).bind( {
		'mousedown': function( e ) {
			var $target = $( e.target );
			if ( $target.is( '.editSurface-paragraph' ) ) {
				$target = $target.children().closestToOffset( { 'left': e.pageX, 'top': e.pageY } );
			}
			if ( !$target.is( '.editSurface-line' ) ) {
				var $line = $target.closest( '.editSurface-line' );
				if ( $line.length ) {
					$target = $line;
				} else {
					return;
				}
			}
			sel = {
				'active': true,
				'from': null,
				'to': null,
				'start': getCursorPosition( e.pageX, e.pageY, $target ),
				'end': null
			};
			cursor.show();
			drawSelection( sel.start.$target.parent() );
			// Move cursor
			if ( sel.start ) {
				cursor.$.css( {
					'top': sel.start.top,
					'left': sel.start.x
				} );
			}
			e.preventDefault();
			return false;
		},
		'mouseup': function( e ) {
			if ( sel.active ) {
				if ( !sel.from || !sel.to
						|| ( sel.from.line === sel.to.line && sel.from.char === sel.to.char ) ) {
					sel.from = null;
					sel.to = null;
					sel.start = null;
					sel.end = null;
					cursor.show();
					clearSelection();
				} else {
					drawSelection( sel.start.$target.parent() );
				}
				sel.active = false;
			}
		},
		'mousemove': function( e ) {
			if ( sel.active ) {
				var $target = $( e.target );
				if ( !$target.is( '.editSurface-line' ) ) {
					$target = sel.start.$target.parent().children().closestToOffset(
						{ 'left': e.pageX, 'top': e.pageY }
					);
				}
				sel.end = getCursorPosition( e.pageX, e.pageY, $target );
				//console.log( [sel.start.char, sel.end.char] );
				//console.log( [sel.start.word, sel.end.word] );
				//console.log( [sel.start.line, sel.end.line] );
				if ( sel.start.line < sel.end.line
						|| ( sel.start.line === sel.end.line
								&& sel.start.char < sel.end.char ) ) {
					sel.from = sel.start;
					sel.to = sel.end;
				} else {
					sel.from = sel.end;
					sel.to = sel.start;
				}
				cursor.hide();
				drawSelection( sel.start.$target.parent() );
			}
		}
	} );
	
	// Functions
	function getSelectionText() {
		var text;
		if ( sel.from && sel.to ) {
			if ( sel.from.line === sel.to.line ) {
				text = sel.from.$target.data( 'flow' ).text.substr(
					sel.from.char, sel.to.char - sel.from.char
				);
			} else {
				text = sel.from.$target.data( 'flow' ).text.substr( sel.from.char );
				var $sibling = sel.from.$target.next();
				for ( var i = sel.from.line + 1; i < sel.to.line; i++ ) {
					text += $sibling.data( 'flow' ).text
					$sibling = $sibling.next();
				}
				text += sel.to.$target.data( 'flow' ).text.substr( 0, sel.to.char );
			}
		}
		return text;
	}
	function getCursorPosition( x, y, $target ) {
		var line = $target.data( 'flow' ),
			offset = $target.offset(),
			height = $target.height(),
			l,
			r = 0,
			cur = x - offset.left;
		for ( var w = 0, eol = line.metrics.length - 1; w <= eol; w++ ) {
			l = r;
			r += line.metrics[w];
			if ( ( w === 0 && cur <= l ) || ( cur >= l && cur <= r ) || ( w === eol ) ) {
				var word = line.words[w],
					a,
					b = { 'l': l, 'c': l, 'r': l };
				for ( var c = 0, eow = word.metrics.length; c <= eow; c++ ) {
					a = b;
					b = {
						'l': a.r,
						'c': a.r + ( word.metrics[c] / 2 ),
						'r': a.r + word.metrics[c]
					};
					if ( ( c === 0 && cur <= a.l ) || ( cur >= a.c && cur <= b.c ) || c === eow ) {
						return {
							'$target': $target,
							'char': word.offset + Math.min( c, word.text.length - 1 ),
							'word': word.index,
							'line': line.index,
							'x': offset.left + b.l,
							'top': offset.top,
							'bottom': offset.top + height,
							'height': height
						};
					}
				}
			}
		}
	}
	
	function clearSelection() {
		ranges.$all.hide();
	}
	
	function drawSelection( $container ) {
		if ( sel.from && sel.to ) {
			if ( sel.from.line === sel.to.line ) {
				// 1 line
				if ( sel.from.char !== sel.to.char ) {
					ranges.$first.show().css( {
						'left': sel.from.x,
						'top': sel.from.top,
						'width': sel.to.x - sel.from.x,
						'height': sel.from.height
					} );
					ranges.$fill.hide();
					ranges.$last.hide();
					// XXX: Demo code!
					$( '#selection p' ).text( getSelectionText() );
					return;
				}
			} else if ( sel.from.line < sel.to.line ) {
				// 2+ lines
				ranges.$first.show().css( {
					'left': sel.from.x,
					'top': sel.from.top,
					'width': ( $container.innerWidth() - sel.from.x )
							+ $container.offset().left,
					'height': sel.from.height
				} );
				if ( sel.from.line < sel.to.line - 1 ) {
					ranges.$fill.show().css( {
						'left': $container.offset().left,
						'top': sel.from.bottom,
						'width': $container.innerWidth(),
						'height': sel.to.top - sel.from.bottom
					} );
				} else {
					ranges.$fill.hide();
				}
				ranges.$last.show().css( {
					'left': $container.offset().left,
					'top': sel.to.top,
					'width': sel.to.x - $container.offset().left,
					'height': sel.to.height
				} );
				// XXX: Demo code!
				$( '#selection p' ).text( getSelectionText() );
				return;
			}
		}
		// No selection
		ranges.$all.hide();
	}
	
	function renderDocument( doc ) {
		$document.empty();
		for ( var i = 0; i < doc.blocks.length; i++ ) {
			switch ( doc.blocks[i].type ) {
				case 'paragraph':
					renderParagraph( doc.blocks[i], $document )
					break;
			}
		}
	}
	
	function renderParagraph( paragraph, $container ) {
		var $paragraph = $( '<div class="editSurface-paragraph"></div>' ).appendTo( $container );
		var lines = [];
		for ( var i = 0; i < paragraph.lines.length; i++ ) {
			lines.push( paragraph.lines[i].text );
		}
		$paragraph.flow( lines.join( '\n' ) );
	}
	
	function update() {
		// Render document
		if ( options.document !== null ) {
			renderDocument( options.document, $this );
		}
	}
	
	var cursor = {
		'$': $( '.editSurface-cursor' ),
		'visible': true,
		'timeout': null,
		'speed': 500
	};
	cursor.blink = function() {
		// Flip
		cursor.visible = !cursor.visible;
		// Hide/show
		cursor.visible ? cursor.$.hide() : cursor.$.show();
		// Repeat
		cursor.timeout = setTimeout( cursor.blink, cursor.speed )
	}
	cursor.show = function() {
		// Start visible (will flip when run)
		cursor.visible = true;
		cursor.$.show();
		// Start/restart blinking
		clearTimeout( cursor.timeout );
		cursor.blink();
	};
	cursor.hide = function() {
		// Hide
		cursor.$.hide();
		// Stop blinking
		clearTimeout( cursor.timeout );
	};
	
	$(window).resize( update );
	update();
};
