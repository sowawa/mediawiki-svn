<?php
if (!defined('MEDIAWIKI')) die();
/**
 * An extension to ease the translation of Mediawiki
 *
 * @package MediaWiki
 * @subpackage Extensions
 *
 * @author Niklas Laxström
 * @copyright Copyright © 2006, Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

$wgExtensionFunctions[] = 'wfSpecialTranslate';
$wgExtensionCredits['specialpage'][] = array(
	'name' => 'Translate',
	'author' => 'Niklas Laxström',
	'url' => 'http://nike.users.idler.fi/betawiki',
	'description' => 'Special page for translating Mediawiki'
);

# Internationalisation file
require_once( 'SpecialTranslate.i18n.php' );

# Message types (ugly?)
require_once( 'maintenance/language/messageTypes.inc' );


# Register the special page
if ( !function_exists( 'extAddSpecialPage' ) ) {
	require( dirname(__FILE__) . '/../ExtensionFunctions.php' );
}
extAddSpecialPage( dirname(__FILE__) . '/SpecialTranslate_body.php', 'Translate', 'SpecialTranslate' );
require_once( 'SpecialTranslate_edit.php' );
global $wgHooks;

# Hook Edit page
$poks = new SpecialTranslateEditTools();
$wgHooks['EditPage::showEditForm:initial'][] =
	array( $poks, 'addTools' );

$wgHooks['SkinTemplateSetupPageCss'][] = 'wfSpecialTranslateAddCss';

function wfSpecialTranslateAddCss($css) {

	$css .=
<<<CSSXYZ
/* Special:Translate */
.mw-special-translate-table {
	width: 100%;
}

.mw-special-translate-table th {
	background-color: #b2b2ff;
}

.mw-special-translate-table tr.orig {
	background-color: #ffe2e2;
}

.mw-special-translate-table tr.new {
	background-color: #e2ffe2;
}

.mw-special-translate-table tr.def {
	background-color: #f0f0ff;
}
CSSXYZ;
	return true;

}


function wfSpecialTranslate() {
	# Add messages
	global $wgMessageCache, $wgTranslateMessages;
	foreach( $wgTranslateMessages as $key => $value ) {
		$wgMessageCache->addMessages( $wgTranslateMessages[$key], $key );
	}
}

?>
