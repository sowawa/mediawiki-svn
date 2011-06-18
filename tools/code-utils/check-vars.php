<?php

/*
 * Checks a number of syntax conventions on variables from a valid PHP file.
 *
 * Run as:
 *  find phase3/ \( -name \*.php -or -name \*.inc \) -not \( -name diffLanguage.php -o -name LocalSettings.php -o -name Parser?????.php \) -exec php tools/code-utils/check-vars.php \{\} +
 */
if( ! $IP = getenv( 'MW_INSTALL_PATH' ) ) {
	$IP = dirname( __FILE__ ) . "/../../phase3/";
}

$IP = rtrim( $IP, "/" );

require_once( "$IP/includes/Defines.php" ); # Faster than parsing
require_once( "$IP/includes/AutoLoader.php" );
$wgAutoloadClasses = &$wgAutoloadLocalClasses;
include_once( "$IP/tests/TestsAutoLoader.php" );
$wgAutoloadClasses['MWInit'] = "$IP/includes/Init.php";

$mwDeprecatedFunctions = false;
@include( dirname( __FILE__ ) . "/deprecated.functions" );
$mwParentClasses = array();
@include( dirname( __FILE__ ) . "/parent.classes" );

if ( !extension_loaded( 'sockets' ) ) dl( 'sockets.so' );
if ( !extension_loaded( 'PDO' ) ) dl( 'pdo.so' );
if ( !extension_loaded( 'zip' ) ) dl( 'zip.so' );
if ( !extension_loaded( 'intl' ) ) dl( 'intl.so' ); // Provides the Collator class, used in Collation.php

$wgAutoloadLocalClasses += array(
		'DBAccessError' => 'LBFactory',
		'Maintenance' => 'Maintenance.php',
		'MessageGroup' => 'Translate extension interface',
		'MessageGroups' => 'Translate extension',
		'PremadeMediawikiExtensionGroups' => 'Translate extension',
		'languages' => 'maintenance/language/languages.inc',
		'extensionLanguages' => 'maintenance/language/languages.inc',
		'MessageWriter' => 'maintenance/language/writeMessagesArray.inc',
		'tidy' => 'pecl tidy',
		'PEAR' => 'pear',
		'Normalizer' => 'pecl intl',
		'Mail' => 'pear Mail',

		'UserDupes' => 'maintenance/userDupes.inc',
		'DeleteDefaultMessages' => 'maintenance/deleteDefaultMessages.php',
		'PopulateCategory' => 'maintenance/populateCategory.php',
		'PopulateParentId' => 'maintenance/populateParentId.php',
		'PopulateRevisionLength' => 'maintenance/populateRevisionLength.php',
		'PopulateLogSearch' => 'maintenance/populateLogSearch.php',
		'BaseDump' => 'maintenance/backupPrefetch.inc',
		'ExportProgressFilter' => 'maintenance/backup.inc'
	);

class CheckVars {
	var $mDebug = false;
	static $mDefaultSettingsGlobals = null;
	static $mRequireKnownClasses = array();
	static $mRequireKnownFunctions = array();
	static $mRequireKnownConstants = array();

	static $mKnownFileClassesDefault = array();
	static $mKnownFunctionsDefault = array();
	static $mConstantsDefault = array();

	# Ignore constants with these prefixes:
	static $constantIgnorePrefixes = array( "PGSQL_", "OCI_", "SQLT_", "DB2_", "XMLREADER_", "SQLSRV_" );
	# Ignore functions with these prefixes:
	static $functionIgnorePrefixes = array( "pg_", "oci_", "db2_", "gmp_", "sqlsrv_", "exif_", "fss_", "tidy_",
			"apc_", "eaccelerator_", "xcache_", "wincache_", "apache_", "xdiff_", "wikidiff2_", "parsekit_",
			"wddx_", "setproctitle", "utf8_", "normalizer_", "dba_", "pcntl_", "finfo_", "mime_content_type", "curl_",
			# GD and images functions:
			"imagecreatetruecolor", "imagecolorallocate", "imagecolortransparent", "imagealphablending",
			"imagecopyresized", "imagesx", "imagesy", "imagecopyresampled", "imagesavealpha",
			"imagedestroy", "imageinterlace", "imagejpeg",
			# readline is usualy not available since linking libreadline with PHP breaks GPL license
			"readline",
		);
	# Functions to be avoided. Insert in lowercase.
	static $poisonedFunctions = array(
		'addslashes' => 'Replace with Database::addQuotes/strencode',
		'mysql_db_query' => 'Deprecated since PHP 5.3.0',
		'mysql_escape_string' => 'Replace with Database::addQuotes/strencode',
		'create_function' => 'create_function should be avoided. See http://www.mediawiki.org/wiki/Security_for_developers#Dynamic_code_generation',
		'eval' => 'eval should be avoided. See r78046', # eval.php is magically not listed for not containing any function. Should get an exception if it starts being parsed.
		'call_user_method' => 'Deprecated since PHP 4.1.0',
		'call_user_method_array' => 'Deprecated since PHP 4.1.0',
		'ereg' => 'Deprecated since PHP 5.3.0',
		'ereg_replace' => 'Deprecated since PHP 5.3.0',
		'eregi' => 'Deprecated since PHP 5.3.0',
		'eregi_replace' => 'Deprecated since PHP 5.3.0',
		'split' => 'Deprecated since PHP 5.3.0',
		'spliti' => 'Deprecated since PHP 5.3.0',
		'sql_regcase' => 'Deprecated since PHP 5.3.0',
		'set_socket_blocking' => 'Deprecated since PHP 5.3.0. Use stream_set_blocking()',
		'session_register' => 'Deprecated since PHP 5.3.0. Use $_SESSION directly',
		'session_unregister' => 'Deprecated since PHP 5.3.0.',
		'session_is_registered' => 'Deprecated since PHP 5.3.0.',
		'set_magic_quotes_runtime' => 'Deprecated since PHP 5.3.0.',
		
		'var_dump' => 'Debugging function.', //r81671#c13996
		//'print_r' => 'Debugging function if second parameter is not true.',
		'wfVarDump' => 'Debugging function.', //var_export() wrapper
		);

	static $enabledWarnings = array(
		'utf8-bom' => true,
		'php-trail' => true,
		'double-php-open' => true,
		'double-;' => true,
		'this-in-static' => true,
		'missed-docblock' => false,
		'profileout' => true,
		'evil-@' => false,
		'global-in-switch' => true,
		'global-as-local' => true,
		'global-names' => true,
		'double-globals' => true,
		'unused-global' => true,
		'undefined-global' => true,
		'function-function' => true,
		'missing-function' => true,
		'missing-class' => true,
		'orphan-parent' => true,
		'$self' => true,
		'function-throw' => true,
		'undefined-constant' => true,
		'missing-requires' => true,
		'deprecated-calls' => true,
		'deprecated-might' => true,
		'poisoned-function' => true,
		'error' => true,
		# 'help' keyword is reserved!!
		);
	/** print out the default warnings list */
	static function dumpWarningsKeywords() {
		print "Warning keywords suitable for -W<[no]keyword>:\n";
		$w = CheckVars::$enabledWarnings ;
		asort( $w ); // sort by status
		print "Keywords disabled by default:\n";
		$prevStatus = false;
		foreach( $w as $key => $status ) {
			if( $status !== $prevStatus ) {
				$prevStatus = $status;
				print "Keywords enabled by default:\n";
			}
			print "\t$key\n";
		}
	}

	protected $generateDeprecatedList = false;
	protected $generateParentList = false;

	/* Values for status */
	const WAITING_FUNCTION = 0;
	const IN_FUNCTION_NAME = 1;
	const IN_FUNCTION = 2;
	const IN_GLOBAL = 3;
	const IN_INTERFACE = 4;
	const IN_REQUIRE_WAITING = 6;
	const IN_FUNCTION_REQUIRE = 8;

	/* Token specializations */
	const CLASS_NAME = -4;
	const CLASS_MEMBER = -5;
	const FUNCTION_NAME = -6;
	const FUNCTION_DEFINITION = -7;

	/* Function attribute */
	const FUNCTION_DEPRECATED = -8;

	function __construct() {
		if ( self::$mDefaultSettingsGlobals == null ) {
			global $IP;
			$this->load( "$IP/includes/DefaultSettings.php", false );
			if ( count( $this->mTokens ) > 0 ) {
				$globals = array (
					'$wgAutoloadLocalClasses', # AutoLoader.php, a couple of readers
					'$wgCanonicalNamespaceNames', # Namespace.php
					'$wgContLang', # Setup.php
					'$wgDeferredUpdateList', # Setup.php
					'$wgExtModifiedFields', '$wgExtNewFields', '$wgExtNewIndexes', '$wgExtNewTables', # Updates
					'$wgFeedClasses', # Defines.php, many uses
					'$wgLang', # Setup.php
					'$wgLanguageNames', # Language.php, read by others
					'$wgMemc', # Setup.php
					'$wgMessageCache', # Setup.php
					'$wgNoDBParam', # maintenance, serialized
					'$wgOut', # Setup.php
					'$wgParser', # Setup.php
					'$wgPostCommitUpdateList', # Initialised in Setup.php, should be removed
					'$wgProfiler', # StartProfiler.php
					'$wgProfiling', # Profiler.php
					'$wgQueryPages', # QueryPage.php
					'$wgRequest', # Setup.php
					'$wgRequestTime', # WebStart.php
					'$wgRUstart', # WebStart.php, for Profiler stuff
					'$wgTitle', # index.php
					'$wgUpdates', # updaters
					'$wgUseEnotif', # Setup.php
					'$wgUseNormalUser', # maintenance
					'$wgUser', # Setup.php
				);

				foreach ( $this->mTokens as $token ) {
					if ( is_array( $token ) && ( $token[0] == T_VARIABLE ) && ( substr( $token[1], 0, 3 ) == '$wg' ) ) {
						$globals[] = $token[1];
					}
				}
				self::$mDefaultSettingsGlobals = array_unique( $globals );
				$this->mTokens = array(); # Free
			}
		}
	}

	protected static $mGlobalsPerFile = array( # Variables which are OK, but only on a few files
			'$wgHtmlEntities' => array( 'Sanitizer.php' ),
			'$wgHtmlEntityAliases' => array( 'Sanitizer.php' ),
			'$wgFullyInitialised' => array( /* Set */ 'Setup.php', /* read */ 'Exception.php' ),
			'$wgContLanguageCode' => array( 'Setup.php' ),
			'$wgUseLatin1' => array( 'upgrade1_5.php' ), # If you upgrade from MW < 1.5 it will be there
			'$wgExtPGNewFields' => array( 'DatabaseUpdater.php', 'PostgresUpdater.php' ),
			'$wgExtPGAlteredFields' => array( 'DatabaseUpdater.php', 'PostgresUpdater.php' ),
			'$optionsWithArgs' => array( 'commandLine.inc' ),
			'$args' => array( 'commandLine.inc' ),
			'$options' => array( 'commandLine.inc', 'upgrade1_5.php' ),
			'$canonicalDecomp' => array( 'UtfNormalGenerate.php' ),
			'$compatibilityDecomp' => array( 'UtfNormalGenerate.php' ),
			'$mmfl' => array( 'mergeMessageFileList.php' ),
			'$checkBlacklist' => array( 'checkLanguage.inc' ),
			'$stderr' => array( 'serialize.php' ),
			'$col' => array( 'UtfNormalTest2.php' ),
			'$lineNo' => array( 'UtfNormalTest2.php' ),
			'$cliUpgrade' => array( 'CliInstaller.php' ),
			'$mediaWiki' => array( 'index.php' ),
			'$wgArticle' => array( 'index.php' ),
		);

	function setGenerateDeprecatedList( $bool = true ) {
		$this->generateDeprecatedList = $bool;
	}
	function getGenerateDeprecatedList() {
		return $this->generateDeprecatedList;
	}
	function saveDeprecatedList( $filename ) {
		$data = "<?php\n\$mwDeprecatedFunctions = array(\n";
		foreach( $this->mDeprecatedFunctionList as $depre => $classes ) {
			$data .= "\t'$depre' => array( '" . implode( "', '", $classes ) . "' ),\n";
		}
		$data .= "\n);\n\n";
		file_put_contents( $filename, $data );
	}

	function setGenerateParentList( $bool = true ) {
		$this->generateParentList = $bool;
	}
	function getGenerateParentList() {
		return $this->generateParentList;
	}
	function saveParentList( $filename ) {
		global $mwParentClasses;
		$data = "<?php\n\$mwParentClasses = array(\n";
		foreach( $mwParentClasses as $class => $parent ) {
			$data .= "\t'$class' => '$parent' ,\n";
		}
		$data .= "\n);\n\n";
		file_put_contents( $filename, $data );
	}
	
	private function initVars() {
		$this->mProblemCount = 0;

		/* These are used even if it's shortcircuited */
		$this->mKnownFileClasses = self::$mKnownFileClassesDefault;
		$this->mUnknownClasses = array();
		$this->mUnknownFunctions = array();
		$this->mKnownFunctions = self::$mKnownFunctionsDefault;
		$this->mConstants = self::$mConstantsDefault;

		$this->mStatus = self::WAITING_FUNCTION;
		$this->mFunctionQualifiers = array();
		$this->mClass = null;
		$this->mParent = null;

		// Predefine constant that might not be defined by this file source code
		$this->mConstants = array( 'PARSEKIT_SIMPLE', 'UNORM_NFC', # Extensions
			/* Defined in Title.php and GlobalFunctions.php */
			'TC_MYSQL', 'TS_UNIX', 'TS_MW', 'TS_DB', 'TS_RFC2822',
			'TS_ISO_8601', 'TS_EXIF', 'TS_ORACLE', 'TS_POSTGRES', 'TS_DB2',
			'TS_ISO_8601_BASIC',
			/* PHP extensions */
			'FILEINFO_MIME', 'FILEINFO_MIME_TYPE', 'MHASH_ADLER32',
			'SIGTERM', 'SIG_DFL',
			'SVN_REVISION_HEAD', 'SVN_REVISION_INITIAL',
		) ;
	}

	function load( $file, $shortcircuit = true ) {
		$this->initVars();
		$this->mFilename = $file;

		$source = file_get_contents( $file );
		if ( substr( $source, 0, 3 ) == "\xEF\xBB\xBF" ) {
			$this->warning( 'utf8-bom', "$file has an UTF-8 BOM" );
		}
		$source = rtrim( $source );
		if ( substr( $source, -2 ) == '?>' ) {
			$this->warning( 'php-trail', "?> at end of file is deprecated in MediaWiki code" );
		}
		if ( $shortcircuit && !preg_match( "/^[^'\"#*]*function [^\"']*\$/m", $source ) ) {
			$this->mTokens = array();
			return;
		}

		/* Skip HipHop specific requires */
		$source = preg_replace( '/if \( isset\( \$_SERVER\[\'MW_COMPILED\'\] \) \) {\\s+require \( \'phase3\/.*\' \);\\s+} else {/', 'if ( true ) {', $source );
		
		$this->mTokens = token_get_all( $source );
	}

	static $functionQualifiers = array( T_ABSTRACT, T_PRIVATE, T_PUBLIC, T_PROTECTED, T_STATIC );

	function execute() {
		global $IP;
		$currentToken = null;

		foreach ( $this->mTokens as $token ) {
			if ( self::isMeaningfulToken( $currentToken ) )
				$lastMeaningfulToken = $currentToken;
			$currentToken = $token;

			if ( $lastMeaningfulToken[0] == T_OPEN_TAG && $token[0] == T_OPEN_TAG ) {
				# See r69767
				$this->warning( 'double-php-open', "{$token[1]} in line {$token[2]} after {$lastMeaningfulToken[1]} in line {$lastMeaningfulToken[2]}" );
			}
			if ( $token == ';' ) {
				if ( $lastMeaningfulToken == ';' ) {
					# See r72751, warn on ;;
					$this->warning( 'double-;', "Empty statement" );
				} elseif ( $lastMeaningfulToken[0] == T_FOR ) {
					# But not on infinte for loops: for ( ; ; )
					$currentToken = array(';', ';', $lastMeaningfulToken[2] );
				}
			}

			if ( $lastMeaningfulToken[0] == T_DECLARE && $token[0] == T_STRING ) {
				$currentToken[0] = T_WHITESPACE; # Ignore the ticks or encoding
				continue;
			}

			if ( is_array( $token ) && ( $token[0] == T_CONSTANT_ENCAPSED_STRING ) && is_array( $lastMeaningfulToken )
				&& ( ( $lastMeaningfulToken[0] == T_STRING ) || ( $lastMeaningfulToken[0] == self::FUNCTION_NAME ) )
				&& ( $lastMeaningfulToken[1] == 'define' ) ) {

				// Mark as defined
				$this->mConstants[] = trim( $token[1], "'\"" );
			}

			if ( is_array( $token ) && ( $token[0] == T_CONSTANT_ENCAPSED_STRING ) && is_array( $lastMeaningfulToken )
				&& ( ( $lastMeaningfulToken[0] == T_STRING ) || ( $lastMeaningfulToken[0] == self::FUNCTION_NAME ) )
				&& ( $lastMeaningfulToken[1] == 'defined' ) ) {

				// FIXME: Should be marked as defined only inside this T_IF
				$this->mConstants[] = trim( $token[1], "'\"" );
			}

			switch ( $this->mStatus ) {
				case self::WAITING_FUNCTION:
					if ( $token == ';' )
						$this->mFunctionQualifiers = array();

					if ( $token[0] == T_COMMENT ) {
						if ( substr( $token[1], 0, 2 ) == '/*' && substr( $token[1], 0, 3 ) != '/**' 
							&& preg_match( '/^\s+\*(?!\/)/m', $token[1] ) && strpos( $token[1], "\$separatorTransformTable = array( ',' => '' )" ) === false ) {
							$this->warning( 'missed-docblock', "Multiline comment with /* in line $token[2]" );
						}
					}

					if ( $token[0] == T_DOC_COMMENT ) {
						if ( strpos( $token[1], '@deprecated' ) !== false ) {
							$this->mFunctionQualifiers[] = self::FUNCTION_DEPRECATED;
						}
					}
					if ( in_array( $token[0], self::$functionQualifiers ) ) {
						$this->mFunctionQualifiers[] = $token[0];
					}
					if ( $token[0] == T_INTERFACE ) {
						$this->mStatus = self::IN_INTERFACE;
					}

					if ( ( $lastMeaningfulToken[0] == T_CLASS ) && ( $token[0] == T_STRING ) ) {
						$this->mKnownFileClasses[] = $token[1];
						$this->mClass = $token[1];
						$this->mParent = null;
					}

					if ( $token[0] == '}' ) {
						$this->mClass = null;
						$this->mParent = null;
					}

					if ( ( $lastMeaningfulToken[0] == T_EXTENDS ) && ( $token[0] == T_STRING ) ) {
						$this->checkClassName( $token );
						$this->mParent = $token[1];
						if ( $this->getGenerateParentList() ) {
							global $mwParentClasses;
							$mwParentClasses[ $this->mClass ] = $this->mParent;
						}
					}

					if ( in_array( $token[0], array( T_REQUIRE, T_REQUIRE_ONCE, T_INCLUDE, T_INCLUDE_ONCE ) ) ) {
						$this->mStatus = self::IN_REQUIRE_WAITING;
						$requirePath = "";
						continue;
					}

					if ( $token[0] != T_FUNCTION )
						continue;
					$this->mStatus = self::IN_FUNCTION_NAME;
					break;

				case self::IN_FUNCTION_NAME:
					if ( ( $token == '&' ) || ( $token[0] == T_WHITESPACE ) )
						continue;
					if ( $token[0] == T_STRING ) {
						$this->mFunction = $token[1];
						$this->mStatus = self::IN_FUNCTION;
						$this->mBraces = 0;
						$this->mInSwitch = 0;
						$this->mInProfilingFunction = false;
						$this->mAfterProfileOut = 0;
						$this->mFunctionGlobals = array();
						$currentToken[0] = self::FUNCTION_DEFINITION;
						$this->mKnownFunctions[] = $this->mClass ? $this->mClass . "::" . $this->mFunction : $this->mFunction;

						if ( $this->generateDeprecatedList && in_array( self::FUNCTION_DEPRECATED, $this->mFunctionQualifiers ) ) {
							if ( ( substr( $this->mFunction, 0, 2 ) != "__" ) ) {
								if ( !isset( $this->mDeprecatedFunctionList[ $this->mFunction ] ) ) {
									$this->mDeprecatedFunctionList[ $this->mFunction ] = array( $this->mClass );
								} else {
									$this->mDeprecatedFunctionList[ $this->mFunction ][] = $this->mClass;
								}
							}
						}

						$this->debug( "Entering into function {$token[1]} at line {$token[2]} " );
						continue;
					}

					$this->error( $token );

				case self::IN_FUNCTION:
					if ( ( $token == ';' ) && ( $this->mBraces == 0 ) ) {
						if ( !in_array( T_ABSTRACT, $this->mFunctionQualifiers ) ) {
							$this->error( $token );
						}
						// abstract function
						$this->mStatus = self::WAITING_FUNCTION;
						continue;
					}
					if ( $token == '{' ) {
						$this->mBraces++;
					} elseif ( $token == '}' ) {
						$this->mBraces--;
						if ( $this->mInSwitch <= $this->mBraces )
							$this->mInSwitch = 0;

						$this->purgeGlobals();
						if ( ! $this->mBraces ) {
							if ( $this->mInProfilingFunction && $this->mAfterProfileOut & 1 ) {
								$this->warning( 'profileout', "Reached end of $this->mClass::$this->mFunction with last statement not being wfProfileOut" );
							}
								
							$this->mStatus = self::WAITING_FUNCTION;
							$this->mFunctionQualifiers = array();
						}
					} elseif ( $token == ';' && $this->mInProfilingFunction ) {
						// Check that there's just a return after wfProfileOut.
						if ( $this->mAfterProfileOut == 1 ) {
							$this->mAfterProfileOut = 2;
						} elseif ( $this->mAfterProfileOut == 2 ) {
							// Set to 3 in order to bail out at the return.
							// This way we don't complay about missing return in internal wfProfile sections.
							$this->mAfterProfileOut = 3;
						}
					} elseif ( $token == '@' ) {
						$this->warning( 'evil-@', "Use of @ operator in function {$this->mFunction}" );
					} elseif ( is_array ( $token ) ) {
						if ( $token[0] == T_GLOBAL ) {
							$this->mStatus = self::IN_GLOBAL;
							if ( $this->mInSwitch ) {
								$this->warning( 'global-in-switch', "Defining global variables inside a switch in line $token[2], function {$this->mFunction}" );
							}
						} elseif ( ( $token[0] == T_CURLY_OPEN ) || ( $token[0] == T_DOLLAR_OPEN_CURLY_BRACES ) ) {
							// {$ and ${ and  All these three end in }, so we need to open an extra brace to balance
							// T_STRING_VARNAME is documented as ${a but it's the text inside the braces
							$this->mBraces++;
						}
						if ( $token[0] == T_STRING_VARNAME ) {
							$token[0] = T_VARIABLE;
							$token[1] = '$' . $token[1];
						}
						if ( $token[0] == T_VARIABLE ) {
							# $this->debug( "Found variable $token[1]" );

							if ( ( $token[1] == '$this' ) && in_array( T_STATIC, $this->mFunctionQualifiers ) ) {
								$this->warning( 'this-in-static', "Use of \$this in static method function {$this->mFunction} in line $token[2]" );
							}

							if ( $lastMeaningfulToken[0] == T_PAAMAYIM_NEKUDOTAYIM ) {
								/* Class variable. No check for now */
							} else {
								if ( isset( $this->mFunctionGlobals[ $token[1] ] ) ) {
										$this->mFunctionGlobals[ $token[1] ][0] ++;
								} elseif ( $this->shouldBeGlobal( $token[1] ) ) {
									$this->warning( 'global-as-local', "{$token[1]} is used as local variable in line $token[2], function {$this->mFunction}" );
								}
							}
						} elseif ( $token[0] == T_RETURN && $this->mInProfilingFunction ) {
							if ( $this->mAfterProfileOut == 2 ) {
								$this->mAfterProfileOut = 0;
							} else {
								$this->warning( 'profileout', "$token[1] in line $token[2] is not preceded by wfProfileOut" );
							}
						} elseif ( $token[0] == T_FUNCTION ) {
							$this->warning( 'function-function', "Uh? Function inside function? A lamda function?" );
							$this->error( $token );
						} elseif ( $token[0] == T_SWITCH ) {
							if ( !$this->mInSwitch )
								$this->mInSwitch = $this->mBraces;
						} elseif ( ( $token[0] == T_PAAMAYIM_NEKUDOTAYIM ) && is_array( $lastMeaningfulToken ) && ( $lastMeaningfulToken[0] == T_VARIABLE ) ) {
							if ( ( $lastMeaningfulToken[1] == '$self' ) || ( $lastMeaningfulToken[1] == '$parent' ) ) {
								# Bug of r69904
								$this->warning( '$self', "$lastMeaningfulToken[1]:: used in line $lastMeaningfulToken[2] It probably should have been " . substr( $lastMeaningfulToken[1], 1 ) . "::" );
							}
						} elseif ( ( $token[0] == T_STRING ) && ( is_array( $lastMeaningfulToken )
								&& in_array( $lastMeaningfulToken[0], array( T_OBJECT_OPERATOR, T_PAAMAYIM_NEKUDOTAYIM ) ) ) ) {
							# Class member or class constant
							$currentToken[0] = self::CLASS_MEMBER;
						} elseif ( $token[0] == T_STRING && is_array( $lastMeaningfulToken ) &&
							( in_array( $lastMeaningfulToken[0], array( T_INSTANCEOF, T_NEW ) ) ) ) {

							$this->checkClassName( $token );
							$currentToken[0] = self::CLASS_NAME;
						} elseif ( in_array( $token[0], array( T_REQUIRE, T_REQUIRE_ONCE, T_INCLUDE, T_INCLUDE_ONCE ) ) ) {
							$this->mStatus = self::IN_FUNCTION_REQUIRE;
							$requirePath = '';
							continue;
						}
					}

					if ( self::isMeaningfulToken( $token ) && ( $lastMeaningfulToken[0] == T_THROW ) ) {
						if ( $token[0] == T_VARIABLE ) {
							// Probably rethrowing from a catch, skip
						} elseif ( $token[0] == T_NEW ) {
							// Correct, a new class instance
							// TODO: Verify it inherits from Exception
						} else {
							// We only want the last interpretation, see r77752
							// throw Exception; -> Exception is a constant
							// throw Exception("Foo"); -> Exception() is a function
							// throw new Exception("Foo"); -> Exception is a class.
							
							$this->warning( 'function-throw', "Not using new when throwing token {$token[1]} in line $token[2], function {$this->mFunction}" );
						}
					}

					/* Try to guess the class of the variable */
					if ( in_array( $token[0], array( T_OBJECT_OPERATOR, T_PAAMAYIM_NEKUDOTAYIM ) ) ) {
						$currentToken['base'] = $lastMeaningfulToken;
					} else
					if ( ( ( $token[0] == T_STRING ) || ( $token[0] == self::CLASS_MEMBER ) )
						&& is_array( $lastMeaningfulToken ) && isset( $lastMeaningfulToken['base'] ) ) {
						$currentToken['base'] = $lastMeaningfulToken['base'];
						$currentToken['class'] = $this->guessClassName( $lastMeaningfulToken['base'] );
					}

					if ( ( $token == '(' ) && is_array( $lastMeaningfulToken ) ) {
						if ( $lastMeaningfulToken[0] == T_STRING ) {
							$lastMeaningfulToken[0] = self::FUNCTION_NAME;
							$this->checkDeprecation( $lastMeaningfulToken );
							$this->checkFunctionName( $lastMeaningfulToken );
							if ( $lastMeaningfulToken[1] == 'wfProfileIn' ) {
								$this->mInProfilingFunction = true;
								$this->mAfterProfileOut = 0;
							} elseif ( $lastMeaningfulToken[1] == 'wfProfileOut' ) {
								global $mwParentClasses;//echo "wfProfileOut $this->mClass " . ( isset( $mwParentClasses[ $this->mClass ] ) ? $mwParentClasses[ $this->mClass ] : "" ). "\n";
								if ( ( isset( $mwParentClasses[ $this->mClass ] ) && $mwParentClasses[ $this->mClass ] == 'ImageHandler') ||
									( $this->mClass == 'Hooks' && $this->mFunction == 'run' ) ) {
									// Do not treat as profiling any more. ImageHandler sons have profile sections just for their wfShellExec(). wfRunHooks profiles each hook.
									$this->mInProfilingFunction = false;
								} else {
									$this->mAfterProfileOut = 1;
								}
							}
						} else if ( $lastMeaningfulToken[0] == self::CLASS_MEMBER ) {
							$this->checkDeprecation( $lastMeaningfulToken );
						}
					}

					/* Detect constants */
					if ( self::isMeaningfulToken( $token ) && is_array( $lastMeaningfulToken ) &&
							( $lastMeaningfulToken[0] == T_STRING ) && !self::isPhpConstant( $lastMeaningfulToken[1] ) ) {

						if ( in_array( $token[0], array( T_PAAMAYIM_NEKUDOTAYIM, T_VARIABLE, T_INSTANCEOF ) ) ) {
							$this->checkClassName( $lastMeaningfulToken );
						} else {

							if ( !defined( $lastMeaningfulToken[1] ) && !in_array( $lastMeaningfulToken[1], $this->mConstants ) && !self::inIgnoreList( $lastMeaningfulToken[1], self::$constantIgnorePrefixes ) ) {
								$this->warning( 'undefined-constant', "Use of undefined constant $lastMeaningfulToken[1] in line $lastMeaningfulToken[2]" );
							}
						}
					}
					continue;

				case self::IN_GLOBAL:
					if ( $token == ',' )
						continue;
					if ( $token == ';' ) {
						$this->mStatus = self::IN_FUNCTION;
						continue;
					}
					if ( !self::isMeaningfulToken( $token ) )
						continue;

					if ( is_array( $token ) ) {
						if ( $token[0] == T_VARIABLE ) {
							if ( !$this->shouldBeGlobal( $token[1] ) && !$this->canBeGlobal( $token[1] ) ) {
								$this->warning( 'global-names', "Global variable {$token[1]} in line {$token[2]}, function {$this->mFunction} does not follow coding conventions" );
							}
							if ( isset( $this->mFunctionGlobals[ $token[1] ] ) ) {
								if ( !$this->mInSwitch ) {
									$this->warning( 'double-globals', $token[1] . " marked as global again in line {$token[2]}, function {$this->mFunction}" );
								}
							} else {
								$this->checkGlobalName( $token[1] );
								$this->mFunctionGlobals[ $token[1] ] = array( 0, $this->mBraces, $token[2] );
							}
							continue;
						}
					}
					$this->error( $token );

				case self::IN_INTERFACE:
					if ( $lastMeaningfulToken[0] == T_INTERFACE )
						$this->mKnownFileClasses[] = $token[1];

					if ( $token == '{' ) {
						$this->mBraces++;
					} elseif ( $token == '}' ) {
						$this->mBraces--;
						if ( !$this->mBraces )
							$this->mStatus = self::WAITING_FUNCTION;
					}
					continue;

				case self::IN_REQUIRE_WAITING:
				case self::IN_FUNCTION_REQUIRE:
					if ( $token == ';' ) {
						$requirePath = trim( $requirePath, ')("' );

						if ( substr( $requirePath, 0, 8 ) == "PHPUnit/" ) {
							$this->mStatus = $this->mStatus - self::IN_REQUIRE_WAITING;
							continue;
						}
						if ( $requirePath == "Testing/Selenium.php" ) {
							$this->mStatus = $this->mStatus - self::IN_REQUIRE_WAITING;
							continue;
						}
						if ( substr( $requirePath, 0, 12 ) == "Net/Gearman/" ) {
							$this->mStatus = $this->mStatus - self::IN_REQUIRE_WAITING;
							continue;
						}
						if ( substr( $requirePath, -18 ) == "/LocalSettings.php" ) {
							$this->mStatus = $this->mStatus - self::IN_REQUIRE_WAITING;
							continue;
						}
						if ( substr( $requirePath, -18 ) == "/StartProfiler.php" ) {
							$this->mStatus = $this->mStatus - self::IN_REQUIRE_WAITING;
							continue;
						}
						if ( strpos( $requirePath, '/wmf-config/' ) !== false ) {
							$this->mStatus = $this->mStatus - self::IN_REQUIRE_WAITING;
							continue;
						}
						if ( $requirePath == "Mail.php" ) { # PEAR mail
							$this->mStatus = $this->mStatus - self::IN_REQUIRE_WAITING;
							continue;
						}

						if ( ( $requirePath == '' ) || ( !file_exists( $requirePath ) && $requirePath[0] != '/' ) ) {
							/* Try prepending the script folder, for maintenance scripts (but see Maintenance.php:758) */
							$requirePath = dirname( $this->mFilename ) . "/" . $requirePath;
						}
						
						if ( !file_exists( $requirePath ) ) {
							if ( strpos( $requirePath, '$' ) === false ) {
								$this->warning( 'missing-requires', "Did not found the expected require of $requirePath" );
							}
						} else {
							$requirePath = realpath( $requirePath );
							if ( isset( self::$mRequireKnownClasses[$requirePath] ) ) {
								$this->mKnownFileClasses = array_merge( $this->mKnownFileClasses, self::$mRequireKnownClasses[$requirePath] );
								$this->mKnownFunctions = array_merge( $this->mKnownFunctions, self::$mRequireKnownFunctions[$requirePath] );
								$this->mConstants = array_merge( $this->mConstants, self::$mRequireKnownConstants[$requirePath] );
							} else {
								$newCheck = new CheckVars;
								$newCheck->load( $requirePath, false );
								$newCheck->execute();
								/* Get the classes defined there */
								$this->mKnownFileClasses = array_merge( $this->mKnownFileClasses, $newCheck->mKnownFileClasses );
								$this->mKnownFunctions = array_merge( $this->mKnownFunctions, $newCheck->mKnownFunctions );
								$this->mConstants = array_merge( $this->mConstants, $newCheck->mConstants );
								self::$mRequireKnownClasses[$requirePath] = $newCheck->mKnownFileClasses;
								self::$mRequireKnownFunctions[$requirePath] = $newCheck->mKnownFunctions;
								self::$mRequireKnownConstants[$requirePath] = $newCheck->mConstants;
							}
						}
						$this->mStatus = $this->mStatus - self::IN_REQUIRE_WAITING;
						continue;
					}

					if ( $token[0] == T_WHITESPACE )
						continue;

					if ( $token[0] == T_STRING_VARNAME ) {
						$token[0] = T_VARIABLE;
						$token[1] = '$' . $token[1];
					}
					if ( $token[0] == T_VARIABLE && $this->mStatus == self::IN_FUNCTION_REQUIRE ) {
						if ( isset( $this->mFunctionGlobals[ $token[1] ] ) ) {
								$this->mFunctionGlobals[ $token[1] ][0] ++;
						} elseif ( $this->shouldBeGlobal( $token[1] ) ) {
							$this->warning( 'global-as-local', "{$token[1]} is used as local variable in line $token[2], function {$this->mFunction}" );
						}
					}
					if ( $token == '.' ) {
						if ( $requirePath == 'dirname(__FILE__)' ) {
							$requirePath = dirname( $this->mFilename );
						} elseif ( $requirePath == 'dirname(dirname(__FILE__))' ) {
							$requirePath = dirname( dirname( $this->mFilename ) );
						} elseif ( $requirePath == 'dirname(dirname(dirname(__FILE__)))' ) {
							$requirePath = dirname( dirname( dirname( $this->mFilename ) ) );
						}
					} else if ( $token[0] == T_CURLY_OPEN || $token == '}' ) {
						continue;
					} else if ( !is_array( $token ) ) {
						if ( $token == '(' && ( $requirePath == 'MWInit::compiledPath' || $requirePath == 'MWInit::interpretedPath' ) ) {
							$requirePath = "$IP/";
						} elseif ( ( $token != '(' ) || $requirePath != '' ) {
							$requirePath .= $token[0];
						}
					} else if ( in_array( $token[0], array( T_CONSTANT_ENCAPSED_STRING, T_ENCAPSED_AND_WHITESPACE ) ) ) {
						$requirePath .= trim( $token[1], '\'"' );
					} else if ( $token[0] == T_VARIABLE ) {
						if ( $token[1] == '$IP' || $token[1] == '$mwPath' ) {
							$requirePath .= $IP;
						} elseif ( $token[1] == '$dir' ) {
							//  Scripts at phase3/maintenance/language/
							$requirePath .= dirname( $this->mFilename );
						} elseif ( $token[1] == '$wgStyleDirectory' ) {
							$requirePath .= "$IP/skins";
						} elseif ( in_array( $token[1], array( '$classFile', '$file', '$_fileName', '$fileName', '$filename' ) ) ) {
							/* Maintenance.php lines 374 and 894 */
							/* LocalisationCache.php, MessageCache.php, AutoLoader.php */
						} else {
							//$this->warning( "require uses unknown variable {$token[1]} in line {$token[2]}" );
							$requirePath .= $token[1];
						}
					} elseif ( $token[0] == T_STRING && $token[1] == 'RUN_MAINTENANCE_IF_MAIN' ) {
						$requirePath .= "$IP/maintenance/doMaintenance.php";
					} elseif ( $token[0] == T_STRING && $token[1] == 'MW_CONFIG_FILE' ) {
						$requirePath .= "$IP/LocalSettings.php";
					} else {
						$requirePath .= $token[1];
					}
					continue;

			}
		}

		$this->checkPendingClasses();
		$this->checkPendingFunctions();
	}

	function checkDeprecation( $token ) {
		global $mwDeprecatedFunctions, $mwParentClasses;

		if ( $mwDeprecatedFunctions && !in_array( self::FUNCTION_DEPRECATED, $this->mFunctionQualifiers ) &&
			isset( $mwDeprecatedFunctions[ $token[1] ] ) ) {

			if ( isset( $token['class'] ) ) {
				$class = $token['class'];
				do {
					if ( in_array( $class, $mwDeprecatedFunctions[ $token[1] ] ) ) {
						$this->warning( 'deprecated-calls', "Non deprecated function $this->mFunction calls deprecated function {$token['class']}::{$token[1]} in line {$token[2]}" );
						return;
					}
					if ( !isset( $mwParentClasses[ $class ] ) ) {
						return;
					}
					$class = $mwParentClasses[ $class ];
				} while( true );
			} else if ( isset( $token['base'] ) ) { # Avoid false positives for local functions, see maintenance/rebuildInterwiki.inc
				$this->warning( 'deprecated-might', "Non deprecated function $this->mFunction may be calling deprecated function " .
					implode( '/', $mwDeprecatedFunctions[ $token[1] ] ) . "::" . $token[1] . " in line {$token[2]}" );
			}
		}
	}

	function checkFunctionName( $token, $warn = 'defer' ) {
		if ( !isset( $token['base'] ) ) {
			// Local function

			if ( substr( $token[1], 0, 2 ) == 'wf' ) {
				// MediaWiki function
				// TODO: List them.
				return;
			}
			if ( $token[1] == 'dieout' && in_array( $this->mFunction, array( 'setup_database', 'initial_setup', 'setup_plpgsql' ) ) ) {
				return;
			}

			if ( isset( self::$poisonedFunctions[ strtolower($token[1]) ] ) ) {
				if ( $token[1] == 'var_dump' && ( substr( $this->mFunction, 0, 4 ) == 'dump' ) || basename( $this->mFilename ) == 'ApiFormatDump.php' ) {
					// Allow var_dump if the function purpose is really to dump contents
					return;
				}
				$this->warning( 'poisoned-function', "Poisoned function {$token[1]} called from {$this->mFunction} in line {$token[2]}: " . self::$poisonedFunctions[strtolower($token[1])] );
				return;
			}

			if ( function_exists( $token[1] ) ) {
				return;
			}
			if ( in_array( $token[1], $this->mKnownFunctions ) ) {
				return;
			}

			if ( self::inIgnoreList( $token[1], self::$functionIgnorePrefixes ) ) {
				return;
			}

			if ( $warn == 'now' ) {
				$this->warning( 'missing-function', "Unavailable function {$token[1]} in line {$token[2]}" );
			} else if ( $warn == 'defer' ) {
				// Defer to the end of the file
				$this->mUnknownFunctions[] = $token;
			}

		}
	}

	function checkPendingFunctions() {
		foreach ( $this->mUnknownFunctions as $functionToken ) {
			$this->checkFunctionName( $functionToken, 'now' );
		}
		$this->mUnknownFunctions = array();
	}

	/* Returns a class name, or null if it couldn't guess */
	function guessClassName( $token ) {
		static $wellKnownVars = array(
			'$wgArticle' => 'Article',
			'$wgTitle' => 'Title',
			'$wgParser' => 'Parser',
			'$wgUser' => 'User',
			'$wgOut' => 'OutputPage',
			'$wgRequest' => 'WebRequest',
			'$request' => 'WebRequest',
			'$wgMessageCache' => 'MessageCache',
			'$wgLang' => 'Language', '$wgContLang' => 'Language',
			'$dbw' => 'DatabaseBase', '$dbr' => 'DatabaseBase',
			'$sk' => 'Skin',
			'$wgMemc' => 'MWMemcached',
		);

		if ( $token[0] == T_VARIABLE ) {
			if ( isset( $wellKnownVars[ $token[1] ] ) ) {
				return $wellKnownVars[ $token[1] ];
			}
			if ( $token[1] == '$this' )
				return $this->mClass;
			$name = substr( $token[1], 1 );
		} elseif ( ( $token[0] == T_STRING ) || ( $token[0] == self::CLASS_MEMBER ) ) {
			if ( ( $token[1] == 'self' ) && !isset( $token['base'] ) )
				return $this->mClass;
			if ( ( $token[1] == 'parent' ) && !isset( $token['base'] ) )
				return $this->getParentName( $token );

			$name = $token[1];

			if ( $token[1][0] == 'm' )  // member
				$name = substr( $token[1], 1 );
		} else {
			return null;
		}
		$className = $this->checkClassName( array( 1=> ucfirst( $name ) ) , 'no' );
		if ( $className ) {
			return $className;
		}

		return null;
	}

	function error( $token ) {
		$msg = "Unexpected token " . ( is_string( $token ) ? $token : token_name( $token[0] ) ) ;
		if ( is_array( $token ) && isset( $token[2] ) ) {
			$msg .= " in line $token[2]";
		}
		$msg .= "\n";
		$this->warning( 'error', $msg );
		die( 1 );
	}

	function warning( $name, $msg ) {
		if ( !self::$enabledWarnings[$name] ) {
			return;
		}
		if ( !$this->mProblemCount ) {
			echo "Problems in {$this->mFilename}:\n";
		}
		$this->mProblemCount++;
		echo " $msg\n";
	}

	function debug( $msg ) {
		if ( $this->mDebug ) {
			echo "$msg\n";
		}
	}

	# Is this the name of a global variable?
	function shouldBeGlobal( $name ) {
		static $specialGlobals = array( '$IP', '$parserMemc', '$messageMemc', '$hackwhere', '$haveProctitle' );
		static $nonGlobals = array(	'$wgOptionalMessages', '$wgIgnoredMessages', '$wgEXIFMessages', # Used by Translate extension, read from maintenance/languages/messageTypes.inc
									'$wgMessageStructure', '$wgBlockComments' ); # Used by Translate extension and maintenance/language/writeMessagesArray.inc, read from maintenance/languages/messages.inc

		return ( ( substr( $name, 0, 3 ) == '$wg' ) || ( substr( $name, 0, 3 ) == '$eg' ) || in_array( $name, $specialGlobals ) ) && !in_array( $name, $nonGlobals );
	}

	# Variables that can be used as global, but also as locals
	function canBeGlobal( $name ) {
		if ( $name == '$argv' ) {
			/* Used as global by maintenance scripts, but also common as function var */
			return true;
		}
		if ( isset( self::$mGlobalsPerFile[$name] ) && in_array( basename( $this->mFilename ) , self::$mGlobalsPerFile[$name] ) ) {
			return true;
		}
		if ( $this->mFunction == 'loadWikimediaSettings' ) {
			/* Skip the error about $site and $lang in Maintenance.php */
			return true;
		}

		return false;
	}

	private function purgeGlobals() {
		foreach ( $this->mFunctionGlobals as $globalName => $globalData ) {
			if ( $globalData[1] <= $this->mBraces )
				continue; # In scope

			#  global $x  still affects the variable after the end of the
			# conditional, but only if the condition was true.
			#  We keep in the safe side and only consider it defined inside
			# the if block (see r69883).

			if ( $globalData[0] == 0 ) {
				$this->warning( 'unused-global', "Unused global $globalName in function {$this->mFunction} line $globalData[2]" );
			}
			unset( $this->mFunctionGlobals[$globalName] );
		}
	}

	# Look for typos in the globals names
	protected function checkGlobalName( $name ) {
		if ( substr( $name, 0, 3 ) == '$wg' ) {
			if ( ( self::$mDefaultSettingsGlobals != null ) && !in_array( $name, self::$mDefaultSettingsGlobals ) ) {
				if ( !isset( self::$mGlobalsPerFile[$name] ) || !in_array( basename( $this->mFilename ) , self::$mGlobalsPerFile[$name] ) ) {
					$this->warning( 'undefined-global', "Global variable $name is not present in DefaultSettings" );
				}
			}
		}
	}

	static function isMeaningfulToken( $token ) {
		if ( is_array( $token ) ) {
			return ( $token[0] != T_WHITESPACE && $token[0] != T_COMMENT );
		} else {
			return strpos( '(&', $token ) === false ;
		}
	}

	# Constants defined by php
	static function isPhpConstant( $name ) {
		return in_array( $name, array( 'false', 'true', 'self', 'parent', 'null' ) );
	}

	/**
	 * Returns a
	 * @param  $token Token holding the class name
	 * @param  $warn  A value from 'no', 'defer', 'now¡
	 * @return mixed  The class name if it is found, false otherwise
	 */
	function checkClassName( $token, $warn = 'defer' ) {
		global $wgAutoloadLocalClasses;

		if ( $token[1] == 'self' )
			return $this->mClass;
		if ( $token[1] == 'parent' )
			return $this->getParentName( $token );

		if ( class_exists( $token[1], false ) ) return $token[1]; # Provided by an extension
		if ( substr( $token[1], 0, 8 ) == "PHPUnit_" ) return $token[1];
		if ( $token[1] == "Testing_Selenium" || $token[1] == "Testing_Selenium_Exception" ) return $token[1];
		if ( substr( $token[1], 0, 12 ) == "Net_Gearman_" ) return $token[1]; # phase3/maintenance/gearman/gearman.inc
		if ( $token[1] == "PEAR_Error" ) return $token[1]; # Services_JSON.php
		if ( $token[1] == "PHP_Timer" ) return $token[1]; # From PEAR, used in ParserHelpers.php
		if ( substr( $token[1], 0, 7 ) == "Imagick" ) return $token[1]; # Imagick extension, can be used by phpunit/includes/api/RandomImageGenerator.php

		if ( !isset( $wgAutoloadLocalClasses[$token[1]] ) && !in_array( $token[1], $this->mKnownFileClasses ) ) {
			if ( $warn == 'now' ) {
				$this->warning( 'missing-class', "Use of unknown class $token[1] in line $token[2]" );
			} else if ( $warn == 'defer' ) {
				// Defer to the end of the file
				$this->mUnknownClasses[] = $token;
			} else {
				return false;
			}
		}
		return $token[1];
	}

	function checkPendingClasses() {
		foreach ( $this->mUnknownClasses as $classToken ) {
			$this->checkClassName( $classToken, 'now' );
		}
		$this->mUnknownClasses = array();
	}

	static function inIgnoreList( $name, $list ) {
		foreach ( $list as $prefix ) {
			if ( substr( $name, 0, strlen( $prefix ) ) == $prefix )
				return true;
		}
		return false;
	}

	function getParentName( $token ) {
		if ( !is_null( $this->mParent ) ) {
			return $this->mParent;
		}
		$this->warning( 'orphan-parent', "Use of parent in orphan class {$this->mClass} in line $token[2]" );
		return "-";
	}

	/**
	 * Sets a number of files which are considered as having always been
	 * loaded before any loaded one. Any functions/classes defined there
	 * will be assumed to be available.
	 */
	function preloadFiles( $files ) {
		$this->initVars();
		$this->mFilename = '__preload';
		$this->mTokens = array( T_OPEN_TAG, '<?php', 0 );

		for ( $i = 1; $i <= count( $files ); $i++ ) {
			$this->mTokens[] = array( T_REQUIRE, 'require', $i );
			$this->mTokens[] = array( T_CONSTANT_ENCAPSED_STRING, "'" . $files[$i - 1] . "'", $i );
			$this->mTokens[] = ';';
		}
		$this->execute();
		self::$mKnownFileClassesDefault = $this->mKnownFileClasses;
		self::$mKnownFunctionsDefault = $this->mKnownFunctions;
		self::$mConstantsDefault = $this->mConstants;
	}
}

if( $argc < 2 ) {
	die (
"Usage:
	php $argv[0] [options] <PHP_source_file1> <PHP_source_file2> ...
	
Options:
	--generate-deprecated-list
	--generate-parent-list
	-Whelp : available warnings methods
	-W[no]key : disabled/enable key warning.
");
}

$cv = new CheckVars();
// $cv->mDebug = true;
array_shift( $argv );
if ( $argv[0] == '--generate-deprecated-list' ) {
	$cv->setGenerateDeprecatedList( true );
	array_shift( $argv );
}
if ( $argv[0] == '--generate-parent-list' ) {
	$cv->setGenerateParentList( true );
	array_shift( $argv );
}

foreach ( $argv as $arg ) {
	if ( preg_match( '/^-W(no-)?(.*)/', $arg, $m ) ) {
		if( $m[2] === 'help' ) {
			CheckVars::dumpWarningsKeywords();
			exit;
		} elseif ( !isset( CheckVars::$enabledWarnings[ $m[2] ] ) ) {
			var_dump($m);
			die( "Wrong warning name $arg\n" );
		}
		CheckVars::$enabledWarnings[ $m[2] ] = strlen( $m[1] ) == 0;
	}
}

$cv->preloadFiles( array( $IP . '/includes/GlobalFunctions.php' ) );

foreach ( $argv as $arg ) {
	if ( substr( $arg, 0, 2 ) == '-W' )
		continue;
	
	$cv->load( $arg );
	$cv->execute();
}
if ( $cv->getGenerateDeprecatedList( ) ) {
	$cv->saveDeprecatedList( dirname( __FILE__ ) . "/deprecated.functions" );
}
if ( $cv->getGenerateParentList( ) ) {
	$cv->saveParentList( dirname( __FILE__ ) . "/parent.classes" );
}
