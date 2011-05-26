<?php

$wgExtensionCredits['other'][] = array(
	'path' => __FILE__,
	'name' => 'DublinCoreRdf',
	'url' => '',
	'author' => 'Evan Prodromou',
);

$wgHooks['MediaWikiPerformAction'][] = 'efDublinCorePerformAction';

$wgAutoloadClasses['DublinCoreRdf'] = $dir . 'DublinCoreRdf_body.php';

function efDublinCorePerformAction( $output, $article, $title, $user, $request, $mediaWiki ) {
	if ( $mediaWiki->getAction() !== 'dublincore' ) {
		return true;
	}

	$rdf = new DublinCoreRdf( $article );
	$rdf->show();
	return false;
}