<?php
/**
 * This is an extension to archive preemptively archive external links so that
 * in the even they go down a backup will be available.
 */

if ( !defined( 'MEDIAWIKI' ) ) {
	echo( "This file is an extension to the MediaWiki software and cannot be used standalone.\n" );
	die( 1 );
}

error_reporting ( E_ALL | E_STRICT );

//$wgJobClasses['synchroniseThreadArticleData'] = 'SynchroniseThreadArticleDataJob';

// Hooks
/*$wgHooks['EditPage::attemptSave'][] = array ( 'getExternalLinks' );

function getExternalLinks (  $editpage )  {
    //var_export ( $editpage , FALSE );
    //echo "hi";
    file_put_contents ( './stuff.txt', 'it works :D' . "\n" . var_export ( $editpage ) );
    return true;
}*/

//$wgHooks['LinkerMakeExternalLink'][] = 'findOutWhatTheHellThisHookGivesUs';
    
function findOutWhatTheHellThisHookGivesUs ( &$url, &$text, &$link, &$attributes ) {
    for ( $i = 0, $go = true ; $go !== false; ++$i ) {
        if  ( file_exists ( './extensions/ArchiveLinks/stuff-' . $i . '.txt' ) ) {
            continue;
        } else {
           file_put_contents ( "./extensions/ArchiveLinks/stuff-$i.txt",
                var_export ( $url , TRUE ) . "\n\n" .
                var_export ( $text , TRUE) . "\n\n" .
                var_export ( $link , TRUE) . "\n\n" .
                var_export ( $attributes , TRUE) . "\n\n"
              );
           $go = false;
        }
    }
    //echo "hi";
    return true;   
}

//$wgHooks['LinkerMakeExternalLink'][] = 'getExternalLinks';
//$wgHooks['EditPage::attemptSave'][] = 'getExternalLinks';

$wgExtensionMessagesFiles['ArchiveLinks'] = dirname( __FILE__ ) . '/ArchiveLinks.i18n.php';

$wgHooks['ArticleSaveComplete'][] = 'ArchiveLinks::queueExternalLinks';
$wgHooks['LinkerMakeExternalLink'][] = 'ArchiveLinks::rewriteLinks';

$wgArchiveLinksConfig = array (
    'archive_service' => 'wikiwix',
    'use_multiple_archives' => false,
);

class ArchiveLinks {
    public static function queueExternalLinks ( &$article ) {
	global $wgParser;
	$external_links = $wgParser->getOutput();
	$external_links = $external_links->mExternalLinks;
	//echo "$stuff";
	//file_put_contents ( './extensions/ArchiveLinks/stuff0.txt', var_export( $external_links , TRUE ));

	$db_master = wfGetDB( DB_MASTER );
	$db_slave = wfGetDB( DB_SLAVE );
	$db_result = array();

	$db_master->begin();

	foreach ( $external_links as $link => $unused_value ) {
	    //$db_result['resource'] = $db_slave->select( 'el_archive_resource', '*', '`el_archive_resource`.`resource_url` = "' . $db_slave->strencode( $link ) . '"');
	    $db_result['blacklist'] = $db_slave->select( 'el_archive_blacklist', '*', '`el_archive_blacklist`.`bl_url` = "' . $db_slave->strencode( $link ) . '"');
	    $db_result['queue'] = $db_slave->select( 'el_archive_queue', '*', '`el_archive_queue`.`url` = "' . $db_slave->strencode( $link ) . '"' );

	    if ( $db_result['blacklist']->numRows() === 0 ) {
		if ( $db_result['queue']->numRows() === 0 ) {
		    // this probably a first time job
		    // but we should check the logs and resource table
		    // to make sure
		    $db_master->insert( 'el_archive_queue', array (
			'page_id' => $article->getID(),
			'url' => $link,
			'delay_time' => '0',
			'insertion_time' => time(),
			'in_progress' => '0',
			));
		} else {
		    //this job is already in the queue, why?
		    // * most likely reason is it has already been inserted by another page
		    // * or we are checking it later because the site was down at last archival
		    //  in either case we don't really need to do anything right now, so skip...
		}

	    }

	    //file_put_contents ( './extensions/ArchiveLinks/stuff.txt', var_export( $db_result , TRUE ));

	    //$db_master->insert('el_archive_queue', $array );
	}

	$db_master->commit();

	return true;   
    }
    
    public static function rewriteLinks (  &$url, &$text, &$link, &$attributes ) {
	if ( array_key_exists('rel', $attributes) && $attributes['rel'] === 'nofollow' ) {
	    global $wgArchiveLinksConfig;
	    if ( $wgArchiveLinksConfig['use_multiple_archives'] ) {
		//need to add support for more than one archival service at once
		//  (a page where you can select one from a list of choices)
	    } else {
		switch ( $wgArchiveLinksConfig['archive_service'] ) {
		    case 'local':
			//We need to have something to figure out where the filestore is...
			$link_to_archive = urlencode( substr_replace( $url, '', 0, 7 ) );
			break;
		    case 'wikiwix':
			$link_to_archive = 'http://archive.wikiwix.com/cache/?url=' . $url;
			break;
		    case 'internet_archive':
			$link_to_archive = 'http://wayback.archive.org/web/*/' . $url;
			break;
		    case 'webcitation':
			$link_to_archive = 'http://webcitation.org/query?url=' . $url;
			break;
		}
	    }
	    //Note to self: need to fix this to use Html.php instead of direct html
	    $link = "<a rel=\"nofollow\" class=\"{$attributes['class']}\" href=\"{$url}\">{$text}</a>&#160;<sup><small><a href=\""
	    . "{$link_to_archive}\">" . wfMsg( 'archive-links-cache-title' ) . '</a></small></sup>&#160;';  
	    return false;
	} else {
	    return true;
	}
    }
    
    /*function retrieveLinks ( ) {
	
    }*/

    /*function queueURL ( $url, &$db_master ) {

    }*/
}

//$wgHooks['ArticleSave'][] = 'test';

function test ( ) {
    /*$db_master = wfGetDB( DB_MASTER );
    
    $db_master->insert('el_archive_blacklist', array( 
	'bl_type' => 0,
	'bl_url' => 'http://example.com',
	'bl_reason' => 'test'
	));*/
    
    //$db_slave = wfGetDB( DB_SLAVE );
    
    /*db_result = $db_slave->select( 'el_archive_blacklist', '*',
	    '`el_archive_blacklist`.`bl_url` = "' . $db_slave->strencode( 'http://example.com' ) . '"');
    */
    //$db_result['queue'] = $db_slave->select( 'el_archive_queue', '*', '`el_archive_queue`.`url` = "' . $db_slave->strencode( 'http://example.com' ) . '"' );
    
    //file_put_contents ( './extensions/ArchiveLinks/stuff.txt', var_export( $db_result['queue']->numRows() , TRUE ));
    //$add_quotes = 'http://example.com';
    //file_put_contents ( './extensions/ArchiveLinks/stuff.txt', var_export( $db_slave->addQuotes( $add_quotes ) , TRUE ));
    
    
    
    return false;
}
/*
class InsertURLsIntoQueue extends Job {
        public function __construct( $title, $params ) {
                // Replace synchroniseThreadArticleData with the an identifier for your job.
                parent::__construct( 'insertURLsIntoQueue', $title, $params );
        }
	
	
        public function run() {
	    
        }
}*/