<?php
/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 * 
 * @author Trevor Parscal
 */

/**
 * Interface for resource loader modules, with name registration and maxage functionality.
 */
abstract class ResourceLoaderModule {
	
	/* Protected Members */
	
	protected $name = null;
	
	/* Methods */
	
	/**
	 * Get this module's name. This is set when the module is registered
	 * with ResourceLoader::register()
	 * @return mixed Name (string) or null if no name was set
	 */
	public function getName() {
		return $this->name;
	}
	
	/**
	 * Set this module's name. This is called by ResourceLodaer::register()
	 * when registering the module. Other code should not call this.
	 * @param $name string Name
	 */
	public function setName( $name ) {
		$this->name = $name;
	}
	
	/**
	 * The maximum number of seconds to cache this module for in the
	 * client-side (browser) cache. Override this only if you have a good
	 * reason not to use $wgResourceLoaderClientMaxage.
	 * @return int Cache maxage in seconds
	 */
	public function getClientMaxage() {
		global $wgResourceLoaderClientMaxage;
		return $wgResourceLoaderClientMaxage;
	}
	
	/**
	 * The maximum number of seconds to cache this module for in the
	 * server-side (Squid / proxy) cache. Override this only if you have a
	 * good reason not to use $wgResourceLoaderServerMaxage.
	 * @return int Cache maxage in seconds
	 */
	public function getServerMaxage() {
		global $wgResourceLoaderServerMaxage;
		return $wgResourceLoaderServerMaxage;
	}
	
	/**
	 * Get all JS for this module for a given language and skin.
	 * Includes all relevant JS except loader scripts.
	 * @param $lang string Language code
	 * @param $skin string Skin name
	 * @param $debug bool Whether to include debug scripts
	 * @return string JS
	 */
	public abstract function getScript( ResourceLoaderContext $context );
	
	/**
	 * Get all CSS for this module for a given skin.
	 * @param $skin string Skin name
	 * @return string CSS
	 */
	public abstract function getStyle( ResourceLoaderContext $context );
	
	/**
	 * Get the messages needed for this module.
	 *
	 * To get a JSON blob with messages, use MessageBlobStore::get()
	 * @return array of message keys. Keys may occur more than once
	 */
	public abstract function getMessages();
	
	/**
	 * Get the loader JS for this module, if set.
	 * @return mixed Loader JS (string) or false if no custom loader set
	 */
	public abstract function getLoaderScript();
	
	/**
	 * Get a list of modules this module depends on.
	 *
	 * Dependency information is taken into account when loading a module
	 * on the client side. When adding a module on the server side,
	 * dependency information is NOT taken into account and YOU are
	 * responsible for adding dependent modules as well. If you don't do
	 * this, the client side loader will send a second request back to the
	 * server to fetch the missing modules, which kind of defeats the
	 * purpose of the resource loader.
	 *
	 * To add dependencies dynamically on the client side, use a custom
	 * loader script, see getLoaderScript()
	 * @return array Array of module names (strings)
	 */
	public abstract function getDependencies();
	
	/**
	 * Get this module's last modification timestamp for a given
	 * combination of language, skin and debug mode flag. This is typically
	 * the highest of each of the relevant components' modification
	 * timestamps. Whenever anything happens that changes the module's
	 * contents for these parameters, the mtime should increase.
	 * @param $lang string Language code
	 * @param $skin string Skin name
	 * @param $debug bool Debug mode flag
	 * @return int UNIX timestamp
	 */
	public abstract function getModifiedTime( ResourceLoaderContext $context );
}

/**
 * Module based on local JS/CSS files. This is the most common type of module.
 */
class ResourceLoaderFileModule extends ResourceLoaderModule {
	
	/* Protected Members */
	
	protected $scripts = array();
	protected $styles = array();
	protected $messages = array();
	protected $dependencies = array();
	protected $debugScripts = array();
	protected $languageScripts = array();
	protected $skinScripts = array();
	protected $skinStyles = array();
	protected $loaders = array();
	protected $parameters = array();
	
	// In-object cache for file dependencies
	protected $fileDeps = array();
	// In-object cache for mtime
	protected $modifiedTime = array();
	
	/* Methods */
	
	/**
	 * Construct a new module from an options array.
	 *
	 * @param $options array Options array. If empty, an empty module will be constructed
	 * 
	 * $options format:
	 * 	array(
	 * 		// Required module options (mutually exclusive)
	 * 		'scripts' => 'dir/script.js' | array( 'dir/script1.js', 'dir/script2.js' ... ),
	 *		
	 * 		// Optional module options
	 * 		'languageScripts' => array(
	 * 			'[lang name]' => 'dir/lang.js' | '[lang name]' => array( 'dir/lang1.js', 'dir/lang2.js' ... )
	 * 			...
	 * 		),
	 * 		'skinScripts' => 'dir/skin.js' | array( 'dir/skin1.js', 'dir/skin2.js' ... ),
	 * 		'debugScripts' => 'dir/debug.js' | array( 'dir/debug1.js', 'dir/debug2.js' ... ),
	 * 
	 * 		// Non-raw module options
	 * 		'dependencies' => 'module' | array( 'module1', 'module2' ... )
	 * 		'loaderScripts' => 'dir/loader.js' | array( 'dir/loader1.js', 'dir/loader2.js' ... ),
	 * 		'styles' => 'dir/file.css' | array( 'dir/file1.css', 'dir/file2.css' ... ),
	 * 		'skinStyles' => array(
	 * 			'[skin name]' => 'dir/skin.css' | '[skin name]' => array( 'dir/skin1.css', 'dir/skin2.css' ... )
	 * 			...
	 * 		),
	 * 		'messages' => array( 'message1', 'message2' ... ),
	 * 	)
	 */
	public function __construct( $options = array() ) {
		foreach ( $options as $option => $value ) {
			switch ( $option ) {
				case 'scripts':
					$this->scripts = (array)$value;
					break;
				case 'styles':
					$this->styles = (array)$value;
					break;
				case 'messages':
					$this->messages = (array)$value;
					break;
				case 'dependencies':
					$this->dependencies = (array)$value;
					break;
				case 'debugScripts':
					$this->debugScripts = (array)$value;
					break;
				case 'languageScripts':
					$this->languageScripts = (array)$value;
					break;
				case 'skinScripts':
					$this->skinScripts = (array)$value;
					break;
				case 'skinStyles':
					$this->skinStyles = (array)$value;
					break;
				case 'loaders':
					$this->loaders = (array)$value;
					break;
			}
		}
	}
	
	/**
	 * Add script files to this module. In order to be valid, a module
	 * must contain at least one script file.
	 * @param $scripts mixed Path to script file (string) or array of paths
	 */
	public function addScripts( $scripts ) {
		$this->scripts = array_merge( $this->scripts, (array)$scripts );
	}
	
	/**
	 * Add style (CSS) files to this module.
	 * @param $styles mixed Path to CSS file (string) or array of paths
	 */
	public function addStyles( $styles ) {
		$this->styles = array_merge( $this->styles, (array)$styles );
	}
	
	/**
	 * Add messages to this module.
	 * @param $messages mixed Message key (string) or array of message keys
	 */
	public function addMessages( $messages ) {
		$this->messages = array_merge( $this->messages, (array)$messages );
	}
	
	/**
	 * Add dependencies. Dependency information is taken into account when
	 * loading a module on the client side. When adding a module on the
	 * server side, dependency information is NOT taken into account and
	 * YOU are responsible for adding dependent modules as well. If you
	 * don't do this, the client side loader will send a second request
	 * back to the server to fetch the missing modules, which kind of
	 * defeats the point of using the resource loader in the first place.
	 *
	 * To add dependencies dynamically on the client side, use a custom
	 * loader (see addLoaders())
	 *
	 * @param $dependencies mixed Module name (string) or array of module names
	 */
	public function addDependencies( $dependencies ) {
		$this->dependencies = array_merge( $this->dependencies, (array)$dependencies );
	}
	
	/**
	 * Add debug scripts to the module. These scripts are only included
	 * in debug mode.
	 * @param $scripts mixed Path to script file (string) or array of paths
	 */
	public function addDebugScripts( $scripts ) {
		$this->debugScripts = array_merge( $this->debugScripts, (array)$scripts );
	}
	
	/**
	 * Add language-specific scripts. These scripts are only included for
	 * a given language.
	 * @param $lang string Language code
	 * @param $scripts mixed Path to script file (string) or array of paths
	 */
	public function addLanguageScripts( $lang, $scripts ) {
		$this->languageScripts = array_merge_recursive(
			$this->languageScripts,
			array( $lang => $scripts )
		);
	}

	/**
	 * Add skin-specific scripts. These scripts are only included for
	 * a given skin.
	 * @param $skin string Skin name, or 'default'
	 * @param $scripts mixed Path to script file (string) or array of paths
	 */
	public function addSkinScripts( $skin, $scripts ) {
		$this->skinScripts = array_merge_recursive(
			$this->skinScripts,
			array( $skin => $scripts )
		);
	}
	
	/**
	 * Add skin-specific CSS. These CSS files are only included for a
	 * given skin. If there are no skin-specific CSS files for a skin,
	 * the files defined for 'default' will be used, if any.
	 * @param $skin string Skin name, or 'default'
	 * @param $scripts mixed Path to CSS file (string) or array of paths
	 */
	public function addSkinStyles( $skin, $scripts ) {
		$this->skinStyles = array_merge_recursive(
			$this->skinStyles,
			array( $skin => $scripts )
		);
	}
	
	/**
	 * Add loader scripts. These scripts are loaded on every page and are
	 * responsible for registering this module using
	 * mediaWiki.loader.register(). If there are no loader scripts defined,
	 * the resource loader will register the module itself.
	 *
	 * Loader scripts are used to determine a module's dependencies
	 * dynamically on the client side (e.g. based on browser type/version).
	 * Note that loader scripts are included on every page, so they should
	 * be lightweight and use mediaWiki.loader.register()'s callback
	 * feature to defer dependency calculation.
	 * @param $scripts mixed Path to script file (string) or array of paths
	 */
	public function addLoaders( $scripts ) {
		$this->loaders = array_merge( $this->loaders, (array)$scripts );
	}
	
	public function getScript( ResourceLoaderContext $context ) {
		$retval = $this->getPrimaryScript() . "\n" .
			$this->getLanguageScript( $context->getLanguage() ) . "\n" .
			$this->getSkinScript( $context->getSkin() );
		if ( $context->getDebug() ) {
			$retval .= $this->getDebugScript();
		}
		return $retval;
	}
	
	public function getStyle( ResourceLoaderContext $context ) {
		$style = $this->getPrimaryStyle() . "\n" . $this->getSkinStyle( $context->getSkin() );
		
		// Extract and store the list of referenced files
		$files = CSSMin::getLocalFileReferences( $style );
		
		// Only store if modified
		if ( $files !== $this->getFileDependencies( $context->getSkin() ) ) {
			$encFiles = FormatJson::encode( $files );
			$dbw = wfGetDb( DB_MASTER );
			$dbw->replace( 'module_deps',
				array( array( 'md_module', 'md_skin' ) ), array(
					'md_module' => $this->getName(),
					'md_skin' => $context->getSkin(),
					'md_deps' => $encFiles,
				)
			);
			
			// Save into memcached
			global $wgMemc;
			$key = wfMemcKey( 'resourceloader', 'module_deps', $this->getName(), $context->getSkin() );
			$wgMemc->set( $key, $encFiles );
		}
		return $style;
	}
	
	public function getMessages() {
		return $this->messages;
	}
	
	public function getDependencies() {
		return $this->dependencies;
	}
	
	public function getLoaderScript() {
		if ( count( $this->loaders ) == 0 ) {
			return false;
		}
		return self::concatScripts( $this->loaders );
	}
	
	/**
	 * Get the last modified timestamp of this module, which is calculated
	 * as the highest last modified timestamp of its constituent files and
	 * the files it depends on (see getFileDependencies()). Only files
	 * relevant to the given language and skin are taken into account, and
	 * files only relevant in debug mode are not taken into account when
	 * debug mode is off.
	 * @param $lang string Language code
	 * @param $skin string Skin name
	 * @param $debug bool Debug mode flag
	 * @return int UNIX timestamp
	 */
	public function getModifiedTime( ResourceLoaderContext $context ) {
		if ( isset( $this->modifiedTime[$context->getHash()] ) ) {
			return $this->modifiedTime[$context->getHash()];
		}
		
		$files = array_merge(
			$this->scripts,
			$this->styles,
			$context->getDebug() ? $this->debugScripts : array(),
			isset( $this->languageScripts[$context->getLanguage()] ) ?
				(array) $this->languageScripts[$context->getLanguage()] : array(),
			(array) self::getSkinFiles( $context->getSkin(), $this->skinScripts ),
			(array) self::getSkinFiles( $context->getSkin(), $this->skinStyles ),
			$this->loaders,
			$this->getFileDependencies( $context->getSkin() )
		);
		$this->modifiedTime[$context->getHash()] = max(
			array_map( 'filemtime', array_map( array( __CLASS__, 'remapFilename' ), $files ) )
		);
		return $this->modifiedTime[$context->getHash()];
	}
	
	/* Protected Members */
	
	/**
	 * Get the primary JS for this module. This is pulled from the
	 * script files added through addScripts()
	 * @return string JS
	 */
	protected function getPrimaryScript() {
		return self::concatScripts( $this->scripts );
	}
	
	/**
	 * Get the primary CSS for this module. This is pulled from the CSS
	 * files added through addStyles()
	 * @return string JS
	 */
	protected function getPrimaryStyle() {
		return self::concatStyles( $this->styles );
	}
	
	/**
	 * Get the debug JS for this module. This is pulled from the script
	 * files added through addDebugScripts()
	 * @return string JS
	 */
	protected function getDebugScript() {
		return self::concatScripts( $this->debugScripts );
	}
	
	/**
	 * Get the language-specific JS for a given language. This is pulled
	 * from the language-specific script files added through addLanguageScripts()
	 * @return string JS
	 */
	protected function getLanguageScript( $lang ) {
		if ( !isset( $this->languageScripts[$lang] ) ) {
			return '';
		}
		return self::concatScripts( $this->languageScripts[$lang] );
	}
	
	/**
	 * Get the skin-specific JS for a given skin. This is pulled from the
	 * skin-specific JS files added through addSkinScripts()
	 * @return string JS
	 */
	protected function getSkinScript( $skin ) {
		return self::concatScripts( self::getSkinFiles( $skin, $this->skinScripts ) );
	}
	
	/**
	 * Get the skin-specific CSS for a given skin. This is pulled from the
	 * skin-specific CSS files added through addSkinStyles()
	 * @return string CSS
	 */
	protected function getSkinStyle( $skin ) {
		return self::concatStyles( self::getSkinFiles( $skin, $this->skinStyles ) );
	}
	
	/**
	 * Helper function to get skin-specific data from an array.
	 * @param $skin string Skin name
	 * @param $map array Map of skin names to arrays
	 * @return $map[$skin] if set and non-empty, or $map['default'] if set, or an empty array
	 */
	protected static function getSkinFiles( $skin, $map ) {
		$retval = array();
		if ( isset( $map[$skin] ) && $map[$skin] ) {
			$retval = $map[$skin];
		} else if ( isset( $map['default'] ) ) {
			$retval = $map['default'];
		}
		return $retval;
	}
	
	/**
	 * Get the files this module depends on indirectly for a given skin.
	 * Currently these are only image files referenced by the module's CSS.
	 * @param $skin string Skin name
	 * @return array of files
	 */
	protected function getFileDependencies( $skin ) {
		// Try in-object cache first
		if ( isset( $this->fileDeps[$skin] ) ) {
			return $this->fileDeps[$skin];
		}
		
		// Now try memcached
		global $wgMemc;
		$key = wfMemcKey( 'resourceloader', 'module_deps', $this->getName(), $skin );
		$deps = $wgMemc->get( $key );
		
		if ( !$deps ) {
			$dbr = wfGetDb( DB_SLAVE );
			$deps = $dbr->selectField( 'module_deps', 'md_deps', array(
					'md_module' => $this->getName(),
					'md_skin' => $skin,
				), __METHOD__
			);
			if ( !$deps ) {
				$deps = '[]'; // Empty array so we can do negative caching
			}
			$wgMemc->set( $key, $deps );
		}
		$this->fileDeps = FormatJson::decode( $deps, true );
		return $this->fileDeps;
	}
	
	/**
	 * Get the contents of a set of files and concatenate them, with
	 * newlines in between. Each file is used only once.
	 * @param $files array Array of file names
	 * @return string Concatenated contents of $files
	 */
	protected static function concatScripts( $files ) {
		return implode( "\n", array_map( 'file_get_contents', array_map( array( __CLASS__, 'remapFilename' ), array_unique( (array) $files ) ) ) );
	}
	
	/**
	 * Get the contents of a set of CSS files, remap then and concatenate
	 * them, with newlines in between. Each file is used only once.
	 * @param $files array Array of file names
	 * @return string Concatenated and remapped contents of $files
	 */
	protected static function concatStyles( $files ) {
		return implode( "\n", array_map( array( __CLASS__, 'remapStyle' ), array_unique( (array) $files ) ) );
	}
	
	/**
	 * Remap a relative to $IP. Used as a callback for array_map()
	 * @param $file string File name
	 * @return string $IP/$file
	 */
	protected static function remapFilename( $file ) {
		global $IP;
		return "$IP/$file";
	}
	
	/**
	 * Get the contents of a CSS file and run it through CSSMin::remap().
	 * This wrapper is needed so we can use array_map() in concatStyles()
	 * @param $file string File name
	 * @return string Remapped CSS
	 */
	protected static function remapStyle( $file ) {
		return CSSMin::remap( file_get_contents( self::remapFilename( $file ) ), dirname( $file ) );
	}
}

/**
 * Custom module for MediaWiki:Common.js and MediaWiki:Skinname.js
 * TODO: Add Site CSS functionality too
 */
class ResourceLoaderSiteModule extends ResourceLoaderModule {
	
	/* Protected Members */
	
	// In-object cache for modified time
	protected $modifiedTime = null;
	
	/* Methods */
	
	public function getScript( ResourceLoaderContext $context ) {
		return Skin::newFromKey( $context->getSkin() )->generateUserJs();
	}
	
	public function getModifiedTime( ResourceLoaderContext $context ) {
		if ( isset( $this->modifiedTime[$context->getHash()] ) ) {
			return $this->modifiedTime[$context->getHash()];
		}
		
		// HACK: We duplicate the message names from generateUserJs()
		// here and weird things (i.e. mtime moving backwards) can happen
		// when a MediaWiki:Something.js page is deleted
		$jsPages = array( Title::makeTitle( NS_MEDIAWIKI, 'Common.js' ),
			Title::makeTitle( NS_MEDIAWIKI, ucfirst( $context->getSkin() ) . '.js' )
		);
		
		// Do batch existence check
		// TODO: This would work better if page_touched were loaded by this as well
		$lb = new LinkBatch( $jsPages );
		$lb->execute();
		
		$this->modifiedTime = 1; // wfTimestamp() interprets 0 as "now"
		foreach ( $jsPages as $jsPage ) {
			if ( $jsPage->exists() ) {
				$this->modifiedTime = max( $this->modifiedTime, wfTimestamp( TS_UNIX, $jsPage->getTouched() ) );
			}
		}
		return $this->modifiedTime;
	}
	
	public function getStyle( ResourceLoaderContext $context ) { return ''; }
	public function getMessages() { return array(); }
	public function getLoaderScript() { return ''; }
	public function getDependencies() { return array(); }
}


class ResourceLoaderStartUpModule extends ResourceLoaderModule {
	
	/* Protected Members */
	
	protected $modifiedTime = null;
	
	/* Methods */
	
	public function getScript( ResourceLoaderContext $context ) {
		global $IP;
		
		$scripts = file_get_contents( "$IP/resources/startup.js" );
		if ( $context->getOnly() === 'scripts' ) {
			// Get all module registrations
			$registration = ResourceLoader::getModuleRegistrations( $context );
			// Build configuration
			$config = FormatJson::encode(
				array( 'server' => $context->getServer(), 'debug' => $context->getDebug() )
			);
			// Add a well-known start-up function
			$scripts .= "window.startUp = function() { $registration mediaWiki.config.set( $config ); };";
			// Build load query for jquery and mediawiki modules
			$query = wfArrayToCGI(
				array(
					'modules' => implode( '|', array( 'jquery', 'mediawiki' ) ),
					'only' => 'scripts',
					'lang' => $context->getLanguage(),
					'dir' => $context->getDirection(),
					'skin' => $context->getSkin(),
					'debug' => $context->getDebug(),
					'version' => wfTimestamp( TS_ISO_8601, round( max(
						ResourceLoader::getModule( 'jquery' )->getModifiedTime( $context ),
						ResourceLoader::getModule( 'mediawiki' )->getModifiedTime( $context )
					), -2 ) )
				)
			);
			// Build HTML code for loading jquery and mediawiki modules
			$loadScript = Html::linkedScript( $context->getServer() . "?$query" );
			// Add code to add jquery and mediawiki loading code; only if the current client is compatible
			$scripts .= "if ( isCompatible() ) { document.write( '$loadScript' ); }";
			// Delete the compatible function - it's not needed anymore
			$scripts .= "delete window['isCompatible'];";
		}
		return $scripts;
	}
	
	public function getModifiedTime( ResourceLoaderContext $context ) {
		global $IP;
		if ( !is_null( $this->modifiedTime ) ) {
			return $this->modifiedTime;
		}
		// HACK getHighestModifiedTime() calls this function, so protect against infinite recursion
		$this->modifiedTime = filemtime( "$IP/resources/startup.js" );
		$this->modifiedTime = ResourceLoader::getHighestModifiedTime( $context );
		return $this->modifiedTime;
	}
	
	public function getClientMaxage() {
		return 300; // 5 minutes
	}
	
	public function getServerMaxage() {
		return 300; // 5 minutes
	}
	
	public function getStyle( ResourceLoaderContext $context ) { return ''; }
	public function getMessages() { return array(); }
	public function getLoaderScript() { return ''; }
	public function getDependencies() { return array(); }
}