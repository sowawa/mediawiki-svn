/* -- (c) Aaron Schulz, Daniel Arnold 2008 */

/* Every time you change this JS please bump $wgFlaggedRevStyleVersion in FlaggedRevs.php */

var FlaggedRevs = {
	'messages': {
		'diffToggleShow'	: '(show changes)',
		'diffToggleHide'	: '(hide changes)',
		'logToggleShow'		: '(show log)',
		'logToggleHide'		: '(hide log)',
		'logDetailsShow'	: '(show details)',
		'logDetailsHide'	: '(hide details)',
		'toggleShow'    	: '(+)',
		'toggleHide'    	: '(-)'
	},
	/* Hide rating/diff clutter */
	'enableShowhide': function() {
		var toggle = document.getElementById('mw-fr-revisiontoggle');
		if( toggle ) {
			toggle.style.display = 'inline';
			var ratings = document.getElementById('mw-fr-revisionratings');
			if( ratings ) {
				ratings.style.display = 'none';
			}
		}
		toggle = document.getElementById('mw-fr-difftoggle');
		if( toggle ) {
			toggle.style.display = 'inline';
			var diff = document.getElementById('mw-fr-stablediff');
			if( diff ) {
				diff.style.display = 'none';
			}
		}
		toggle = document.getElementById('mw-fr-logtoggle');
		if( toggle ) {
			toggle.style.display = 'inline';
			var log = document.getElementById('mw-fr-logexcerpt');
			if( log ) {
				log.style.display = 'none';
			}
		}
	},
	
	/* Toggles ratings */
	'toggleRevRatings': function() {
		var ratings = document.getElementById('mw-fr-revisionratings');
		if( !ratings ) return;
		var toggle = document.getElementById('mw-fr-revisiontoggle');
		if( !toggle ) return;
		// Collapsed -> expand
		if( ratings.style.display == 'none' ) {
			ratings.style.display = 'block';
			if( toggle.tagName == 'IMG' ) { // arrow
				toggle.alt = this.messages.toggleHide; // fallback text
				toggle.src = toggle.src.replace( 'arrow-up.png', 'arrow-down.png' );
			} else { // text
				toggle.innerHTML = this.messages.toggleHide;
			}
		// Expanded -> collapse
		} else {
			ratings.style.display = 'none';
			if( toggle.tagName == 'IMG' ) { // arrow
				toggle.alt = this.messages.toggleShow; // fallback text
				toggle.src = toggle.src.replace( 'arrow-down.png', 'arrow-up.png' );
			} else { // text
				toggle.innerHTML = this.messages.toggleShow;
			}
		}
	},
	
	/* Toggles diffs */
	'toggleDiff': function() {
		var diff = document.getElementById('mw-fr-stablediff');
		if( !diff ) return;
		var toggle = document.getElementById('mw-fr-difftoggle');
		if( !toggle ) return;
		if( diff.style.display == 'none' ) {
			diff.style.display = 'block';
			toggle.innerHTML = this.messages.diffToggleHide;
		} else {
			diff.style.display = 'none';
			toggle.innerHTML = this.messages.diffToggleShow;
		}
	},
	
	/* Toggles log excerpts */
	'toggleLog': function() {
		var log = document.getElementById('mw-fr-logexcerpt');
		if( !log ) return;
		var toggle = document.getElementById('mw-fr-logtoggle');
		if( !toggle ) return;
		if( log.style.display == 'none' ) {
			log.style.display = 'block';
			toggle.innerHTML = this.messages.logToggleHide;
		} else {
			log.style.display = 'none';
			toggle.innerHTML = this.messages.logToggleShow;
		}
	},
	
	/* Toggles log excerpts */
	'toggleLogDetails': function() {
		var log = document.getElementById('mw-fr-logexcerpt');
		if( !log ) return;
		var toggle = document.getElementById('mw-fr-logtoggle');
		if( !toggle ) return;
		if( log.style.display == 'none' ) {
			log.style.display = 'block';
			toggle.innerHTML = this.messages.logDetailsHide;
		} else {
			log.style.display = 'none';
			toggle.innerHTML = this.messages.logDetailsShow;
		}
	}
};

addOnloadHook(FlaggedRevs.enableShowhide);
