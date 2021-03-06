/**
* Timed text edit interface based off of participatory culture foundation timed text mockups.
*/
( function( mw, $ ) {

mw.TimedTextEdit = function( parentTimedText ) {
	return this.init( parentTimedText );
};
mw.TimedTextEdit.prototype = {
	// The target container for the interface:
	target_container: null,

	// Interface steps can be "transcribe", "sync", "translate"
	textEditStages:{
		'upload':{
			'icon' : 'folder-open'
		}
		/*
		'transcribe':{
			'icon' : 'comment'
		},
		'sync':{
			'icon' : 'clock'
		},
		'translate':{
			'icon' : 'flag'
		}
		*/
	},

	/**
	 * @constructor
	 * @param {Object} parentTimedText The parent TimedText object that called the editor
	 */
	init: function( parentTimedText ) {
		this.parentTimedText = parentTimedText;
	},

	/**
	 * Show the editor UI
	 */
	showUI: function() {
		// Setup the parent container:
		this.createDialogContainer();

		// Setup the timedText editor interface
		this.initDialog();
	},

	/**
	 * Setup the dialog layout: s
	 */
	initDialog: function() {
		var _this =this;
		_this.createTabs();
	},

	/**
	 * Creates interface tabs from the textEditStages
	 */
	createTabs: function() {
		var _this = this;
		$tabContainer = $( '<div />' )
			.attr( 'id', "TimedTextEdit-tabs" )
			.append( '<ul />' );
		for(var edit_stage_id in this.textEditStages) {
			var editStage = this.textEditStages[ edit_stage_id ];
			// Append the menu item:
			$tabContainer.find('ul').append(
				$('<li>').append(
					$('<a>')
					.attr( 'href', '#tab-' + edit_stage_id )
					.append(
						$('<span />')
						.css( "float","left" )
						.addClass( 'ui-icon ui-icon-' + editStage.icon )
						,
						$('<span>')
						.text( gM('mwe-timedtext-stage-' + edit_stage_id) )
					)
				)
			);
			// Append the menu item content container
			$tabContainer.append(
				$('<div>')
				.attr( 'id', 'tab-' + edit_stage_id )
				.css({
					'height': $( window ).height() - 270,
					'position': 'relative'
				})
			);
		}
		//debugger
		// Add the tags to the target:
		$( _this.target_container ).append( $tabContainer );

		//Create all the "interfaces"
		for(var edit_stage_id in this.textEditStages) {
			_this.createInterface( edit_stage_id );
		}

		//Add tabs interface
		$('#TimedTextEdit-tabs').tabs( {
			select: function( event, ui ) {
				_this.selectTab( $( ui.tab ).attr( 'href' ).replace('#','') );
			}
		});

	},
	selectTab: function( tab_id ) {
		mw.log('sel: ' + tab_id);
	},

	/**
	 * Creates an interface for a given stage id
	 * @return {Object} the jquery interface
	 */
	createInterface: function( edit_stage_id) {
		$target = $('#tab-' + edit_stage_id);
		if( this[edit_stage_id + 'Interface']) {
			this[ edit_stage_id + 'Interface']( $target );
		}else{
			$target.append( ' interface under development' );
		}
	},
	/**
	* Builds out and binds the upload interface to a given target
	* @param {Object} $target jQuery target for the upload interface
	*/
	uploadInterface: function( $target ) {
		var _this = this;
		// Check if user has XHR file upload support & we are on the target wiki

		$target.append(
			$('<div />')
			.addClass( "leftcolumn" )
			.append('<h4>')
			.text( gM('mwe-timedtext-upload-text') ),
			$('<div />')
			.addClass( 'rightcolumn' )
			.append(
				$( '<span />' )
				.attr('id', "timed-text-rightcolum-desc")
				.append(
					$('<h4>')
						.text( gM('mwe-timedtext-upload-text-desc-title') ),
					$('<i>').text ( gM( 'mwe-timedtext-upload-text-desc-help' ) ),
					$('<ul>').append(
						$('<li>').text( gM('mwe-timedtext-upload-text-desc-help-browse') ),
						$('<li>').text( gM('mwe-timedtext-upload-text-desc-help-select') ),
						$('<li>').text( gM('mwe-timedtext-upload-text-desc-help-review') )
					)
				),
				//The text preview
				$('<h3>')
					.text( gM( 'mwe-timedtext-upload-text-preview' ) ),
				$('<textarea id="timed-text-file-preview"></textarea>')
			)
		);

		// Adjust the height of the text preview:
		$('#timed-text-file-preview')
		.css({
			'width':'100%',
			'height': '300px'
		});

		// Add Select file:
		$target.append(
			$('<div>').css({
				'width':'300px',
				'float': 'left'
			}).append(
				$('<input />')
				.attr( {
					'type': "file",
					'id' : "timed-text-file-upload"
				}),
				$('<br />')
			)
		);


		$target.append(
			//Get a little helper input field to update the language
			$('<input />')
			.attr( {
				'id' : "timed-text-langKey-input",
				'type' : "text",
				'maxlength' : "10",
				'size' :"3"
			} )
			.change(function() {
				var langKey = $(this).val();
				if( mw.Language.names[ langKey ] ) {
					$buttonTarget.find('.btnText').text(
						mw.Language.names[ langKey ]
					);
				}
			}),
			// Get a jQuery button object with language menu:
			$.button( {
				'style': { 'float' : 'left' },
				'class': 'language-select-btn',
				'text': gM('mwe-timedtext-select-language'),
				'icon': 'triangle-1-e'
			} )
			.attr('id', 'language-select')
		)


		var $buttonTarget = $target.find('.language-select-btn');

		// Add menu container:
		var loc = $buttonTarget.position();
		$target.append(
			$('<div>')
			.addClass('ui-widget ui-widget-content ui-corner-all')
			.attr( 'id', 'upload-language-select' )
			.loadingSpinner()
			.css( {
				'position' 	: 'relative',
				'z-index' 	: 10,
				'height'	: '180px',
				'width' 	: '180px',
				'overflow'	: 'auto',
				'font-size'	: '12px',
				'z-index'	: 1005
			} )
			.hide()
		);
		// Add menu binding to button target
		setTimeout(function(){
			$buttonTarget.menu( {
				'content'	: _this.getLanguageList(),
				'backLinkText' : gM( 'mwe-timedtext-back-btn' ),
				'targetMenuContainer': '#upload-language-select',
				'keepPosition' : true
			} );
			// force the layout ( menu binding does strange things )
			$('#upload-language-select').css( {'left': '315px', 'top' : '87px', 'position' : 'absolute'});
		},10);


		//Add upload input bindings:
		$( '#timed-text-file-upload' ).change( function( ev ) {
			if ( $(this).val() ) {

				// Update the preview text area:
				var file = $( '#timed-text-file-upload' ).get(0).files[0];
				if( file.fileSize > 1048576 ) {
					$( '#timed-text-file-preview' ).text( 'Error the file you selected is too lage');
					return ;
				}
				var srtData = file.getAsBinary();
				srtData = srtData.replace( '\r\n', '\n' );
				$( '#timed-text-file-preview' ).text( srtData );

				// Update the selected language
				var langKey = $(this).val().split( '.' );
				var extension = langKey.pop();
				langKey = langKey.pop();
				if( mw.Language.names[ langKey ] ) {
					$buttonTarget.find('.btnText').text(
						mw.Language.names[ langKey ]
					);
					// Update the key code
					$('#timed-text-langKey-input').val( langKey );
				}
			}
		});

		//Add an upload button:
		$target.append(
			$('<div />')
			.css('clear', 'both'),
			$('<br />'),
			$('<br />'),
			$.button( {
				'style': { 'float' : 'left' },
				'text': gM('mwe-timedtext-upload-text'),
				'icon': 'disk'
			} )
			.click( function() {
				_this.uploadTextFile();
			})
		);

	},
	/**
	 * Uploads the text content
	 */
	uploadTextFile: function() {
		// Put a dialog ontop
		mw.addLoaderDialog( gM( 'mwe-timedtext-uploading-text') );

		// Get timed text target title
		// NOTE: this should be cleaned up with accessors
		var targetTitleKey = this.parentTimedText.embedPlayer.apiTitleKey;

		// Add TimedText NS and language key and ".srt"
		targetTitleKey = 'TimedText:' + targetTitleKey + '.' + $('#timed-text-langKey-input').val() + '.srt';

		// Get a token
		mw.getToken( targetTitleKey, function( token ) {
			mw.log("got token: " + token);
			var request = {
				'action' : 'edit',
				'title' : targetTitleKey,
				'text' : $('#timed-text-file-preview').val(),
				'token': token
			};
			mw.getJSON( request, function( data ) {
				//Close the loader dialog:
				mw.closeLoaderDialog();

				if( data.edit && data.edit.result == 'Success' ) {
					var buttons = { };
					buttons[ gM("mwe-timedtext-upload-text-another")] = function() {
						// just close the current dialog:
						$( this ).dialog('close');
					};
					buttons[ gM( "mwe-timedtext-upload-text-done-uploading" ) ] = function() {
						window.location.reload();
					};
					//Edit success
					setTimeout(function(){
						mw.addDialog( {
							'width' : '400px',
							'title' : gM( "mwe-timedtext-upload-text-done"),
							'content' : gM("mwe-timedtext-upload-text-success"),
							'buttons' : buttons
						});
					}, 10 );
				}else{
					//Edit fail
					setTimeout(function(){
						mw.addDialog({
							'width' : '400px',
							'title' : gM( "mwe-timedtext-upload-text-fail-title"),
							'content' :gM( "mwe-timedtext-upload-text-fail-desc"),
							'buttons' : gM( 'mwe-ok' )
						});
					},10 );
				}
			});
		})
	},
	
	/**
	 * Gets the language set.
	 *
	 * Checks off languages that area already "loaded" according to parentTimedText
	 *
	 * This is cpu intensive function
	 *	Optimize: switch to html string building, insert and bind
	 * 		(instead of building html with jquery calls )
	 * 	Optimize: pre-sort both language lists and continue checks where we left off
	 *
	 *  ~ what really a lot of time is putting this ~into~ the dom ~
	 */
	getLanguageList: function() {
		var _this = this;
		var $langMenu = $( '<ul>' );
		// Loop through all supported languages:
		for ( var langKey in mw.Language.names ) {
			var language = mw.Language.names [ langKey ];
			var source_icon = 'radio-on';
			//check if the key is in the _this.parentTimedText source array
			for( var i in _this.parentTimedText.textSources ) {
				var pSource = _this.parentTimedText.textSources[i];
				if( pSource.lang == langKey) {
					source_icon = 'bullet';
				}
			}
			// call out to "anonymous" function to variable scope the langKey
			$langMenu.append(
				_this.getLangMenuItem( langKey , source_icon)
			);
		}
		return $langMenu;
	},
	
	getLangMenuItem: function( langKey , source_icon) {
		return $.getLineItem(
			langKey + ' - ' + mw.Language.names[ langKey ],
			source_icon,
			function() {
				mw.log( "Selected: " + langKey );
				// Update the input box text
				$('#timed-text-langKey-input').val( langKey );
				// Update the menu item:
				$('#language-select').find('.btnText').text( mw.Language.names[ langKey ] )
			}
			);
	},
	/**
	 * Creates the interface dialog container
	 */
	createDialogContainer: function() {
		var _this = this;
		//Setup the target container:
		_this.target_container = '#timedTextEdit_target';
		$( _this.target_container ).remove();
		$( 'body' ).append(
			$('<div>')
				.attr({
					'id' : 'timedTextEdit_target',
					'title' : gM( 'mwe-timedtext-editor' )
				})
				.addClass('TimedTextEdit')
		);

		// Build cancel button
		var cancelButton = {};
		var cancelText = gM( 'mwe-cancel' );
		cancelButton[ cancelText ] = function() {
			_this.onCancelClipEdit();
		};

		$( _this.target_container ).dialog( {
			bgiframe: true,
			autoOpen: true,
			width: $(window).width()-50,
			height: $(window).height()-50,
			position : 'center',
			modal: true,
			draggable: false,
			resizable: false,
			buttons: cancelButton,
			close: function() {
				// @@TODO if we are 'editing' we should confirm they want to exist:
				$( this ).parents( '.ui-dialog' ).fadeOut( 'slow' );
			}
		} );
		// set a non-blocking fit window request
		setTimeout(function(){
			$( _this.target_container ).dialogFitWindow();
		},10);

		// Add the window resize hook to keep dialog layout
		$( window ).resize( function() {
			$( _this.target_container ).dialogFitWindow();
		} );

	},

	onCancelClipEdit: function() {
		var _this = this;
		// Cancel edit
		$( _this.target_container ).dialog( 'close' );
	}
};

} )( window.mediaWiki, window.jQuery );