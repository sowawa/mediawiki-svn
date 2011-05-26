<?php

$wgExtensionCredits['other'][] = array(
	'path' => __FILE__,
	'name' => 'DublinCore',
	'url' => '',
	'author' => 'Evan Prodromou',
);

$wgHooks['MediaWikiPerformAction'][] = 'efDublinCorePreformAction';

$wgAutoloadClasses['DublinCoreRdf'] = $dir . 'DublinCoreRdf_body.php';

function efDublinCorePreformAction( $output, $article, $title, $user, $request, $mediaWiki ) {
	if ( $mediaWiki->getAction() !== 'dublincore' ) {
		return true;
	}

	$rdf = new DublinCoreRdf( $article );
	$rdf->show();
	return false;
}