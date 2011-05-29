<?php

/**
 * Semantic Graph Extension - this extension is an provides some parser functions
 * to create and display graphs based on the structure of a wiki.
 *
 * To activate this extension, add the following into your LocalSettings.php file:
 * require_once('$IP/extensions/SemanticGraph/includes/SemanticGraph2.php');
 *
 * @ingroup Extensions
 * @author Rob Challen <rjchallen@gmail.com>
 * @version 0.8.6
 * @link http://www.mediawiki.org/wiki/Extension:MyExtension Documentation
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */
 
/**
 * Protect against register_globals vulnerabilities.
 * This line must be present before any global variable is referenced.
 */
if( !defined( 'MEDIAWIKI' ) ) {
        echo( "This is an extension to the MediaWiki package and cannot be run standalone.\n" );
        die( -1 );
}

define( 'SemanticGraph_VERSION', '0.9 alpha' );

# Define a setup function
$wgExtensionFunctions[] = 'efSGraphParserFunction_Setup';
# Add a hook to initialise the magic word
$wgHooks['LanguageGetMagic'][]       = 'efSGraphParserFunction_Magic';
// Extension credits that will show up on Special:Version    
$wgExtensionCredits[defined( 'SEMANTIC_EXTENSION_TYPE' ) ? 'semantic' : 'parserhook'][] = array(
        'name'           => 'SemanticGraph',
        'version'        => SemanticGraph_VERSION,
        'author'         => 'Rob Challen', 
        'url'            => 'http://www.mediawiki.org/wiki/Extension:Semantic_Graph',
        'descriptionmsg' => 'semanticgraph-desc'
);

$wgExtensionMessagesFiles['SemanticGraph']	  				= dirname( __FILE__ ) . '/../SemanticGraph.i18n.php';

$wgAutoloadClasses['SemanticGraphSettings']		  			= dirname( __FILE__ ) . '/SemanticGraphSettings.php';
$wgAutoloadClasses['graphfile']		  						= dirname( __FILE__ ) . '/SemanticGraphFiles.php';
$wgAutoloadClasses['renderer']		  						= dirname( __FILE__ ) . '/SemanticGraphRenderer.php';
$wgAutoloadClasses['networkgraph']		  					= dirname( __FILE__ ) . '/SemanticGraphBuilders.php';
$wgAutoloadClasses['localmap']		  						= dirname( __FILE__ ) . '/SemanticGraphBuilders.php';
$wgAutoloadClasses['freemindmap']		  					= dirname( __FILE__ ) . '/SemanticGraphBuilders.php';
$wgAutoloadClasses['GraphQuery']		  					= dirname( __FILE__ ) . '/SemanticGraphQuery.php';

include_once('SemanticGraphHelperFunctions.php');
$wgSemanticGraphSettings = new SemanticGraphSettings();

function efSGraphParserFunction_Setup() {
        global $wgParser;
        # Set a function hook associating the "example" magic word with our function
        $wgParser->setFunctionHook( 'sgraph', 'efSGraphParserFunction_Render' );
        $wgParser->setFunctionHook( 'smm', 'efSMMParserFunction_Render' );
        $wgParser->setFunctionHook( 'shypergraph', 'efSHypergraphParserFunction_Render' );
        $wgParser->setFunctionHook( 'mm2', 'efMM2ParserFunction_Render' );
        $wgParser->setFunctionHook( 'bigpic', 'efBigPicParserFunction_Render' );
        $wgParser->setFunctionHook( 'locallinks', 'efLocalLinksParserFunction_Render' );
}
 
function efSGraphParserFunction_Magic( &$magicWords, $langCode ) {
        # Add the magic word
        # The first array element is case sensitive, in this case it is not case sensitive
        # All remaining elements are synonyms for our parser function
        $magicWords['sgraph'] = array( 0, 'sgraph');
        $magicWords['smm'] = array( 0, 'smm');
        $magicWords['mm2'] = array( 0, 'mm2');
        $magicWords['shypergraph'] = array( 0, 'shypergraph');
        $magicWords['bigpic'] = array( 0, 'bigpic');
        # unless we return true, other parser functions extensions won't get loaded.
        return true;
}

function efArgs($params,$type) {
	global $wgScriptPath, $wgOut;
	global $wgSemanticGraphSettings;
	if (($args = $wgSemanticGraphSettings->parseOptions($params,$type)) == false) {
		$t = '<p>'.$wgSemanticGraphSettings->lastError.'</p>';
		$t .= $wgSemanticGraphSettings->usage($type);
		$wgSemanticGraphSettings->lastError = $t;
		return false;
	}
	return $args;
}

function efSGraphParserFunction_Render( &$parser) {
	global $wgSemanticGraphSettings;
	$initargs = efArgs(func_get_args(),'sgraph');
	if ($initargs == false) return $wgSemanticGraphSettings->lastError;
	 
	$renderer = new renderer('dot');
	$file = new graphfile($initargs['name'], 'dot');
	$ng = new networkgraph($initargs);
    
	$dottext = $renderer->enter('preamble', $initargs);
	$dottext .= $initargs['dotoptions'];
	$ng->buildfromWiki();
	$dottext .= $ng->getSubGraph($renderer);
	$dottext .= $renderer->enter('conclusion', $initargs);
	
	return array($file->renderGraphFromDot($dottext, $initargs['engine'], $initargs['width'], $initargs['height'], $initargs['svg'], $initargs['boxresize'], $initargs['zoom']), 'noparse' => true, 'isHTML' => true);
	//testing:     
	//return "<pre>".$dottext."</pre>";
}

function efSHypergraphParserFunction_Render( &$parser) {
	global $wgSemanticGraphSettings;
	$startargs = efArgs(func_get_args(),'shypergraph');
	if ($startargs == false) return $wgSemanticGraphSettings->lastError;
		
	$renderer = new renderer('hypergraph');
    	$file = new graphfile($startargs['name'], 'hypergraph');
    	$ng = new networkgraph($startargs);
    
    	$hgtext = $renderer->enter('preamble', $startargs);
	$ng->buildfromWiki();
	$hgtext .= $ng->getSubGraph($renderer);
	$hgtext .= $renderer->enter('conclusion', $startargs);
		
	return array($file->renderGraphFromHGraph($hgtext, $startargs['width'], $startargs['height']), 'noparse' => true, 'isHTML' => true);
	//testing:
	//return "<pre>$hgtext</pre>";
}

function efSMMParserFunction_Render( &$parser) {
	global $wgSemanticGraphSettings;
	$initargs = efArgs(func_get_args(), 'smm');
	if ($initargs == false) return $wgSemanticGraphSettings->lastError;
     
	$file = new graphfile($initargs['name'], 'mm');
    	$map = new freemindmap($initargs);
	$map->buildFromWiki();
	$map->setupLinks();
	$mmtext = $map->getMap();
    
    	//return "<pre>$mmtext</pre><pre>height: $h</pre>";
    	$input = $file->renderGraphFromMM($mmtext, $initargs['width'], $initargs['height']);
	return $parser->insertStripItem( $input, $parser->mStripState );
}

function efMM2ParserFunction_Render( &$parser ) {
	global $wgSemanticGraphSettings, $wgServer;
	$args = efArgs(func_get_args(),'mm2');
	if ($args == false) return $wgSemanticGraphSettings->lastError;
	$args['name'] = md5($args['image']);
	
	$file = new graphfile($args['name'],'mm');
	
	//find the original file & read it
	$base = new freemindmap($args);
	$base->buildFromFile();
	$base->setupLinks();
	$mmtext = $base->getMap();
	
	//testing: 
    	//return "<pre>$mmtext</pre>";
    	$imagePage = new ImagePage(Title::newFromDBkey($args['image']));
    	$out = $file->renderGraphFromMM($mmtext, $args['width'], $args['height']);
    	$out .= '<br/><a href="'.$imagePage->getUploadUrl().'">Upload new version...</a>';
    	return array($out, 'noparse' => true, 'isHTML' => true);
}

function efBigPicParserFunction_Render( &$parser ) {
	global $wgSemanticGraphSettings, $wgServer;
	$args = efArgs(func_get_args(),'bigpic');
	if ($args == false) return $wgSemanticGraphSettings->lastError;
	$t= Title::newFromDBkey($args['image']);
	$img = wfFindFile($t);
	$fileurl = $img->getFullURL();
	$out = efBigPic($fileurl, $img->getWidth(), $img->getHeight(), $args['width'], $args['height'], $args['boxresize'], $args['zoom']);
	return array($out, 'noparse' => true, 'isHTML' => true);
}

function efLocalLinksParserFunction_Render( &$parser) {
	global $wgSemanticGraphSettings, $wgTitle;
	$initargs = efArgs(func_get_args(), 'locallinks');
	if ($initargs == false) return $wgSemanticGraphSettings->lastError;
	$initargs['resource'] = $wgTitle->getDBkey();
     
	$map = new localmap($initargs);
	$map->buildFromWiki();
	$mmtext = $map->getMap();
    
    	//return "<pre>$mmtext</pre><pre>height: $h</pre>";
    	$input = $file->renderGraphFromMM($mmtext, $initargs['width'], $initargs['height']);
	return $parser->insertStripItem( $input, $parser->mStripState );
}
