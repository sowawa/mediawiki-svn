<?php
/**
 * Special:UploadWizard
 *
 * Easy to use multi-file upload page.
 *
 * @file
 * @ingroup SpecialPage
 * @ingroup Upload
 */

class SpecialUploadWizard extends SpecialPage {
	// the HTML form without javascript
	private $simpleForm;

	// $request is the request (usually wgRequest)
	// $par is everything in the URL after Special:UploadWizard. Not sure what we can use it for
	public function __construct( $request = null, $par = null ) {
		global $wgRequest;
		// here we would configure ourselves based on stuff in $request and $wgRequest, but so far, we
		// don't have such things

		parent::__construct( 'UploadWizard', 'upload' );

		// create a simple form for non-JS fallback, which targets the old Special:Upload page.
		// at some point, if we completely subsume its functionality, change that to point here again,
	 	// but then we'll need to process non-JS uploads in the same way Special:Upload does.
		$this->simpleForm = new UploadWizardSimpleForm();
		$this->simpleForm->setTitle(
			SpecialPage::getTitleFor( 'Upload' )
		);
	}

	/**
	 * Replaces default execute method
	 * Checks whether uploading enabled, user permissions okay,
	 * @param $subPage, e.g. the "foo" in Special:UploadWizard/foo.
	 */
	public function execute( $subPage ) {
		global $wgRequest, $wgLang, $wgUser, $wgOut, $wgExtensionAssetsPath,
		       $wgUploadWizardDisableResourceLoader, $wgUploadWizardConfig;

		// side effects: if we can't upload, will print error page to wgOut
		// and return false
		if ( !( $this->isUploadAllowed() && $this->isUserUploadAllowed( $wgUser ) ) ) {
			return;
		}

		$this->setHeaders();
		$this->outputHeader();
		
		// if query string includes 'skiptutorial=true' set config variable to true
		if ( $wgRequest->getText( 'skiptutorial' ) ) {
			$wgUploadWizardConfig['skipTutorial'] = true;
		}

		// fallback for non-JS
		$wgOut->addHTML( '<noscript>' );
		$wgOut->addHTML( '<p class="errorbox">' . wfMsg( 'mwe-upwiz-js-off' ) . '</p>' );
		$this->simpleForm->show();
		$wgOut->addHTML( '</noscript>' );


		// global javascript variables
		$this->addJsVars( $subPage );

		// dependencies (css, js)
		if ( !$wgUploadWizardDisableResourceLoader && class_exists( 'ResourceLoader' ) ) {
			$wgOut->addModules( 'ext.uploadWizard' );
		} else {
			$basepath = "$wgExtensionAssetsPath/UploadWizard";
			$dependencyLoader = new UploadWizardDependencyLoader( $wgLang->getCode() );
			if ( $wgUploadWizardConfig['debug'] ) {
				// each file as an individual script or style
				$dependencyLoader->outputHtmlDebug( $wgOut, $basepath );
			} else {
				// combined & minified
				$dependencyLoader->outputHtml( $wgOut, $basepath );
			}
		}

		// where the uploadwizard will go
		// TODO import more from UploadWizard's createInterface call.
		$wgOut->addHTML( self::getWizardHtml() );

	}

	/**
	 * Adds some global variables for our use, as well as initializes the UploadWizard
	 * 
	 * TODO once bug https://bugzilla.wikimedia.org/show_bug.cgi?id=26901
	 * is fixed we should package configuration with the upload wizard instead of
	 * in uploadWizard output page. 
	 * 
	 * @param subpage, e.g. the "foo" in Special:UploadWizard/foo
	 */
	public function addJsVars( $subPage ) {
		global $wgOut, $wgUpwizDir, $wgUploadWizardConfig, $wgSitename;

		// Merge the default configuration with the local settings $wgUploadWizardConfig configuration
		$configPath =  $wgUpwizDir . '/UploadWizard.config.php';
		if( is_file( $configPath ) ){
			$wgUploadWizardConfig = array_merge( include( $configPath ), $wgUploadWizardConfig );
		}
		 
		$wgOut->addScript( 
			Skin::makeVariablesScript( 
				array(
					'UploadWizardConfig' => $wgUploadWizardConfig 
				) +
				// Site name is a true global not specific to Upload Wizard
				array( 
					'wgSiteName' => $wgSitename
				)
			)
		);

	}

	/**
	 * Check if anyone can upload (or if other sitewide config prevents this)
	 * Side effect: will print error page to wgOut if cannot upload.
	 * @return boolean -- true if can upload
	 */
	private function isUploadAllowed() {
		global $wgOut, $wgEnableAPI;

		// Check uploading enabled
		if ( !UploadBase::isEnabled() ) {
			$wgOut->showErrorPage( 'uploaddisabled', 'uploaddisabledtext' );
			return false;
		}

		// XXX does wgEnableAPI affect all uploads too?

		// Check whether we actually want to allow changing stuff
		if ( wfReadOnly() ) {
			$wgOut->readOnlyPage();
			return false;
		}

		// we got all the way here, so it must be okay to upload
		return true;
	}

	/**
	 * Check if the user can upload
	 * Side effect: will print error page to wgOut if cannot upload.
	 * @param User
	 * @return boolean -- true if can upload
	 */
	private function isUserUploadAllowed( $user ) {
		global $wgOut, $wgGroupPermissions;

		if ( !$user->isAllowed( 'upload' ) ) {
			if ( !$user->isLoggedIn() && ( $wgGroupPermissions['user']['upload']
				|| $wgGroupPermissions['autoconfirmed']['upload'] ) ) {
				// Custom message if logged-in users without any special rights can upload
				$wgOut->showErrorPage( 'uploadnologin', 'uploadnologintext' );
			} else {
				$wgOut->permissionRequired( 'upload' );
			}
			return false;
		}

		// Check blocks
		if ( $user->isBlocked() ) {
			$wgOut->blockedPage();
			return false;
		}

		// we got all the way here, so it must be okay to upload
		return true;
	}

	/**
	 * Return the basic HTML structure for the entire page
	 * Will be enhanced by the javascript to actually do stuff
	 * @return {String} html
	 */
	function getWizardHtml() {
		global $wgUploadWizardConfig, $wgExtensionAssetsPath;

		if ( array_key_exists( 'fallbackToAltUploadForm', $wgUploadWizardConfig ) 
			&& array_key_exists( 'altUploadForm', $wgUploadWizardConfig ) 
			&& $wgUploadWizardConfig['altUploadForm'] != ''
			&& $wgUploadWizardConfig[ 'fallbackToAltUploadForm' ] 			) {

			$linkHtml = '';
			$altUploadForm = Title::newFromText( $wgUploadWizardConfig[ 'altUploadForm' ] );
			if ( $altUploadForm instanceof Title ) {
				$linkHtml = Html::rawElement( 'p', array( 'style' => 'text-align: center;' ), 
					Html::rawElement( 'a', array( 'href' => $altUploadForm->getLocalURL() ), 
						$wgUploadWizardConfig['altUploadForm'] 
					) 
				);
			}

			return 	 
				Html::rawElement( 'div', array( 'id' => 'upload-wizard', 'class' => 'upload-section' ),
					Html::rawElement( 'p', array( 'style' => 'text-align: center' ), wfMsg( 'mwe-upwiz-extension-disabled' ) ) 
					. $linkHtml
				);

		}
	
		$tutorialHtml = '';		
		// only load the tutorial HTML if we aren't skipping the first step
		// TODO should use user preference not a cookie ( so the user does not have to skip it for every browser )
		if ( !isset( $_COOKIE['skiptutorial'] ) && !$wgUploadWizardConfig['skipTutorial'] ) {
			$tutorialHtml = UploadWizardTutorial::getHtml();
		}
		// TODO move this into UploadWizard.js or some other javascript resource so the upload wizard
		// can be dynamically included ( for example the add media wizard ) 
		return
		  '<div id="upload-wizard" class="upload-section">'

			// if loading takes > 2 seconds display spinner. Note we are evading Resource Loader here, and linking directly. Because we want an image to appear if RL's package is late.
			// using some &nbsp;'s which is a bit of superstition, to make sure jQuery will hide this (it seems that it doesn't sometimes, when it has no content)
			// the min-width & max-width is copied from the #uploadWizard properties, so in nice browsers the spinner is right where the button will go.
		.	'<div id="mwe-first-spinner" style="min-width:750px; max-width:900px; height:200px; line-height:200px; text-align:center;">'
		.	'&nbsp;<img src="' . $wgExtensionAssetsPath . '/UploadWizard/resources/images/24px-spinner-0645ad.gif" width="24" height="24" />&nbsp;'
		.	'</div>'
		
		    // the arrow steps - hide until styled
		.   '<ul id="mwe-upwiz-steps" style="display:none;">'
		.     '<li id="mwe-upwiz-step-tutorial"><div>' . wfMsg( 'mwe-upwiz-step-tutorial' ) . '</div></li>'
		.     '<li id="mwe-upwiz-step-file"><div>' . wfMsg( 'mwe-upwiz-step-file' ) . '</div></li>'
		.     '<li id="mwe-upwiz-step-deeds"><div>'  . wfMsg( 'mwe-upwiz-step-deeds' )  . '</div></li>'
		.     '<li id="mwe-upwiz-step-details"><div>'  . wfMsg( 'mwe-upwiz-step-details' )  . '</div></li>'
		.     '<li id="mwe-upwiz-step-thanks"><div>'   . wfMsg( 'mwe-upwiz-step-thanks' )  .  '</div></li>'
		.   '</ul>'

		    // the individual steps, all at once - hide until needed
		.   '<div id="mwe-upwiz-content">'

		.     '<div class="mwe-upwiz-stepdiv" id="mwe-upwiz-stepdiv-tutorial" style="display:none;">'
		.       '<div id="mwe-upwiz-tutorial">'
		.         $tutorialHtml
		.       '</div>'
		.       '<div class="mwe-upwiz-buttons">'
		.          '<input type="checkbox" id="mwe-upwiz-skip" value="1" name="skip">'
		.          '<label for="mwe-upwiz-skip">' . wfMsg('mwe-upwiz-skip-tutorial-future') . '</label>'
		.          '<button class="mwe-upwiz-button-next">' . wfMsg( "mwe-upwiz-next" )  . '</button>'
		.       '</div>'
		.     '</div>'

		.     '<div class="mwe-upwiz-stepdiv ui-helper-clearfix" id="mwe-upwiz-stepdiv-file" style="display:none;">'
		.       '<div id="mwe-upwiz-files">'
		.         '<div id="mwe-upwiz-filelist" class="ui-corner-all"></div>'
		.         '<div id="mwe-upwiz-upload-ctrls" class="mwe-upwiz-file ui-helper-clearfix">'
		.            '<div id="mwe-upwiz-add-file-container" class="mwe-upwiz-add-files-0">'
		.              '<button id="mwe-upwiz-add-file">' . wfMsg( "mwe-upwiz-add-file-0-free" ) . '</button>'
		.  	     '</div>'
		.	     '<div id="mwe-upwiz-upload-ctrl-container">'
		.		'<button id="mwe-upwiz-upload-ctrl">' . wfMsg( "mwe-upwiz-upload" ) . '</button>'
		.	     '</div>'
		.         '</div>'
		.         '<div id="mwe-upwiz-progress" class="ui-helper-clearfix"></div>'
		.         '<div id="mwe-upwiz-continue" class="ui-helper-clearfix"></div>'
		.       '</div>'
		.       '<div class="mwe-upwiz-buttons">'
		.	   '<div class="mwe-upwiz-file-next-all-ok mwe-upwiz-file-endchoice">'
		.             wfMsg( "mwe-upwiz-file-all-ok" )
		.             '<button class="mwe-upwiz-button-next">' . wfMsg( "mwe-upwiz-next-file" )  . '</button>'
		.          '</div>'
		.	   '<div class="mwe-upwiz-file-next-some-failed mwe-upwiz-file-endchoice">'
		.             wfMsg( "mwe-upwiz-file-some-failed" )
		.             '<button class="mwe-upwiz-button-retry">' . wfMsg( "mwe-upwiz-file-retry" )  . '</button>'
		.             '<button class="mwe-upwiz-button-next">' . wfMsg( "mwe-upwiz-next-file-despite-failures" )  . '</button>'
		.          '</div>'
		.	   '<div class="mwe-upwiz-file-next-all-failed mwe-upwiz-file-endchoice">'
		.             wfMsg( "mwe-upwiz-file-all-failed" )
		.             '<button class="mwe-upwiz-button-retry"> ' . wfMsg( "mwe-upwiz-file-retry" )  . '</button>'
		.          '</div>'
		.       '</div>'
		.     '</div>'

		.     '<div class="mwe-upwiz-stepdiv" id="mwe-upwiz-stepdiv-deeds" style="display:none;">'
		.       '<div id="mwe-upwiz-deeds-thumbnails" class="ui-helper-clearfix"></div>'
		.       '<div id="mwe-upwiz-deeds" class="ui-helper-clearfix"></div>'
		.       '<div id="mwe-upwiz-deeds-custom" class="ui-helper-clearfix"></div>'
		.       '<div class="mwe-upwiz-buttons">'
		.          '<button class="mwe-upwiz-button-next">' . wfMsg( "mwe-upwiz-next-deeds" )  . '</button>'
		.       '</div>'
		.     '</div>'

		.     '<div class="mwe-upwiz-stepdiv" id="mwe-upwiz-stepdiv-details" style="display:none;">'
		.       '<div id="mwe-upwiz-macro-files" class="mwe-upwiz-filled-filelist ui-corner-all"></div>'
		.       '<div class="mwe-upwiz-buttons">'
		.	   '<div id="mwe-upwiz-details-error-count" class="mwe-upwiz-file-endchoice mwe-error"></div>'
		.	   '<div class="mwe-upwiz-start-next mwe-upwiz-file-endchoice">'
		.            '<button class="mwe-upwiz-button-next">' . wfMsg( "mwe-upwiz-next-details" )  . '</button>'
		.          '</div>'
		.	   '<div class="mwe-upwiz-file-next-some-failed mwe-upwiz-file-endchoice">'
		.             wfMsg( "mwe-upwiz-file-some-failed" )
		.             '<button class="mwe-upwiz-button-retry">' . wfMsg( "mwe-upwiz-file-retry" )  . '</button>'
		.             '<button class="mwe-upwiz-button-next-despite-failures">' . wfMsg( "mwe-upwiz-next-file-despite-failures" )  . '</button>'
		.          '</div>'
		.	   '<div class="mwe-upwiz-file-next-all-failed mwe-upwiz-file-endchoice">'
		.             wfMsg( "mwe-upwiz-file-all-failed" )
		.             '<button class="mwe-upwiz-button-retry"> ' . wfMsg( "mwe-upwiz-file-retry" )  . '</button>'
		.          '</div>'
		.       '</div>'
		.     '</div>'

		.     '<div class="mwe-upwiz-stepdiv" id="mwe-upwiz-stepdiv-thanks" style="display:none;">'
		.       '<div id="mwe-upwiz-thanks"></div>'
		.       '<div class="mwe-upwiz-buttons">'
		.          '<button class="mwe-upwiz-button-home">' . wfMsg( "mwe-upwiz-home" ) . '</button>'
		.          '<button class="mwe-upwiz-button-begin">' . wfMsg( "mwe-upwiz-upload-another" )  . '</button>'
		.       '</div>'
		.     '</div>'

		.   '</div>'

		.   '<div class="mwe-upwiz-clearing"></div>'

		. '</div>';
	}

}


/**
 * This is a hack on UploadForm, to make one that works from UploadWizard when JS is not available.
 */
class UploadWizardSimpleForm extends UploadForm {

	/*
 	 * Normally, UploadForm adds its own Javascript.
 	 * We wish to prevent this, because we want to control the case where we have Javascript.
 	 * So, we make the addUploadJS a no-op.
	 */
	protected function addUploadJS( ) { }

}


