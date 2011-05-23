<?php

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

# The location of the TestSwarm that you're going to run against. Ending in slash.
$swarmUrl = "http://toolserver.org/~krinkle/testswarm/";

# Your TestSwarm username.
$userName = "MediaWiki";

## replace this
# Your authorization token.
$userAuthToken = "*******";

# The maximum number of times you want the tests to be run.
# If a client reports a test as broken, the test swarm will send
# that revision/testmodule combination up to this many times to the 
# same browser/version combination before giving up and continueing
# with other testmodules and revisions.
$testMaxRuns = 2;

# Stuff for the multi-directory sparse checkout
# Because we don't want the entire MediaWiki install, but do need data from multiple dirs
# > Without leading or trailing slashes!
$svnCoRepoInfo = array(
	// To be checked out with "--depth empty"
	'rootBase' => 'http://svn.wikimedia.org/svnroot/mediawiki/trunk/phase3',

	// Updated with "--set-depth infinity", also used to get svn revision
	'resourcesDir' => 'resources',

	// Updated with "--set-depth empty"
	'qunitBase' => 'tests',

	// Updated with "--set-depth infinity", also used to get svn revision
	'qunitDir' => 'tests/qunit',
);

# The directory in which the checkouts will occur. Without trailing slash!
$svnCoTargetDir = "/home/krinkle/testswarm-mediawiki/tmp-checkout";
$svnCoTargetUrl = "http://toolserver.org/~krinkle/testswarm-tmp-checkouts";

# URL where tests can be executed
# $1 = revision id
# $2 = module name
$testUrlPattern = $svnCoTargetUrl . '/r$1/tests/qunit/?filter=$2';

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

# Get latest revision number of HEAD for QUnit tests dir and Resources dir
$svnHeadRevs = array(
	'tests' => null,
	'resources' => null,
);
$svnHeadRevTop = null;

foreach ( array( 
	'tests' => $svnCoRepoInfo['rootBase'] . '/' . $svnCoRepoInfo['qunitDir'],
	'resources' => $svnCoRepoInfo['rootBase'] . '/' . $svnCoRepoInfo['resourcesDir'],
) as $dirKey => $dirUrl ) {

	$tmpCmd = array();

	exec( "svn info $dirUrl", $tmpCmd['output'], $tmpCmd['return'] );
	
	if ( is_array( $tmpCmd['output'] ) && count( $tmpCmd['output'] ) ) {

		foreach( $tmpCmd['output'] as $cmdLine ) {

			$lineParts = explode( ':', $cmdLine, 2 );
			if ( trim( $lineParts[0] ) == 'Last Changed Rev' ) {

				$svnHeadRevs[$dirKey] = trim( $lineParts[1] );
				break;
			}

			unset( $cmdLine, $lineParts );
		}
	}
	unset( $tmpCmd, $key, $dirUrl );
}

if ( empty( $svnHeadRevs['tests'] ) || empty( $svnHeadRevs['resources'] ) ) {
	die("Problem getting svn info.");
}

# Determine the highest of each
$svnHeadRevTop = max( intval( $svnHeadRevs['tests'] ), intval( $svnHeadRevs['resources'] ) );

# Check out a specific revision
# We're doing a sparse checkout of phase3
# and get /resources and /tests/qunit

$revTargetTmpDir = "$svnCoTargetDir/r$svnHeadRevTop";

if ( is_dir( $svnCoTargetDir ) ) {
	// Already checked out ?
	if ( is_dir( "$revTargetTmpDir" ) ) {
		stopper("Last revision (r$svnHeadRevTop) has been done already. Skipping this loop.");
	}
	$cmdExc = array();
	$cmd = null;

	// Checkout empty root of mediawiki
	$cmd = "svn checkout -r $svnHeadRevTop {$svnCoRepoInfo['rootBase']} $revTargetTmpDir --depth empty";
	logger( $cmd ); exec( $cmd, $cmdExc['output'], $cmdExc['return'] );
	logger( print_r( $cmdExc['output'], true ) );

	// Checkout full depth of the resources directory
	$cmd = "svn update -r $svnHeadRevTop --set-depth infinity $revTargetTmpDir/{$svnCoRepoInfo['resourcesDir']}";
	logger( $cmd ); exec( $cmd, $cmdExc['output'], $cmdExc['return'] );
	logger( print_r( $cmdExc['output'], true ) );

	// Checkout empty qunit's parent directory
	$cmd = "svn update -r $svnHeadRevTop --set-depth empty $revTargetTmpDir/{$svnCoRepoInfo['qunitBase']}";
	logger( $cmd ); exec( $cmd, $cmdExc['output'], $cmdExc['return'] );
	logger( print_r( $cmdExc['output'], true ) );

	// Checkout full depth of qunit directory
	$cmd = "svn update -r $svnHeadRevTop --set-depth infinity $revTargetTmpDir/{$svnCoRepoInfo['qunitDir']}";
	logger( $cmd ); exec( $cmd, $cmdExc['output'], $cmdExc['return'] );
	logger( print_r( $cmdExc['output'], true ) );

	unset( $cmd, $cmdExc );

} else {
	stopper("Problem locating temporary checkout directory.");
}

# Get array of modules

$unitDir = glob( "$revTargetTmpDir/{$svnCoRepoInfo['qunitDir']}/suites/resources/*/*.js" );
$suites = array_map( 'basename', $unitDir );

# Add jobs

if ( true ) {

	$params = array(
		"state" => "addjob",
		"output" => "dump",
		"user" => $userName,
		"max" => $testMaxRuns,
		"job_name" => str_replace( '$1', $svnHeadRevTop, $jobNamePattern ),
		"browsers" => $browsers,
		"auth" => $userAuthToken
	);
	
	$query = http_build_query( $params );

	foreach ( $suites as $suite ) {
		$query .= "&suites[]=" . rawurlencode( $suite ) .
		          "&urls[]=" . getTestUrl( $svnHeadRevTop, $suite );
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
