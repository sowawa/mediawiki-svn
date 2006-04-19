<?php
/**
 * Class for creating namespace objects
 * and static namespace-related functions.
 *
 * Definitions:
 *
 * Namespace: 
 *  A prefix of the form "Abc:" represented on the database level
 *  with an integer number. Using namespaces, pages with the same title
 *  can exist in different contexts; for example, a page 
 *  "User:Stephen King" could coexist with "Stephen King". (No prefix
 *  refers to the "article namespace", or to the target namespace; see
 *  below.
 *
 * Default name, synonyms:
 *  Any namespace can have an arbitrary number of valid names. This is
 *  to ensure that, for example, "Image:", "File:", "Video:" etc. can
 *  all be used to access uploaded files. The default name is the name
 *  which all these valid names ''redirect to'', that is, if you access
 *  index.php?title=Image:Bear.jpg, it will redirect you to
 *  index.php?Title=File:Bear.jpg, because "File:" is the default name
 *  for this namespace. Any name which is not the default name will be
 *  referred to herein as a synyonm.
 *
 * Canonical namespace name:
 *  Due to the fact that namespace names are translated into many 
 *  languages, and customized to many wikis, it is desirable to have
 *  a reliable way by which a namespace with a particular ''meaning''
 *  can be accessed on any wiki installation. For example, you may
 *  want to access a user page on the Serbian Wikipedia, but you don't
 *  know the equivalent of "User:" in Serbian. During installation,
 *  some namespace names are flagged as canonical. These cannot
 *  be changed using the namespace manager, and can be expected
 *  to work on all wikis. A canonical namespace name does not have
 *  to be the default name, e.g., "User:" would still redirect to
 *  the Serbian equivalent.
 *
 * Namespace target:
 *  Depending on the nature of your project, it may be desirable
 *  that all [[unprefixed links]] within a namespace point to a
 *  particular destination (another namespace or InterWiki). An
 *  example is Wikibooks, where namespaces are used to separate
 *  different types of books, and you would like all links within
 *  a book to point to other chapters in the book unless otherwise
 *  specified. Another example is Wikinews, where links from the
 *  main namespace typically point to Wikipedia.
 *
 *  Quite simply put, if this target is set for a namespace, all
 *  links without a valid namespace or InterWiki prefix in pages 
 *  in that namespace are treated as if they were written:
 *  [[LINK TARGET:Actual title|Actual title]].
 * 
 * How to modify a namespace and its properties:
 *  Do NOT alter the $wgNamespaces object. Instead, create a clone() 
 *  of the object and change its properties. To delete a name,
 *  set it to null (not ''). Call its save() method. It will be 
 *  compared to the matching object in $wgNamespaces and modified 
 *  if posible. 
 *
 * How to create a new namespace:
 *  Create a new namespace object with the desired properties. Set
 *  the index to NULL. Call its save() method.
 *
 * @package MediaWiki
 */

# Namespace name operations
# used by save() function
define('NS_NAME_MODIFY',1);
define('NS_NAME_DELETE',2);
define('NS_NAME_ADD',3);

/**
 * Definitions of the NS_ constants are in Defines.php
 * @access private
 */
/*
$wgCanonicalNamespaceNames = array(
	NS_MEDIA            => 'Media',
	NS_SPECIAL          => 'Special',
	NS_TALK	            => 'Talk',
	NS_USER             => 'User',
	NS_USER_TALK        => 'User_talk',
	NS_PROJECT          => 'Project',
	NS_PROJECT_TALK     => 'Project_talk',
	NS_IMAGE            => 'Image',
	NS_IMAGE_TALK       => 'Image_talk',
	NS_MEDIAWIKI        => 'MediaWiki',
	NS_MEDIAWIKI_TALK   => 'MediaWiki_talk',
	NS_TEMPLATE         => 'Template',
	NS_TEMPLATE_TALK    => 'Template_talk',
	NS_HELP             => 'Help',
	NS_HELP_TALK        => 'Help_talk',
	NS_CATEGORY	        => 'Category',
	NS_CATEGORY_TALK    => 'Category_talk',
);

if( is_array( $wgExtraNamespaces ) ) {
	$wgCanonicalNamespaceNames = $wgCanonicalNamespaceNames + $wgExtraNamespaces;
}
*/

/**
 * This class defines the namespace objects which are stored in
 * $wgNamespaces.
 *
 * @package MediaWiki
 */
class Namespace {

	# Fundamentals
	var 
	$index,               # Database-level index of this namespace
	$systemType;          # If non-empty, string constant that defines 
	                      # a system namespace.

	# Options
	var 
	$isMovable,           # Can pages in this namespace be moved?
	$parentIndex,         # If this is a talk page, what is its mother
	                      # namespace? Otherwise NULL.
	$allowsSubpages,      # Are subpages of the form [[Namespace:Foo/Bar]]
	                      # valid?
	$isSearchedByDefault, # Are pages in this namespace searched by default?
	$isHidden,            # Should this namespace be hidden from the UI?
	$target;	      # Treat unprefixed links as prefixed with "$target:"?

	# Associated names
	var 
	$names = array(),     # Contains all the namespace names	
	$defaultNameIndex,    # Index of the name all other names redirect to?
	$canonicalNameIndex;  # Index of the name that's valid everywhere

	/** 
	 * Constructor with reasonable defaults.
	 */
	function Namespace() {
	
		$this->setIndex(NULL);
		$this->setMovable();
		$this->setParentIndex(NULL);
		$this->setSubpages(false);
		$this->setSearchedByDefault(false);
		$this->setTarget(NULL);
		$this->setHidden(false);	
	}	

	/**
	 * @return index to the correct $wgNamespaces object
	 *  or database record for this namespace.
	 */
	function getIndex() {
		return $this->index;
	}

	/**
	 * @param $index New index for this namespace. 
	 *  Generally only used during creation.
	 */
	function setIndex($index) {
		$this->index=$index;
	}

	/**
	 * @return String like NS_MAIN for identifying
	 *  system namespaces (see Defines.php).
	 */
	function getSystemType() {
		return $this->systemType;	
	}
	
	/**
	 * Set the system type for this namespace.
	 * @param  string Constant name - needs to exist
	 *  in Defines.php.
	 * @return bool depending on success
	 *
	 */
	function setSystemType($type) {
		$typeString=(string)$type;
		if(defined($typeString)) {
			$this->systemType=$typeString;
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Is this a system namsepace?
	 * @return bool
	 */
	function isSystemNamespace() {
		$sys=$this->getSystemType();
		return !empty($sys);
	}
	
	/**
	 * Check if pages in this namespace can be moved.
	 * Special pages, images and categories cannot be moved
	 * @return bool
	 */
	function isMovable() {
		return $this->isMovable;	
	}

	/**
	 * Can pages in this namespace be moved?
	 * @param bool
	 */
	function setMovable($movable=true) {
		$this->isMovable=(bool)$movable;
	}

	/**
	 * Are pages from this namespace hidden in lists?
	 * @return bool
	 */	
	function isHidden() {
		return $this->isHidden;
	}

	/**
	 * Should pages from this namespace be hidden in lists?
	 * @param bool
	 */
	function setHidden($hidden=true) {
		$this->isHidden=(bool)$hidden;	
	}
	
	/**
	 * @return int Index of the parent namespace to a 
	 *   child namespace (talk or otherwise), NULL if none
	 */
	function getParentIndex() {
		return $this->parentIndex;
	}

	/**
	 * @return int Same as getParentIndex(), but returns
	 *    this namespace's index if no parent namespace exists.
	 */
	function getSubject() {
		if($this->isTalk()) {
			return $this->getParentIndex();
		} else {
			return $this->getIndex();
		}
	}
	
	/**
	 * Set parent namespace
	 * @param int
	 */
	function setParentIndex($index) {
		$this->parentIndex=$index;
	}

	/**
	 * Does this namespace have a parent namespace?
	 * @return bool
	 */	
	function hasParent() {
		return ($this->getParentIndex()!=NULL);
	}

	/**
	 * Synonym for hasParent(), but might be logically
	 * different in the near future, if parent/child
	 * relationships go beyond talk pages.
	 * @return bool
	 */
	function isTalk() {
		return $this->hasParent();	
	}

	/**
	 * Check if the given namespace is not a talk page
	 * @return bool
	 */
	function isMain( $index ) {
		return !$this->isTalk();
	}

	/**
	 * Is this a "special" namespace (Media:, Special:)?
	 * Special namespaces cannot contain any pages.
	 * @return bool
	 */	
	function isSpecial() {
		return($this->getIndex()<NS_MAIN);
	}

	/**
	 * Is content in this namespace searched by default?
	 * @return bool
	 */
	function isSearchedByDefault() {
		return $this->isSearchedByDefault;		
	}

	/**
	 * Should this namespace be searched by default?
	 * @param bool
	 */
	function setSearchedByDefault($search=true) {
		$this->isSearchedByDefault=(bool)$search;
	}
	
	/**
	 Get the index of the discussion namespace associated
	 with a namespace. If this _is_ a discussion namespace, 
	 return its index.

	 @return int	 

	 TODO: support multiple discussion namespaces,
	 so that things like a Review: namespace
	 become possible in parallel to normal talk
	 pages. 
	*/
	function getTalk() {
		global $wgNamespaces;		
		/* This behavior is expected by Title.php! */
		if($this->isTalk()) return $this->getIndex();
		foreach($wgNamespaces as $ns) {
			if($ns->hasParent() && $ns->parentIndex==$this->index) {
				return $ns->index;
			}
		}
		return null;
	}
	
	/**
	 * Return the default prefix for unprefixed links
	 * from this namespace.
	 * @return string
	 */
	function getTarget() {
		return $this->target;	
	}

	/**
	 * Set the default prefix for unprefixed links, e.g.
	 * "User:" (local namespace prefix) or "MeatBall:"
	 * (InterWiki prefix).
	 *
	 * @param string
	 */
	function setTarget($target) {
		$this->target=(string)$target;
	}

	/**
	 * Does this namespace allow [[/subpages]]?
	 * @return bool
	 */
	function allowsSubpages() {
		return $this->allowsSubpages;
	
	}

	/**
	 * Should this namespace allow [[/subpages]]?
	 * @param bool
	 */
	function setSubpages($subpages=true) {
		$this->allowsSubpages=(bool)$subpages;
	}	

	/**
	 * Return the default name for this namespace, if any.
	 * The default name is the one all others redirect to.
	 *
	 * @return string	
	 */
	function getDefaultName() {	
		if(isset($this->defaultNameIndex) && array_key_exists($this->defaultNameIndex,$this->names)) {
			return $this->names[$this->defaultNameIndex];	
		} else {
			return null;
		}
	}

	/** 
	  Used when a default name is deleted, to assign a new one
	  @return int - index to the first non-empty name of this namespace
			null if there are no non-empty names.
        */
	function getNewDefaultNameIndex() {
		foreach($this->names as $nsi=>$name) {
			if(!empty($name)) {
				return $nsi;
			}
		}
		return null;
	}

	/**
	 * Among the names of this namespace, which one should
	 * be set as the default name?
	 * @param int Key to the names array
	 */	
	function setDefaultNameIndex($index) {
		$this->defaultNameIndex=$index;
	}

	/**
	 * Among the names of this namespace, which one
	 * should be "canonical" (i.e. not editable, and
	 * assumed to exist under this name in other
	 * wikis)?
	 * @param int Key to the names array
	 */
	function setCanonicalNameIndex($index) {
		$this->canonicalNameIndex=$index;
	}
	
	/**
	 * Add a name to the list of names for this
	 * namespace.
	 * @return index of the newly added name,
	 *  or NULL if hte name is not valid.	
	 */
	function addName($name) {
		$index=count($this->names);
		if($this->isValidName($name)) {
			$name=strtr($name, ' ','_');
			$this->names[$index]=$name;
			return $index;
		} else {
			return NULL;
		}
	}

	/**
	 * Return the key in the name list for a given
	 * name.	
	 * @return int Matching key or NULL
	 */
	function getNameIndexForName($findname) {
		foreach($this->names as $nsi=>$name) {
			if($name==$findname) {
				return $nsi;
			}
		}
		return null;
	}

	/**
	 * Change a namespace name.
	 * @param $oldname The old name
	 * @param $newname The new name
	 * @param $checkvalid Does the new name have to be
	 *  valid? (is checked by save() in any case)
	 */
	function setName($oldname,$newname,$checkvalid=true) {
		if($checkvalid && !$this->isValidName($newname)) {
			return NULL;
		}
		$newname=strtr($newname, ' ','_');
		$nsi=$this->getNameIndexForName($oldname);
		if(!is_null($nsi)) {
			$this->names[$nsi]=$newname;
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Is this a valid namespace name? Valid characters
	 * are defined in the NS_CHAR constant.
	 * @return bool
	 */
	function isValidName($name) {
		# Consist only of (at least one) valid char(s)
		if(preg_match("/^".NS_CHAR."+$/",$name)) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * How many pages does this namespace contain?
	 * @return The number of pages
	*/
	function countPages() {
		$dbs =& wfGetDB(DB_SLAVE);
		return $dbs->selectField(
			'page',
			'count(*)',
			array('page_namespace'=>$this->getIndex())
		);		
	}

	/**
	 * Get the default name with spaces instead of 
	 * underscores.
	 * @return string
	 */
	function getFormattedDefaultName() {
		$ns=$this->getDefaultName();
		return strtr($ns, '_',' ');
	}
	/**
	 * @return the key in the names array for the default
	 * name of this namespace
	 */
	function getDefaultNameIndex() {
		return $this->defaultNameIndex;
	}

	/**
	 * @return the key in the names array for the 
	 * canonical name of this namespace
	 */
	function getCanonicalNameIndex() {
		return $this->canonicalNameIndex;
	}

	/**
	 * @return The canonical name associated with
	 *  this namespace, or NULL
	 */
	function getCanonicalName() {
		if(!is_null($this->getCanonicalNameIndex())) {
			return $this->names[$this->getCanonicalNameIndex()];
		} else {
			return null;
		}
	}

	/**
	 * @param int Key to the names array of the name
	 *  which should be removed.
	 */
	function removeNameByIndex($index) {
		if(array_key_exists($index,$this->names)) {
			unset($this->names[$index]);
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Kill them all! Well, all the ones in this namespace
	 * object. And only if we save().
	 */
	function removeAllNames() {
		$this->names=array();
		return true;
	}
		
	/**
	 * Serialize this namespace to the database.
	 * No part of the operation will be completed
	 * unless it cannot be fully done.
	 *
	 * If the namespace index is NULL, a new namespace 
	 * will be created.
	 *
	 * @param boolean $testSave
	 *   If this is set to true, no actual changes
	 *   will be made. This is useful for testing
	 *   transactions on a number of namespaces,
	 *   and not completing any of them unless 
	 *   all of them will succeed.
	 * @param boolean $overrideInterwiki
	 *   If a namespace name overlaps with an Interwiki
	 *   prefix, should it be created anyway?
	 *  
	 * @return array()
	 *   An array that describes the results of the
	 *   operation, as follows:
	 *
	 *       array(
	 *        NS_RESULT=>
	 *          NS_MODIFIED | NS_CREATED | NS_NAME_ISSUES
	 *          NS_MISSING | NS_IDENTICAL
	 *        NS_SAVE_ID=>namespace ID or NULL
	 *        NS_ILLEGAL_NAMES=>array(names)
	 *        NS_DUPLICATE_NAMES=>array(names)
	 *        NS_INTERWIKI_NAMES=>array(names)
	 *        NS_PREFIX_NAMES=>array(names)
	 *       )
	 *
	 *  NS_RESULT can be:
	 * 
	 *  NS_MODIFIED    - existing namespace successfully changed
	 *  NS_CREATED     - new namespace successfully created
	 *  NS_IDENTICAL   - the version in the database is
	 *                   identical with the one to be saved
	 *  NS_NAME_ISSUES - operation failed due to issues with
	 *                   name changes
	 *  NS_MISSING     - namespace with $this->index not found 
	 *                   in DB, cannot be altered.
	 *
	 *  In order to show useful result information, we record
	 *  exactly which names have been added, removed or changed
	 *  (NS_NAMES_ADDED, NS_NAMES_MODIFIED, NS_NAMES_DELETED).
	 *  If the save fails, we record which name change(s) caused
	 *  the problem:
	 *
	 *      NS_ILLEGAL_NAMES
	 *      names which contain illegal characters
	 *
	 *      NS_DUPLICATE_NAMES
	 *      names which already exist
	 *
	 *      NS_INTERWIKI_NAMES 
	 *      names which are also used as Interwiki prefixes
	 *      (cf. $overrideInterwiki parameter)
	 *
	 *      NS_PREFIX_NAMES
	 *      names which are used as hardcoded title prefixes
	 *
	 *  How this function works:
	 *  ------------------------
	 *  Check if the namespace has a valid ID (not null)
	 *
	 *     NO: We will try to create it.
	 *
	 *      1.1) Obtain an index from $wgNamespaces
	 *      1.2) Make a list of the names that are going
	 *           to be added.
	 *      1.3) Proceed to 2.2)
	 * 
	 *     YES: We will try to modify it.
	 *
	 *      2.1) Compare this object with the corresponding
	 *           one in $wgNamespaces and make a list of
	 *           the names that are going to be removed
	 *           (set to NULL), changed, or added.
	 *      2.2) Verify whether all namespace name operations
	 *           are possible. If not, return the appropriate
	 *           error codes.
	 *      2.3) If all operations are possible, update or
	 *           update the namespace and return the appropriate
	 *           result array.
	 */
	function save($overrideInterwiki=false,
		      $testSave=false) {
	
		global $wgNamespaces;
		
		$fname='Namespace::save';
		$rv=array(
		 NS_RESULT=>null,
		 NS_ILLEGAL_NAMES=>array(),
		 NS_DUPLICATE_NAMES=>array(),
		 NS_INTERWIKI_NAMES=>array(),
		 NS_PREFIX_NAMES=>array()
		);
		$nameOperations=array();
		$dbs =& wfGetDB( DB_SLAVE );
		$index=$this->getIndex();
		if(is_null($index)) {
			$create = true;
			end($wgNamespaces);
			$index=$wgNamespaces[key($wgNamespaces)]->getIndex()+1;
			$this->setIndex($index);
			foreach($this->names as $name) {
				$nameOperations[$name]=NS_NAME_ADD;
			}
		} else {
			$create = false;
			# Does this namespace exist?			
			if(!array_key_exists($index,$wgNamespaces)) {
				$rv[NS_RESULT]=NS_MISSING;
				return $rv;
			}			
			# Has anything actually been changed?
			if($this===$wgNamespaces[$this->getIndex()]) {
				$rv[NS_RESULT]=NS_IDENTICAL;
				return $rv;			
			}
			$oldcount=count($wgNamespaces[$index]->names);
			$newcount=count($this->names);
			for($i=0;$i<$oldcount || $i<$newcount;$i++) {
				$existsOld=array_key_exists($i, $wgNamespaces[$index]->names);
				$existsNew=array_key_exists($i, $this->names);
				if($existsOld && $existsNew) {
					if(strcasecmp($wgNamespaces[$index]->names[$i], $this->names[$i])!=0) {
						$nameOperations[$this->names[$i]]=NS_NAME_MODIFY;
					}
				} elseif($existsOld && !$existsNew) {
					$nameOperations[$wgNamespaces[$index]->names[$i]]=NS_NAME_DELETE;
				} elseif(!$existsOld && $existsNew) {
					$nameOperations[$this->names[$i]]=NS_NAME_ADD;
				}
			}
			
		}

		# Are there any name operations to do? If so, check 
		# whether they are possible before doing anything else.
		foreach($nameOperations as $name=>$operation) {
			if($operation==NS_NAME_ADD || $operation==NS_NAME_MODIFY) {
				
				# Illegal characters?
				# This should never happen if the setters
				# are used.
				if(!$this->isValidName($name)) {
					$rv[NS_RESULT]=NS_NAME_ISSUES;
					$rv[NS_ILLEGAL_NAMES][]=$name;
				}

				# Duplicate names
				foreach($wgNamespaces as $exns) {
					$dupes=array_keys($exns->names,$name);
					if(count($dupes)) {
						$rv[NS_RESULT] = NS_NAME_ISSUES;
						$rv[NS_DUPLICATE_NAMES][]=$name;
					}
				}

				# Interwiki
				if(Title::getInterwikiLink( $name)) {
					$rv[NS_RESULT]=NS_NAME_ISSUES;
					$rv[NS_INTERWIKI_NAMES][]=$name;
				}

				# Pseudo-namespaces (title prefixes)
				$likename = str_replace( '_', '\\_', $name);
				$likename = str_replace( '%', '\\%', $likename);
				$match = $dbs->addQuotes($likename.":%");
				$res = $dbs->select(
					'page',
					array('page_title'),
					array('page_namespace'=>0,
					      'page_title LIKE '.$match,
					      ),
					$fname,
					array('LIMIT'=>1)
				);
				if($dbs->numRows($res) > 0) {
					$rv[NS_RESULT]=NS_NAME_ISSUES;
					$rv[NS_PREFIX_NAMES][]=$name;
				}
				$dbs->freeResult($res);
			} 
		}

		# If there are problems, return the array
		if($rv[NS_RESULT]==NS_NAME_ISSUES) {
			return $rv;
		}

		$dbm =& wfGetDB(DB_MASTER);		
		$nsasdb=array(
			'ns_id'=>$index,
			'ns_system'=>$this->getSystemType(),
			'ns_subpages'=>$this->allowsSubpages(),
			'ns_search_default'=>$this->isSearchedByDefault(),
			'ns_target'=>$this->getTarget(),
			'ns_parent'=>$this->getParentIndex(),
			'ns_hidden'=>$this->isHidden()
			);		
		if($create) {
			# testSave checks should always be placed
			# right before a transaction that alters
			# the database or memory state.
			if(!$testSave) {
				$dbm->insert(
					'namespace',
					$nsasdb,
					$fname,
					array()
					);
			}
		} 
		foreach($nameOperations as $name=>$operation) {
			if($operation==NS_NAME_ADD) {
				$isDefault = ($name==$this->getDefaultName());
				$isCanonical = ($name==$this->getCanonicalName());
				if(!$testSave) {
					$dbm->insert(
						'namespace_names',
						array(
						'ns_id'=>$this->getIndex(),
						'ns_name'=>$name,
						'ns_default'=>$isDefault,
						'ns_canonical'=>$isCanonical
						),
						$fname,
						array()
					);
				}
			} elseif($operation==NS_NAME_MODIFY) {				$oldname = $wgNamespaces[$index]->names[$this->getNameIndexForName($name)];
				if(!$testSave) {
					$dbm->update(
						'namespace_names',
						array( /* SET */
						'ns_name'=>$name,
						),
						array(
						'ns_name'=>$oldname),
					        $fname);
				}
			} elseif($operation==NS_NAME_DELETE) {
				$dbm->delete(
					'namespace_names',
					array('ns_name'=>$name),
					'*');
			}
		}
		if($create) {
			$rv[NS_RESULT]=NS_CREATED; 
			
			# If this was just a test for a new
			# namespace, reset the index to NULL so
			# it will be created for real
			# if save() is called on the same object.
			if($testSave) {
				$this->setIndex(NULL);
			}
		} else {
			# Set canonical and default names.
			# This needs to happen after other name operations
			# because we can't operate on the new names until
			# they exist. :-)
			$oldDefaultName=$wgNamespaces[$index]->getDefaultName();
			$newDefaultName=$this->getDefaultName();

			# Note that canonical names normally should NEVER change,
			# but we provide the functionality just in case it's needed
			# by some maintenance scripts.
			$oldCanonical=$wgNamespaces[$index]->getCanonicalName();
			$newCanonical=$this->getCanonicalName();
			if(!$testSave) {
				$dbm->update(
					'namespace',
					$nsasdb, /* SET */
					array('ns_id'=>$index),
					$fname
				);
				if($oldDefaultName != $newDefaultName) {
					$dbm->update(
						'namespace_names',
						array('ns_default'=>0), /* SET */
						array('ns_name'=>$oldDefaultName),
						$fname
					);
					$dbm->update(
						'namespace_names',
						array('ns_default'=>1), /* SET */
						array('ns_name'=>$newDefaultName),
						$fname
					);					
				}
				if($oldCanonical != $newCanonical) {
					$dbm->update(
						'namespace_names',
						array('ns_canonical'=>0), /* SET */
						array('ns_name'=>$oldCanonical),
						$fname
					);
					$dbm->update(
						'namespace_names',
						array('ns_canonical'=>1), /* SET */
						array('ns_name'=>$newCanonical),
						$fname
					);					
				}
			}

			$rv[NS_RESULT]=NS_MODIFIED;
		}
		$rv[NS_SAVE_ID]=$index;

		# Note that it may be desirable to call Namespace::load()
		# in addition to this since the name (not namespace) indexes in
		# the database can be different from the one in the array.
		if(!$testSave) {
			$wgNamespaces[$index]=$this;
		}
		return $rv;
	}

	/**
 	 * A simple shortcut to save() with the right parameters
	 * to run in test mode. See save() documentation.
	 */
	function testSave($overrideInterwiki=false) {
		return $this->save($overrideInterwiki,true);
	}
	
	/**
	 * Delete a namespace from the database and the namespace array.
	 * Only use this on clone()s of objects in the $wgNamespaces array.
	 * This function allows deleting system namespaces if explicitly
	 * specified; this should however not be possible through the 
	 * user interface.
	 *
	 * @param $deleteSystem bool Override system namespace protection
	 */
	function deleteNamespace($deleteSystem=false) {
		global $wgNamespaces;

		if(is_null($this->getIndex())) {
			return array(NS_RESULT=>NS_MISSING);
		}
		
		if($this->isSystemNamespace() && !$deleteSystem) {
			return array(NS_RESULT=>NS_PROTECTED);
		}
		if($this->countPages()>0) {
			return array(NS_RESULT=>NS_HAS_PAGES);
		}
		# Remove all names
		$this->removeAllNames();
		# Try saving
		$trv=$this->testSave();
		if($trv[NS_RESULT]!=NS_MODIFIED) {
			return $trv;
		}

		# As we just have to delete everything, we go
		# right into the database if the test succeeds.
		$dbm =& wfGetDB( DB_MASTER );
		$dbm->delete(
			'namespace',
			array('ns_id'=>$this->getIndex()),
			'*');		
		$dbm->delete(
			'namespace_names',
			array('ns_id'=>$this->getIndex()),
			'*');
		# Don't forget it's still in memory.
		unset($wgNamespaces[$this->getIndex()]);
		return array(NS_RESULT=>NS_DELETED);
	}

	/* ---------- all static functions below  ----------- */

	/**
	 * For _any_ name (among all namespaces), return
	 * the index of the namespace to which it belongs.
	 *
	 * @param string Name to search for
	 * @return int Index of the namespace associated 
	 *  with this name (or NULL)
	 * @static
	 */
	function getIndexForName ( $name ) {
		global $wgNamespaces;
		foreach ($wgNamespaces as $ns) {
			foreach($ns->names as $synonym) {
				if(strcasecmp($synonym,$name)==0) {
					return $ns->getIndex();
				}
			}
		}
		return NULL;
	}

	/**
	 * Return the default name for any namespace name
	 * given as a parameter, even if it is the default
	 * name already. Searches all namespaces.
	 *
	 * @param string Any namespace name
	 * @return string The name (may be identical)
	 * @static
         */	
	function getDefaultNameForName ( $name ) {
                global $wgNamespaces;
                $index=Namespace::getIndexForName($name);
                if(!is_null($index)) {
                        return $wgNamespaces[$index]->getDefaultName();
                } else {
                        return null;
                }
	}
	
	/**
	 * Return an array of the default names of all
	 * namespaces. Resets array pointer of $wgNamespaces.
	 * @param $includeHidden Should hidden namespaces
	 *  be part of the array?
	 * @return array
	 * @static
	 */
	function &getDefaultNamespaces($includeHidden=false) {
		global $wgNamespaces;
		$dns=array();
		foreach($wgNamespaces as $ns) {
			if(!$ns->isHidden() || $includeHidden) {
				$dn=$ns->getDefaultName();
				if(!is_null($dn)) {
					$dns[$ns->getIndex()]=$dn;
				} else {
					$dns[$ns->getIndex()]='';
				}
			}
		}		
		return $dns;
	}
       
   /**
	 * A convenience function that returns the same thing as
	 * getDefaultNamespaces() except with the array values changed to ' '
	 * where it found '_', useful for producing output to be displayed
	 * e.g. in <select> forms.
	 *
	 * @static
	 * @param $includeHidden
	 * @return array
	 */
	function getFormattedDefaultNamespaces($includeHidden=false) {
		$ns = Namespace::getDefaultNamespaces($includeHidden);
		foreach($ns as $k => $v) {
			$ns[$k] = strtr($v, '_', ' ');
		}
		return $ns;
	}

	/**
	 * Load or reload namespace definitions from the database
	 * into a global array.
	 *
	 * @param $purgeCache If definitions exist in memory, should
	 *   they be reloaded anyway?
	 *
	 * @static
	 */
	function load($purgeCache=false) {

		global $wgNamespaces, $wgMemc, $wgDBname;
		$key="$wgDBname:namespaces:list";
		if(!$purgeCache) {
			$fromMemory = $wgMemc->get($key);
			if(is_array($fromMemory)) {
				# Cached definitions found
				$wgNamespaces=$fromMemory;
				return true;
			}
		}
		$wgNamespaces = array();
		$dbr =& wfGetDB( DB_SLAVE );
		$res = $dbr->select( 'namespace',
			array('ns_id','ns_search_default','ns_subpages', 'ns_parent', 'ns_target', 'ns_system', 'ns_hidden'),
			array(),
			'Setup',
			array('ORDER BY'=>'ns_id ASC')
		);
		while( $row = $dbr->fetchObject( $res ) ){	
			# See Namespace.php for documentation on all namespace
			# properties which are accessed below.	
			$id=$row->ns_id;
			$wgNamespaces[$id]=new Namespace();

			# Cannot currently be changed through the UI - is
			# there a need for it to be changeable?
			$wgNamespaces[$id]->setMovable(
				$id < NS_MAIN || $id==NS_IMAGE || 
				$id==NS_CATEGORY ? false : true );
			$wgNamespaces[$id]->setIndex($id);
			$wgNamespaces[$id]->setSystemType($row->ns_system);
			$wgNamespaces[$id]->setSearchedByDefault($row->ns_search_default);
			$wgNamespaces[$id]->setSubpages($row->ns_subpages);
			$wgNamespaces[$id]->setHidden($row->ns_hidden);
			$wgNamespaces[$id]->setTarget($row->ns_target);
			$wgNamespaces[$id]->setParentIndex($row->ns_parent);
			$res2 = $dbr->select( 'namespace_names', array('ns_name','ns_default,ns_canonical'),
					array('ns_id = '. $row->ns_id),
					'Setup', array('order by'=>'ns_default desc,ns_canonical desc,ns_id asc'));
			
			# Add the list of valid names
			while($row2 = $dbr->fetchObject($res2) ) {
				$nsi=$wgNamespaces[$id]->addName($row2->ns_name);
				if($row2->ns_default) {
					$wgNamespaces[$id]->setDefaultNameIndex($nsi);
				}
				if($row2->ns_canonical) {
					$wgNamespaces[$id]->setCanonicalNameIndex($nsi);
				}
			}
		}
		$dbr->freeResult( $res );
		$wgMemc->set($key,$wgNamespaces);
	}

	/**
	* Convert a "pseudonamespace" (just prefixed titles) into a real
	* one.
	*
	* @param string $prefix - The pseudonamespace prefix string
	* @param Namespace $target - the target namespace object
	* @param Namespace $source - the source namespace object (should
	*  usually be $wgNamespaces[NS_MAIN] or ..[NS_TALK]). This is the
	*  one we expect the prefixed titles to be stored in.
	* @param boolean $merge - Is it acceptable to merge into a namespace
	*  which does already contain pages? This is potentially irreversible!
	*
	* Why pass around Namespace objects? This saves us some validation,
	* since the indexes can be assumed to exist.
	*
	* @static
	*/
	function convertPseudonamespace($prefix,$target,$source,$merge=false) {
		$dbm =& wfGetDB(DB_MASTER);
		$dbs =& wfGetDB(DB_SLAVE);
		$fname="Namespace::convertPseudonamespace";
		$targetcount=$target->countPages();
		if(!$merge && $targetcount>0) {
			return array(NS_RESULT=>NS_NON_EMPTY);
		}
		$table = $dbs->tableName( 'page' );
		$eprefix     = $dbs->strencode( $prefix );
		$likeprefix = str_replace( '_', '\\_', $eprefix);
		$targetid=$target->getIndex();
		$sourceid=$source->getIndex();
		
		$sql = "SELECT page_id AS id,
		               page_title AS oldtitle,
		               TRIM(LEADING '$eprefix:' FROM page_title) AS title
		          FROM {$table}
		         WHERE page_namespace=$sourceid
		           AND page_title LIKE '$likeprefix:%'";
		
		$result = $dbs->query( $sql, $fname );
		$set = array();
		while( $row = $dbs->fetchObject( $result ) ) {
			$set[] = $row;
		}
		$dbs->freeResult( $result );
		
		if(!count($set)) {
			return array(NS_RESULT=>NS_PSEUDO_NOT_FOUND);
		} else {
			# Check duplicates
			if($targetcount) {
				$dupeTitles=array();
				foreach($set as $row) {
					$pageExists=$dbs->selectField(
						'page',
						'count(*)',
						array('page_title'=>$row->title,
						      'page_namespace'=>$targetid)
					);
					if($pageExists) {
						$dupeTitles[]=$row->title;
					}
				}
				if(count($dupeTitles)) {
					return(array(
						NS_RESULT => NS_DUPLICATE_TITLES,
						NS_DUPLICATE_TITLE_LIST => $dupeTitles));

				}
			}		
			foreach($set as $row) {
				$dbm->update( $table,
					array(
						"page_namespace" => $targetid,
						"page_title"     => $row->title,
					),
					array(
						"page_namespace" => $sourceid,
						"page_title"     => $row->oldtitle,
					),
					$fname );
			}
		}
		return array(NS_RESULT=>NS_PSEUDO_CONVERTED);

	}

}
		
?>
