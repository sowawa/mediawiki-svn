<?php
/**
 * Local file in the wiki's own database, only stored in Swift
 *
 * @file
 * @ingroup FileRepo
 */

/**
 * Class to represent a local file in the wiki's own database, only stored in Swift
 *
 * Provides methods to retrieve paths (physical, logical, URL),
 * to generate image thumbnails or for uploading.
 *
 * Note that only the repo object knows what its file class is called. You should
 * never name a file class explictly outside of the repo class. Instead use the
 * repo's factory functions to generate file objects, for example:
 *
 * RepoGroup::singleton()->getLocalRepo()->newFile($title);
 *
 * The convenience functions wfLocalFile() and wfFindFile() should be sufficient
 * in most cases.
 *
 * @ingroup FileRepo
 */
class SwiftFile extends LocalFile {
	/**#@+
	 * @private
	 */
	var
		$conn;             # our connection to the Swift proxy.
		#$fileExists,       # does the file file exist on disk? (loadFromXxx)
		#$historyLine,      # Number of line to return by nextHistoryLine() (constructor)
		#$historyRes,       # result of the query for the file's history (nextHistoryLine)
		#$width,            # \
		#$height,           #  |
		#$bits,             #   --- returned by getimagesize (loadFromXxx)
		#$attr,             # /
		#$media_type,       # MEDIATYPE_xxx (bitmap, drawing, audio...)
		#$mime,             # MIME type, determined by MimeMagic::guessMimeType
		#$major_mime,       # Major mime type
		#$minor_mime,       # Minor mime type
		#$size,             # Size in bytes (loadFromXxx)
		#$metadata,         # Handler-specific metadata
		#$timestamp,        # Upload timestamp
		#$sha1,             # SHA-1 base 36 content hash
		#$user, $user_text, # User, who uploaded the file
		#$description,      # Description of current revision of the file
		#$dataLoaded,       # Whether or not all this has been loaded from the database (loadFromXxx)
		#$upgraded,         # Whether the row was upgraded on load
		#$locked,           # True if the image row is locked
		#$missing,          # True if file is not present in file system. Not to be cached in memcached
		#$deleted;          # Bitfield akin to rev_deleted
	/**#@-*/

	/**
	 * Create a LocalFile from a title
	 * Do not call this except from inside a repo class.
	 *
	 * Note: $unused param is only here to avoid an E_STRICT
	 */
	static function newFromTitle( $title, $repo, $unused = null ) {
		if ( empty($title) ) { return null; }
		return new self( $title, $repo );
	}

	/**
	 * Create a LocalFile from a title
	 * Do not call this except from inside a repo class.
	 */
	static function newFromRow( $row, $repo ) {
		$title = Title::makeTitle( NS_FILE, $row->img_name );
		$file = new self( $title, $repo );
		$file->loadFromRow( $row );

		return $file;
	}

	/**
	 * Constructor.
	 * Do not call this except from inside a repo class.
	 */
	function __construct( $title, $repo ) {
		if ( !is_object( $title ) ) {
			throw new MWException( __CLASS__ . " constructor given bogus title." );
		}

		parent::__construct( $title, $repo );

		$this->metadata = '';
		$this->historyLine = 0;
		$this->historyRes = null;
		$this->dataLoaded = false;
	}

	/**
	 * Get the memcached key for the main data for this file, or false if
	 * there is no access to the shared cache.
	 */
	function getCacheKey() {
		$hashedName = md5( $this->getName() );

		return $this->repo->getSharedCacheKey( 'file', $hashedName );
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
			wfProfileOut( __METHOD__ );
			return false;
		}

		$cachedValues = $wgMemc->get( $key );

		// Check if the key existed and belongs to this version of MediaWiki
		if ( isset( $cachedValues['version'] ) && ( $cachedValues['version'] == MW_FILE_VERSION ) ) {
			wfDebug( "Pulling file metadata from cache key $key\n" );
			$this->fileExists = $cachedValues['fileExists'];
			if ( $this->fileExists ) {
				$this->setProps( $cachedValues );
			}
			$this->dataLoaded = true;
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

		$fields = $this->getCacheFields( '' );
		$cache = array( 'version' => MW_FILE_VERSION );
		$cache['fileExists'] = $this->fileExists;

		if ( $this->fileExists ) {
			foreach ( $fields as $field ) {
				$cache[$field] = $this->$field;
			}
		}

		$wgMemc->set( $key, $cache, 60 * 60 * 24 * 7 ); // A week
	}

	/**
	 * Load metadata from the file itself
	 */
	function loadFromFile() {
		wfDebug( __METHOD__ . $this->getPath() . "\n" );
		$this->setProps( self::getPropsFromPath( $this->getPath() ) );
	}

	function getCacheFields( $prefix = 'img_' ) {
		static $fields = array( 'size', 'width', 'height', 'bits', 'media_type',
			'major_mime', 'minor_mime', 'metadata', 'timestamp', 'sha1', 'user', 'user_text', 'description' );
		static $results = array();

		if ( $prefix == '' ) {
			return $fields;
		}

		if ( !isset( $results[$prefix] ) ) {
			$prefixedFields = array();
			foreach ( $fields as $field ) {
				$prefixedFields[] = $prefix . $field;
			}
			$results[$prefix] = $prefixedFields;
		}

		return $results[$prefix];
	}

	/**
	 * Load file metadata from the DB
	 */
	function loadFromDB() {
		# Polymorphic function name to distinguish foreign and local fetches
		$fname = get_class( $this ) . '::' . __FUNCTION__;
		wfProfileIn( $fname );

		# Unconditionally set loaded=true, we don't want the accessors constantly rechecking
		$this->dataLoaded = true;

		$dbr = $this->repo->getMasterDB();

		$row = $dbr->selectRow( 'image', $this->getCacheFields( 'img_' ),
			array( 'img_name' => $this->getName() ), $fname );

		if ( $row ) {
			$this->loadFromRow( $row );
		} else {
			$this->fileExists = false;
		}

		wfProfileOut( $fname );
	}

	/**
	 * Decode a row from the database (either object or array) to an array
	 * with timestamps and MIME types decoded, and the field prefix removed.
	 */
	function decodeRow( $row, $prefix = 'img_' ) {
		$array = (array)$row;
		$prefixLength = strlen( $prefix );

		// Sanity check prefix once
		if ( substr( key( $array ), 0, $prefixLength ) !== $prefix ) {
			throw new MWException( __METHOD__ .  ": incorrect $prefix parameter" );
		}

		$decoded = array();

		foreach ( $array as $name => $value ) {
			$decoded[substr( $name, $prefixLength )] = $value;
		}

		$decoded['timestamp'] = wfTimestamp( TS_MW, $decoded['timestamp'] );

		if ( empty( $decoded['major_mime'] ) ) {
			$decoded['mime'] = 'unknown/unknown';
		} else {
			if ( !$decoded['minor_mime'] ) {
				$decoded['minor_mime'] = 'unknown';
			}
			$decoded['mime'] = $decoded['major_mime'] . '/' . $decoded['minor_mime'];
		}

		# Trim zero padding from char/binary field
		$decoded['sha1'] = rtrim( $decoded['sha1'], "\0" );

		return $decoded;
	}

	/**
	 * Load file metadata from a DB result row
	 */
	function loadFromRow( $row, $prefix = 'img_' ) {
		$this->dataLoaded = true;
		$array = $this->decodeRow( $row, $prefix );

		foreach ( $array as $name => $value ) {
			$this->$name = $value;
		}

		$this->fileExists = true;
		$this->maybeUpgradeRow();
	}

	/**
	 * Load file metadata from cache or DB, unless already loaded
	 */
	function load() {
		if ( !$this->dataLoaded ) {
			if ( !$this->loadFromCache() ) {
				$this->loadFromDB();
				$this->saveToCache();
			}
			$this->dataLoaded = true;
		}
	}

	/**
	 * Upgrade a row if it needs it
	 */
	function maybeUpgradeRow() {
		if ( wfReadOnly() ) {
			return;
		}

		if ( is_null( $this->media_type ) ||
			$this->mime == 'image/svg'
		) {
			$this->upgradeRow();
			$this->upgraded = true;
		} else {
			$handler = $this->getHandler();
			if ( $handler && !$handler->isMetadataValid( $this, $this->metadata ) ) {
				$this->upgradeRow();
				$this->upgraded = true;
			}
		}
	}

	function getUpgraded() {
		return $this->upgraded;
	}

	/**
	 * Fix assorted version-related problems with the image row by reloading it from the file
	 */
	function upgradeRow() {
		wfProfileIn( __METHOD__ );

		$this->loadFromFile();

		# Don't destroy file info of missing files
		if ( !$this->fileExists ) {
			wfDebug( __METHOD__ . ": file does not exist, aborting\n" );
			wfProfileOut( __METHOD__ );
			return;
		}

		$dbw = $this->repo->getMasterDB();
		list( $major, $minor ) = self::splitMime( $this->mime );

		if ( wfReadOnly() ) {
			wfProfileOut( __METHOD__ );
			return;
		}
		wfDebug( __METHOD__ . ': upgrading ' . $this->getName() . " to the current schema\n" );

		$dbw->update( 'image',
			array(
				'img_width' => $this->width,
				'img_height' => $this->height,
				'img_bits' => $this->bits,
				'img_media_type' => $this->media_type,
				'img_major_mime' => $major,
				'img_minor_mime' => $minor,
				'img_metadata' => $this->metadata,
				'img_sha1' => $this->sha1,
			), array( 'img_name' => $this->getName() ),
			__METHOD__
		);

		$this->saveToCache();
		wfProfileOut( __METHOD__ );
	}

	/**
	 * Set properties in this object to be equal to those given in the
	 * associative array $info. Only cacheable fields can be set.
	 *
	 * If 'mime' is given, it will be split into major_mime/minor_mime.
	 * If major_mime/minor_mime are given, $this->mime will also be set.
	 */
	function setProps( $info ) {
		$this->dataLoaded = true;
		$fields = $this->getCacheFields( '' );
		$fields[] = 'fileExists';

		foreach ( $fields as $field ) {
			if ( isset( $info[$field] ) ) {
				$this->$field = $info[$field];
			}
		}

		// Fix up mime fields
		if ( isset( $info['major_mime'] ) ) {
			$this->mime = "{$info['major_mime']}/{$info['minor_mime']}";
		} elseif ( isset( $info['mime'] ) ) {
			$this->mime = $info['mime'];
			list( $this->major_mime, $this->minor_mime ) = self::splitMime( $this->mime );
		}
	}

	/** splitMime inherited */
	/** getName inherited */
	/** getTitle inherited */
	/** getURL inherited */
	/** getViewURL inherited */
	/** isVisible inherited */

	function getPath() {
		return $this->getLocalCopy($this->repo->container, $this->getRel());
	}

	/** Get the path of the archive directory, or a particular file if $suffix is specified */
	function getArchivePath( $suffix = false ) {
		return $this->getLocalCopy($this->repo->getZoneContainer('public'), $this->getArchiveRel( $suffix ));
	}

	/** Get the path of the thumbnail directory, or a particular file if $suffix is specified */
	function getThumbPath( $suffix = false ) {
		$path = $this->getRel();
		if ( $suffix !== false ) {
			$path .= '/' . $suffix;
		}
		return $this->getLocalCopy($this->repo->getZoneContainer('thumb'), $path);
	}

	/** Given a container and relative path, return an absolute path pointing at a copy of the file */
	function getLocalCopy($container, $rel) {
		// if we already have a local copy, return it.
		if ($this->temp_path) { return $this->temp_path; }

		// get a temporary place to put the original.
		$this->temp_path = tempnam( wfTempDir(), 'swift_in_' );

		/* Fetch the image out of Swift */
		$conn = $this->repo->connect();
		$cont = $this->repo->get_container($conn,$container);

		try {
			$obj = $cont->get_object($rel);
		} catch (NoSuchObjectException $e) {
			throw new MWException( "Unable to open original file at $container/$rel");
		}

		wfDebug(  __METHOD__ . " writing to " . $this->temp_path . "\n");
		try {
			$obj->save_to_filename( $this->temp_path);
		} catch (IOException $e) {
			throw new MWException( __METHOD__ . ": error opening '$e'" );
		} catch (InvalidResponseException $e) {
			throw new MWException( __METHOD__ . "unexpected response '$e'" );
		}

		return $this->temp_path;
	}

	function __destruct() {
		if ($this->temp_path) {
			// Clean up temporary data.
			unlink($this->temp_path);
			$this->temp_path = null;
		}
	}

	function isMissing() {
		if ( $this->missing === null ) {
			list( $fileExists ) = $this->repo->fileExistsBatch( array( $this->getVirtualUrl() ), FileRepo::FILES_ONLY );
			$this->missing = !$fileExists;
		}
		return $this->missing;
	}

	/**
	 * Return the width of the image
	 *
	 * Returns false on error
	 */
	public function getWidth( $page = 1 ) {
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
	 */
	public function getHeight( $page = 1 ) {
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
	 * Returns ID or name of user who uploaded the file
	 *
	 * @param $type string 'text' or 'id'
	 */
	function getUser( $type = 'text' ) {
		$this->load();

		if ( $type == 'text' ) {
			return $this->user_text;
		} elseif ( $type == 'id' ) {
			return $this->user;
		}
	}

	/**
	 * Get handler-specific metadata
	 */
	function getMetadata() {
		$this->load();
		return $this->metadata;
	}

	function getBitDepth() {
		$this->load();
		return $this->bits;
	}

	/**
	 * Return the size of the image file, in bytes
	 */
	public function getSize() {
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
		return $this->media_type;
	}

	/** canRender inherited */
	/** mustRender inherited */
	/** allowInlineDisplay inherited */
	/** isSafeFile inherited */
	/** isTrustedFile inherited */

	/**
	 * Returns true if the file file exists on disk.
	 * @return boolean Whether file file exist on disk.
	 */
	public function exists() {
		$this->load();
		return $this->fileExists;
	}

	/** getTransformScript inherited */
	/** getUnscaledThumb inherited */
	/** thumbName inherited */
	/** createThumb inherited */
	/** getThumbnail inherited */

	/**
	 * Transform a media file
	 *
	 * @param $params Array: an associative array of handler-specific parameters.
	 *                Typical keys are width, height and page.
	 * @param $flags Integer: a bitfield, may contain self::RENDER_NOW to force rendering
	 * @return MediaTransformOutput | false
	 */
	function transform( $params, $flags = 0 ) {
		global $wgUseSquid, $wgIgnoreImageErrors, $wgThumbnailEpoch, $wgServer;
		global $wgTmpDirectory;

		wfProfileIn( __METHOD__ );
		do {
			if ( !$this->canRender() ) {
				// not a bitmap or renderable image, don't try.
				$thumb = $this->iconThumb();
				break;
			}

			// Get the descriptionUrl to embed it as comment into the thumbnail. Bug 19791.
			$descriptionUrl =  $this->getDescriptionUrl();
			if ( $descriptionUrl ) {
				$params['descriptionUrl'] = $wgServer . $descriptionUrl;
			}

			// make the thumb name and URL out of the normalized parameters.
			// we only use the thumbTemp for a temporary file.
			$normalisedParams = $params;
			$this->handler->normaliseParams( $this, $normalisedParams );
			$thumbName = $this->thumbName( $normalisedParams );
			$thumbUrl = $this->getThumbUrl( $thumbName );

			// get a temporary place to put the original.
			$thumbTemp = tempnam( $wgTmpDirectory, 'transform_out_' );

			$thumb = $this->handler->doTransform( $this, $thumbTemp, $thumbUrl, $params );

			// Store the thumbnail into Swift, but in the thumb version of the container.
			wfDebug(  __METHOD__ . "Creating thumb " . $this->getRel() . "/" . $thumbName . "\n");
			$conn = $this->repo->connect();
			$container = $this->repo->get_container($conn,$this->repo->container . "%2Fthumb");
			$this->repo->write_swift_object( $thumbTemp, $container, $this->getRel() . "/" . $thumbName);
			// php-cloudfiles throws exceptions, so failure never gets here.
			
			// Clean up temporary data.
			unlink($thumbTemp);

		} while (false);

		wfProfileOut( __METHOD__ );
		return is_object( $thumb ) ? $thumb : false;
	}


	/**
	 * Fix thumbnail files from 1.4 or before, with extreme prejudice
	 * Upgrading directly from 1.4 to 1.8/SwiftMedia is not supported.
	 */
	function migrateThumbFile( $thumbName ) {
		throw new MWException( __METHOD__.": not implemented" );
	}
	/**
	 * Get the public root directory of the repository.
	 */
	function getRootDirectory() {
		throw new MWException( __METHOD__.": not implemented" );
	}


	/** getHandler inherited */
	/** iconThumb inherited */
	/** getLastError inherited */

	/**
	 * Get all thumbnail names previously generated for this file
	 */
	function getThumbnails() {
		$this->load();

		$prefix = $this->getRel();
		$conn = $this->repo->connect();
		$container = $this->repo->get_container($conn,$this->repo->container . "%2Fthumb");
		$files = $container->list_objects(0, NULL, $prefix);
		return $files;
	}

	/**
	 * Refresh metadata in memcached, but don't touch thumbnails or squid
	 */
	function purgeMetadataCache() {
		$this->loadFromDB();
		$this->saveToCache();
		$this->purgeHistory();
	}

	/**
	 * Purge the shared history (OldLocalFile) cache
	 */
	function purgeHistory() {
		global $wgMemc;

		$hashedName = md5( $this->getName() );
		$oldKey = $this->repo->getSharedCacheKey( 'oldfile', $hashedName );

		if ( $oldKey ) {
			$wgMemc->delete( $oldKey );
		}
	}

	/**
	 * Delete all previously generated thumbnails, refresh metadata in memcached and purge the squid
	 */
	function purgeCache() {
		// Refresh metadata cache
		$this->purgeMetadataCache();

		// Delete thumbnails
		$this->purgeThumbnails();

		// Purge squid cache for this file
		SquidUpdate::purge( array( $this->getURL() ) );
	}

	/**
	 * Delete cached transformed files
	 */
	function purgeThumbnails() {
		global $wgUseSquid, $wgExcludeFromThumbnailPurge;

		// Delete thumbnails
		$files = $this->getThumbnails();
		$urls = array();

		$conn = $this->repo->connect();
		$container = $this->repo->get_container($conn,$this->repo->container . "%2Fthumb");
		foreach ( $files as $file ) {
			// I have no idea how to implement this given that we don't have paths in Swift
			// Only remove files not in the $wgExcludeFromThumbnailPurge configuration variable
			// $ext = pathinfo( "$dir/$file", PATHINFO_EXTENSION );
			//if ( in_array( $ext, $wgExcludeFromThumbnailPurge ) ) {
			//	continue;
			//}
			
			$urls[] = $this->getThumbUrl($file);
			$this->repo->swift_delete($container, $file);
		}

		// Purge the squid
		if ( $wgUseSquid ) {
			SquidUpdate::purge( $urls );
		}
	}

	/** purgeDescription inherited */
	/** purgeEverything inherited */

	function getHistory( $limit = null, $start = null, $end = null, $inc = true ) {
		$dbr = $this->repo->getSlaveDB();
		$tables = array( 'oldimage' );
		$fields = OldSwiftFile::selectFields();
		$conds = $opts = $join_conds = array();
		$eq = $inc ? '=' : '';
		$conds[] = "oi_name = " . $dbr->addQuotes( $this->title->getDBkey() );

		if ( $start ) {
			$conds[] = "oi_timestamp <$eq " . $dbr->addQuotes( $dbr->timestamp( $start ) );
		}

		if ( $end ) {
			$conds[] = "oi_timestamp >$eq " . $dbr->addQuotes( $dbr->timestamp( $end ) );
		}

		if ( $limit ) {
			$opts['LIMIT'] = $limit;
		}

		// Search backwards for time > x queries
		$order = ( !$start && $end !== null ) ? 'ASC' : 'DESC';
		$opts['ORDER BY'] = "oi_timestamp $order";
		$opts['USE INDEX'] = array( 'oldimage' => 'oi_name_timestamp' );

		wfRunHooks( 'SwiftFile::getHistory', array( &$this, &$tables, &$fields,
			&$conds, &$opts, &$join_conds ) );

		$res = $dbr->select( $tables, $fields, $conds, __METHOD__, $opts, $join_conds );
		$r = array();

		foreach ( $res as $row ) {
			if ( $this->repo->oldFileFromRowFactory ) {
				$r[] = call_user_func( $this->repo->oldFileFromRowFactory, $row, $this->repo );
			} else {
				$r[] = OldSwiftFile::newFromRow( $row, $this->repo );
			}
		}

		if ( $order == 'ASC' ) {
			$r = array_reverse( $r ); // make sure it ends up descending
		}

		return $r;
	}

	/**
	 * Return the history of this file, line by line.
	 * starts with current version, then old versions.
	 * uses $this->historyLine to check which line to return:
	 *  0      return line for current version
	 *  1      query for old versions, return first one
	 *  2, ... return next old version from above query
	 */
	public function nextHistoryLine() {
		# Polymorphic function name to distinguish foreign and local fetches
		$fname = get_class( $this ) . '::' . __FUNCTION__;

		$dbr = $this->repo->getSlaveDB();

		if ( $this->historyLine == 0 ) {// called for the first time, return line from cur
			$this->historyRes = $dbr->select( 'image',
				array(
					'*',
					"'' AS oi_archive_name",
					'0 as oi_deleted',
					'img_sha1'
				),
				array( 'img_name' => $this->title->getDBkey() ),
				$fname
			);

			if ( 0 == $dbr->numRows( $this->historyRes ) ) {
				$this->historyRes = null;
				return false;
			}
		} elseif ( $this->historyLine == 1 ) {
			$this->historyRes = $dbr->select( 'oldimage', '*',
				array( 'oi_name' => $this->title->getDBkey() ),
				$fname,
				array( 'ORDER BY' => 'oi_timestamp DESC' )
			);
		}
		$this->historyLine ++;

		return $dbr->fetchObject( $this->historyRes );
	}

	/**
	 * Reset the history pointer to the first element of the history
	 */
	public function resetHistory() {
		$this->historyLine = 0;

		if ( !is_null( $this->historyRes ) ) {
			$this->historyRes = null;
		}
	}

	/** getFullPath inherited */
	/** getHashPath inherited */
	/** getRel inherited */
	/** getUrlRel inherited */
	/** getArchiveRel inherited */
	/** getArchiveUrl inherited */
	/** getThumbUrl inherited */
	/** getArchiveVirtualUrl inherited */
	/** getThumbVirtualUrl inherited */
	/** isHashed inherited */

	/**
	 * Upload a file and record it in the DB
	 * @param $srcPath String: source path or virtual URL
	 * @param $comment String: upload description
	 * @param $pageText String: text to use for the new description page,
	 *                  if a new description page is created
	 * @param $flags Integer: flags for publish()
	 * @param $props Array: File properties, if known. This can be used to reduce the
	 *               upload time when uploading virtual URLs for which the file info
	 *               is already known
	 * @param $timestamp String: timestamp for img_timestamp, or false to use the current time
	 * @param $user Mixed: User object or null to use $wgUser
	 *
	 * @return FileRepoStatus object. On success, the value member contains the
	 *     archive name, or an empty string if it was a new file.
	 */
	function upload( $srcPath, $comment, $pageText, $flags = 0, $props = false, $timestamp = false, $user = null ) {
		$this->lock();
		$status = $this->publish( $srcPath, $flags );

		if ( $status->ok ) {
			if ( !$this->recordUpload2( $status->value, $comment, $pageText, $props, $timestamp, $user ) ) {
				$status->fatal( 'filenotfound', $srcPath );
			}
		}

		$this->unlock();

		return $status;
	}

	/**
	 * Record a file upload in the upload log and the image table
	 * @deprecated use upload()
	 */
	function recordUpload( $oldver, $desc, $license = '', $copyStatus = '', $source = '',
		$watch = false, $timestamp = false )
	{
		$pageText = SpecialUpload::getInitialPageText( $desc, $license, $copyStatus, $source );

		if ( !$this->recordUpload2( $oldver, $desc, $pageText ) ) {
			return false;
		}

		if ( $watch ) {
			global $wgUser;
			$wgUser->addWatch( $this->getTitle() );
		}
		return true;

	}

	/**
	 * Record a file upload in the upload log and the image table
	 */
	function recordUpload2( $oldver, $comment, $pageText, $props = false, $timestamp = false, $user = null )
	{
		if ( is_null( $user ) ) {
			global $wgUser;
			$user = $wgUser;
		}

		$dbw = $this->repo->getMasterDB();
		$dbw->begin();

		if ( !$props ) {
			$props = $this->repo->getFileProps( $this->getVirtualUrl() );
		}

		$props['description'] = $comment;
		$props['user'] = $user->getId();
		$props['user_text'] = $user->getName();
		$props['timestamp'] = wfTimestamp( TS_MW );
		$this->setProps( $props );

		# Delete thumbnails
		$this->purgeThumbnails();

		# The file is already on its final location, remove it from the squid cache
		SquidUpdate::purge( array( $this->getURL() ) );

		# Fail now if the file isn't there
		if ( !$this->fileExists ) {
			wfDebug( __METHOD__ . ": File " . $this->getRel() . " went missing!\n" );
			return false;
		}

		$reupload = false;

		if ( $timestamp === false ) {
			$timestamp = $dbw->timestamp();
		}

		# Test to see if the row exists using INSERT IGNORE
		# This avoids race conditions by locking the row until the commit, and also
		# doesn't deadlock. SELECT FOR UPDATE causes a deadlock for every race condition.
		$dbw->insert( 'image',
			array(
				'img_name' => $this->getName(),
				'img_size' => $this->size,
				'img_width' => intval( $this->width ),
				'img_height' => intval( $this->height ),
				'img_bits' => $this->bits,
				'img_media_type' => $this->media_type,
				'img_major_mime' => $this->major_mime,
				'img_minor_mime' => $this->minor_mime,
				'img_timestamp' => $timestamp,
				'img_description' => $comment,
				'img_user' => $user->getId(),
				'img_user_text' => $user->getName(),
				'img_metadata' => $this->metadata,
				'img_sha1' => $this->sha1
			),
			__METHOD__,
			'IGNORE'
		);

		if ( $dbw->affectedRows() == 0 ) {
			$reupload = true;

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
					'oi_metadata' => 'img_metadata',
					'oi_media_type' => 'img_media_type',
					'oi_major_mime' => 'img_major_mime',
					'oi_minor_mime' => 'img_minor_mime',
					'oi_sha1' => 'img_sha1'
				), array( 'img_name' => $this->getName() ), __METHOD__
			);

			# Update the current image row
			$dbw->update( 'image',
				array( /* SET */
					'img_size' => $this->size,
					'img_width' => intval( $this->width ),
					'img_height' => intval( $this->height ),
					'img_bits' => $this->bits,
					'img_media_type' => $this->media_type,
					'img_major_mime' => $this->major_mime,
					'img_minor_mime' => $this->minor_mime,
					'img_timestamp' => $timestamp,
					'img_description' => $comment,
					'img_user' => $user->getId(),
					'img_user_text' => $user->getName(),
					'img_metadata' => $this->metadata,
					'img_sha1' => $this->sha1
				), array( /* WHERE */
					'img_name' => $this->getName()
				), __METHOD__
			);
		} else {
			# This is a new file
			# Update the image count
			$site_stats = $dbw->tableName( 'site_stats' );
			$dbw->query( "UPDATE $site_stats SET ss_images=ss_images+1", __METHOD__ );
		}

		$descTitle = $this->getTitle();
		$article = new ImagePage( $descTitle );
		$article->setFile( $this );

		# Add the log entry
		$log = new LogPage( 'upload' );
		$action = $reupload ? 'overwrite' : 'upload';
		$log->addEntry( $action, $descTitle, $comment, array(), $user );

		if ( $descTitle->exists() ) {
			# Create a null revision
			$latest = $descTitle->getLatestRevID();
			$nullRevision = Revision::newNullRevision(
				$dbw,
				$descTitle->getArticleId(),
				$log->getRcComment(),
				false
			);
			$nullRevision->insertOn( $dbw );

			wfRunHooks( 'NewRevisionFromEditComplete', array( $article, $nullRevision, $latest, $user ) );
			$article->updateRevisionOn( $dbw, $nullRevision );

			# Invalidate the cache for the description page
			$descTitle->invalidateCache();
			$descTitle->purgeSquid();
		} else {
			# New file; create the description page.
			# There's already a log entry, so don't make a second RC entry
			# Squid and file cache for the description page are purged by doEdit.
			$article->doEdit( $pageText, $comment, EDIT_NEW | EDIT_SUPPRESS_RC );
		}

		# Commit the transaction now, in case something goes wrong later
		# The most important thing is that files don't get lost, especially archives
		$dbw->commit();

		# Save to cache and purge the squid
		# We shall not saveToCache before the commit since otherwise
		# in case of a rollback there is an usable file from memcached
		# which in fact doesn't really exist (bug 24978)
		$this->saveToCache();

		# Hooks, hooks, the magic of hooks...
		wfRunHooks( 'FileUpload', array( $this, $reupload, $descTitle->exists() ) );

		# Invalidate cache for all pages using this file
		$update = new HTMLCacheUpdate( $this->getTitle(), 'imagelinks' );
		$update->doUpdate();

		# Invalidate cache for all pages that redirects on this page
		$redirs = $this->getTitle()->getRedirectsHere();

		foreach ( $redirs as $redir ) {
			$update = new HTMLCacheUpdate( $redir, 'imagelinks' );
			$update->doUpdate();
		}

		return true;
	}

	/**
	 * Move or copy a file to its public location. If a file exists at the
	 * destination, move it to an archive. Returns a FileRepoStatus object with
	 * the archive name in the "value" member on success.
	 *
	 * The archive name should be passed through to recordUpload for database
	 * registration.
	 *
	 * @param $srcPath String: local filesystem path to the source image
	 * @param $flags Integer: a bitwise combination of:
	 *     File::DELETE_SOURCE    Delete the source file, i.e. move
	 *         rather than copy
	 * @return FileRepoStatus object. On success, the value member contains the
	 *     archive name, or an empty string if it was a new file.
	 */
	function publish( $srcPath, $flags = 0 ) {
		$this->lock();

		$dstRel = $this->getRel();
		$archiveName = gmdate( 'YmdHis' ) . '!' . $this->getName();
		$archiveRel = 'archive/' . $this->getHashPath() . $archiveName;
		$flags = $flags & File::DELETE_SOURCE ? LocalRepo::DELETE_SOURCE : 0;
		$status = $this->repo->publish( $srcPath, $dstRel, $archiveRel, $flags );

		if ( $status->value == 'new' ) {
			$status->value = '';
		} else {
			$status->value = $archiveName;
		}

		$this->unlock();

		return $status;
	}

	/** getLinksTo inherited */
	/** getExifData inherited */
	/** isLocal inherited */
	/** wasDeleted inherited */

	/**
	 * Move file to the new title
	 *
	 * Move current, old version and all thumbnails
	 * to the new filename. Old file is deleted.
	 *
	 * Cache purging is done; checks for validity
	 * and logging are caller's responsibility
	 *
	 * @param $target Title New file name
	 * @return FileRepoStatus object.
	 */
	function move( $target ) {
		wfDebugLog( 'imagemove', "Got request to move {$this->name} to " . $target->getText() );
		$this->lock();

		$batch = new SwiftFileMoveBatch( $this, $target );
		$batch->addCurrent();
		$batch->addOlds();

		$status = $batch->execute();
		wfDebugLog( 'imagemove', "Finished moving {$this->name}" );

		$this->purgeEverything();
		$this->unlock();

		if ( $status->isOk() ) {
			// Now switch the object
			$this->title = $target;
			// Force regeneration of the name and hashpath
			unset( $this->name );
			unset( $this->hashPath );
			// Purge the new image
			$this->purgeEverything();
		}

		return $status;
	}

	/**
	 * Delete all versions of the file.
	 *
	 * Moves the files into an archive directory (or deletes them)
	 * and removes the database rows.
	 *
	 * Cache purging is done; logging is caller's responsibility.
	 *
	 * @param $reason
	 * @param $suppress
	 * @return FileRepoStatus object.
	 */
	function delete( $reason, $suppress = false ) {
		$this->lock();

		$batch = new SwiftFileDeleteBatch( $this, $reason, $suppress );
		$batch->addCurrent();

		# Get old version relative paths
		$dbw = $this->repo->getMasterDB();
		$result = $dbw->select( 'oldimage',
			array( 'oi_archive_name' ),
			array( 'oi_name' => $this->getName() ) );
		foreach ( $result as $row ) {
			$batch->addOld( $row->oi_archive_name );
		}
		//wfDebug(__METHOD__ . " deleting these files: " . var_export($batch, true) . "\n"); 
		$status = $batch->execute();

		if ( $status->ok ) {
			// Update site_stats
			$site_stats = $dbw->tableName( 'site_stats' );
			$dbw->query( "UPDATE $site_stats SET ss_images=ss_images-1", __METHOD__ );
			$this->purgeEverything();
		}

		$this->unlock();

		return $status;
	}

	/**
	 * Delete an old version of the file.
	 *
	 * Moves the file into an archive directory (or deletes it)
	 * and removes the database row.
	 *
	 * Cache purging is done; logging is caller's responsibility.
	 *
	 * @param $archiveName String
	 * @param $reason String
	 * @param $suppress Boolean
	 * @throws MWException or FSException on database or file store failure
	 * @return FileRepoStatus object.
	 */
	function deleteOld( $archiveName, $reason, $suppress = false ) {
		$this->lock();

		$batch = new SwiftFileDeleteBatch( $this, $reason, $suppress );
		$batch->addOld( $archiveName );
		$status = $batch->execute();

		$this->unlock();

		if ( $status->ok ) {
			$this->purgeDescription();
			$this->purgeHistory();
		}

		return $status;
	}

	/**
	 * Restore all or specified deleted revisions to the given file.
	 * Permissions and logging are left to the caller.
	 *
	 * May throw database exceptions on error.
	 *
	 * @param $versions set of record ids of deleted items to restore,
	 *                    or empty to restore all revisions.
	 * @param $unsuppress Boolean
	 * @return FileRepoStatus
	 */
	function restore( $versions = array(), $unsuppress = false ) {
		$batch = new SwiftFileRestoreBatch( $this, $unsuppress );

		if ( !$versions ) {
			$batch->addAll();
		} else {
			$batch->addIds( $versions );
		}

		$status = $batch->execute();

		if ( !$status->isGood() ) {
			return $status;
		}

		$cleanupStatus = $batch->cleanup();
		$cleanupStatus->successCount = 0;
		$cleanupStatus->failCount = 0;
		$status->merge( $cleanupStatus );

		return $status;
	}

	/** isMultipage inherited */
	/** pageCount inherited */
	/** scaleHeight inherited */
	/** getImageSize inherited */

	/**
	 * Get the URL of the file description page.
	 */
	function getDescriptionUrl() {
		return $this->title->getLocalUrl();
	}

	/**
	 * Get the HTML text of the description page
	 * This is not used by ImagePage for local files, since (among other things)
	 * it skips the parser cache.
	 */
	function getDescriptionText() {
		global $wgParser;
		$revision = Revision::newFromTitle( $this->title );
		if ( !$revision ) return false;
		$text = $revision->getText();
		if ( !$text ) return false;
		$pout = $wgParser->parse( $text, $this->title, new ParserOptions() );
		return $pout->getText();
	}

	function getDescription() {
		$this->load();
		return $this->description;
	}

	function getTimestamp() {
		$this->load();
		return $this->timestamp;
	}

	function getSha1() {
		$this->load();
		// Initialise now if necessary
		if ( $this->sha1 == '' && $this->fileExists ) {
			$this->sha1 = File::sha1Base36( $this->getPath() );
			if ( !wfReadOnly() && strval( $this->sha1 ) != '' ) {
				$dbw = $this->repo->getMasterDB();
				$dbw->update( 'image',
					array( 'img_sha1' => $this->sha1 ),
					array( 'img_name' => $this->getName() ),
					__METHOD__ );
				$this->saveToCache();
			}
		}

		return $this->sha1;
	}

	/**
	 * Start a transaction and lock the image for update
	 * Increments a reference counter if the lock is already held
	 * @return boolean True if the image exists, false otherwise
	 */
	function lock() {
		$dbw = $this->repo->getMasterDB();

		if ( !$this->locked ) {
			$dbw->begin();
			$this->locked++;
		}

		return $dbw->selectField( 'image', '1', array( 'img_name' => $this->getName() ), __METHOD__ );
	}

	/**
	 * Decrement the lock reference count. If the reference count is reduced to zero, commits
	 * the transaction and thereby releases the image lock.
	 */
	function unlock() {
		if ( $this->locked ) {
			--$this->locked;
			if ( !$this->locked ) {
				$dbw = $this->repo->getMasterDB();
				$dbw->commit();
			}
		}
	}

	/**
	 * Roll back the DB transaction and mark the image unlocked
	 */
	function unlockAndRollback() {
		$this->locked = false;
		$dbw = $this->repo->getMasterDB();
		$dbw->rollback();
	}
} // SwiftFile class

# ------------------------------------------------------------------------------

/**
 * Helper class for file deletion
 * @ingroup FileRepo
 */
class SwiftFileDeleteBatch {

	/**
	 * @var SwiftFile
	 */
	var $file;

	var $reason, $srcRels = array(), $archiveUrls = array(), $deletionBatch, $suppress;
	var $status;

	function __construct( File $file, $reason = '', $suppress = false ) {
		$this->file = $file;
		$this->reason = $reason;
		$this->suppress = $suppress;
		$this->status = $file->repo->newGood();
	}

	function addCurrent() {
		$this->srcRels['.'] = $this->file->getRel();
	}

	function addOld( $oldName ) {
		$this->srcRels[$oldName] = $this->file->getArchiveRel( $oldName );
		$this->archiveUrls[] = $this->file->getArchiveUrl( $oldName );
	}

	function getOldRels() {
		if ( !isset( $this->srcRels['.'] ) ) {
			$oldRels =& $this->srcRels;
			$deleteCurrent = false;
		} else {
			$oldRels = $this->srcRels;
			unset( $oldRels['.'] );
			$deleteCurrent = true;
		}

		return array( $oldRels, $deleteCurrent );
	}

	/*protected*/ function getHashes() {
		$hashes = array();
		list( $oldRels, $deleteCurrent ) = $this->getOldRels();

		if ( $deleteCurrent ) {
			$hashes['.'] = $this->file->getSha1();
		}

		if ( count( $oldRels ) ) {
			$dbw = $this->file->repo->getMasterDB();
			$res = $dbw->select(
				'oldimage',
				array( 'oi_archive_name', 'oi_sha1' ),
				'oi_archive_name IN (' . $dbw->makeList( array_keys( $oldRels ) ) . ')',
				__METHOD__
			);

			foreach ( $res as $row ) {
				if ( rtrim( $row->oi_sha1, "\0" ) === '' ) {
					// Get the hash from the file
					$oldUrl = $this->file->getArchiveVirtualUrl( $row->oi_archive_name );
					$props = $this->file->repo->getFileProps( $oldUrl );

					if ( $props['fileExists'] ) {
						// Upgrade the oldimage row
						$dbw->update( 'oldimage',
							array( 'oi_sha1' => $props['sha1'] ),
							array( 'oi_name' => $this->file->getName(), 'oi_archive_name' => $row->oi_archive_name ),
							__METHOD__ );
						$hashes[$row->oi_archive_name] = $props['sha1'];
					} else {
						$hashes[$row->oi_archive_name] = false;
					}
				} else {
					$hashes[$row->oi_archive_name] = $row->oi_sha1;
				}
			}
		}

		$missing = array_diff_key( $this->srcRels, $hashes );

		foreach ( $missing as $name => $rel ) {
			$this->status->error( 'filedelete-old-unregistered', $name );
		}

		foreach ( $hashes as $name => $hash ) {
			if ( !$hash ) {
				$this->status->error( 'filedelete-missing', $this->srcRels[$name] );
				unset( $hashes[$name] );
			}
		}

		return $hashes;
	}

	function doDBInserts() {
		global $wgUser;

		$dbw = $this->file->repo->getMasterDB();
		$encTimestamp = $dbw->addQuotes( $dbw->timestamp() );
		$encUserId = $dbw->addQuotes( $wgUser->getId() );
		$encReason = $dbw->addQuotes( $this->reason );
		$encGroup = $dbw->addQuotes( 'deleted' );
		$ext = $this->file->getExtension();
		$dotExt = $ext === '' ? '' : ".$ext";
		$encExt = $dbw->addQuotes( $dotExt );
		list( $oldRels, $deleteCurrent ) = $this->getOldRels();

		// Bitfields to further suppress the content
		if ( $this->suppress ) {
			$bitfield = 0;
			// This should be 15...
			$bitfield |= Revision::DELETED_TEXT;
			$bitfield |= Revision::DELETED_COMMENT;
			$bitfield |= Revision::DELETED_USER;
			$bitfield |= Revision::DELETED_RESTRICTED;
		} else {
			$bitfield = 'oi_deleted';
		}

		if ( $deleteCurrent ) {
			$concat = $dbw->buildConcat( array( "img_sha1", $encExt ) );
			$where = array( 'img_name' => $this->file->getName() );
			$dbw->insertSelect( 'filearchive', 'image',
				array(
					'fa_storage_group' => $encGroup,
					'fa_storage_key'   => "CASE WHEN img_sha1='' THEN '' ELSE $concat END",
					'fa_deleted_user'      => $encUserId,
					'fa_deleted_timestamp' => $encTimestamp,
					'fa_deleted_reason'    => $encReason,
					'fa_deleted'		   => $this->suppress ? $bitfield : 0,

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
					'fa_timestamp'    => 'img_timestamp'
				), $where, __METHOD__ );
		}

		if ( count( $oldRels ) ) {
			$concat = $dbw->buildConcat( array( "oi_sha1", $encExt ) );
			$where = array(
				'oi_name' => $this->file->getName(),
				'oi_archive_name IN (' . $dbw->makeList( array_keys( $oldRels ) ) . ')' );
			$dbw->insertSelect( 'filearchive', 'oldimage',
				array(
					'fa_storage_group' => $encGroup,
					'fa_storage_key'   => "CASE WHEN oi_sha1='' THEN '' ELSE $concat END",
					'fa_deleted_user'      => $encUserId,
					'fa_deleted_timestamp' => $encTimestamp,
					'fa_deleted_reason'    => $encReason,
					'fa_deleted'		   => $this->suppress ? $bitfield : 'oi_deleted',

					'fa_name'         => 'oi_name',
					'fa_archive_name' => 'oi_archive_name',
					'fa_size'         => 'oi_size',
					'fa_width'        => 'oi_width',
					'fa_height'       => 'oi_height',
					'fa_metadata'     => 'oi_metadata',
					'fa_bits'         => 'oi_bits',
					'fa_media_type'   => 'oi_media_type',
					'fa_major_mime'   => 'oi_major_mime',
					'fa_minor_mime'   => 'oi_minor_mime',
					'fa_description'  => 'oi_description',
					'fa_user'         => 'oi_user',
					'fa_user_text'    => 'oi_user_text',
					'fa_timestamp'    => 'oi_timestamp',
					'fa_deleted'      => $bitfield
				), $where, __METHOD__ );
		}
	}

	function doDBDeletes() {
		$dbw = $this->file->repo->getMasterDB();
		list( $oldRels, $deleteCurrent ) = $this->getOldRels();

		if ( count( $oldRels ) ) {
			$dbw->delete( 'oldimage',
				array(
					'oi_name' => $this->file->getName(),
					'oi_archive_name' => array_keys( $oldRels )
				), __METHOD__ );
		}

		if ( $deleteCurrent ) {
			$dbw->delete( 'image', array( 'img_name' => $this->file->getName() ), __METHOD__ );
		}
	}

	/**
	 * Run the transaction
	 */
	function execute() {
		global $wgUseSquid;
		wfProfileIn( __METHOD__ );

		$this->file->lock();
		// Leave private files alone
		$privateFiles = array();
		list( $oldRels, $deleteCurrent ) = $this->getOldRels();
		$dbw = $this->file->repo->getMasterDB();

		if ( !empty( $oldRels ) ) {
			$res = $dbw->select( 'oldimage',
				array( 'oi_archive_name' ),
				array( 'oi_name' => $this->file->getName(),
					'oi_archive_name IN (' . $dbw->makeList( array_keys( $oldRels ) ) . ')',
					$dbw->bitAnd( 'oi_deleted', File::DELETED_FILE ) => File::DELETED_FILE ),
				__METHOD__ );

			foreach ( $res as $row ) {
				$privateFiles[$row->oi_archive_name] = 1;
			}
		}
		// Prepare deletion batch
		$hashes = $this->getHashes();
		$this->deletionBatch = array();
		$ext = $this->file->getExtension();
		$dotExt = $ext === '' ? '' : ".$ext";

		foreach ( $this->srcRels as $name => $srcRel ) {
			// Skip files that have no hash (missing source).
			// Keep private files where they are.
			if ( isset( $hashes[$name] ) && !array_key_exists( $name, $privateFiles ) ) {
				$hash = $hashes[$name];
				$key = $hash . $dotExt;
				$dstRel = $this->file->repo->getDeletedHashPath( $key ) . $key;
				$this->deletionBatch[$name] = array( $srcRel, $dstRel );
			}
		}

		// Lock the filearchive rows so that the files don't get deleted by a cleanup operation
		// We acquire this lock by running the inserts now, before the file operations.
		//
		// This potentially has poor lock contention characteristics -- an alternative
		// scheme would be to insert stub filearchive entries with no fa_name and commit
		// them in a separate transaction, then run the file ops, then update the fa_name fields.
		$this->doDBInserts();

		// Removes non-existent file from the batch, so we don't get errors.
		$this->deletionBatch = $this->removeNonexistentFiles( $this->deletionBatch );

		// Execute the file deletion batch
		$status = $this->file->repo->deleteBatch( $this->deletionBatch );

		if ( !$status->isGood() ) {
			$this->status->merge( $status );
		}

		if ( !$this->status->ok ) {
			// Critical file deletion error
			// Roll back inserts, release lock and abort
			// TODO: delete the defunct filearchive rows if we are using a non-transactional DB
			$this->file->unlockAndRollback();
			wfProfileOut( __METHOD__ );
			return $this->status;
		}

		// Purge squid
		if ( $wgUseSquid ) {
			$urls = array();

			foreach ( $this->srcRels as $srcRel ) {
				$urlRel = str_replace( '%2F', '/', rawurlencode( $srcRel ) );
				$urls[] = $this->file->repo->getZoneUrl( 'public' ) . '/' . $urlRel;
			}
			SquidUpdate::purge( $urls );
		}

		// Delete image/oldimage rows
		$this->doDBDeletes();

		// Commit and return
		$this->file->unlock();
		wfProfileOut( __METHOD__ );

		return $this->status;
	}

	/**
	 * Removes non-existent files from a deletion batch.
	 */
	function removeNonexistentFiles( $batch ) {
		$files = $newBatch = array();

		foreach ( $batch as $batchItem ) {
			list( $src, $dest ) = $batchItem;
			$files[$src] = $this->file->repo->getVirtualUrl( 'public' ) . '/' . rawurlencode( $src );
		}

		$result = $this->file->repo->fileExistsBatch( $files, FSRepo::FILES_ONLY );

		foreach ( $batch as $batchItem ) {
			if ( $result[$batchItem[0]] ) {
				$newBatch[] = $batchItem;
			}
		}

		return $newBatch;
	}
}

# ------------------------------------------------------------------------------

/**
 * Helper class for file undeletion
 * @ingroup FileRepo
 */
class SwiftFileRestoreBatch {
	/**
	 * @var SwiftFile
	 */
	var $file;

	var $cleanupBatch, $ids, $all, $unsuppress = false;

	function __construct( File $file, $unsuppress = false ) {
		$this->file = $file;
		$this->cleanupBatch = $this->ids = array();
		$this->ids = array();
		$this->unsuppress = $unsuppress;
	}

	/**
	 * Add a file by ID
	 */
	function addId( $fa_id ) {
		$this->ids[] = $fa_id;
	}

	/**
	 * Add a whole lot of files by ID
	 */
	function addIds( $ids ) {
		$this->ids = array_merge( $this->ids, $ids );
	}

	/**
	 * Add all revisions of the file
	 */
	function addAll() {
		$this->all = true;
	}

	/**
	 * Run the transaction, except the cleanup batch.
	 * The cleanup batch should be run in a separate transaction, because it locks different
	 * rows and there's no need to keep the image row locked while it's acquiring those locks
	 * The caller may have its own transaction open.
	 * So we save the batch and let the caller call cleanup()
	 */
	function execute() {
		global $wgLang;

		if ( !$this->all && !$this->ids ) {
			// Do nothing
			return $this->file->repo->newGood();
		}

		$exists = $this->file->lock();
		$dbw = $this->file->repo->getMasterDB();
		$status = $this->file->repo->newGood();

		// Fetch all or selected archived revisions for the file,
		// sorted from the most recent to the oldest.
		$conditions = array( 'fa_name' => $this->file->getName() );

		if ( !$this->all ) {
			$conditions[] = 'fa_id IN (' . $dbw->makeList( $this->ids ) . ')';
		}

		$result = $dbw->select( 'filearchive', '*',
			$conditions,
			__METHOD__,
			array( 'ORDER BY' => 'fa_timestamp DESC' )
		);

		$idsPresent = array();
		$storeBatch = array();
		$insertBatch = array();
		$insertCurrent = false;
		$deleteIds = array();
		$first = true;
		$archiveNames = array();

		foreach ( $result as $row ) {
			$idsPresent[] = $row->fa_id;

			if ( $row->fa_name != $this->file->getName() ) {
				$status->error( 'undelete-filename-mismatch', $wgLang->timeanddate( $row->fa_timestamp ) );
				$status->failCount++;
				continue;
			}

			if ( $row->fa_storage_key == '' ) {
				// Revision was missing pre-deletion
				$status->error( 'undelete-bad-store-key', $wgLang->timeanddate( $row->fa_timestamp ) );
				$status->failCount++;
				continue;
			}

			$deletedRel = $this->file->repo->getDeletedHashPath( $row->fa_storage_key ) . $row->fa_storage_key;
			$deletedUrl = $this->file->repo->getVirtualUrl() . '/deleted/' . $deletedRel;

			$sha1 = substr( $row->fa_storage_key, 0, strcspn( $row->fa_storage_key, '.' ) );

			# Fix leading zero
			if ( strlen( $sha1 ) == 32 && $sha1[0] == '0' ) {
				$sha1 = substr( $sha1, 1 );
			}

			if ( is_null( $row->fa_major_mime ) || $row->fa_major_mime == 'unknown'
				|| is_null( $row->fa_minor_mime ) || $row->fa_minor_mime == 'unknown'
				|| is_null( $row->fa_media_type ) || $row->fa_media_type == 'UNKNOWN'
				|| is_null( $row->fa_metadata ) ) {
				// Refresh our metadata
				// Required for a new current revision; nice for older ones too. :)
				$props = RepoGroup::singleton()->getFileProps( $deletedUrl );
			} else {
				$props = array(
					'minor_mime' => $row->fa_minor_mime,
					'major_mime' => $row->fa_major_mime,
					'media_type' => $row->fa_media_type,
					'metadata'   => $row->fa_metadata
				);
			}

			if ( $first && !$exists ) {
				// This revision will be published as the new current version
				$destRel = $this->file->getRel();
				$insertCurrent = array(
					'img_name'        => $row->fa_name,
					'img_size'        => $row->fa_size,
					'img_width'       => $row->fa_width,
					'img_height'      => $row->fa_height,
					'img_metadata'    => $props['metadata'],
					'img_bits'        => $row->fa_bits,
					'img_media_type'  => $props['media_type'],
					'img_major_mime'  => $props['major_mime'],
					'img_minor_mime'  => $props['minor_mime'],
					'img_description' => $row->fa_description,
					'img_user'        => $row->fa_user,
					'img_user_text'   => $row->fa_user_text,
					'img_timestamp'   => $row->fa_timestamp,
					'img_sha1'        => $sha1
				);

				// The live (current) version cannot be hidden!
				if ( !$this->unsuppress && $row->fa_deleted ) {
					$storeBatch[] = array( $deletedUrl, 'public', $destRel );
					$this->cleanupBatch[] = $row->fa_storage_key;
				}
			} else {
				$archiveName = $row->fa_archive_name;

				if ( $archiveName == '' ) {
					// This was originally a current version; we
					// have to devise a new archive name for it.
					// Format is <timestamp of archiving>!<name>
					$timestamp = wfTimestamp( TS_UNIX, $row->fa_deleted_timestamp );

					do {
						$archiveName = wfTimestamp( TS_MW, $timestamp ) . '!' . $row->fa_name;
						$timestamp++;
					} while ( isset( $archiveNames[$archiveName] ) );
				}

				$archiveNames[$archiveName] = true;
				$destRel = $this->file->getArchiveRel( $archiveName );
				$insertBatch[] = array(
					'oi_name'         => $row->fa_name,
					'oi_archive_name' => $archiveName,
					'oi_size'         => $row->fa_size,
					'oi_width'        => $row->fa_width,
					'oi_height'       => $row->fa_height,
					'oi_bits'         => $row->fa_bits,
					'oi_description'  => $row->fa_description,
					'oi_user'         => $row->fa_user,
					'oi_user_text'    => $row->fa_user_text,
					'oi_timestamp'    => $row->fa_timestamp,
					'oi_metadata'     => $props['metadata'],
					'oi_media_type'   => $props['media_type'],
					'oi_major_mime'   => $props['major_mime'],
					'oi_minor_mime'   => $props['minor_mime'],
					'oi_deleted'      => $this->unsuppress ? 0 : $row->fa_deleted,
					'oi_sha1'         => $sha1 );
			}

			$deleteIds[] = $row->fa_id;

			if ( !$this->unsuppress && $row->fa_deleted & File::DELETED_FILE ) {
				// private files can stay where they are
				$status->successCount++;
			} else {
				$storeBatch[] = array( $deletedUrl, 'public', $destRel );
				$this->cleanupBatch[] = $row->fa_storage_key;
			}

			$first = false;
		}

		unset( $result );

		// Add a warning to the status object for missing IDs
		$missingIds = array_diff( $this->ids, $idsPresent );

		foreach ( $missingIds as $id ) {
			$status->error( 'undelete-missing-filearchive', $id );
		}

		// Remove missing files from batch, so we don't get errors when undeleting them
		$storeBatch = $this->removeNonexistentFiles( $storeBatch );

		// Run the store batch
		// Use the OVERWRITE_SAME flag to smooth over a common error
		$storeStatus = $this->file->repo->storeBatch( $storeBatch, FileRepo::OVERWRITE_SAME );
		$status->merge( $storeStatus );

		if ( !$status->isGood() ) {
			// Even if some files could be copied, fail entirely as that is the
			// easiest thing to do without data loss
			$this->cleanupFailedBatch( $storeStatus, $storeBatch );
			$status->ok = false;
			$this->file->unlock();

			return $status;
		}

		// Run the DB updates
		// Because we have locked the image row, key conflicts should be rare.
		// If they do occur, we can roll back the transaction at this time with
		// no data loss, but leaving unregistered files scattered throughout the
		// public zone.
		// This is not ideal, which is why it's important to lock the image row.
		if ( $insertCurrent ) {
			$dbw->insert( 'image', $insertCurrent, __METHOD__ );
		}

		if ( $insertBatch ) {
			$dbw->insert( 'oldimage', $insertBatch, __METHOD__ );
		}

		if ( $deleteIds ) {
			$dbw->delete( 'filearchive',
				array( 'fa_id IN (' . $dbw->makeList( $deleteIds ) . ')' ),
				__METHOD__ );
		}

		// If store batch is empty (all files are missing), deletion is to be considered successful
		if ( $status->successCount > 0 || !$storeBatch ) {
			if ( !$exists ) {
				wfDebug( __METHOD__ . " restored {$status->successCount} items, creating a new current\n" );

				// Update site_stats
				$site_stats = $dbw->tableName( 'site_stats' );
				$dbw->query( "UPDATE $site_stats SET ss_images=ss_images+1", __METHOD__ );

				$this->file->purgeEverything();
			} else {
				wfDebug( __METHOD__ . " restored {$status->successCount} as archived versions\n" );
				$this->file->purgeDescription();
				$this->file->purgeHistory();
			}
		}

		$this->file->unlock();

		return $status;
	}

	/**
	 * Removes non-existent files from a store batch.
	 */
	function removeNonexistentFiles( $triplets ) {
		$files = $filteredTriplets = array();
		foreach ( $triplets as $file )
			$files[$file[0]] = $file[0];

		$result = $this->file->repo->fileExistsBatch( $files, FSRepo::FILES_ONLY );

		foreach ( $triplets as $file ) {
			if ( $result[$file[0]] ) {
				$filteredTriplets[] = $file;
			}
		}

		return $filteredTriplets;
	}

	/**
	 * Removes non-existent files from a cleanup batch.
	 */
	function removeNonexistentFromCleanup( $batch ) {
		$files = $newBatch = array();
		$repo = $this->file->repo;

		foreach ( $batch as $file ) {
			$files[$file] = $repo->getVirtualUrl( 'deleted' ) . '/' .
				rawurlencode( $repo->getDeletedHashPath( $file ) . $file );
		}

		$result = $repo->fileExistsBatch( $files, FSRepo::FILES_ONLY );

		foreach ( $batch as $file ) {
			if ( $result[$file] ) {
				$newBatch[] = $file;
			}
		}

		return $newBatch;
	}

	/**
	 * Delete unused files in the deleted zone.
	 * This should be called from outside the transaction in which execute() was called.
	 */
	function cleanup() {
		if ( !$this->cleanupBatch ) {
			return $this->file->repo->newGood();
		}

		$this->cleanupBatch = $this->removeNonexistentFromCleanup( $this->cleanupBatch );

		$status = $this->file->repo->cleanupDeletedBatch( $this->cleanupBatch );

		return $status;
	}
	
	function cleanupFailedBatch( $storeStatus, $storeBatch ) {
		$cleanupBatch = array(); 
		
		foreach ( $storeStatus->success as $i => $success ) {
			if ( $success ) {
				$cleanupBatch[] = array( $storeBatch[$i][1], $storeBatch[$i][1] );
			}
		}
		$this->file->repo->cleanupBatch( $cleanupBatch );
	}
}

# ------------------------------------------------------------------------------

/**
 * Helper class for file movement
 * @ingroup FileRepo
 */
class SwiftFileMoveBatch {
	var $file, $cur, $olds, $oldCount, $archive, $target, $db;

	function __construct( File $file, Title $target ) {
		$this->file = $file;
		$this->target = $target;
		$this->oldHash = $this->file->repo->getHashPath( $this->file->getName() );
		$this->newHash = $this->file->repo->getHashPath( $this->target->getDBkey() );
		$this->oldName = $this->file->getName();
		$this->newName = $this->file->repo->getNameFromTitle( $this->target );
		$this->oldRel = $this->oldHash . $this->oldName;
		$this->newRel = $this->newHash . $this->newName;
		$this->db = $file->repo->getMasterDb();
	}

	/**
	 * Add the current image to the batch
	 */
	function addCurrent() {
		$this->cur = array( $this->oldRel, $this->newRel );
	}

	/**
	 * Add the old versions of the image to the batch
	 */
	function addOlds() {
		$archiveBase = 'archive';
		$this->olds = array();
		$this->oldCount = 0;

		$result = $this->db->select( 'oldimage',
			array( 'oi_archive_name', 'oi_deleted' ),
			array( 'oi_name' => $this->oldName ),
			__METHOD__
		);

		foreach ( $result as $row ) {
			$oldName = $row->oi_archive_name;
			$bits = explode( '!', $oldName, 2 );

			if ( count( $bits ) != 2 ) {
				wfDebug( "Old file name missing !: '$oldName' \n" );
				continue;
			}

			list( $timestamp, $filename ) = $bits;

			if ( $this->oldName != $filename ) {
				wfDebug( "Old file name doesn't match: '$oldName' \n" );
				continue;
			}

			$this->oldCount++;

			// Do we want to add those to oldCount?
			if ( $row->oi_deleted & File::DELETED_FILE ) {
				continue;
			}

			$this->olds[] = array(
				"{$archiveBase}/{$this->oldHash}{$oldName}",
				"{$archiveBase}/{$this->newHash}{$timestamp}!{$this->newName}"
			);
		}
	}

	/**
	 * Perform the move.
	 */
	function execute() {
		$repo = $this->file->repo;
		$status = $repo->newGood();
		$triplets = $this->getMoveTriplets();

		$triplets = $this->removeNonexistentFiles( $triplets );
		
		// Copy the files into their new location
		$statusMove = $repo->storeBatch( $triplets );
		wfDebugLog( 'imagemove', "Moved files for {$this->file->name}: {$statusMove->successCount} successes, {$statusMove->failCount} failures" );
		if ( !$statusMove->isGood() ) {
			wfDebugLog( 'imagemove', "Error in moving files: " . $statusMove->getWikiText() );
			$this->cleanupTarget( $triplets );
			$statusMove->ok = false;
			return $statusMove;
		}

		$this->db->begin();
		$statusDb = $this->doDBUpdates();
		wfDebugLog( 'imagemove', "Renamed {$this->file->name} in database: {$statusDb->successCount} successes, {$statusDb->failCount} failures" );
		if ( !$statusDb->isGood() ) {
			$this->db->rollback();
			// Something went wrong with the DB updates, so remove the target files
			$this->cleanupTarget( $triplets );
			$statusDb->ok = false;
			return $statusDb;
		}
		$this->db->commit();
		
		// Everything went ok, remove the source files
		$this->cleanupSource( $triplets );
		
		$status->merge( $statusDb );
		$status->merge( $statusMove );

		return $status;
	}

	/**
	 * Do the database updates and return a new FileRepoStatus indicating how
	 * many rows where updated.
	 *
	 * @return FileRepoStatus
	 */
	function doDBUpdates() {
		$repo = $this->file->repo;
		$status = $repo->newGood();
		$dbw = $this->db;

		// Update current image
		$dbw->update(
			'image',
			array( 'img_name' => $this->newName ),
			array( 'img_name' => $this->oldName ),
			__METHOD__
		);

		if ( $dbw->affectedRows() ) {
			$status->successCount++;
		} else {
			$status->failCount++;
			$status->fatal( 'imageinvalidfilename' );
			return $status;
		}

		// Update old images
		$dbw->update(
			'oldimage',
			array(
				'oi_name' => $this->newName,
				'oi_archive_name = ' . $dbw->strreplace( 'oi_archive_name', $dbw->addQuotes( $this->oldName ), $dbw->addQuotes( $this->newName ) ),
			),
			array( 'oi_name' => $this->oldName ),
			__METHOD__
		);

		$affected = $dbw->affectedRows();
		$total = $this->oldCount;
		$status->successCount += $affected;
		$status->failCount += $total - $affected;
		if ( $status->failCount ) {
			$status->error( 'imageinvalidfilename' );
		}

		return $status;
	}

	/**
	 * Generate triplets for storeBatch().
	 */
	function getMoveTriplets() {
		$moves = array_merge( array( $this->cur ), $this->olds );
		$triplets = array();	// The format is: (srcUrl, destZone, destUrl)

		foreach ( $moves as $move ) {
			// $move: (oldRelativePath, newRelativePath)
			$srcUrl = $this->file->repo->getVirtualUrl() . '/public/' . rawurlencode( $move[0] );
			$triplets[] = array( $srcUrl, 'public', $move[1] );
			wfDebugLog( 'imagemove', "Generated move triplet for {$this->file->name}: {$srcUrl} :: public :: {$move[1]}" );
		}

		return $triplets;
	}

	/**
	 * Removes non-existent files from move batch.
	 */
	function removeNonexistentFiles( $triplets ) {
		$files = array();

		foreach ( $triplets as $file ) {
			$files[$file[0]] = $file[0];
		}

		$result = $this->file->repo->fileExistsBatch( $files, FSRepo::FILES_ONLY );
		$filteredTriplets = array();

		foreach ( $triplets as $file ) {
			if ( $result[$file[0]] ) {
				$filteredTriplets[] = $file;
			} else {
				wfDebugLog( 'imagemove', "File {$file[0]} does not exist" );
			}
		}

		return $filteredTriplets;
	}
	
	/**
	 * Cleanup a partially moved array of triplets by deleting the target 
	 * files. Called if something went wrong half way.
	 */
	function cleanupTarget( $triplets ) {
		// Create dest pairs from the triplets
		$pairs = array();
		foreach ( $triplets as $triplet ) {
			$pairs[] = array( $triplet[1], $triplet[2] );
		}
		
		$this->file->repo->cleanupBatch( $pairs );
	}
	
	/**
	 * Cleanup a fully moved array of triplets by deleting the source files.
	 * Called at the end of the move process if everything else went ok. 
	 */
	function cleanupSource( $triplets ) {
		// Create source file names from the triplets
		$files = array();
		foreach ( $triplets as $triplet ) {
			$files[] = $triplet[0];
		}
		
		$this->file->repo->cleanupBatch( $files );
	}
}

/**
 * Repository that stores files in Swift and registers them
 * in the wiki's own database.
 *
 * @file
 * @ingroup FileRepo
 */

class SwiftRepo extends LocalRepo {
	var $fileFactory = array( 'SwiftFile', 'newFromTitle' );
	var $fileFactoryKey = array( 'SwiftFile', 'newFromKey' );
	var $fileFromRowFactory = array( 'SwiftFile', 'newFromRow' );
	var $oldFileFactory = array( 'OldSwiftFile', 'newFromTitle' );
	var $oldFileFactoryKey = array( 'OldSwiftFile', 'newFromKey' );
	var $oldFileFromRowFactory = array( 'OldSwiftFile', 'newFromRow' );

	function __construct( $info ) {
		FileRepo::__construct( $info );

		// Required settings
		$this->url = $info['url'];

		// Optional settings
		$this->hashLevels = isset( $info['hashLevels'] ) ? $info['hashLevels'] : 2;
		$this->deletedHashLevels = isset( $info['deletedHashLevels'] ) ?
			$info['deletedHashLevels'] : $this->hashLevels;

		if ( isset( $info['thumbUrl'] ) ) {
			$this->thumbUrl = $info['thumbUrl'];
		} else {
			$this->thumbUrl = "{$this->url}/thumb";
		}

		// Required settings
		$this->swiftuser= $info['user'];
		$this->swiftkey= $info['key'];
		$this->authurl= $info['authurl'];
		$this->container= $info['container'];
	}

	/**
	 * Get a connection to the swift proxy.
	 *
	 * @return CF_Connection
	 */
	function connect() {
		$auth = new CF_Authentication($this->swiftuser, $this->swiftkey, NULL, $this->authurl);
		try {
			$auth->authenticate();
		} catch (AuthenticationException $e) {
			throw new MWException( "We can't authenticate ourselves." );
		} catch (InvalidResponseException $e) {
			throw new MWException( __METHOD__ . "unexpected response '$e'" );
		}
		return new CF_Connection($auth);
	}

	/**
	 * Given a connection and container name, return the container.
	 * We KNOW the container should exist, so puke if it doesn't.
	 *
	 * @return CF_Container
	 */
	function get_container($conn, $cont) {
		try {
			return $conn->get_container($cont);
		} catch (NoSuchContainerException $e) {
			throw new MWException( "A container we thought existed, doesn't." );
		} catch (InvalidResponseException $e) {
			throw new MWException( __METHOD__ . "unexpected response '$e'" );
		}
	}

	/**
	 * Given a filename, container, and object name, write the file into the object.
	 * None of these error conditions are recoverable by the user, so we just dump
	 * an Internal Error on them.
	 *
	 * @return CF_Container
	 */
	function write_swift_object( $srcPath, $dstc, $dstRel) {
		try {
			$obj = $dstc->create_object($dstRel);
			$obj->load_from_filename( $srcPath, True);
		} catch (SyntaxException $e) {
		       throw new MWException( "missing required parameters" );
		} catch (BadContentTypeException $e) {
		       throw new MWException( "No Content-Type was/could be set" );
		} catch (InvalidResponseException $e) {
			throw new MWException( __METHOD__ . "unexpected response '$e'" );
		} catch (IOException $e) {
			throw new MWException( "error opening file '$e'" );
		}
	}

	/**
	 * Given a container and object name, delete the object.
	 * None of these error conditions are recoverable by the user, so we just dump
	 * an Internal Error on them.
	 *
	 */
	function swift_delete( $container, $rel ) {
		try {
			$container->delete_object($rel);
		} catch (SyntaxException $e) {
		       throw new MWException( "Swift object name not well-formed: '$e'" );
		} catch (NoSuchObjectException $e) {
		       throw new MWException( "Swift object we are trying to delete does not exist: '$e'" );
		} catch (InvalidResponseException $e) {
		       throw new MWException( "unexpected response '$e'" );
		}
	}

	/**
	 * Store a batch of files
	 *
	 * @param $triplets Array: (src,zone,dest) triplets as per store()
	 * @param $flags Integer: bitwise combination of the following flags:
	 *     self::DELETE_SOURCE     Delete the source file after upload
	 *     self::OVERWRITE         Overwrite an existing destination file instead of failing
	 *     self::OVERWRITE_SAME    Overwrite the file if the destination exists and has the
	 *                             same contents as the source
	 * @return $status
	 */
	function storeBatch( $triplets, $flags = 0 ) {
		wfDebug( __METHOD__  . ': Storing ' . count( $triplets ) . 
			" triplets; flags: {$flags}\n" );
		
		// Validate each triplet 
		$status = $this->newGood();
		foreach ( $triplets as $i => $triplet ) {
			list( $srcPath, $dstZone, $dstRel ) = $triplet;

			if ( !$this->validateFilename( $dstRel ) ) {
				throw new MWException( "Validation error in $dstRel" );
			}

			// Check overwriting
			if (0) { #FIXME
			if ( !( $flags & self::OVERWRITE ) && file_exists( $dstPath ) ) {
				if ( $flags & self::OVERWRITE_SAME ) {
					$hashSource = sha1_file( $srcPath );
					$hashDest = sha1_file( $dstPath );
					if ( $hashSource != $hashDest ) {
						$status->fatal( 'fileexistserror', $dstPath );
					}
				} else {
					$status->fatal( 'fileexistserror', $dstPath );
				}
			}
			}
		}

		// Abort now on failure
		if ( !$status->ok ) {
			return $status;
		}

		// Execute the store operation for each triplet
		$conn = $this->connect();

		foreach ( $triplets as $i => $triplet ) {
			list( $srcPath, $dstZone, $dstRel ) = $triplet;

			// Point to the container.
			$dstContainer = $this->getZoneContainer( $dstZone );
			$dstc = $this->get_container($conn, $dstContainer);

			$good = true;

			// Where are we copying this from?
			if (self::isVirtualUrl( $srcPath )) {
				$src = $this->resolveVirtualUrl( $srcPath );
				list ($srcContainer, $srcRel) = $src;
				$srcc = $this->get_container($conn, $srcContainer);

				$this->swiftcopy($srcc, $srcRel, $dstc, $dstRel);
				if ( $flags & self::DELETE_SOURCE ) {
					$this->swift_delete( $srcc, $srcRel );
				}
			} else {
				$this->write_swift_object( $srcPath, $dstc, $dstRel);
				// php-cloudfiles throws exceptions, so failure never gets here.
				if ( $flags & self::DELETE_SOURCE ) {
					unlink ( $srcPath );
				}		
			}

			if ( !( $flags & self::SKIP_VALIDATION ) ) {
				// FIXME: Swift will return the MD5 of the data written.
				if (0) { // ( $hashDest === false || $hashSource !== $hashDest ) {
					wfDebug( __METHOD__ . ': File copy validation failed: ' . 
						"$srcPath ($hashSource) to $dstPath ($hashDest)\n" );
					
					$status->error( 'filecopyerror', $srcPath, $dstPath );
					$good = false;
				}
			}
			if ( $good ) {
				$status->successCount++;
			} else {
				$status->failCount++;
			}
			$status->success[$i] = $good;
		}
		return $status;
	}

	/**
	 * Pick a random name in the temp zone and store a file to it.
	 * @param $originalName String: the base name of the file as specified
	 *     by the user. The file extension will be maintained.
	 * @param $srcPath String: the current location of the file.
	 * @return FileRepoStatus object with the URL in the value.
	 */
	function storeTemp( $originalName, $srcPath ) {
		$date = gmdate( "YmdHis" );
		$hashPath = $this->getHashPath( $originalName );
		$dstRel = "$hashPath$date!$originalName";
		$dstUrlRel = $hashPath . $date . '!' . rawurlencode( $originalName );

		$result = $this->store( $srcPath, 'temp', $dstRel );
		$result->value = $this->getVirtualUrl( 'temp' ) . '/' . $dstUrlRel;
		return $result;
	}


	/**
	 * Append the contents of the source path to the given file, OR queue
	 * the appending operation in anticipation of a later appendFinish() call.
	 * @param $srcPath String: location of the source file
	 * @param $toAppendPath String: path to append to.
	 * @param $flags Integer: bitfield, may be FileRepo::DELETE_SOURCE to indicate
	 *        that the source file should be deleted if possible
	 * @return mixed Status or false
	 */

	function append( $srcPath, $toAppendPath, $flags = 0 ){
		throw new MWException( __METHOD__.": Not yet implemented." );
		// I think we need to count the number of files whose names
		// start with $toAppendPath, then add that count (with leading zeroes) to
		// the end of $toAppendPath and write the chunk there.

		// Count the number of files whose names start with $toAppendPath
		$conn = $this->connect();
		$container = $this->repo->get_container($conn,$this->repo->container . "%2Ftemp");
		$nextone = count($container->list_objects(0, NULL, $srcPath));

		// Do the append to the next name
		$status = $this->store( $srcPath, 'temp', sprintf("%s.%05d", $toAppendPath, $nextone) );
	
		if ( $flags & self::DELETE_SOURCE ) {
			unlink( $srcPath );
		}

		return $status;
	}
	/**
	 * Finish the append operation.
	 * @param $toAppendPath String: path to append to.
	 */
	function appendFinish( $toAppendPath ){
		$conn = $this->connect();
		$container = $this->repo->get_container( $conn,$this->repo->container . "%2Ftemp" );
		$parts = $container->list_objects( 0, NULL, $srcPath );
		// list_objects() returns a sorted list.

		// The first object as the same name as the destination, so
		// we read it into memory and then write it out as the first chunk.
		$obj = $container->get_object( array_shift($parts) );
		$first = $obj->read();

		$biggie = $container->create_object( $toAppendPath );
		$biggie->write( $first );

		foreach ( $parts as $part ) {
			$obj = $container->get_object( $part );
			$biggie->write( $obj->read() );
		}
		return newGood();
	}

	/**
	 * Move a group of files to the deletion archive.
	 * If no valid deletion archive is configured, this may either delete the
	 * file or throw an exception, depending on the preference of the repository.
	 *
	 * @param $sourceDestPairs Array of source/destination pairs. Each element
	 *        is a two-element array containing the source file path relative to the
	 *        public root in the first element, and the archive file path relative
	 *        to the deleted zone root in the second element.
	 * @return FileRepoStatus
	 */
	function deleteBatch( $sourceDestPairs ) {
		wfDebug(  __METHOD__ . " deleting " . var_export($sourceDestPairs, true) . "\n");

		/**
		 * Move the files
		 */
		$triplets = array();
		foreach ( $sourceDestPairs as $pair ) {
			list( $srcRel, $archiveRel ) = $pair;

			$triplets[] = array( "mwrepo://{$this->name}/public/$srcRel", 'deleted', $archiveRel );
			
		}
		$status = $this->storeBatch( $triplets, FileRepo::OVERWRITE_SAME | FileRepo::DELETE_SOURCE );
		return $status;
	}


	function newFromArchiveName( $title, $archiveName ) {
		return OldSwiftFile::newFromArchiveName( $title, $this, $archiveName );
	}

	/**
	 * Checks existence of specified array of files.
	 *
	 * @param $files Array: URLs of files to check
	 * @param $flags Integer: bitwise combination of the following flags:
	 *     self::FILES_ONLY     Mark file as existing only if it is a file (not directory)
	 * @return Either array of files and existence flags, or false
	 */
	function fileExistsBatch( $files, $flags = 0 ) {
		if ($flags != self::FILES_ONLY) {
			// we ONLY support when $flags & self::FILES_ONLY is set!
			throw new MWException( "Swift Media Store doesn't have directories");
		}
		$result = array();
		$conn = $this->connect();

		foreach ( $files as $key => $file ) {
			if ( !self::isVirtualUrl( $file ) ) {
				throw new MWException( __METHOD__ . " requires a virtual URL, not '$file'");
			}
			$rvu = $this->resolveVirtualUrl( $file );
			list ($cont, $rel) = $rvu;
			$container = $this->get_container($conn,$cont);
			try {
				$obj = $container->get_object($rel);
				$result[$key] = true;
			} catch (NoSuchObjectException $e) {
				$result[$key] = false;
			}
		}

		return $result;
	}


	// FIXME: do we really need to reject empty titles?
	function newFile( $title, $time = false ) {
		if ( empty($title) ) { return null; }
		return parent::newFile( $title, $time );
	}

	/**
	 * Copy a file from one place to another place in the same container
	 * @param $srcContainer CF_Container
	 * @param $srcRel String: relative path to the source file.
	 * @param $dstContainer CF_Container
	 * @param $dstRel String: relative path to the destination.
	 */
	function swiftcopy($srcContainer, $srcRel, $dstContainer, $dstRel ) {
		// The destination must exist already.
		$obj = $dstContainer->create_object($dstRel);
		$obj->content_type = "text/plain";

		try {
			$obj->write(".");
		} catch (SyntaxException $e ) {
			throw new MWException( "Write failed: $e" );
		} catch (BadContentTypeException $e ) {
			throw new MWException( "Missing Content-Type: $e" );
		} catch (MisMatchedChecksumException $e ) {
			throw new MWException( __METHOD__ . "should not happen: '$e'" );
		} catch (InvalidResponseException $e ) {
			throw new MWException( __METHOD__ . "unexpected response '$e'" );
		}

		try {
			$obj = $dstContainer->get_object($dstRel);
		} catch (NoSuchObjectException $e) {
			throw new MWException( "The object we just created does not exist: " . $dstContainer->name . "/$dstRel: $e" );
		}

		wfDebug( __METHOD__ . " copying to " . $dstContainer->name . "/" . $dstRel . " from " . $srcContainer->name . "/" . $srcRel . "\n");

		try {
			$obj->copy($srcContainer->name . "/" . $srcRel);
		} catch (SyntaxException $e ) {
			throw new MWException( "Source file does not exist: " . $srcContainer->name . "/$srcRel: $e" );
		} catch (MisMatchedChecksumException $e ) {
			throw new MWException( "Checksums do not match: $e" );
		} catch (InvalidResponseException $e ) {
			throw new MWException( __METHOD__ . "unexpected response '$e'" );
		}
	}

	/**
	 * Publish a batch of files
	 * @param $triplets Array: (source,dest,archive) triplets as per publish()
	 * @param $flags Integer: bitfield, may be FileRepo::DELETE_SOURCE to indicate
	 *        that the source files should be deleted if possible
	 */
	function publishBatch( $triplets, $flags = 0 ) {
		$conn = $this->connect();
		$container = $this->get_container($conn,$this->container);

		# paranoia
		$status = $this->newGood( array() );
		foreach ( $triplets as $i => $triplet ) {
			list( $srcPath, $dstRel, $archiveRel ) = $triplet;

			if ( !$this->validateFilename( $dstRel ) ) {
				throw new MWException( "Validation error in $dstRel" );
			}
			if ( !$this->validateFilename( $archiveRel ) ) {
				throw new MWException( "Validation error in $archiveRel" );
			}
			if ( !is_file( $srcPath ) ) {
				// Make a list of files that don't exist for return to the caller
				$status->fatal( 'filenotfound', $srcPath );
			}
		}

		if ( !$status->ok ) {
			return $status;
		}

		foreach ( $triplets as $i => $triplet ) {
			list( $srcPath, $dstRel, $archiveRel ) = $triplet;

			// Archive destination file if it exists
			try {
				$pic = $container->get_object($dstRel);
			} catch (NoSuchObjectException $e) {
				$pic = NULL;
			}
			if( $pic ) {
				$this->swiftcopy($container, $dstRel, $container, $archiveRel );
				wfDebug(__METHOD__.": moved file $dstRel to $archiveRel\n");
				$status->value[$i] = 'archived';
			} else {
				$status->value[$i] = 'new';
			}

			$good = true;
			$this->write_swift_object( $srcPath, $container, $dstRel);
			// php-cloudfiles throws exceptions, so failure never gets here.
			if ( $flags & self::DELETE_SOURCE ) {
				unlink ( $srcPath );
			}		

			if ( $good ) {
				$status->successCount++;
				wfDebug(__METHOD__.": wrote tempfile $srcPath to $dstRel\n");
			} else {
				$status->failCount++;
			}
		}
		return $status;
	}

	/**
	 * Deletes a batch of files. Each file can be a (zone, rel) pairs, a
	 * virtual url or a real path. It will try to delete each file, but 
	 * ignores any errors that may occur
	 * 
	 * @param $pairs array List of files to delete
	 */
	function cleanupBatch( $files ) {
		$conn = $this->connect();
		foreach ( $files as $file ) {
			if ( is_array( $file ) ) {
				// This is a pair, extract it
				list( $cont, $rel ) = $file;
			} else {
				if ( self::isVirtualUrl( $file ) ) {
					// This is a virtual url, resolve it 
					$path = $this->resolveVirtualUrl( $file );
					list( $cont, $rel) = $path;
				} else {
					// FIXME: This is a full file name
					throw new MWException( __METHOD__.": $file needs an unlink()" );
				}
			}
			
			wfDebug( __METHOD__.": $cont/$rel\n" );
			$container = $this->get_container($conn,$cont);
			$this->swift_delete( $container, $rel );
		}
	}

	/**
	 * Delete files in the deleted directory if they are not referenced in the
	 * filearchive table. This needs to be done in the repo because it needs to
	 * interleave database locks with file operations, which is potentially a
	 * remote operation.
	 * @return FileRepoStatus
	 */
	function cleanupDeletedBatch( $storageKeys ) {
		$conn = $this->connect();
		$cont = $this->getZoneContainer( 'deleted' );
		$container = $this->get_container($conn,$cont);

		$dbw = $this->getMasterDB();
		$status = $this->newGood();
		$storageKeys = array_unique($storageKeys);
		foreach ( $storageKeys as $key ) {
			$hashPath = $this->getDeletedHashPath( $key );
			$rel = "$hashPath$key";
			$dbw->begin();
			$inuse = $dbw->selectField( 'filearchive', '1',
				array( 'fa_storage_group' => 'deleted', 'fa_storage_key' => $key ),
				__METHOD__, array( 'FOR UPDATE' ) );
			if( !$inuse ) {
				$sha1 = self::getHashFromKey( $key );
				$ext = substr( $key, strcspn( $key, '.' ) + 1 );
				$ext = File::normalizeExtension($ext);
				$inuse = $dbw->selectField( 'oldimage', '1',
					array( 'oi_sha1' => $sha1,
						'oi_archive_name ' . $dbw->buildLike( $dbw->anyString(), ".$ext" ),
						$dbw->bitAnd('oi_deleted', File::DELETED_FILE) => File::DELETED_FILE ),
					__METHOD__, array( 'FOR UPDATE' ) );
			}
			if ( !$inuse ) {
				wfDebug( __METHOD__ . ": deleting $key\n" );
				$this->swift_delete( $container, $rel );
			} else {
				wfDebug( __METHOD__ . ": $key still in use\n" );
				$status->successCount++;
			}
			$dbw->commit();
		}
		return $status;
	}

	/**
	 * Makes no sense in our context -- don't let anybody call it.
	 */
	function getZonePath( $zone ) {
		throw new MWException( __METHOD__.": not implemented" );
	}

	/**
	 * Get the Swift container corresponding to one of the three basic zones
	 */
	function getZoneContainer( $zone ) {
		switch ( $zone ) {
			case 'public':
				return $this->container;
			case 'temp':
				return $this->container . "%2Ftemp";
			case 'deleted':
				return $this->container . "%2Fdeleted";
			case 'thumb':
				return $this->container . "%2Fthumb";
			default:
				return false;
		}
	}
	/**
	 * Get the ($container, $object) corresponding to a virtual URL
	 */
	function resolveVirtualUrl( $url ) {
		if ( substr( $url, 0, 9 ) != 'mwrepo://' ) {
			throw new MWException( __METHOD__.": unknown protocol" );
		}

		$bits = explode( '/', substr( $url, 9 ), 3 );
		if ( count( $bits ) != 3 ) {
			throw new MWException( __METHOD__.": invalid mwrepo URL: $url" );
		}
		list( $repo, $zone, $rel ) = $bits;
		if ( $repo !== $this->name ) {
			throw new MWException( __METHOD__.": fetching from a foreign repo is not supported" );
		}
		$container = $this->getZoneContainer( $zone );
		if ( $container === false) {
			throw new MWException( __METHOD__.": invalid zone: $zone" );
		}
		return array($container, rawurldecode( $rel ));
	}




}

/**
 * Old file in the in the oldimage table
 *
 * @file
 * @ingroup FileRepo
 */

/**
 * Class to represent a file in the oldimage table
 *
 * @ingroup FileRepo
 */
class OldSwiftFile extends SwiftFile {
	var $requestedTime, $archive_name;

	const CACHE_VERSION = 1;
	const MAX_CACHE_ROWS = 20;

	static function newFromTitle( $title, $repo, $time = null ) {
		# The null default value is only here to avoid an E_STRICT
		if( $time === null )
			throw new MWException( __METHOD__.' got null for $time parameter' );
		return new self( $title, $repo, $time, null );
	}

	static function newFromArchiveName( $title, $repo, $archiveName ) {
		return new self( $title, $repo, null, $archiveName );
	}

	static function newFromRow( $row, $repo ) {
		$title = Title::makeTitle( NS_FILE, $row->oi_name );
		$file = new self( $title, $repo, null, $row->oi_archive_name );
		$file->loadFromRow( $row, 'oi_' );
		return $file;
	}

	/**
	 * @static
	 * @param  $sha1
	 * @param $repo LocalRepo
	 * @param bool $timestamp
	 * @return bool|OldLocalFile
	 */
	static function newFromKey( $sha1, $repo, $timestamp = false ) {
		$conds = array( 'oi_sha1' => $sha1 );
		if( $timestamp ) {
			$conds['oi_timestamp'] = $timestamp;
		}
		$dbr = $repo->getSlaveDB();
		$row = $dbr->selectRow( 'oldimage', self::selectFields(), $conds, __METHOD__ );
		if( $row ) {
			return self::newFromRow( $row, $repo );
		} else {
			return false;
		}
	}
	
	/**
	 * Fields in the oldimage table
	 */
	static function selectFields() {
		return array(
			'oi_name',
			'oi_archive_name',
			'oi_size',
			'oi_width',
			'oi_height',
			'oi_metadata',
			'oi_bits',
			'oi_media_type',
			'oi_major_mime',
			'oi_minor_mime',
			'oi_description',
			'oi_user',
			'oi_user_text',
			'oi_timestamp',
			'oi_deleted',
			'oi_sha1',
		);
	}

	/**
	 * @param $title Title
	 * @param $repo FileRepo
	 * @param $time String: timestamp or null to load by archive name
	 * @param $archiveName String: archive name or null to load by timestamp
	 */
	function __construct( $title, $repo, $time, $archiveName ) {
		parent::__construct( $title, $repo );
		$this->requestedTime = $time;
		$this->archive_name = $archiveName;
		if ( is_null( $time ) && is_null( $archiveName ) ) {
			throw new MWException( __METHOD__.': must specify at least one of $time or $archiveName' );
		}
	}

	function getCacheKey() {
		return false;
	}

	function getArchiveName() {
		if ( !isset( $this->archive_name ) ) {
			$this->load();
		}
		return $this->archive_name;
	}

	function isOld() {
		return true;
	}

	function isVisible() {
		return $this->exists() && !$this->isDeleted(File::DELETED_FILE);
	}

	function loadFromDB() {
		wfProfileIn( __METHOD__ );
		$this->dataLoaded = true;
		$dbr = $this->repo->getSlaveDB();
		$conds = array( 'oi_name' => $this->getName() );
		if ( is_null( $this->requestedTime ) ) {
			$conds['oi_archive_name'] = $this->archive_name;
		} else {
			$conds[] = 'oi_timestamp = ' . $dbr->addQuotes( $dbr->timestamp( $this->requestedTime ) );
		}
		$row = $dbr->selectRow( 'oldimage', $this->getCacheFields( 'oi_' ),
			$conds, __METHOD__, array( 'ORDER BY' => 'oi_timestamp DESC' ) );
		if ( $row ) {
			$this->loadFromRow( $row, 'oi_' );
		} else {
			$this->fileExists = false;
		}
		wfProfileOut( __METHOD__ );
	}

	function getCacheFields( $prefix = 'img_' ) {
		$fields = parent::getCacheFields( $prefix );
		$fields[] = $prefix . 'archive_name';
		$fields[] = $prefix . 'deleted';
		return $fields;
	}

	function getRel() {
		return 'archive/' . $this->getHashPath() . $this->getArchiveName();
	}

	function getUrlRel() {
		return 'archive/' . $this->getHashPath() . rawurlencode( $this->getArchiveName() );
	}

	function upgradeRow() {
		wfProfileIn( __METHOD__ );
		$this->loadFromFile();

		# Don't destroy file info of missing files
		if ( !$this->fileExists ) {
			wfDebug( __METHOD__.": file does not exist, aborting\n" );
			wfProfileOut( __METHOD__ );
			return;
		}

		$dbw = $this->repo->getMasterDB();
		list( $major, $minor ) = self::splitMime( $this->mime );

		wfDebug(__METHOD__.': upgrading '.$this->archive_name." to the current schema\n");
		$dbw->update( 'oldimage',
			array(
				'oi_width' => $this->width,
				'oi_height' => $this->height,
				'oi_bits' => $this->bits,
				'oi_media_type' => $this->media_type,
				'oi_major_mime' => $major,
				'oi_minor_mime' => $minor,
				'oi_metadata' => $this->metadata,
				'oi_sha1' => $this->sha1,
			), array(
				'oi_name' => $this->getName(),
				'oi_archive_name' => $this->archive_name ),
			__METHOD__
		);
		wfProfileOut( __METHOD__ );
	}

	/**
	 * @param $field Integer: one of DELETED_* bitfield constants
	 *               for file or revision rows
	 * @return bool
	 */
	function isDeleted( $field ) {
		$this->load();
		return ($this->deleted & $field) == $field;
	}

	/**
	 * Returns bitfield value
	 * @return int
	 */
	function getVisibility() {
		$this->load();
		return (int)$this->deleted;
	}

	/**
	 * Determine if the current user is allowed to view a particular
	 * field of this image file, if it's marked as deleted.
	 *
	 * @param $field Integer
	 * @return bool
	 */
	function userCan( $field ) {
		$this->load();
		return Revision::userCanBitfield( $this->deleted, $field );
	}
}


class Junkyjunk {
	/**
	 * Remove a temporary file or mark it for garbage collection
	 * @param $virtualUrl String: the virtual URL returned by storeTemp
	 * @return Boolean: true on success, false on failure
	 */
	function freeTemp( $virtualUrl ) {
		$temp = "mwrepo://{$this->name}/temp";
		if ( substr( $virtualUrl, 0, strlen( $temp ) ) != $temp ) {
			wfDebug( __METHOD__.": Invalid virtual URL\n" );
			return false;
		}
		$path = $this->resolveVirtualUrl( $virtualUrl );
		$success = unlink( $path );
		return $success;
	}



	/**
	 * Removes non-existent files from move batch.
	 */
	function move_removeNonexistentFiles( $triplets ) {
		$files = array();

		foreach ( $triplets as $file ) {
			$files[$file[0]] = $file[0];
		}

		$result = $this->file->repo->fileExistsBatch( $files, FSRepo::FILES_ONLY );
		$filteredTriplets = array();

		foreach ( $triplets as $file ) {
			if ( $result[$file[0]] ) {
				$filteredTriplets[] = $file;
			} else {
				wfDebugLog( 'imagemove', "File {$file[0]} does not exist" );
			}
		}

		return $filteredTriplets;
	}
		/**
	 * Removes non-existent files from a store batch.
	 */
	function store_removeNonexistentFiles( $triplets ) {
		$files = $filteredTriplets = array();

		foreach ( $triplets as $file )
			$files[$file[0]] = $file[0];

		$result = $this->file->repo->fileExistsBatch( $files, FSRepo::FILES_ONLY );

		foreach ( $triplets as $file ) {
			if ( $result[$file[0]] ) {
				$filteredTriplets[] = $file;
			}
		}

		return $filteredTriplets;
	}

	/**
	 * Removes non-existent files from a deletion batch.
	 */
	function deletion_removeNonexistentFiles( $triplets ) {
		$files = $filteredTriplets = array();

		foreach ( $triplets as $file) {
			list( $src, $dest ) = $file;
			$files[$src] = $this->file->repo->getVirtualUrl( 'public' ) . '/' . rawurlencode( $src );
		}

		$result = $this->file->repo->fileExistsBatch( $files, FSRepo::FILES_ONLY );

		foreach ( $triplets as $file ) {
			if ( $result[$file[0]] ) {
				$filteredTriplets[] = $file;
			}
		}

		return $filteredTriplets;
	}

}
