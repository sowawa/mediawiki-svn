/**
 * FlaggedRevs Stylesheet
 * @author Aaron Schulz
 * @author Krinkle <krinklemail@gmail.com> 2011
 */

window.FlaggedRevs = {
	/* Dropdown collapse timer */
	'boxCollapseTimer': null,

	/* Enables rating/diff clutter via show/hide */
	'enableShowhide': function() {
		// Rating detail box
		var toggle = document.getElementById('mw-fr-revisiontoggle');
		if ( toggle ) {
			toggle.style.display = 'inline'; /* show toggle control */
			this.hideBoxDetails(); /* hide the initially displayed ratings */
		}
		// Diff detail box
		toggle = document.getElementById('mw-fr-difftoggle');
		if ( toggle ) {
			toggle.style.display = 'inline'; /* show toggle control */
			var diff = document.getElementById('mw-fr-stablediff');
			if ( diff ) {
				diff.style.display = 'none';
			}
		}
		// Log detail box
		toggle = document.getElementById('mw-fr-logtoggle');
		if ( toggle ) {
			toggle.style.display = 'inline'; /* show toggle control */
			var log = document.getElementById('mw-fr-logexcerpt');
			if ( log ) {
				log.style.display = 'none';
			}
		}
	},

	/* Expands flag info box details */
	'showBoxDetails': function() {
		var ratings = document.getElementById('mw-fr-revisiondetails');
		if ( ratings ) {
			ratings.style.display = 'block';
		}
	},

	/* Collapses flag info box details */
	'hideBoxDetails': function( event ) {
		var ratings = document.getElementById('mw-fr-revisiondetails');
		if ( ratings ) {
			ratings.style.display = 'none';
		}
	},

	/* Toggles flag info box details for (+/-) control */
	'toggleBoxDetails': function() {
		var toggle = document.getElementById('mw-fr-revisiontoggle');
		if ( !toggle ) {
			return;
		}
		var ratings = document.getElementById('mw-fr-revisiondetails');
		if ( !ratings ) {
			return;
		}
		// Collapsed -> expand
		if ( ratings.style.display == 'none' ) {
			this.showBoxDetails();
			toggle.innerHTML = mw.msg('revreview-toggle-hide');
		// Expanded -> collapse
		} else {
			this.hideBoxDetails();
			toggle.innerHTML = mw.msg('revreview-toggle-show');
		}
	},

	/* Expands flag info box details on mouseOver */
	'onBoxMouseOver': function( event ) {
		window.clearTimeout( this.boxCollapseTimer );
		this.boxCollapseTimer = null;
		this.showBoxDetails();
	},

	/* Hides flag info box details on mouseOut *except* for event bubbling */
	'onBoxMouseOut': function( event ) {
		if ( !this.isMouseOutBubble( event, 'mw-fr-revisiontag' ) ) {
			this.boxCollapseTimer = window.setTimeout( this.hideBoxDetails, 150 );
		}
	},

	/* Checks is mouseOut event is for a child of parentId */
	'isMouseOutBubble': function( event, parentId ) {
		var toNode = null;
		if ( event.relatedTarget !== undefined ) {
			toNode = event.relatedTarget; // FF/Opera/Safari
		} else {
			toNode = event.toElement; // IE
		}
		if ( toNode ) {
			var nextParent = toNode.parentNode;
			while ( nextParent ) {
				if ( nextParent.id == parentId ) {
					return true; // event bubbling
				}
				nextParent = nextParent.parentNode; // next up
			}
		}
		return false;
	},

	/* Toggles diffs */
	'toggleDiff': function() {
		var diff = document.getElementById('mw-fr-stablediff');
		if ( !diff ) {
			return;
		}
		var toggle = document.getElementById('mw-fr-difftoggle');
		if ( !toggle ) {
			return;
		}
		if ( diff.style.display == 'none' ) {
			diff.style.display = 'block';
			toggle.getElementsByTagName('a')[0].innerHTML =
				mw.msg('revreview-diff-toggle-hide');
		} else {
			diff.style.display = 'none';
			toggle.getElementsByTagName('a')[0].innerHTML =
				mw.msg('revreview-diff-toggle-show');
		}
	},

	/* Toggles log excerpts */
	'toggleLog': function() {
		var log = document.getElementById('mw-fr-logexcerpt');
		if ( !log ) {
			return;
		}
		var toggle = document.getElementById('mw-fr-logtoggle');
		if ( !toggle ) {
			return;
		}
		if ( log.style.display == 'none' ) {
			log.style.display = 'block';
			toggle.getElementsByTagName('a')[0].innerHTML =
				mw.msg('revreview-log-toggle-hide');
		} else {
			log.style.display = 'none';
			toggle.getElementsByTagName('a')[0].innerHTML =
				mw.msg('revreview-log-toggle-show');
		}
	},

	/* Toggles log excerpts */
	'toggleLogDetails': function() {
		var log = document.getElementById('mw-fr-logexcerpt');
		if ( !log ) {
			return;
		}
		var toggle = document.getElementById('mw-fr-logtoggle');
		if ( !toggle ) {
			return;
		}
		if ( log.style.display == 'none' ) {
			log.style.display = 'block';
			toggle.getElementsByTagName('a')[0].innerHTML = mw.msg('revreview-log-details-hide');
		} else {
			log.style.display = 'none';
			toggle.getElementsByTagName('a')[0].innerHTML = mw.msg('revreview-log-details-show');
		}
	},

	/* Enables changing of save button when "review this" checkbox changes */
	'setCheckTrigger': function() {
		var checkbox = document.getElementById('wpReviewEdit');
		if ( checkbox ) {
			checkbox.onclick = FlaggedRevs.updateSaveButton;
		}
	},

	/* Update save button when "review this" checkbox changes */
	'updateSaveButton': function() {
		var checkbox = document.getElementById('wpReviewEdit');
		var save = document.getElementById('wpSave');
		if ( checkbox && save ) {
			// Review pending changes
			if ( checkbox.checked ) {
				save.value = mw.msg('savearticle');
				save.title = mw.msg('tooltip-save') +
					' [' + mw.msg('accesskey-save') + ']';
			// Submit for review
			} else {
				save.value = mw.msg('revreview-submitedit');
				save.title = mw.msg('revreview-submitedit-title')
					+ ' [' + mw.msg('accesskey-save') + ']';
			}
		}
		mw.util.updateTooltipAccessKeys( [ save ] ); // update accesskey in save.title
	}
};

// Perform some onload (which is when this script is included) events:
FlaggedRevs.enableShowhide();
FlaggedRevs.setCheckTrigger();
