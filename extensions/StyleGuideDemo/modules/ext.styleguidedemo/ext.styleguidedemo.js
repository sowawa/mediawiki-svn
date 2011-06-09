/**
 * Script for the Style guide demo extension.
 */
jQuery( document ).ready(function( $ ) {

	// Set up the help system
	$( '.mw-help-field-data' )
		.hide()
		.closest( '.mw-help-field-container' )
			.find( '.mw-help-field-hint' )
				.show()
				.click( function() {
					$(this)
					.closest( '.mw-help-field-container' )
						.find( '.mw-help-field-data' )
							.slideToggle( 'fast' );
				} );

});
