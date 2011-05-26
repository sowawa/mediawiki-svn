<?php

$wgExtensionCredits['other'][] = array(
	'path' => __FILE__,
	'name' => 'CreativeCoreRdf',
	'url' => '',
	'author' => 'Evan Prodromou',
);

$wgHooks['MediaWikiPerformAction'][] = 'efCreativeCoreRdfPreformAction';

$wgAutoloadClasses['CreativeCoreRdf'] = $dir . 'CreativeCoreRdf_body.php';

function efCreativeCoreRdfPreformAction( $output, $article, $title, $user, $request, $mediaWiki ) {
	if ( $mediaWiki->getAction() !== 'creativecommons' ) {
		return true;
	}

	$rdf = new CreativeCommonsRdf( $article );
	$rdf->show();
	return false;
}