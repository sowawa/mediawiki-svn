<?php
/**
 * Class representing a MediaWiki article and history
 *
 * FlaggedArticle::getTitleInstance() is preferred over constructor calls
 */
class FlaggedArticle extends Article {
	/* Process cache variables */
	protected $stableRev = null;
	protected $pendingRevs = null;
	protected $pageConfig = null;
	protected $imagePage = null; // for file pages

	/**
	 * Get a FlaggedArticle for a given title
	 * @param Title
	 * @return FlaggedArticle
	 */
	public static function getTitleInstance( Title $title ) {
		// Check if there is already an instance on this title
		if ( !isset( $title->flaggedRevsArticle ) ) {
			$title->flaggedRevsArticle = new self( $title );
		}
		return $title->flaggedRevsArticle;
	}

	/**
	 * Get a FlaggedArticle for a given article
	 * @param Article
	 * @return FlaggedArticle
	 */
	public static function getArticleInstance( Article $article ) {
		return self::getTitleInstance( $article->mTitle );
	}

	/**
	 * Clear object process cache values
	 * @return void
	 */
	public function clear() {
		$this->stableRev = null;
		$this->pendingRevs = null;
		$this->pageConfig = null;
		$this->imagePage = null;
		parent::clear();
	}

	/**
	 * Get the current file version of this file page
	 * @TODO: kind of hacky
	 * @return mixed (File/false)
	 */
	public function getFile() {
		if ( $this->getTitle()->getNamespace() != NS_FILE ) {
			return false; // not a file page
		}
		if ( is_null( $this->imagePage ) ) {
			$this->imagePage = new ImagePage( $this->getTitle() );
		}
		return $this->imagePage->getFile();
	}

	/**
	 * Get the displayed file version of this file page
	 * @TODO: kind of hacky
	 * @return mixed (File/false)
	 */
	public function getDisplayedFile() {
		if ( $this->getTitle()->getNamespace() != NS_FILE ) {
			return false; // not a file page
		}
		if ( is_null( $this->imagePage ) ) {
			$this->imagePage = new ImagePage( $this->getTitle() );
		}
		return $this->imagePage->getDisplayedFile();
	}

	 /**
	 * Is the stable version shown by default for this page?
	 * @param int $flags, FR_MASTER
	 * @return bool
	 */
	public function isStableShownByDefault( $flags = 0 ) {
		if ( !$this->isReviewable( $flags ) ) {
			return false; // no stable versions can exist
		}
		$config = $this->getStabilitySettings( $flags ); // page configuration
		return (bool)$config['override'];
	}

	/**
	 * Do edits have to be reviewed before being shown by default (going live)?
	 * @param int $flags, FR_MASTER
	 * @return bool
	 */
	public function editsRequireReview( $flags = 0 ) {
		return (
			$this->isReviewable( $flags ) && // reviewable page
			$this->isStableShownByDefault( $flags ) && // and stable versions override
			$this->getStableRev( $flags ) // and there is a stable version
		);
	}

	/**
	 * Are edits to this page currently pending?
	 * @param int $flags, FR_MASTER
	 * @return bool
	 */
	public function revsArePending( $flags = 0 ) {
		if ( $this->isReviewable() ) {
			$srev = $this->getStableRev( $flags );
			if ( $srev ) {
				if ( $flags & FR_MASTER ) {
					$latest = $this->getTitle()->getLatestRevID( Title::GAID_FOR_UPDATE );
				} else {
					$latest = $this->getLatest();
				}
				return ( $srev->getRevId() != $latest ); // edits need review
			}
		}
		return false; // all edits go live
	}

	/**
	 * Get number of revs since the stable revision
	 * Note: slower than revsArePending()
	 * @param int $flags FR_MASTER
	 * @return int
	 */
	public function getPendingRevCount( $flags = 0 ) {
		global $wgMemc, $wgParserCacheExpireTime;
		# Cached results available?
		if ( !( $flags & FR_MASTER ) && $this->pendingRevs !== null ) {
			return $this->pendingRevs;
		}
		$srev = $this->getStableRev( $flags );
		if ( !$srev ) {
			return 0; // none
		}
		$count = null;
		$sRevId = $srev->getRevId();
		# Try the cache...
		$key = wfMemcKey( 'flaggedrevs', 'countPending', $this->getId() );
		if ( !( $flags & FR_MASTER ) ) {
			$tuple = FlaggedRevs::getMemcValue( $wgMemc->get( $key ), $this );
			# Items is cached and newer that page_touched...
			if ( $tuple !== false ) {
				# Confirm that cache value was made against the same stable rev Id.
				# This avoids lengthy cache pollution if $sRevId is outdated.
				list( $cRevId, $cPending ) = explode( '-', $tuple, 2 );
				if ( $cRevId == $sRevId ) {
					$count = (int)$cPending;
				}
			}
		}
		# Otherwise, fetch result from DB as needed...
		if ( is_null( $count ) ) {
			$db = ( $flags & FR_MASTER ) ?
				wfGetDB( DB_MASTER ) : wfGetDB( DB_SLAVE );
			$srevTS = $db->timestamp( $srev->getRevTimestamp() );
			$count = $db->selectField( 'revision', 'COUNT(*)',
				array( 'rev_page' => $this->getId(),
					'rev_timestamp > ' . $db->addQuotes( $srevTS ) ), // bug 15515
				__METHOD__ );
			# Save result to cache...
			$data = FlaggedRevs::makeMemcObj( "{$sRevId}-{$count}" );
			$wgMemc->set( $key, $data, $wgParserCacheExpireTime );
		}
		$this->pendingRevs = $count;
		return $this->pendingRevs;
	}

	/**
	* Checks if the stable version is synced with the current revision
	* Note: slower than getPendingRevCount()
	* @return bool
	*/
	public function stableVersionIsSynced() {
		global $wgMemc, $wgParserCacheExpireTime;
		$srev = $this->getStableRev();
		if ( !$srev ) {
			return true;
		}
		# Stable text revision must be the same as the current
		if ( $this->revsArePending() ) {
			return false;
		# Stable file revision must be the same as the current
		} elseif ( $this->getTitle()->getNamespace() == NS_FILE ) {
			$file = $this->getFile(); // current upload version
			if ( $file && $file->getTimestamp() > $srev->getFileTimestamp() ) {
				return false;
			}
		}
		# If using the current version of includes, there is nothing else to check.
		if ( FlaggedRevs::inclusionSetting() == FR_INCLUDES_CURRENT ) {
			return true; // short-circuit
		}
		# Try the cache...
		$key = wfMemcKey( 'flaggedrevs', 'includesSynced', $this->getId() );
		$value = FlaggedRevs::getMemcValue( $wgMemc->get( $key ), $this );
		if ( $value === "true" ) {
			return true;
		} elseif ( $value === "false" ) {
			return false;
		}
		# Since the stable and current revisions have the same text and only outputs,
		# the only other things to check for are template and file differences in the output.
		# (a) Check if the current output has a newer template/file used
		# (b) Check if the stable version has a file/template that was deleted
		$synced = ( !$srev->findPendingTemplateChanges()
			&& !$srev->findPendingFileChanges( 'noForeign' ) );
		# Save to cache. This will be updated whenever the page is touched.
		$data = FlaggedRevs::makeMemcObj( $synced ? "true" : "false" );
		$wgMemc->set( $key, $data, $wgParserCacheExpireTime );

		return $synced;
	}

	/**
	 * Are template/file changes and ONLY template/file changes pending?
	 * @return bool
	 */
	public function onlyTemplatesOrFilesPending() {
		return ( !$this->revsArePending() && !$this->stableVersionIsSynced() );
	}

	/**
	 * Is this page less open than the site defaults?
	 * @return bool
	 */
	public function isPageLocked() {
		return ( !FlaggedRevs::isStableShownByDefault() && $this->isStableShownByDefault() );
	}

	/**
	 * Is this page more open than the site defaults?
	 * @return bool
	 */
	public function isPageUnlocked() {
		return ( FlaggedRevs::isStableShownByDefault() && !$this->isStableShownByDefault() );
	}

	/**
	 * Tags are only shown for unreviewed content and this page is not locked/unlocked?
	 * @return bool
	 */
	public function lowProfileUI() {
		return FlaggedRevs::lowProfileUI() &&
			FlaggedRevs::isStableShownByDefault() == $this->isStableShownByDefault();
	}

	 /**
	 * Is this article reviewable?
	 * @param int $flags, FR_MASTER
	 * @return bool
	 */
	public function isReviewable( $flags = 0 ) {
		if ( !FlaggedRevs::inReviewNamespace( $this->getTitle() ) ) {
			return false;
		}
		# Check if flagging is disabled for this page via config
		if ( FlaggedRevs::useOnlyIfProtected() ) {
			$config = $this->getStabilitySettings( $flags ); // page configuration
			return (bool)$config['override']; // stable is default or flagging disabled
		}
		return true;
	}

	/**
	* Is this page in patrollable?
	* @param int $flags, FR_MASTER
	* @return bool
	*/
	public function isPatrollable( $flags = 0 ) {
		if ( !FlaggedRevs::inPatrolNamespace( $this->getTitle() ) ) {
			return false;
		}
		return !$this->isReviewable( $flags ); // pages that are reviewable are not patrollable
	}

	/**
	 * Get the stable revision ID
	 * @param int $flags
	 * @return int
	 */
	public function getStable( $flags = 0 ) {
		$srev = $this->getStableRev( $flags );
		return $srev ? $srev->getRevId() : 0;
	}

	/**
	 * Get the stable revision
	 * @param int $flags
	 * @return mixed (FlaggedRevision/null)
	 */
	public function getStableRev( $flags = 0 ) {
		# Cached results available?
		if ( $this->stableRev === null || ( $flags & FR_MASTER ) ) {
			$this->loadStableRevAndConfig();
		}
		if ( $this->stableRev ) {
			return $this->stableRev;
		}
		return null; // false => null
	}

	/**
	 * Get visiblity restrictions on page
	 * @param int $flags, FR_MASTER
	 * @return array (select,override)
	 */
	public function getStabilitySettings( $flags = 0 ) {
		if ( !( $flags & FR_MASTER ) && $this->pageConfig !== null ) {
			return $this->pageConfig; // use process cache
		}
		$this->loadStableRevAndConfig();
		return $this->pageConfig;
	}

	/**
	 * Get a DB row of the stable version and page config of a title.
	 * @param Title $title, page title
	 * @param int $flags FR_MASTER
	 */
	protected function loadStableRevAndConfig( $flags = 0 ) {
		$this->stableRev = false; // false => "found nothing"
		$this->pageConfig = FlaggedRevs::getDefaultVisibilitySettings(); // default
		if ( !FlaggedRevs::inReviewNamespace( $this->getTitle() ) ) {
			return; // short-circuit
		}
		# User master/slave as appropriate...
		$db = ( $flags & FR_MASTER ) ?
			wfGetDB( DB_MASTER ) : wfGetDB( DB_SLAVE );
		$row = $db->selectRow(
			array( 'page', 'flaggedpages', 'flaggedrevs', 'flaggedpage_config' ),
			array_merge( FlaggedRevision::selectFields(),
				array( 'fpc_override', 'fpc_level', 'fpc_expiry' ) ),
			array( 'page_id' => $this->getID() ),
			__METHOD__,
			array(),
			array(
				'flaggedpages' => array( 'LEFT JOIN', 'fp_page_id = page_id' ),
				'flaggedrevs' => array( 'LEFT JOIN',
					'fr_page_id = fp_page_id AND fr_rev_id = fp_stable' ),
				'flaggedpage_config' => array( 'LEFT JOIN', 'fpc_page_id = page_id' ) )
		);
		if ( !$row ) {
			return; // no page found at all
		}
		if ( $row->fpc_override !== null ) { // page config row found
			$this->pageConfig = FlaggedRevs::getVisibilitySettingsFromRow( $row );
		}
		if ( $row->fr_rev_id !== null ) { // stable rev row found		
			// Page may not reviewable, which implies no stable version
			if ( !FlaggedRevs::useOnlyIfProtected() || $this->pageConfig['override']  ) {
				$this->stableRev = new FlaggedRevision( $row );
			}
		}
	}
}