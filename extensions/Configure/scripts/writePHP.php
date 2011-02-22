<?php

/**
 * Maintenance script that helps to do maintenance with configuration files.
 *
 * @file
 * @ingroup Extensions
 * @author Alexandre Emsenhuber
 * @license GPLv2 or higher
 */

$IP = getenv( 'MW_INSTALL_PATH' );
if ( $IP === false )
	$IP = dirname( __FILE__ ) . '/../../..';

require_once( "$IP/maintenance/Maintenance.php" );

class ConfigurationWriter extends Maintenance {
	public $file;

	public function __construct(){
		parent::__construct();
		$this->mDescription = 'Script that writes the configuration';
		$this->addOption( 'file', 'write to a specific file (default: STDOUT)', false, true );
		$this->addOption( 'version', 'version to write', false, true );
		$this->addOption( 'wiki', 'write the file for that wiki', false, true );
		$this->addOption( 'wgConf', 'write \$wgConf->settings' );
	}

	/**
	 * Main execution point
	 */
	public function execute(){
		global $wgConf;

		if( !$wgConf instanceof WebConfiguration ){
			$this->error( "You need to call efConfigureSetup() to use this maintenance script.", true );
		}

		# Maybe we have a specific file
		if( $this->hasOption( 'file' ) )
			$this->file = fopen( $this->getOption( 'file' ), 'w' );
		else
			$this->file = STDOUT;

		# Get the version
		if( $this->hasOption( 'version' ) )
			$version = $this->getOption( 'version' );
		else
			$version = false;

		# Write the configuration
		if( $this->hasOption( 'wgConf' ) ){
			$this->writeWgConf( $version );
		} else {
			$this->writeSettings( $version );
		}

		# Maybe close the file
		if( $this->file && $this->file !== STDOUT )
			fclose( $this->file );
	}

	/**
	 * Wrapper to write $string in our file
	 *
	 * @param $string string
	 */
	protected function write( $string ){
		fwrite( $this->file, $string );
	}

	/**
	 * Get the header for generated file
	 */
	protected function getFileHeader(){
		return <<<HEADER
<?php
/**
 * This file has been automatically generated by the Configure extension
 * You will find more information about settings at
 * http://www.mediawiki.org/wiki/Manual:Configuration_settings
 */


HEADER;
	}

	/**
	 * Write $wgConf->setting for all sites
	 */
	protected function writeWgConf( $version ){
		global $wgConf, $IP;
		if( $version ){
			$arr = $wgConf->getOldSettings( $version );
			if( !count( $arr ) ){
				fwrite( STDERR, "The version given ($version) is invalid\n" );
				return;
			}
			$settingsVal = array();
			foreach( $arr as $site => $settings ){
				if( !is_array( $settings ) )
					continue;
				foreach( $settings as $name => $val ){
					if( $name != '__includes' )
						$settingsVal[$name][$site] = $val;
				}
			}
		} else {
			$settingsVal = $wgConf->settings;
		}
		$this->write( $this->getFileHeader() );
		$this->write( "\$wgConf->settings = array(\n" );
		foreach( $this->getSettings( CONF_SETTINGS_CORE ) as $main => $sub ){
			$this->write( "\n## $main\n\n" );
			foreach( $sub as $section => $settings ){
				$this->write( "## $section\n" );
				foreach( $settings as $name => $type ){
					if( isset( $settingsVal[$name] ) ){
						$this->write( "'$name' => " . $this->writeSettingArray( $name, $type, $settingsVal[$name] ) . ",\n" );
					} else {
						$this->write( "# Missing \$$name\n" );
					}
				}
			}
		}
		$this->write( "\n\n## extensions\n" );
		$this->write( "## ----------\n" );
		$paths = array();
		foreach( ConfigurationSettings::singleton( CONF_SETTINGS_BOTH )->getAllExtensionsObjects() as $ext ){
			if( $ext->isActivated() ){
				$this->write( "\n## " . $ext->getName() . "\n" );
				$path = str_replace( $IP, '$IP', $ext->getFile() );
				$paths[] = ( "require( \"$path\" );" );
				foreach( $ext->getSettings() as $name => $type ){
					if( isset( $settingsVal[$name] ) )
						$this->write( "'$name' => " . $this->writeSettingArray( $name, $type, $settingsVal[$name] ) . ",\n" );
					else
						$this->write( "# Missing \$$name\n" );
				}
			}
		}
		$this->write( ");\n" );

		if( count( $paths ) ){
			$this->write( "\n" );
			$this->write( implode( "\n", $paths ) );
		}
		$this->write( "\n" );
	}

	/**
	 * Write sepecific settings
	 */
	protected function writeSettings( $version ){
		global $wgConf, $IP;
		if( $this->hasOption( 'wiki' ) )
			$wiki = $this->getOption( 'wiki' );
		else
			$wiki = $wgConf->getWiki();
		if( $version ){
			$arr = $wgConf->getOldSettings( $version );
			if( !count( $arr ) ){
				$this->error( "The version given ($version) is invalid\n", true );
			}
			if( !isset( $arr[$wiki] ) ){
				$this->error( "'$wiki' could not be found in this version\n", true );
				return;
			}
			$settingsVal = $arr[$wiki];
		} else {
			$settingsVal = $wgConf->getCurrent( $wiki );
		}
		$this->write( $this->getFileHeader() );
		foreach( $this->getSettings( CONF_SETTINGS_CORE ) as $main => $sub ){
			$this->write( "\n## $main\n\n" );
			foreach( $sub as $section => $settings ){
				$this->write( "## $section\n" );
				foreach( $settings as $name => $type ){
					if( isset( $settingsVal[$name] ) )
						$this->write( "\$$name = " . $this->writeSetting( $name, $type, $settingsVal[$name] ) . ";\n" );
					else
						$this->write( "# Missing \$$name\n" );
				}
			}
		}
		$this->write( "\n\n## extensions\n" );
		$this->write( "## ----------\n" );
		foreach( ConfigurationSettings::singleton( CONF_SETTINGS_BOTH )->getAllExtensionsObjects() as $ext ){
			if( $ext->isActivated() ){
				$this->write( "\n## " . $ext->getName() . "\n" );
				$path = str_replace( $IP, '$IP', $ext->getFile() );
				$this->write( "require( \"$path\" );\n" );
				foreach( $ext->getSettings() as $name => $type ){
					if( isset( $settingsVal[$name] ) )
						$this->write( "\$$name = " . $this->writeSetting( $name, $type, $settingsVal[$name] ) . ";\n" );
					else
						$this->write( "# Missing \$$name\n" );
				}
			}
		}
	}

	/**
	 * Get a 3D array of settings
	 */
	protected function getSettings( $type ){
		 $all = ConfigurationSettings::singleton( $type )->getSettings();
		 $rm = ConfigurationSettings::singleton( $type )->getUneditableSettings();
		 foreach( $all as &$sect )
		 	foreach( $sect as &$sub )
		 		foreach( $sub as $name => $unused )
		 			if( in_array( $name, $rm ) )
		 				unset( $sub[$name] );
		 return $all;
	}

	/**
	 * Callback for simple arrays
	 */
	public static function map_simple( $val ){
		return var_export( $val, true );
	}

	/**
	 * Callback for "simple-dual" arrays
	 */
	public static function map_simple_dual( $val ){
		return 'array( ' . var_export( $val[0], true ) . ', ' .	var_export( $val[1], true ) . ' )';
	}

	protected static function getNsConst( $ns ){
		static $map = array(
			-2 => 'NS_MEDIA',
			-1 => 'NS_SPECIAL',
			0 => 'NS_MAIN',
			1 => 'NS_TALK',
			2 => 'NS_USER',
			3 => 'NS_USER_TALK',
			4 => 'NS_PROJECT',
			5 => 'NS_PROJECT_TALK',
			6 => 'NS_FILE',
			7 => 'NS_FILE_TALK',
			8 => 'NS_MEDIAWIKI',
			9 => 'NS_MEDIAWIKI_TALK',
			10 => 'NS_TEMPLATE',
			11 => 'NS_TEMPLATE_TALK',
			12 => 'NS_HELP',
			13 => 'NS_HELP_TALK',
			14 => 'NS_CATEGORY',
			15 => 'NS_CATEGORY_TALK',
		);
		if( isset( $map[$ns] ) )
			return $map[$ns];
		else
			return $ns;
	}

	public static function map_assoc_ns( &$val, $index ){
		$val = self::getNsConst( $index ) . ' => ' . var_export( $val, true );
	}

	public static function map_array_ns( &$val, $index ){
		$val = self::getNsConst( $index ) . ' => array( ' . implode( ', ', array_map( array( __CLASS__, 'map_simple' ), $val ) ) . ' )';
	}

	protected function writeSettingArray( $name, $type, /*array*/ $arr ){
		if( !is_array( $arr ) || !count( $arr ) )
			return 'array()';
		$ret = "array(\n";
		foreach( $arr as $site => $val ){
			$php = str_replace( "\n", "\n\t\t", $this->writeSetting( $name, $type, $val ) );
			$siteVar = var_export( $site, true );
			$ret .= "\t$siteVar => $php,\n";
		}
		$ret .= ")";
		return $ret;
	}

	protected function writeSetting( $name, $type, $val ){
		# For non-array, just use var_export()
		if( $type != 'array' )
			return var_export( $val, true );

		if( is_array( $val ) && !count( $val ) )
			return 'array()';

		$arrType = ConfigurationSettings::singleton( CONF_SETTINGS_BOTH )->getArrayType( $name );
		# If we don't know what it is for an array or it is an associative array,
		# also use var_export()
		if( $arrType == 'array' || $arrType === null || $arrType == 'assoc' )
			return var_export( $val, true );

		if( $arrType == 'simple' ){
			$ret = "array(\n  " . implode( ",\n  ", array_map( array( __CLASS__, 'map_simple' ), (array)$val ) ) . "\n)";
			return $ret;

		}

		if( $arrType == 'simple-dual' ){
			$ret = "array(\n  " . implode( ",\n  ", array_map( array( __CLASS__, 'map_simple_dual' ), $val ) ) . "\n)";
			return $ret;

		}

		if( in_array( $arrType, array( 'ns-bool', 'ns-text' ) ) ){
			array_walk( $val, array( __CLASS__, 'map_assoc_ns' ) );
			$ret = "array(\n  " . implode( ",\n  ",  $val ) . "\n)";
			return $ret;
		}

		if( $arrType == 'ns-simple' ){
			$ret = "array(\n  " . implode( ",\n  ", array_map( array( __CLASS__, 'getNsConst' ), $val ) ) . "\n)";
			return $ret;

		}

		if( $arrType == 'ns-array' ){
			array_walk( $val, array( __CLASS__, 'map_array_ns' ) );
			$ret = "array(\n  " . implode( ",\n  ",  $val ) . "\n)";
			return $ret;
		}

		# Should no happend
		return var_export( $val, true );
	}
}

$maintClass = 'ConfigurationWriter';
require_once( DO_MAINTENANCE );