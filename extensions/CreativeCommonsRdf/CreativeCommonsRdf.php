<?php

$wgExtensionCredits['other'][] = array(
	'path' => __FILE__,
	'name' => 'CreativeCoreRdf',
	'url' => 'http://www.mediawiki.org/wiki/Extension:CreativeCoreRdf',
	'author' => 'Evan Prodromou',
	'descriptionmsg' => "CreativeCommons RDF-metadata"
);

$wgHooks['MediaWikiPerformAction'][] = 'efCreativeCommonsRdfPreformAction';

$wgAutoloadClasses['CreativeCommonsRdf'] = $dir . 'CreativeCommonsRdf_body.php';

function efCreativeCommonsRdfPreformAction( $output, $article, $title, $user, $request, $mediaWiki ) {
	if ( $mediaWiki->getAction() !== 'creativecommons' ) {
		return true;
	}

	$rdf = new CreativeCommonsRdf( $article );
	$rdf->show();
	return false;
}