/* Preview module for wikiEditor */
( function( $ ) { $.wikiEditor.modules.preview = {

/**
 * API accessible functions
 */
api: {
	//
},
/**
 * Internally used functions
 */
fn: {
	/**
	 * Creates a preview module within a wikiEditor
	 * @param context Context object of editor to create module in
	 * @param config Configuration object to create module from
	 */
	create: function( context, config ) {
		if ( 'preview' in context.modules ) {
			return;
		}
		context.modules.preview = {
			'previewText': null,
			'changesText': null
		};
		context.$preview = context.fn.addView( {
			'name': 'preview',
			'titleMsg': 'wikieditor-preview-tab',
			'init': function( context ) {
				// Gets the latest copy of the wikitext
				var wikitext = context.fn.getContents();
				// Aborts when nothing has changed since the last preview
				if ( context.modules.preview.previewText == wikitext ) {
					return;
				}
				context.$preview.find( '.wikiEditor-preview-contents' ).empty();
				context.$preview.find( '.wikiEditor-preview-loading' ).show();
				$.post(
					wgScriptPath + '/api.php',
					{
						'action': 'parse',
						'title': wgPageName,
						'text': wikitext,
						'prop': 'text',
						'pst': '',
						'format': 'json'
					},
					function( data ) {
						if (
							typeof data.parse == 'undefined' ||
							typeof data.parse.text == 'undefined' ||
							typeof data.parse.text['*'] == 'undefined'
						) {
							return;
						}
						context.modules.preview.previewText = wikitext;
						context.$preview.find( '.wikiEditor-preview-loading' ).hide();
						context.$preview.find( '.wikiEditor-preview-contents' )
							.html( data.parse.text['*'] )
							.find( 'a:not([href^=#])' ).click( function() { return false; } );
					},
					'json'
				);
			}
		} );
		
		context.$changesTab = context.fn.addView( {
			'name': 'changes',
			'titleMsg': 'wikieditor-preview-changes-tab',
			'init': function( context ) {
				// Gets the latest copy of the wikitext
				var wikitext = context.fn.getContents();
				// Aborts when nothing has changed since the last time
				if ( context.modules.preview.changesText == wikitext ) {
					return;
				}
				context.$changesTab.find( 'table.diff tbody' ).empty();
				context.$changesTab.find( '.wikiEditor-preview-loading' ).show();
				
				var postdata = {
					'action': 'query',
					'indexpageids': '',
					'prop': 'revisions',
					'titles': wgPageName,
					'rvdifftotext': wikitext,
					'rvprop': '',
					'format': 'json'
				};
				var section = $( '[name=wpSection]' ).val();
				if ( section != '' )
					postdata['rvsection'] = section;
				
				$.post( wgScriptPath + '/api.php', postdata, function( data ) {
						// Add diff CSS
						if ( $( 'link[href=' + stylepath + '/common/diff.css]' ).size() == 0 ) {
							$( 'head' ).append( $( '<link />' ).attr( {
								'rel': 'stylesheet',
								'type': 'text/css',
								'href': stylepath + '/common/diff.css'
							} ) );
						}
						try {
							var diff = data.query.pages[data.query.pageids[0]]
								.revisions[0].diff['*'];
							context.$changesTab.find( 'table.diff tbody' )
								.html( diff );
							context.$changesTab
								.find( '.wikiEditor-preview-loading' ).hide();
							context.modules.preview.changesText = wikitext;
						} catch (e) { } // "blah is undefined" error, ignore
					}, 'json'
				);
			}
		} );

		var loadingMsg = gM( 'wikieditor-preview-loading' );
		context.$preview
			.add( context.$changesTab )
			.append( $( '<div />' )
				.addClass( 'wikiEditor-preview-loading' )
				.append( $( '<img />' )
					.addClass( 'wikiEditor-preview-spinner' )
					.attr( {
						'src': $.wikiEditor.imgPath + 'dialogs/loading.gif',
						'valign': 'absmiddle',
						'alt': loadingMsg,
						'title': loadingMsg
					} )
				)
				.append(
					$( '<span></span>' ).text( loadingMsg )
				)
			)
			.append( $( '<div />' )
				.addClass( 'wikiEditor-preview-contents' )
			);
		context.$changesTab.find( '.wikiEditor-preview-contents' )
			.html( '<table class="diff"><col class="diff-marker" /><col class="diff-content" />' +
				'<col class="diff-marker" /><col class="diff-content" /><tbody /></table>' );
	}
}

}; } )( jQuery );