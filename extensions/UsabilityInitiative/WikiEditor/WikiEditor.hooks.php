<?php
/**
 * Hooks for Usability Initiative WikiEditor extension
 *
 * @file
 * @ingroup Extensions
 */

class WikiEditorHooks {

	static $modules = array(
		'highlight' => array(
			'i18n' => 'WikiEditorHighlight',
			'preferences' => array(
				'enable' => array(
					'key' => 'wikieditor-highlight',
					'ui' => array(
						'type' => 'toggle',
						'label-message' => 'wikieditor-highlight-preference',
						'section' => 'editing/experimental',
					),
				),
			),
			'scripts' => array(
				'raw' => array( 'src' => 'Modules/Preview/Preview.js', 'version' => 1 ),
			),
		),
		'preview' => array(
			'i18n' => 'WikiEditorPreview',
			'preferences' => array(
				'enable' => array(
					'key' => 'wikieditor-preview',
					'ui' => array(
						'type' => 'toggle',
						'label-message' => 'wikieditor-preview-preference',
						'section' => 'editing/experimental',
					),
				),
			),
			'messages' => array(
				'wikieditor-preview-tab-edit',
				'wikieditor-preview-tab-preview',
				'wikieditor-preview-loading',
			),
			'scripts' => array(
				'raw' => array( 'src' => 'Modules/Preview/Preview.js', 'version' => 1 ),
			),
		),
		'toc' => array(
			'i18n' => 'WikiEditorToc',
			'preferences' => array(
				'enable' => array(
					// Ideally this key would be 'wikieditor-toc'
				 	'key' => 'usenavigabletoc',
					'ui' => array(
						'type' => 'toggle',
						'label-message' => 'wikieditor-toc-preference',
						'section' => 'editing/experimental',
					),
				),
			),
			'variables' => array(
				// These are probably only for testing purposes?
  				'wgNavigableTOCCollapseEnable' => $wgNavigableTOCCollapseEnable,
				'wgNavigableTOCResizable' => $wgNavigableTOCResizable
			),
			'scripts' => array(
				'raw' => array( 'src' => 'Modules/Toc/Toc.js', 'version' => 1 ),
			),
		),
		'toolbar' => array(
			'i18n' => 'WikiEditorToolbar',
			'preferences' => array(
				'enable' => array(
					// Ideally this key would be 'wikieditor-toolbar'
					'key' => 'usebetatoolbar',
					'ui' => array(
						'type' => 'toggle',
						'label-message' => 'wikieditor-toolbar-preference',
						'section' => 'editing/experimental',
					),
				),
				'dialogs' => array(
					// Ideally this key would be 'wikieditor-toolbar-dialogs'
					'key' => 'usebetatoolbar-cgd',
					'ui' => array(
						'type' => 'toggle',
						'label-message' => 'wikieditor-toolbar-dialogs-preference',
						'section' => 'editing/experimental',
					),
				),
			),
			'messages' => array(
				// This is a mixed bunch that needs to be separated between dialog and toolbar messages
				'wikieditor-toolbar-loading',
				/* Main Section */
				'wikieditor-toolbar-tool-bold',
				'wikieditor-toolbar-tool-bold-example',
				'wikieditor-toolbar-tool-italic',
				'wikieditor-toolbar-tool-italic-example',
				'wikieditor-toolbar-tool-ilink',
				'wikieditor-toolbar-tool-ilink-example',
				'wikieditor-toolbar-tool-xlink',
				'wikieditor-toolbar-tool-xlink-example',
				'wikieditor-toolbar-tool-link',
				'wikieditor-toolbar-tool-link-title',
				'wikieditor-toolbar-tool-link-int',
				'wikieditor-toolbar-tool-link-int-target',
				'wikieditor-toolbar-tool-link-int-text',
				'wikieditor-toolbar-tool-link-ext',
				'wikieditor-toolbar-tool-link-ext-target',
				'wikieditor-toolbar-tool-link-ext-text',
				'wikieditor-toolbar-tool-link-insert',
				'wikieditor-toolbar-tool-link-cancel',
				'wikieditor-toolbar-tool-link-int-target-status-exists',
				'wikieditor-toolbar-tool-link-int-target-status-notexists',
				'wikieditor-toolbar-tool-link-int-target-status-invalid',
				'wikieditor-toolbar-tool-link-int-target-status-loading',
				'wikieditor-toolbar-tool-link-int-invalid',
				'wikieditor-toolbar-tool-link-lookslikeinternal',
				'wikieditor-toolbar-tool-link-lookslikeinternal-int',
				'wikieditor-toolbar-tool-link-lookslikeinternal-ext',
				'wikieditor-toolbar-tool-link-empty',
				'wikieditor-toolbar-tool-file',
				'wikieditor-toolbar-tool-file-pre',
				'wikieditor-toolbar-tool-file-example',
				'wikieditor-toolbar-tool-reference',
				'wikieditor-toolbar-tool-reference-example',
				'wikieditor-toolbar-tool-signature',
				/* Formatting Section */
				'wikieditor-toolbar-section-advanced',
				'wikieditor-toolbar-tool-heading',
				'wikieditor-toolbar-tool-heading-1',
				'wikieditor-toolbar-tool-heading-2',
				'wikieditor-toolbar-tool-heading-3',
				'wikieditor-toolbar-tool-heading-4',
				'wikieditor-toolbar-tool-heading-5',
				'wikieditor-toolbar-tool-heading-example',
				'wikieditor-toolbar-group-list',
				'wikieditor-toolbar-tool-ulist',
				'wikieditor-toolbar-tool-ulist-example',
				'wikieditor-toolbar-tool-olist',
				'wikieditor-toolbar-tool-olist-example',
				'wikieditor-toolbar-group-size',
				'wikieditor-toolbar-tool-big',
				'wikieditor-toolbar-tool-big-example',
				'wikieditor-toolbar-tool-small',
				'wikieditor-toolbar-tool-small-example',
				'wikieditor-toolbar-group-baseline',
				'wikieditor-toolbar-tool-superscript',
				'wikieditor-toolbar-tool-superscript-example',
				'wikieditor-toolbar-tool-subscript',
				'wikieditor-toolbar-tool-subscript-example',
				'wikieditor-toolbar-group-insert',
				'wikieditor-toolbar-tool-gallery',
				'wikieditor-toolbar-tool-gallery-example',
				'wikieditor-toolbar-tool-newline',
				'wikieditor-toolbar-tool-table',
				'wikieditor-toolbar-tool-table-example-old',
				'wikieditor-toolbar-tool-table-example',
				'wikieditor-toolbar-tool-table-example-header',
				'wikieditor-toolbar-tool-table-title',
				'wikieditor-toolbar-tool-table-dimensions-rows',
				'wikieditor-toolbar-tool-table-dimensions-columns',
				'wikieditor-toolbar-tool-table-dimensions-header',
				'wikieditor-toolbar-tool-table-wikitable',
				'wikieditor-toolbar-tool-table-sortable',
				'wikieditor-toolbar-tool-table-preview',
				'wikieditor-toolbar-tool-table-insert',
				'wikieditor-toolbar-tool-table-cancel',
				'wikieditor-toolbar-tool-table-toomany',
				'wikieditor-toolbar-tool-table-invalidnumber',
				'wikieditor-toolbar-tool-table-zero',
				'wikieditor-toolbar-tool-replace',
				'wikieditor-toolbar-tool-replace-title',
				'wikieditor-toolbar-tool-replace-search',
				'wikieditor-toolbar-tool-replace-replace',
				'wikieditor-toolbar-tool-replace-case',
				'wikieditor-toolbar-tool-replace-regex',
				'wikieditor-toolbar-tool-replace-button-findnext',
				'wikieditor-toolbar-tool-replace-button-replacenext',
				'wikieditor-toolbar-tool-replace-button-replaceall',
				'wikieditor-toolbar-tool-replace-close',
				'wikieditor-toolbar-tool-replace-nomatch',
				'wikieditor-toolbar-tool-replace-success',
				'wikieditor-toolbar-tool-replace-emptysearch',
				'wikieditor-toolbar-tool-replace-invalidregex',
				/* Special Characters Section */
				'wikieditor-toolbar-section-characters',
				'wikieditor-toolbar-characters-page-latin',
				'wikieditor-toolbar-characters-page-latinextended',
				'wikieditor-toolbar-characters-page-ipa',
				'wikieditor-toolbar-characters-page-symbols',
				'wikieditor-toolbar-characters-page-greek',
				'wikieditor-toolbar-characters-page-cyrillic',
				'wikieditor-toolbar-characters-page-arabic',
				'wikieditor-toolbar-characters-page-hebrew',
				'wikieditor-toolbar-characters-page-telugu',
				'wikieditor-toolbar-characters-page-sinhala',
				'wikieditor-toolbar-characters-page-gujarati',
				/* Help Section */
				'wikieditor-toolbar-section-help',
				'wikieditor-toolbar-help-heading-description',
				'wikieditor-toolbar-help-heading-syntax',
				'wikieditor-toolbar-help-heading-result',
				'wikieditor-toolbar-help-page-format',
				'wikieditor-toolbar-help-page-link',
				'wikieditor-toolbar-help-page-heading',
				'wikieditor-toolbar-help-page-list',
				'wikieditor-toolbar-help-page-file',
				'wikieditor-toolbar-help-page-reference',
				'wikieditor-toolbar-help-page-discussion',
				'wikieditor-toolbar-help-content-bold-description',
				'wikieditor-toolbar-help-content-bold-syntax',
				'wikieditor-toolbar-help-content-bold-result',
				'wikieditor-toolbar-help-content-italic-description',
				'wikieditor-toolbar-help-content-italic-syntax',
				'wikieditor-toolbar-help-content-italic-result',
				'wikieditor-toolbar-help-content-bolditalic-description',
				'wikieditor-toolbar-help-content-bolditalic-syntax',
				'wikieditor-toolbar-help-content-bolditalic-result',
				'wikieditor-toolbar-help-content-ilink-description',
				'wikieditor-toolbar-help-content-ilink-syntax',
				'wikieditor-toolbar-help-content-ilink-result',
				'wikieditor-toolbar-help-content-xlink-description',
				'wikieditor-toolbar-help-content-xlink-syntax',
				'wikieditor-toolbar-help-content-xlink-result',
				'wikieditor-toolbar-help-content-heading1-description',
				'wikieditor-toolbar-help-content-heading1-syntax',
				'wikieditor-toolbar-help-content-heading1-result',
				'wikieditor-toolbar-help-content-heading2-description',
				'wikieditor-toolbar-help-content-heading2-syntax',
				'wikieditor-toolbar-help-content-heading2-result',
				'wikieditor-toolbar-help-content-heading3-description',
				'wikieditor-toolbar-help-content-heading3-syntax',
				'wikieditor-toolbar-help-content-heading3-result',
				'wikieditor-toolbar-help-content-heading4-description',
				'wikieditor-toolbar-help-content-heading4-syntax',
				'wikieditor-toolbar-help-content-heading4-result',
				'wikieditor-toolbar-help-content-heading5-description',
				'wikieditor-toolbar-help-content-heading5-syntax',
				'wikieditor-toolbar-help-content-heading5-result',
				'wikieditor-toolbar-help-content-ulist-description',
				'wikieditor-toolbar-help-content-ulist-syntax',
				'wikieditor-toolbar-help-content-ulist-result',
				'wikieditor-toolbar-help-content-olist-description',
				'wikieditor-toolbar-help-content-olist-syntax',
				'wikieditor-toolbar-help-content-olist-result',
				'wikieditor-toolbar-help-content-file-description',
				'wikieditor-toolbar-help-content-file-syntax',
				'wikieditor-toolbar-help-content-file-result',
				'wikieditor-toolbar-help-content-reference-description',
				'wikieditor-toolbar-help-content-reference-syntax',
				'wikieditor-toolbar-help-content-reference-result',
				'wikieditor-toolbar-help-content-rereference-description',
				'wikieditor-toolbar-help-content-rereference-syntax',
				'wikieditor-toolbar-help-content-rereference-result',
				'wikieditor-toolbar-help-content-showreferences-description',
				'wikieditor-toolbar-help-content-showreferences-syntax',
				'wikieditor-toolbar-help-content-showreferences-result',
				'wikieditor-toolbar-help-content-signaturetimestamp-description',
				'wikieditor-toolbar-help-content-signaturetimestamp-syntax',
				'wikieditor-toolbar-help-content-signaturetimestamp-result',
				'wikieditor-toolbar-help-content-signature-description',
				'wikieditor-toolbar-help-content-signature-syntax',
				'wikieditor-toolbar-help-content-signature-result',
				'wikieditor-toolbar-help-content-indent-description',
				'wikieditor-toolbar-help-content-indent-syntax',
				'wikieditor-toolbar-help-content-indent-result',
			),
			'scripts' => array(
				'raw' => array( 'src' => 'Modules/Toolbar/Toolbar.js', 'version' => 1 ),
				'min' => array( 'src' => 'Modules/Toolbar/Toolbar.min.js', 'version' => 1 ),
			),
		),
	);
	
	/* Static Functions */
	
	/**
	 * EditPage::showEditForm:initial hook
	 * Adds the modules to the edit form
	 */
	 public static function addModules( &$toolbar ) {
		global $wgUser, $wgWikiEditorStyleVersion, $wgWikiEditorEnable, $wgUsabilityInitiativeResourceMode;
		
		$preferences = array();
		for ( $wgWikiEditorEnable as $module => $enable ) {
			if (
				$enable['global'] || (
					$enable['user']
					&& isset( self::$modules[$module]['preferences']['enable'] )
					&& $wgUser->getOption( self::$modules[$module]['preferences']['enable']['key'] )
				)
			) {
				UsabilityInitiativeHooks::initialize();
				// Scripts
				$mode = $wgUsabilityInitiativeResourceMode;
				if ( !isset( self::$modules[$module]['scripts'][$mode] ) ) {
					$mode = 'raw';
				}
				UsabilityInitiativeHooks::addScript(
					self::$modules[$module]['scripts'][$mode]['src'],
					self::$modules[$module]['scripts'][$mode]['version']
				);
				// Messages
				if ( isset( self::$modules[$module]['i18n'], self::$modules[$module]['messages'] ) ) {		
					wfLoadExtensionMessages( self::$modules[$module]['i18n'] );
					UsabilityInitiativeHooks::addMessages( self::$modules[$module]['messages'] );
				}
				// Variables
				if ( isset( self::$modules[$module]['variables'] ) ) {
					UsabilityInitiativeHooks::addVariables( self::$modules[$module]['variables'] );
				}
				// Preferences
				$preferences[$module] = array();
				if ( isset( self::$modules[$module]['preferences'] ) ) {
					foreach ( self::$modules[$module]['preferences'] as $name => $preference ) {
						if ( $name !== 'enable' ) {
							$preferences[$module][$name] = $wgUser->getOption( $preference['key'] );
						}
					}
				}
			}
		}
		/* Do something with these other preferences that makes them accessible to the wikiEditor code??
		$preferencesList = array();
		foreach ( $preferences as $module => $values ) {
			$modulePreferences = array();
			foreach ( $values as $key => $value ) {
				$modulePreferences .= "'" . Xml::escapeJsString( $key ) . "':'" . Xml::escapeJsString( $key ) . "'";
			}
			$preferencesList[] =
				"'" . Xml::escapeJsString( $module ) . "':{" . implode( ',', $modulePreferences ) . '}';
		}
		$out->addScript(
			Xml::tags(
				'script',
				array( 'type' => $wgJsMimeType ),
				'$.wikiEditor.config = {' . implode( ',', $preferencesList ) . '};'
			)
		);
		*/
		return true;
	}
	
	/**
	 * GetPreferences hook
	 * Add module-releated items to the preferences
	 */
	public static function addPreferences( $user, &$defaultPreferences ) {
		global $wgWikiEditorEnable;
		
		foreach ( $wgWikiEditorEnable as $module => $enable ) {
			if ( isset( self::$modules[$module]['i18n'], self::$modules[$module]['preferences'] ) ) {
				wfLoadExtensionMessages( self::$modules[$module]['i18n'] );
				foreach ( self::$modules[$module]['preferences'] as $preference ) {
					// The preference with the key 'enable' determines if the rest are even relevant, so in the future
					// setting up some dependencies on that might make sense
					$defaultPreferences[$preference['key']] = $preference['ui'];
				}
			}
		}
		return true;
	}
}
