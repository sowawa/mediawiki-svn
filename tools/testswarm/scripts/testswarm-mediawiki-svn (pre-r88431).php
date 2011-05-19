<?php

	die( 'Deprecated' );
	// This is an old file from before MediaWiki
	// moved the test suite outside the resources
	// directory which required us to modify the script
	// to allow sparse checkouts.
	// Bellow is the script as it was before then.
	// This may be useful for others using TestSwarm
	// and needing a PHP script to automaticlaly populate
	// tests from SVN. 
	// -- Krinkle (2011-05-20)

/**
 * INIT
 * ----------
 */
// We like debug messages to be outputted to wherever the cronjob is set to
error_reporting(E_ALL); ini_set('display_errors', 1);

// Toggle this to hide debug info (commands executed and their output etc.)
$GLOBALS['debugMode'] = true;

// Make sure we're in the directory where the script is located if not already
chdir( dirname ( __FILE__ ) );

/**
 * FUNCTIONS
 * ----------
 */

function logger( $msg ) {
	if ( $GLOBALS['debugMode'] ) {
		echo "$msg\n";
	}
}

function stopper( $msg ) {
	die( "$msg\n" );
}

function getTestUrl( $rev, $suite ) {
	$url = $GLOBALS['testUrlPattern'];
	$url = str_replace( '$1', rawurlencode( $rev ), $url );
	$url = str_replace( '$2', rawurlencode( $suite ), $url );
	return $url;
}


/**
 * CONFIGURE
 * ----------
 */

# The location of the TestSwarm that you're going to run against. Ending in slash!
$swarmUrl = "http://toolserver.org/~krinkle/testswarm/";

# Your TestSwarm username.
$userName = "MediaWiki";

## replace this
# Your authorization token.
$userAuthToken = "*******";

# The maximum number of times you want the tests to be run.
$testMaxRuns = 2;

# The URL from which a copy will be checked out. Without trailing slash!
$svnCoRepoDir = "http://svn.wikimedia.org/svnroot/mediawiki/trunk/phase3/resources";

# The directory in which the checkouts will occur. Without trailing slash!
$svnCoTargetDir = "/home/krinkle/testswarm-mediawiki/tmp-checkout";
$svnCoTargetUrl = "http://toolserver.org/~krinkle/testswarm-tmp-checkouts";

# URL where tests can be executed
# $1 = revision id
# $2 = module name
$testUrlPattern = $svnCoTargetUrl . '/r$1/test/?filter=$2';

# The name of the job that will be submitted
# (pick a descriptive, but short, name to make it easy to search)

# Note: The string $1 will be replaced with the current revision id.
$jobNamePattern = 'MediaWiki Commit <a href="http://www.mediawiki.org/wiki/Special:Code/MediaWiki/$1">r$1</a>';

# The browsers you wish to run against. Options include:
#  - "all" all available browsers.
#  - "popular" the most popular browser (99%+ of all browsers in use)
#  - "current" the current release of all the major browsers
#  - "gbs" the browsers currently supported in Yahoo's Graded Browser Support
#  - "beta" upcoming alpha/beta of popular browsers
#  - "popularbeta" the most popular browser and their upcoming releases
#  - "popularbetamobile"
$browsers = "popularbeta";

# All the suites that you wish to run within this job
# (can be any number of suites)

## Comment out to insert static suite list here (ie. QUnit modules)
#$suites = array(
#	'foo.js',
#	'bar.util.js',
#	'jquery.baz.js',
#);

########### NO NEED TO CONFIGURE BELOW HERE ############

/**
 * DOING STUFF
 * ----------
 */

# Get latest revision number of HEAD

$svnInfoCMD = array();
$svnHeadRev = null;
exec( "svn info $svnCoRepoDir", $svnInfoCMD['output'], $svnInfoCMD['return'] );

if ( is_array( $svnInfoCMD['output'] ) && count( $svnInfoCMD['output'] ) ) {
	foreach( $svnInfoCMD['output'] as $line ) {
		$lineParts = explode( ':', $line, 2 );
		if ( trim( $lineParts[0] ) == 'Last Changed Rev' ) {
			$svnHeadRev = trim( $lineParts[1] );
			break;
		}
	}
	unset( $line, $lineParts );
}

if ( empty( $svnHeadRev ) ) {
	die("Problem getting svn info.");
}

# Check out a specific revision

if ( is_dir( $svnCoTargetDir ) ) {
	// Already checked out ?
	if ( is_dir( "$svnCoTargetDir/r$svnHeadRev" ) ) {
		stopper("Last revision has been done already. Skipping this loop.");
	}
	$svnCheckoutCMD = array();
	logger( "> $ svn checkout -r $svnHeadRev $svnCoRepoDir $svnCoTargetDir/r$svnHeadRev" );
	exec( "svn checkout -r $svnHeadRev $svnCoRepoDir $svnCoTargetDir/r$svnHeadRev", $svnCheckoutCMD['output'], $svnCheckoutCMD['return'] );
	logger( print_r( $svnCheckoutCMD['output'], true ) );
	unset( $svnCheckoutCMD );

} else {
	stopper( "Problem locating temporary checkout directory." );
}

# Get array of test modules

$unitDir = glob( "$svnCoTargetDir/r$svnHeadRev/test/unit/*/*.js" );
$suites = array_map( 'basename', $unitDir );


# Add jobs

if ( true ) {

	$params = array(
		"state" => "addjob",
		"output" => "dump",
		"user" => $userName,
		"max" => $testMaxRuns,
		"job_name" => str_replace( '$1', $svnHeadRev, $jobNamePattern ),
		"browsers" => $browsers,
		"auth" => $userAuthToken
	);
	
	$query = http_build_query( $params );

	foreach ( $suites as $suite ) {
		$query .= "&suites[]=" . rawurlencode( $suite ) .
		          "&urls[]=" . getTestUrl( $svnHeadRev, $suite );
	}

	logger( "curl -d \"$query\" $swarmUrl" );

	$curlPostCMD = array();
	exec( "curl -d \"$query\" $swarmUrl", $curlPostCMD['output'], $curlPostCMD['return'] );

	logger( "Results: {$curlPostCMD['output']}" );

	if ( $curlPostCMD['output'] ) {
		file_put_contents(
			__DIR__ . '/testswarm-mediawiki-svn.log',
			'[' . date('r') . '] ' . implode( ' \\', $curlPostCMD['output'] ) . "\n",
			FILE_APPEND
		);

	} else {
		stopper( "Job not submitted properly." );
	}

} else {
    stopper( "No new revision." );
}