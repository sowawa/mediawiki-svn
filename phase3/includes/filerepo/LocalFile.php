<?php
/**
 */

/**
 * Bump this number when serialized cache records may be incompatible.
 */
define( 'MW_FILE_VERSION', 3 );

/**
 * Class to represent a local file in the wiki's own database
 *
 * Provides methods to retrieve paths (physical, logical, URL),
 * to generate image thumbnails or for uploading.
 *
 * @addtogroup FileRepo
 */
class LocalFile extends File
{
	/**#@+
	 * @private
	 */
	var	$fileExists,    # does the file file exist on disk? (loadFromXxx)
		$historyLine,	# Number of line to return by nextHistoryLine() (constructor)
		$historyRes,	# result of the query for the file's history (nextHistoryLine)
		$width,         # \
		$height,        #  |
		$bits,          #   --- returned by getimagesize (loadFromXxx)
		$attr,          # /
		$type,          # MEDIATYPE_xxx (bitmap, drawing, audio...)
		$mime,          # MIME type, determined by MimeMagic::guessMimeType
		$size,          # Size in bytes (loadFromXxx)
		$metadata,      # Metadata
		$dataLoaded;    # Whether or not all this has been loaded from the database (loadFromXxx)

	/**#@-*/

	function newFromTitle( $title, $repo ) {
		return new self( $title, $repo );
	}

	function __construct( $title, $repo ) {
		if( !is_object( $title ) ) {
			throw new MWException( __CLASS__.' constructor given bogus title.' );
		}
		parent::__construct( $title, $repo );
		$this->metadata = '';
		$this->historyLine = 0;
		$this->dataLoaded = false;
	}

	/**
	 * Get the memcached key
	 */
	function getCacheKey() {
		$hashedName = md5($this->name);
		return wfMemcKey( 'file', $hashedName );
	}

	/**
	 * Try to load file metadata from memcached. Returns true on success.
	 */
	function loadFromCache() {
		global $wgMemc;
		wfProfileIn( __METHOD__ );
		$this->dataLoaded = false;
		$key = $this->getCacheKey();
		if ( !$key ) {
			return false;
		}
		$cachedValues = $wgMemc->get( $key );

		// Check if the key existed and belongs to this version of MediaWiki
		if (!empty($cachedValues) && is_array($cachedValues)
		  && isset($cachedValues['version']) && ( $cachedValues['version'] == MW_FILE_VERSION )
		  && isset( $cachedValues['mime'] ) && isset( $cachedValues['metadata'] ) )
		{
			wfDebug( "Pulling file metadata from local cache\n" );
			$this->name = $cachedValues['name'];
			$this->fileExists = $cachedValues['fileExists'];
			$this->width = $cachedValues['width'];
			$this->height = $cachedValues['height'];
			$this->bits = $cachedValues['bits'];
			$this->type = $cachedValues['type'];
			$this->mime = $cachedValues['mime'];
			$this->metadata = $cachedValues['metadata'];
			$this->size = $cachedValues['size'];
			$this->dataLoaded = true;
			$this->path = $this->getFullPath();
		}
		if ( $this->dataLoaded ) {
			wfIncrStats( 'image_cache_hit' );
		} else {
			wfIncrStats( 'image_cache_miss' );
		}

		wfProfileOut( __METHOD__ );
		return $this->dataLoaded;
	}

	/**
	 * Save the file metadata to memcached
	 */
	function saveToCache() {
		global $wgMemc;
		$this->load();
		$key = $this->getCacheKey();
		if ( !$key ) {
			return;
		}
		$cachedValues = array(
			'version'    => MW_FILE_VERSION,
			'name'       => $this->name,
			'fileExists' => $this->fileExists,
			'width'      => $this->width,
			'height'     => $this->height,
			'bits'       => $this->bits,
			'type'       => $this->type,
			'mime'       => $this->mime,
			'metadata'   => $this->metadata,
			'size'       => $this->size );

		$wgMemc->set( $key, $cachedValues, 60 * 60 * 24 * 7 ); // A week
	}

	/**
	 * Load metadata from the file itself
	 */
	function loadFromFile() {
		global $wgContLang;
		wfProfileIn( __METHOD__ );
		$this->path = $this->getFullPath();
		$this->fileExists = file_exists( $this->path );
		$gis = array();

		if ( $this->fileExists ) {
			$magic=& MimeMagic::singleton();

			$this->mime = $magic->guessMimeType($this->path,true);
			$this->type = $magic->getMediaType($this->path,$this->mime);
			$handler = MediaHandler::getHandler( $this->mime );

			# Get size in bytes
			$this->size = filesize( $this->path );

			# Height, width and metadata
			if ( $handler ) {
				$gis = $handler->getImageSize( $this, $this->path );
				$this->metadata = $handler->getMetadata( $this, $this->path );
			} else {
				$gis = false;
				$this->metadata = '';
			}

			wfDebug(__METHOD__.': '.$this->path." loaded, ".$this->size." bytes, ".$this->mime.".\n");
		} else {
			$this->mime = NULL;
			$this->type = MEDIATYPE_UNKNOWN;
			$this->metadata = '';
			wfDebug(__METHOD__.': '.$this->path." NOT FOUND!\n");
		}

		if( $gis ) {
			$this->width = $gis[0];
			$this->height = $gis[1];
		} else {
			$this->width = 0;
			$this->height = 0;
		}

		#NOTE: $gis[2] contains a code for the image type. This is no longer used.

		#NOTE: we have to set this flag early to avoid load() to be called
		# be some of the functions below. This may lead to recursion or other bad things!
		# as ther's only one thread of execution, this should be safe anyway.
		$this->dataLoaded = true;

		if ( isset( $gis['bits'] ) )  $this->bits = $gis['bits'];
		else $this->bits = 0;

		wfProfileOut( __METHOD__ );
	}

	/**
	 * Load file metadata from the DB
	 */
	function loadFromDB() {
		global $wgContLang;
		wfProfileIn( __METHOD__ );

		$dbr = $this->repo->getSlaveDB();

		$row = $dbr->selectRow( 'image',
			array( 'img_size', 'img_width', 'img_height', 'img_bits',
			       'img_media_type', 'img_major_mime', 'img_minor_mime', 'img_metadata' ),
			array( 'img_name' => $this->name ), __METHOD__ );
		if ( $row ) {
			$this->fileExists = true;
			$this->loadFromRow( $row );
			$this->path = $this->getFullPath();
			// Check for rows from a previous schema, quietly upgrade them
			$this->maybeUpgradeRow();
		} else {
			$this->size = 0;
			$this->width = 0;
			$this->height = 0;
			$this->bits = 0;
			$this->type = 0;
			$this->fileExists = false;
			$this->metadata = '';
			$this->mime = false;
		}

		# Unconditionally set loaded=true, we don't want the accessors constantly rechecking
		$this->dataLoaded = true;
		wfProfileOut( __METHOD__ );
	}

	/*
	 * Load file metadata from a DB result row
	 */
	function loadFromRow( &$row ) {
		$this->size = $row->img_size;
		$this->width = $row->img_width;
		$this->height = $row->img_height;
		$this->bits = $row->img_bits;
		$this->type = $row->img_media_type;

		$major= $row->img_major_mime;
		$minor= $row->img_minor_mime;

		if (!$major) $this->mime = "unknown/unknown";
		else {
			if (!$minor) $minor= "unknown";
			$this->mime = $major.'/'.$minor;
		}
		$this->metadata = $row->img_metadata;

		$this->dataLoaded = true;
	}

	/**
	 * Load file metadata from cache or DB, unless already loaded
	 */
	function load() {
		if ( !$this->dataLoaded ) {
			if ( !$this->loadFromCache() ) {
				$this->loadFromDB();
				if ( $this->fileExists ) {
					// FIXME: We can do negative caching for local files, because the cache
					// will be purged on upload. But we can't do it when shared files
					// are enabled, since updates to that won't purge foreign caches.
					$this->saveToCache();
				} 
			}
			$this->dataLoaded = true;
		}
	}

	/**
	 * Upgrade a row if it needs it
	 */
	function maybeUpgradeRow() {
		if ( is_null($this->type) || $this->mime == 'image/svg' ) {
			$this->upgradeRow();
		} else {
			$handler = $this->getHandler();
			if ( $handler && !$handler->isMetadataValid( $this, $this->metadata ) ) {
				$this->upgradeRow();
			}
		}
	}

	/**
	 * Fix assorted version-related problems with the image row by reloading it from the file
	 */
	function upgradeRow() {
		wfProfileIn( __METHOD__ );

		$this->loadFromFile();

		$dbw = $this->repo->getMasterDB();
		list( $major, $minor ) = self::splitMime( $this->mime );

		wfDebug(__METHOD__.': upgrading '.$this->name." to the current schema\n");

		$dbw->update( 'image',
			array(
				'img_width' => $this->width,
				'img_height' => $this->height,
				'img_bits' => $this->bits,
				'img_media_type' => $this->type,
				'img_major_mime' => $major,
				'img_minor_mime' => $minor,
				'img_metadata' => $this->metadata,
			), array( 'img_name' => $this->name ), __METHOD__
		);
		wfProfileOut( __METHOD__ );
	}

	/** splitMime inherited */
	/** getName inherited */
	/** getTitle inherited */
	/** getURL inherited */
	/** getViewURL inherited */

	/**
	 * Return the path of the file in the
	 * local file system as an absolute path
	 * @public
	 */
	function getPath() {
		$this->load();
		return $this->path;
	}

	/**
	 * Return the width of the image
	 *
	 * Returns false on error
	 * @public
	 */
	function getWidth( $page = 1 ) {
		$this->load();
		if ( $this->isMultipage() ) {
			$dim = $this->getHandler()->getPageDimensions( $this, $page );
			if ( $dim ) {
				return $dim['width'];
			} else {
				return false;
			}
		} else {
			return $this->width;
		}
	}

	/**
	 * Return the height of the image
	 *
	 * Returns false on error
	 * @public
	 */
	function getHeight( $page = 1 ) {
		$this->load();
		if ( $this->isMultipage() ) {
			$dim = $this->getHandler()->getPageDimensions( $this, $page );
			if ( $dim ) {
				return $dim['height'];
			} else {
				return false;
			}
		} else {
			return $this->height;
		}
	}

	/**
	 * Get handler-specific metadata
	 */
	function getMetadata() {
		$this->load();
		return $this->metadata;
	}

	/**
	 * Return the size of the image file, in bytes
	 * @public
	 */
	function getSize() {
		$this->load();
		return $this->size;
	}

	/**
	 * Returns the mime type of the file.
	 */
	function getMimeType() {
		$this->load();
		return $this->mime;
	}

	/**
	 * Return the type of the media in the file.
	 * Use the value returned by this function with the MEDIATYPE_xxx constants.
	 */
	function getMediaType() {
		$this->load();
		return $this->type;
	}

	/** canRender inherited */
	/** mustRender inherited */
	/** allowInlineDisplay inherited */
	/** isSafeFile inherited */
	/** isTrustedFile inherited */

	/**
	 * Returns true if the file file exists on disk.
	 * @return boolean Whether file file exist on disk.
	 * @public
	 */
	function exists() {
		$this->load();
		return $this->fileExists;
	}

	/** getTransformScript inherited */
	/** getUnscaledThumb inherited */
	/** thumbName inherited */
	/** createThumb inherited */
	/** getThumbnail inherited */
	/** transform inherited */
	
	/**
	 * Fix thumbnail files from 1.4 or before, with extreme prejudice
	 */
	function migrateThumbFile( $thumbName ) {
		$thumbDir = $this->getThumbPath();
		$thumbPath = "$thumbDir/$thumbName";
		if ( is_dir( $thumbPath ) ) {
			// Directory where file should be
			// This happened occasionally due to broken migration code in 1.5
			// Rename to broken-*
			for ( $i = 0; $i < 100 ; $i++ ) {
				$broken = $this->repo->getZonePath('public') . "/broken-$i-$thumbName";
				if ( !file_exists( $broken ) ) {
					rename( $thumbPath, $broken );
					break;
				}
			}
			// Doesn't exist anymore
			clearstatcache();
		}
		if ( is_file( $thumbDir ) ) {
			// File where directory should be
			unlink( $thumbDir );
			// Doesn't exist anymore
			clearstatcache();
		}
	}

	/** getHandler inherited */
	/** iconThumb inherited */
	/** getLastError inherited */

	/**
	 * Get all thumbnail names previously generated for this file
	 */
	function getThumbnails() {
		if ( $this->isHashed() ) {
			$this->load();
			$files = array();
			$dir = $this->getThumbPath();

			if ( is_dir( $dir ) ) {
				$handle = opendir( $dir );

				if ( $handle ) {
					while ( false !== ( $file = readdir($handle) ) ) {
						if ( $file{0} != '.' ) {
							$files[] = $file;
						}
					}
					closedir( $handle );
				}
			}
		} else {
			$files = array();
		}

		return $files;
	}

	/**
	 * Refresh metadata in memcached, but don't touch thumbnails or squid
	 */
	function purgeMetadataCache() {
		clearstatcache();
		$this->loadFromFile();
		$this->saveToCache();
	}

	/**
	 * Delete all previously generated thumbnails, refresh metadata in memcached and purge the squid
	 */
	function purgeCache( $archiveFiles = array() ) {
		global $wgUseSquid;

		// Refresh metadata cache
		$this->purgeMetadataCache();

		// Delete thumbnails
		$files = $this->getThumbnails();
		$dir = $this->getThumbPath();
		$urls = array();
		foreach ( $files as $file ) {
			$m = array();
			# Check that the base file name is part of the thumb name
			# This is a basic sanity check to avoid erasing unrelated directories
			if ( strpos( $file, $this->name ) !== false ) {
				$url = $this->getThumbUrl( $file );
				$urls[] = $url;
				@unlink( "$dir/$file" );
			}
		}

		// Purge the squid
		if ( $wgUseSquid ) {
			$urls[] = $this->getURL();
			foreach ( $archiveFiles as $file ) {
				$urls[] = $this->getArchiveUrl( $file );
			}
			wfPurgeSquidServers( $urls );
		}
	}

	/** purgeDescription inherited */
	/** purgeEverything inherited */

	/**
	 * Return the history of this file, line by line.
	 * starts with current version, then old versions.
	 * uses $this->historyLine to check which line to return:
	 *  0      return line for current version
	 *  1      query for old versions, return first one
	 *  2, ... return next old version from above query
	 *
	 * @public
	 */
	function nextHistoryLine() {
		$dbr = $this->repo->getSlaveDB();

		if ( $this->historyLine == 0 ) {// called for the first time, return line from cur
			$this->historyRes = $dbr->select( 'image',
				array(
					'img_size',
					'img_description',
					'img_user','img_user_text',
					'img_timestamp',
					'img_width',
					'img_height',
					"'' AS oi_archive_name"
				),
				array( 'img_name' => $this->title->getDBkey() ),
				__METHOD__
			);
			if ( 0 == $dbr->numRows( $this->historyRes ) ) {
				return FALSE;
			}
		} else if ( $this->historyLine == 1 ) {
			$this->historyRes = $dbr->select( 'oldimage',
				array(
					'oi_size AS img_size',
					'oi_description AS img_description',
					'oi_user AS img_user',
					'oi_user_text AS img_user_text',
					'oi_timestamp AS img_timestamp',
					'oi_width as img_width',
					'oi_height as img_height',
					'oi_archive_name'
				),
				array( 'oi_name' => $this->title->getDBkey() ),
				__METHOD__,
				array( 'ORDER BY' => 'oi_timestamp DESC' )
			);
		}
		$this->historyLine ++;

		return $dbr->fetchObject( $this->historyRes );
	}

	/**
	 * Reset the history pointer to the first element of the history
	 * @public
	 */
	function resetHistory() {
		$this->historyLine = 0;
	}

	/** getFullPath inherited */
	/** getHashPath inherited */
	/** getRel inherited */
	/** getUrlRel inherited */
	/** getArchivePath inherited */
	/** getThumbPath inherited */
	/** getArchiveUrl inherited */
	/** getThumbUrl inherited */
	/** getArchiveVirtualUrl inherited */
	/** getThumbVirtualUrl inherited */
	/** isHashed inherited */

	/**
	 * Record a file upload in the upload log and the image table
	 */
	function recordUpload( $oldver, $desc, $license = '', $copyStatus = '', $source = '', $watch = false ) {
		global $wgUser, $wgUseCopyrightUpload;

		$dbw = $this->repo->getMasterDB();

		// Delete thumbnails and refresh the metadata cache
		$this->purgeCache();

		// Fail now if the file isn't there
		if ( !$this->fileExists ) {
			wfDebug( __METHOD__.": File ".$this->path." went missing!\n" );
			return false;
		}

		if ( $wgUseCopyrightUpload ) {
			if ( $license != '' ) {
				$licensetxt = '== ' . wfMsgForContent( 'license' ) . " ==\n" . '{{' . $license . '}}' . "\n";
			}
			$textdesc = '== ' . wfMsg ( 'filedesc' ) . " ==\n" . $desc . "\n" .
			  '== ' . wfMsgForContent ( 'filestatus' ) . " ==\n" . $copyStatus . "\n" .
			  "$licensetxt" .
			  '== ' . wfMsgForContent ( 'filesource' ) . " ==\n" . $source ;
		} else {
			if ( $license != '' ) {
				$filedesc = $desc == '' ? '' : '== ' . wfMsg ( 'filedesc' ) . " ==\n" . $desc . "\n";
				 $textdesc = $filedesc .
					 '== ' . wfMsgForContent ( 'license' ) . " ==\n" . '{{' . $license . '}}' . "\n";
			} else {
				$textdesc = $desc;
			}
		}

		$now = $dbw->timestamp();

		#split mime type
		if (strpos($this->mime,'/')!==false) {
			list($major,$minor)= explode('/',$this->mime,2);
		}
		else {
			$major= $this->mime;
			$minor= "unknown";
		}

		# Test to see if the row exists using INSERT IGNORE
		# This avoids race conditions by locking the row until the commit, and also
		# doesn't deadlock. SELECT FOR UPDATE causes a deadlock for every race condition.
		$dbw->insert( 'image',
			array(
				'img_name' => $this->name,
				'img_size'=> $this->size,
				'img_width' => intval( $this->width ),
				'img_height' => intval( $this->height ),
				'img_bits' => $this->bits,
				'img_media_type' => $this->type,
				'img_major_mime' => $major,
				'img_minor_mime' => $minor,
				'img_timestamp' => $now,
				'img_description' => $desc,
				'img_user' => $wgUser->getID(),
				'img_user_text' => $wgUser->getName(),
				'img_metadata' => $this->metadata,
			),
			__METHOD__,
			'IGNORE'
		);

		if( $dbw->affectedRows() == 0 ) {
			# Collision, this is an update of a file
			# Insert previous contents into oldimage
			$dbw->insertSelect( 'oldimage', 'image',
				array(
					'oi_name' => 'img_name',
					'oi_archive_name' => $dbw->addQuotes( $oldver ),
					'oi_size' => 'img_size',
					'oi_width' => 'img_width',
					'oi_height' => 'img_height',
					'oi_bits' => 'img_bits',
					'oi_timestamp' => 'img_timestamp',
					'oi_description' => 'img_description',
					'oi_user' => 'img_user',
					'oi_user_text' => 'img_user_text',
				), array( 'img_name' => $this->name ), __METHOD__
			);

			# Update the current image row
			$dbw->update( 'image',
				array( /* SET */
					'img_size' => $this->size,
					'img_width' => intval( $this->width ),
					'img_height' => intval( $this->height ),
					'img_bits' => $this->bits,
					'img_media_type' => $this->type,
					'img_major_mime' => $major,
					'img_minor_mime' => $minor,
					'img_timestamp' => $now,
					'img_description' => $desc,
					'img_user' => $wgUser->getID(),
					'img_user_text' => $wgUser->getName(),
					'img_metadata' => $this->metadata,
				), array( /* WHERE */
					'img_name' => $this->name
				), __METHOD__
			);
		} else {
			# This is a new file
			# Update the image count
			$site_stats = $dbw->tableName( 'site_stats' );
			$dbw->query( "UPDATE $site_stats SET ss_images=ss_images+1", __METHOD__ );
		}

		$descTitle = $this->getTitle();
		$article = new Article( $descTitle );
		$minor = false;
		$watch = $watch || $wgUser->isWatched( $descTitle );
		$suppressRC = true; // There's already a log entry, so don't double the RC load

		if( $descTitle->exists() ) {
			// TODO: insert a null revision into the page history for this update.
			if( $watch ) {
				$wgUser->addWatch( $descTitle );
			}

			# Invalidate the cache for the description page
			$descTitle->invalidateCache();
			$descTitle->purgeSquid();
		} else {
			// New file; create the description page.
			$article->insertNewArticle( $textdesc, $desc, $minor, $watch, $suppressRC );
		}

		# Hooks, hooks, the magic of hooks...
		wfRunHooks( 'FileUpload', array( $this ) );

		# Add the log entry
		$log = new LogPage( 'upload' );
		$log->addEntry( 'upload', $descTitle, $desc );

		# Commit the transaction now, in case something goes wrong later
		# The most important thing is that files don't get lost, especially archives
		$dbw->immediateCommit();

		# Invalidate cache for all pages using this file
		$update = new HTMLCacheUpdate( $this->getTitle(), 'imagelinks' );
		$update->doUpdate();

		return true;
	}

	/**
	 * Move or copy a file to its public location. If a file exists at the  
	 * destination, move it to an archive. Returns the archive name on success 
	 * or an empty string if it was a new file, and a wikitext-formatted 
	 * WikiError object on failure. 
	 *
	 * The archive name should be passed through to recordUpload for database
	 * registration.
	 *
	 * @param string $sourcePath Local filesystem path to the source image
	 * @param integer $flags A bitwise combination of:
	 *     File::DELETE_SOURCE    Delete the source file, i.e. move 
	 *         rather than copy
	 * @return The archive name on success or an empty string if it was a new 
	 *     file, and a wikitext-formatted WikiError object on failure. 
	 */
	function publish( $srcPath, $flags = 0 ) {
		$dstPath = $this->getFullPath();
		$archiveName = gmdate( 'YmdHis' ) . "!{$this->name}";
		$archivePath = $this->getArchivePath( $archiveName );
		$flags = $flags & File::DELETE_SOURCE ? LocalRepo::DELETE_SOURCE : 0;
		$status = $this->repo->publish( $srcPath, $dstPath, $archivePath, $flags );
		if ( WikiError::isError( $status ) ) {
			return $status;
		} elseif ( $status == 'new' ) {
			return '';
		} else {
			return $archiveName;
		}
	}

	/** getLinksTo inherited */
	/** getExifData inherited */
	/** isLocal inherited */
	/** wasDeleted inherited */
	
	/**
	 * Delete all versions of the file.
	 *
	 * Moves the files into an archive directory (or deletes them)
	 * and removes the database rows.
	 *
	 * Cache purging is done; logging is caller's responsibility.
	 *
	 * @param $reason
	 * @return true on success, false on some kind of failure
	 */
	function delete( $reason, $suppress=false ) {
		$transaction = new FSTransaction();
		$urlArr = array( $this->getURL() );

		if( !FileStore::lock() ) {
			wfDebug( __METHOD__.": failed to acquire file store lock, aborting\n" );
			return false;
		}

		try {
			$dbw = $this->repo->getMasterDB();
			$dbw->begin();

			// Delete old versions
			$result = $dbw->select( 'oldimage',
				array( 'oi_archive_name' ),
				array( 'oi_name' => $this->name ) );

			while( $row = $dbw->fetchObject( $result ) ) {
				$oldName = $row->oi_archive_name;

				$transaction->add( $this->prepareDeleteOld( $oldName, $reason, $suppress ) );

				// We'll need to purge this URL from caches...
				$urlArr[] = $this->getArchiveUrl( $oldName );
			}
			$dbw->freeResult( $result );

			// And the current version...
			$transaction->add( $this->prepareDeleteCurrent( $reason, $suppress ) );

			$dbw->immediateCommit();
		} catch( MWException $e ) {
			wfDebug( __METHOD__.": db error, rolling back file transactions\n" );
			$transaction->rollback();
			FileStore::unlock();
			throw $e;
		}

		wfDebug( __METHOD__.": deleted db items, applying file transactions\n" );
		$transaction->commit();
		FileStore::unlock();


		// Update site_stats
		$site_stats = $dbw->tableName( 'site_stats' );
		$dbw->query( "UPDATE $site_stats SET ss_images=ss_images-1", __METHOD__ );

		$this->purgeEverything( $urlArr );

		return true;
	}


	/**
	 * Delete an old version of the file.
	 *
	 * Moves the file into an archive directory (or deletes it)
	 * and removes the database row.
	 *
	 * Cache purging is done; logging is caller's responsibility.
	 *
	 * @param $reason
	 * @throws MWException or FSException on database or filestore failure
	 * @return true on success, false on some kind of failure
	 */
	function deleteOld( $archiveName, $reason, $suppress=false ) {
		$transaction = new FSTransaction();
		$urlArr = array();

		if( !FileStore::lock() ) {
			wfDebug( __METHOD__.": failed to acquire file store lock, aborting\n" );
			return false;
		}

		$transaction = new FSTransaction();
		try {
			$dbw = $this->repo->getMasterDB();
			$dbw->begin();
			$transaction->add( $this->prepareDeleteOld( $archiveName, $reason, $suppress ) );
			$dbw->immediateCommit();
		} catch( MWException $e ) {
			wfDebug( __METHOD__.": db error, rolling back file transaction\n" );
			$transaction->rollback();
			FileStore::unlock();
			throw $e;
		}

		wfDebug( __METHOD__.": deleted db items, applying file transaction\n" );
		$transaction->commit();
		FileStore::unlock();

		$this->purgeDescription();

		// Squid purging
		global $wgUseSquid;
		if ( $wgUseSquid ) {
			$urlArr = array(
				$this->getArchiveUrl( $archiveName ),
			);
			wfPurgeSquidServers( $urlArr );
		}
		return true;
	}

	/**
	 * Delete the current version of a file.
	 * May throw a database error.
	 * @return true on success, false on failure
	 */
	private function prepareDeleteCurrent( $reason, $suppress=false ) {
		return $this->prepareDeleteVersion(
			$this->getFullPath(),
			$reason,
			'image',
			array(
				'fa_name'         => 'img_name',
				'fa_archive_name' => 'NULL',
				'fa_size'         => 'img_size',
				'fa_width'        => 'img_width',
				'fa_height'       => 'img_height',
				'fa_metadata'     => 'img_metadata',
				'fa_bits'         => 'img_bits',
				'fa_media_type'   => 'img_media_type',
				'fa_major_mime'   => 'img_major_mime',
				'fa_minor_mime'   => 'img_minor_mime',
				'fa_description'  => 'img_description',
				'fa_user'         => 'img_user',
				'fa_user_text'    => 'img_user_text',
				'fa_timestamp'    => 'img_timestamp' ),
			array( 'img_name' => $this->name ),
			$suppress,
			__METHOD__ );
	}

	/**
	 * Delete a given older version of a file.
	 * May throw a database error.
	 * @return true on success, false on failure
	 */
	private function prepareDeleteOld( $archiveName, $reason, $suppress=false ) {
		$oldpath = $this->getArchivePath() .
			DIRECTORY_SEPARATOR . $archiveName;
		return $this->prepareDeleteVersion(
			$oldpath,
			$reason,
			'oldimage',
			array(
				'fa_name'         => 'oi_name',
				'fa_archive_name' => 'oi_archive_name',
				'fa_size'         => 'oi_size',
				'fa_width'        => 'oi_width',
				'fa_height'       => 'oi_height',
				'fa_metadata'     => 'NULL',
				'fa_bits'         => 'oi_bits',
				'fa_media_type'   => 'NULL',
				'fa_major_mime'   => 'NULL',
				'fa_minor_mime'   => 'NULL',
				'fa_description'  => 'oi_description',
				'fa_user'         => 'oi_user',
				'fa_user_text'    => 'oi_user_text',
				'fa_timestamp'    => 'oi_timestamp' ),
			array(
				'oi_name' => $this->name,
				'oi_archive_name' => $archiveName ),
			$suppress,
			__METHOD__ );
	}

	/**
	 * Do the dirty work of backing up an image row and its file
	 * (if $wgSaveDeletedFiles is on) and removing the originals.
	 *
	 * Must be run while the file store is locked and a database
	 * transaction is open to avoid race conditions.
	 *
	 * @return FSTransaction
	 */
	private function prepareDeleteVersion( $path, $reason, $table, $fieldMap, $where, $suppress=false, $fname ) {
		global $wgUser, $wgSaveDeletedFiles;

		// Dupe the file into the file store
		if( file_exists( $path ) ) {
			if( $wgSaveDeletedFiles ) {
				$group = 'deleted';

				$store = FileStore::get( $group );
				$key = FileStore::calculateKey( $path, $this->extension );
				$transaction = $store->insert( $key, $path,
					FileStore::DELETE_ORIGINAL );
			} else {
				$group = null;
				$key = null;
				$transaction = FileStore::deleteFile( $path );
			}
		} else {
			wfDebug( __METHOD__." deleting already-missing '$path'; moving on to database\n" );
			$group = null;
			$key = null;
			$transaction = new FSTransaction(); // empty
		}

		if( $transaction === false ) {
			// Fail to restore?
			wfDebug( __METHOD__.": import to file store failed, aborting\n" );
			throw new MWException( "Could not archive and delete file $path" );
			return false;
		}
		
		// Bitfields to further supress the file content
		// Note that currently, live files are stored elsewhere
		// and cannot be partially deleted
		$bitfield = 0;
		if ( $suppress ) {
			$bitfield |= self::DELETED_FILE;
			$bitfield |= self::DELETED_COMMENT;
			$bitfield |= self::DELETED_USER;
			$bitfield |= self::DELETED_RESTRICTED;
		}

		$dbw = $this->repo->getMasterDB();
		$storageMap = array(
			'fa_storage_group' => $dbw->addQuotes( $group ),
			'fa_storage_key'   => $dbw->addQuotes( $key ),

			'fa_deleted_user'      => $dbw->addQuotes( $wgUser->getId() ),
			'fa_deleted_timestamp' => $dbw->timestamp(),
			'fa_deleted_reason'    => $dbw->addQuotes( $reason ),
			'fa_deleted'		   => $bitfield);
		$allFields = array_merge( $storageMap, $fieldMap );

		try {
			if( $wgSaveDeletedFiles ) {
				$dbw->insertSelect( 'filearchive', $table, $allFields, $where, $fname );
			}
			$dbw->delete( $table, $where, $fname );
		} catch( DBQueryError $e ) {
			// Something went horribly wrong!
			// Leave the file as it was...
			wfDebug( __METHOD__.": database error, rolling back file transaction\n" );
			$transaction->rollback();
			throw $e;
		}

		return $transaction;
	}

	/**
	 * Restore all or specified deleted revisions to the given file.
	 * Permissions and logging are left to the caller.
	 *
	 * May throw database exceptions on error.
	 *
	 * @param $versions set of record ids of deleted items to restore,
	 *                    or empty to restore all revisions.
	 * @return the number of file revisions restored if successful,
	 *         or false on failure
	 */
	function restore( $versions=array(), $Unsuppress=false ) {
		global $wgUser;
	
		if( !FileStore::lock() ) {
			wfDebug( __METHOD__." could not acquire filestore lock\n" );
			return false;
		}

		$transaction = new FSTransaction();
		try {
			$dbw = $this->repo->getMasterDB();
			$dbw->begin();

			// Re-confirm whether this file presently exists;
			// if no we'll need to create an file record for the
			// first item we restore.
			$exists = $dbw->selectField( 'image', '1',
				array( 'img_name' => $this->name ),
				__METHOD__ );

			// Fetch all or selected archived revisions for the file,
			// sorted from the most recent to the oldest.
			$conditions = array( 'fa_name' => $this->name );
			if( $versions ) {
				$conditions['fa_id'] = $versions;
			}

			$result = $dbw->select( 'filearchive', '*',
				$conditions,
				__METHOD__,
				array( 'ORDER BY' => 'fa_timestamp DESC' ) );

			if( $dbw->numRows( $result ) < count( $versions ) ) {
				// There's some kind of conflict or confusion;
				// we can't restore everything we were asked to.
				wfDebug( __METHOD__.": couldn't find requested items\n" );
				$dbw->rollback();
				FileStore::unlock();
				return false;
			}

			if( $dbw->numRows( $result ) == 0 ) {
				// Nothing to do.
				wfDebug( __METHOD__.": nothing to do\n" );
				$dbw->rollback();
				FileStore::unlock();
				return true;
			}

			$revisions = 0;
			while( $row = $dbw->fetchObject( $result ) ) {
				if ( $Unsuppress ) {
				// Currently, fa_deleted flags fall off upon restore, lets be careful about this
				} else if ( ($row->fa_deleted & Revision::DELETED_RESTRICTED) && !$wgUser->isAllowed('hiderevision') ) {
				// Skip restoring file revisions that the user cannot restore
					continue;
				}
				$revisions++;
				$store = FileStore::get( $row->fa_storage_group );
				if( !$store ) {
					wfDebug( __METHOD__.": skipping row with no file.\n" );
					continue;
				}

				$restoredImage = new self( $row->fa_name, $this->repo );

				if( $revisions == 1 && !$exists ) {
					$destPath = $restoredImage->getFullPath();
					$destDir = dirname( $destPath );
					if ( !is_dir( $destDir ) ) {
						wfMkdirParents( $destDir );
					}

					// We may have to fill in data if this was originally
					// an archived file revision.
					if( is_null( $row->fa_metadata ) ) {
						$tempFile = $store->filePath( $row->fa_storage_key );

						$magic = MimeMagic::singleton();
						$mime = $magic->guessMimeType( $tempFile, true );
						$media_type = $magic->getMediaType( $tempFile, $mime );
						list( $major_mime, $minor_mime ) = self::splitMime( $mime );
						$handler = MediaHandler::getHandler( $mime );
						if ( $handler ) {
							$metadata = $handler->getMetadata( false, $tempFile );
						} else {
							$metadata = '';
						}
					} else {
						$metadata   = $row->fa_metadata;
						$major_mime = $row->fa_major_mime;
						$minor_mime = $row->fa_minor_mime;
						$media_type = $row->fa_media_type;
					}

					$table = 'image';
					$fields = array(
						'img_name'        => $row->fa_name,
						'img_size'        => $row->fa_size,
						'img_width'       => $row->fa_width,
						'img_height'      => $row->fa_height,
						'img_metadata'    => $metadata,
						'img_bits'        => $row->fa_bits,
						'img_media_type'  => $media_type,
						'img_major_mime'  => $major_mime,
						'img_minor_mime'  => $minor_mime,
						'img_description' => $row->fa_description,
						'img_user'        => $row->fa_user,
						'img_user_text'   => $row->fa_user_text,
						'img_timestamp'   => $row->fa_timestamp );
				} else {
					$archiveName = $row->fa_archive_name;
					if( $archiveName == '' ) {
						// This was originally a current version; we
						// have to devise a new archive name for it.
						// Format is <timestamp of archiving>!<name>
						$archiveName =
							wfTimestamp( TS_MW, $row->fa_deleted_timestamp ) .
							'!' . $row->fa_name;
					}
					$restoredImage = new self( $row->fa_name, $this->repo );
					$destDir = $restoredImage->getArchivePath();
					if ( !is_dir( $destDir ) ) {
						wfMkdirParents( $destDir );
					}
					$destPath = $destDir . DIRECTORY_SEPARATOR . $archiveName;

					$table = 'oldimage';
					$fields = array(
						'oi_name'         => $row->fa_name,
						'oi_archive_name' => $archiveName,
						'oi_size'         => $row->fa_size,
						'oi_width'        => $row->fa_width,
						'oi_height'       => $row->fa_height,
						'oi_bits'         => $row->fa_bits,
						'oi_description'  => $row->fa_description,
						'oi_user'         => $row->fa_user,
						'oi_user_text'    => $row->fa_user_text,
						'oi_timestamp'    => $row->fa_timestamp );
				}

				$dbw->insert( $table, $fields, __METHOD__ );
				// @todo this delete is not totally safe, potentially
				$dbw->delete( 'filearchive',
					array( 'fa_id' => $row->fa_id ),
					__METHOD__ );

				// Check if any other stored revisions use this file;
				// if so, we shouldn't remove the file from the deletion
				// archives so they will still work.
				$useCount = $dbw->selectField( 'filearchive',
					'COUNT(*)',
					array(
						'fa_storage_group' => $row->fa_storage_group,
						'fa_storage_key'   => $row->fa_storage_key ),
					__METHOD__ );
				if( $useCount == 0 ) {
					wfDebug( __METHOD__.": nothing else using {$row->fa_storage_key}, will deleting after\n" );
					$flags = FileStore::DELETE_ORIGINAL;
				} else {
					$flags = 0;
				}

				$transaction->add( $store->export( $row->fa_storage_key,
					$destPath, $flags ) );
			}

			$dbw->immediateCommit();
		} catch( MWException $e ) {
			wfDebug( __METHOD__." caught error, aborting\n" );
			$transaction->rollback();
			throw $e;
		}

		$transaction->commit();
		FileStore::unlock();

		if( $revisions > 0 ) {
			if( !$exists ) {
				wfDebug( __METHOD__." restored $revisions items, creating a new current\n" );

				// Update site_stats
				$site_stats = $dbw->tableName( 'site_stats' );
				$dbw->query( "UPDATE $site_stats SET ss_images=ss_images+1", __METHOD__ );

				$this->purgeEverything();
			} else {
				wfDebug( __METHOD__." restored $revisions as archived versions\n" );
				$this->purgeDescription();
			}
		}

		return $revisions;
	}

	/** isMultipage inherited */
	/** pageCount inherited */
	/** scaleHeight inherited */
	/** getImageSize inherited */
} //class

/**
 * Backwards compatibility class
 */
class Image extends LocalFile {
	function __construct( $title ) {
		$repo = FileRepoGroup::singleton()->getRepo( 0 );
		parent::__construct( $title, $repo );
	}
}

/**
 * Aliases for backwards compatibility with 1.6
 */
define( 'MW_IMG_DELETED_FILE', File::DELETED_FILE );
define( 'MW_IMG_DELETED_COMMENT', File::DELETED_COMMENT );
define( 'MW_IMG_DELETED_USER', File::DELETED_USER );
define( 'MW_IMG_DELETED_RESTRICTED', File::DELETED_RESTRICTED );

?>
