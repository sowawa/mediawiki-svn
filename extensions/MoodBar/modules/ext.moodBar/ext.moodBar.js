/**
 * Front-end scripting for the MoodBar MediaWiki extension
 *
 * @author Timo Tijhof, 2011
 */
( function( $ ) {

	var mb = mw.moodBar = {

		ui: {
			// jQuery objects
			pMoodbar: null,
			trigger: null,
			overlay: null // @todo
		},
		event: {
			trigger: function( e ) {
				e.preventDefault();
			}
		},
		init: function() {
			var ui = mb.ui;

			// Create portlet
			ui.pMoodbar = $( '<div id="p-moodbar"></div>' );

			// Create trigger
			ui.trigger = $( '<a>', {
				href: '#',
				title: mw.msg( 'tooltip-p-moodbar-trigger-using' ),
				text: mw.msg( 'moodbar-trigger-using', mw.config.get( 'wgSiteName' ) ),
				click: mb.event.trigger
			});

			// Insert trigger into portlet
			ui.trigger
				.wrap( '<p>' )
				.parent()
				.appendTo( ui.pMoodbar );

			// Inject portlet into document, when document is ready
			$( mb.inject );
		},
		inject: function() {
			$( '#mw-head' ).append( mb.ui.pMoodbar );
		}
	
	};

	mw.moodBar.init();

} )( jQuery );
