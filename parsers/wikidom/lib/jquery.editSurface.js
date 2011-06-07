$.fn.editSurface = function( options ) {
	var $this = $(this);
	
	options = $.extend( {
		// Defaults
		'document': null
	}, options );
	
	$this
		.addClass( 'editSurface-container' )
		.append( '<div class="editSurface-document"></div>' )
		.before( '<div class="editSurface-range"></div>'
				+ '<div class="editSurface-range"></div>'
				+ '<div class="editSurface-range"></div>');
	var ranges = {
		'$all': $( '.editSurface-range' ),
		'$first': $( '.editSurface-range:eq(0)' ),
		'$fill': $( '.editSurface-range:eq(1)' ),
		'$last': $( '.editSurface-range:eq(2)' )
	};
	var $document = $this.find( '.editSurface-document' );
	var sel = {
		'active': false,
		'from': null,
		'to': null,
		'start': null,
		'end': null
	};
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
	function getSelection( e ) {
		var $target = $( e.target );
		var metrics = $target.data( 'metrics' );
		var text = $target.data( 'text' );
		var line = $target.data( 'line' );
		if ( !$.isArray( metrics ) || metrics.length === 0 ) {
			return null;
		}
		var to = metrics.length - 1;
		var l = 0;
		var r = 0;
		for ( var i = 0; i <= to; i++ ) {
			l = r;
			r += metrics[i];
			if ( ( e.layerX > l && e.layerX <= r ) || ( e.layerX >= r && i == to ) ) {
				var offset = $target.offset();
				var height = $target.height();
				return {
						'$target': $target,
						'index': i,
						'line': line,
						'top': offset.top,
						'left': offset.left + l,
						'right': r,
						'bottom': offset.top + height,
						'width': r - l,
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
						'left': sel.from.left,
						'top': sel.from.top,
						'width': sel.to.right - sel.from.right,
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
					'left': sel.from.left,
					'top': sel.from.top,
					'width': ( $container.innerWidth() - sel.from.left )
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
					'width': sel.to.left - $container.offset().left,
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
		$paragraph
			.mousedown( function( e ) {
				// TODO: If the target is not a line, find the nearest line to the cursor and use it
				if ( $( e.target ).is( '.editSurface-line' ) ) {
					e.preventDefault();
					e.stopPropagation();
					sel = {
						'active': true,
						'from': null,
						'to': null,
						'start': getSelection( e ),
						'end': null
					};
					drawSelection( $paragraph );
				}
				return false;
			} )
			.mouseup( function( e ) {
				if ( !$( e.target ).is( '.editSurface-line' ) || !sel.from || !sel.to
						|| ( sel.from.line === sel.to.line && sel.from.index === sel.to.index ) ) {
					sel.from = null;
					sel.to = null;
					sel.start = null;
					sel.end = null;
				}
				sel.active = false;
				drawSelection( $paragraph );
			} )
			.mousemove( function( e ) {
				// TODO: If the target is not a line, find the nearest line to the cursor and use it
				if ( $( e.target ).is( '.editSurface-line' ) && sel.active ) {
					sel.end = getSelection( e );
					if ( sel.start.line < sel.end.line
							|| ( sel.start.line === sel.end.line
									&& sel.start.index < sel.end.index ) ) {
						sel.from = sel.start;
						sel.to = sel.end;
					} else {
						sel.from = sel.end;
						sel.to = sel.start;
					}
					drawSelection( $paragraph );
				}
			} );
	}
	
	function update() {
		// Render document
		if ( options.document !== null ) {
			renderDocument( options.document, $this );
		}
	}
	
	$(window).resize( update );
	update();
};
