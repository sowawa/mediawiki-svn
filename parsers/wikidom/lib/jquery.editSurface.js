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
	
	$(document)
		.mousedown( function( e ) {
			var $target = $( e.target );
			// TODO: If the target is not a line, find the nearest line to the cursor and use it
			if ( $target.is( '.editSurface-line' ) ) {
				e.preventDefault();
				sel = {
					'active': true,
					'from': null,
					'to': null,
					'start': getCursorPosition( e ),
					'end': null
				};
				console.log( sel.start );
				cursor.show();
				drawSelection( $target.parent() );
				// Move cursor
				if ( sel.start ) {
					cursor.$.css( {
						'top': sel.start.top,
						'left': sel.start.x
					} );
				}
			}
			return false;
		} )
		.mouseup( function( e ) {
			var $target = $( e.target );
			if ( $target.is( '.editSurface-line' ) && ( !sel.from || !sel.to
					|| ( sel.from.line === sel.to.line && sel.from.index === sel.to.index ) ) ) {
				sel.from = null;
				sel.to = null;
				sel.start = null;
				sel.end = null;
				cursor.show();
			}
			sel.active = false;
			drawSelection( $target.parent() );
		} )
		.mousemove( function( e ) {
			var $target = $( e.target );
			// TODO: If the target is not a line, find the nearest line to the cursor and use it
			if ( $target.is( '.editSurface-line' ) && sel.active ) {
				sel.end = getCursorPosition( e );
				if ( sel.start && sel.end && sel.start.line < sel.end.line
						|| ( sel.start.line === sel.end.line
								&& sel.start.index < sel.end.index ) ) {
					sel.from = sel.start;
					sel.to = sel.end;
				} else {
					sel.from = sel.end;
					sel.to = sel.start;
				}
				cursor.hide();
				drawSelection( $target.parent() );
			}
		} );
	
	
	// Shortcuts
	var $document = $this.find( '.editSurface-document' );
	var ranges = {
		'$all': $( '.editSurface-range' ),
		'$first': $( '.editSurface-range:eq(0)' ),
		'$fill': $( '.editSurface-range:eq(1)' ),
		'$last': $( '.editSurface-range:eq(2)' )
	};
	
	// Functions
	function getSelectionText() {
		var text;
		if ( sel.from && sel.to ) {
			if ( sel.from.line === sel.to.line ) {
				text = sel.from.$target.data( 'text' ).substr(
					sel.from.index, sel.to.index - sel.from.index
				);
			} else {
				text = sel.from.$target.data( 'text' ).substr( sel.from.index );
				var $sibling = sel.from.$target.next();
				for ( var i = sel.from.line + 1; i < sel.to.line; i++ ) {
					text += $sibling.data( 'text' )
					$sibling = $sibling.next();
				}
				text += sel.to.$target.data( 'text' ).substr( 0, sel.to.index );
			}
		}
		return text;
	}
	// TODO: Take x and y, and infer the target
	function getCursorPosition( e ) {
		var $target = $( e.target );
		var metrics = $target.data( 'metrics' );
		var text = $target.data( 'text' );
		var line = $target.data( 'line' );
		if ( !$.isArray( metrics ) || metrics.length === 0 ) {
			return null;
		}
		var to = metrics.length - 1;
		var a;
		var b = { 'l': 0, 'c': 0, 'r': 0 };
		var x = e.layerX;
		for ( var i = 0; i <= to; i++ ) {
			a = b;
			b = { 'l': a.r, 'c': a.r + ( metrics[i] / 2 ), 'r': a.r + metrics[i] };
			if ( ( i === 0 && x < a.l ) || ( x > a.c && x <= b.c ) || ( i === to && x >= b.r ) ) {
				var offset = $target.offset();
				var height = $target.height();
				return {
						'$target': $target,
						'index': i,
						'line': line,
						'x': offset.left + b.l,
						'top': offset.top,
						'bottom': offset.top + height,
						'height': height
				};
			}
		}
		return null;
	}
	
	function drawSelection( $container ) {
		if ( sel.from && sel.to ) {
			if ( sel.from.line === sel.to.line ) {
				// 1 line
				if ( sel.from.index !== sel.to.index ) {
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
		$paragraph.flow( lines.join( ' ' ) );
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
