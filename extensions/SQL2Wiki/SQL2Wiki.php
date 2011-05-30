<?php
# Alert the user that this is not a valid entry point to MediaWiki if they try to access the special pages file directly.
if (!defined('MEDIAWIKI')) {
        echo <<<EOT
To install this extension put the following line in LocalSettings.php:
require_once( "\$IP/extensions/SQL2Wiki/SQL2Wiki.php" );
EOT;
        exit( 1 );
}
$wgExtensionCredits['parserhook'][] = array(
	'name' => 'SQL2Wiki',
	'author' => 'freakolowsky [Jure Kajzer] original version by Patrick Müller',
	'url' => 'http://www.mediawiki.org/wiki/Extension:SQL2Wiki',
	'description' => 'Show SQL data directly in the page contents..',
	'descriptionmsg' => 'sql2wiki-desc',
	'version' => '1.0.0',
);

$wgExtensionCredits['specialpage'][] = array(
	'name' => 'SQL2Wiki',
	'author' => 'Jure Kajzer',
	'url' => 'http://www.mediawiki.org/wiki/Extension:SQL2Wiki',
	'description' => 'Run SQL2Wiki code on-click',
	'descriptionmsg' => 'sql2wiki-special',
	'version' => '1.0.0',
);

$dir = dirname(__FILE__) . '/';
$wgExtensionMessagesFiles['SQL2Wiki'] = $dir . 'SQL2Wiki.i18n.php';

$wgAutoloadClasses['SQL2Wiki'] = $dir . 'SQL2Wiki.body.php';
$wgHooks['ParserFirstCallInit'][] = 'SQL2Wiki::initTags';

$wgAutoloadClasses['SpecialSQL2Wiki'] = $dir . 'SpecialSQL2Wiki.php';
$wgSpecialPages['SQL2Wiki'] = 'SpecialSQL2Wiki';


// list of database contact data
$wgExSql2WikiDatabases = array();



/*








$wgExtensionFunctions[] = "wfSQL2Wiki";

$wgAutoloadClasses['sql2wiki'] = $dir . 'SQL2Wiki_body.php';
$wgExtensionMessagesFiles['sql2wiki'] = $dir . 'SQL2Wiki.i18n.php';
$wgSpecialPages['sql2wiki'] = 'SQL2Wiki';

$sql2wiki_DB_handles = array();

require_once($dir . 'SQL2Wiki_body.php');

$wgOracleDBMSEnabled = false;

function SQL2Wiki_enableDBMSOutput($dbObj, $state = true) {
	global $wgOracleDBMSEnabled;
	$wgOracleDBMSEnabled = $state;
	if ($state)
		$dbObj->doQuery ('begin dbms_output.enable(null); end;');
	else
		$dbObj->doQuery ('begin dbms_output.disable; end;');
}

function SQL2Wiki_getDBMSOutput($dbObj) {
	global $wgOracleDBMSEnabled;
		
	if ($wgOracleDBMSEnabled === false)
		return false;

	$out = array();
	$qReturn = null;
	$wdc = 0;
	$qReturn = $dbObj->doQuery('select column_value from table(get_output_lines())');
	while(($qLine = $qReturn->fetchObject()) !== FALSE) {
			$out[] = $qLine->column_value;
	}

	return $out;
}

function wfSQL2Wiki() {
	global $wgParser;
	$wgParser->setHook( "sql2wiki", "renderSQL" );
	$wgParser->setHook( "plsql2wiki", "renderPLSQL" );
}

function SQL2Wiki_execute($db,$input,$enable_output,&$dbObj,&$result,&$output,&$error){
	global $wgDBtype;
        global $sql2wiki_DB_handles;
	
	reset ($sql2wiki_DB_handles);
	$bFound = false;
	while(list($index,$val)=each($sql2wiki_DB_handles)) {
		if ( $db == $index ) {
			$aParams = explode(";", $val);
	
			foreach($aParams as $parameter) {
				if( count( $aParams ) < 3 ){
						$error="Error in DB_handler definition !";
				                return false;
				}
				$host = trim($aParams[0]);
				$user = trim($aParams[1]);
				$pass = trim($aParams[2]);
				$charset = trim($aParams[3]);
				$bFound = true;
			}
		}
	}
	if ( !$bFound ){
			$error="Error in DB_handler definition !";
	                return false;
	}
	
	$dbObj = new DatabaseOracle($db, $user, $pass, $host, false, 128);
	if (!$dbObj->isOpen()){
		$error='<b>SQL2Wiki failed to connect to DB &quot;'.$db.'&quot;</b>';	
                return false;
	}

        $ignore = $dbObj->ignoreErrors(true);

      	SQL2Wiki_enableDBMSOutput($dbObj, $enable_output);

        $result = $dbObj->doQuery ($input);

        $output='';
        if ($enable_output){
           $output = implode(SQL2Wiki_getDBMSOutput($dbObj), "\n");
           if ($output === false){
		$error='<b>SQL2Wiki completed successfully</b>';
                return false;
      	   }
	}
    
        $dbObj->ignoreErrors($ignore);

	if ($dbObj->lastError() != null){
		$error='<b>SQL2Wiki exited with error: '.$dbObj->lastError().' '.$input.'</b>';	
                return false;
	}	
        elseif (isset($argv["quiet"])){
		$error='';
	        return false;
	}
       
        return true;
}


function renderSQL( $input, $argv, &$parser ) {
        global $wgOut;

	$db = $argv["database"];
	
        if (isset($argv["preexpand"]) && $argv["preexpand"] == 'true') {
		$input = $parser->recursiveTagParse($input);
	}

        if (!SQL2Wiki_execute($db,$input,false,$dbObj,$result,$output,$error))
           return $error;

	if (isset($argv["inline"])) {
		$field_separator = isset($argv["fieldseparator"]) ? $argv["fieldseparator"] : '';
		$line_separator = isset($argv["lineseparator"]) ? $argv["lineseparator"] : '';

		while (($line = $result->fetchObject()) !== FALSE) {
			if ($output != '')
				$output .= $line_separator."\n";

			foreach ($line as $value){
				$output .= $value.$field_separator;
			}
			$output = substr($output, 0, strlen($output)-strlen($field_separator));
		}

		if ($argv["inline"] != '') {
			$other_params = '';
			foreach($argv as $key=>$value)
				if (!in_array($key, array('database', 'inline', 'fieldseparator', 'lineseparator', 'cache', 'expand', 'preexpand')))
					$other_params .= ' '.$key.'="'.trim($value, '"').'"';
			$output = '<'.$argv["inline"].$other_params.'>'.$output.'</'.$argv["inline"].'>';
		}
	} else {
		$table_style = isset($argv["tablestyle"]) ? ' style="'.$argv["tablestyle"].'" ' : ' style="border: black solid 1px;" ';
		$h_row_style = isset($argv["hrowstyle"]) ? ' style="'.$argv["hrow_style"].'" ' : '';
		$h_cell_style = isset($argv["hcellstyle"]) ? ' style="'.$argv["hcellstyle"].'" ' : ' style="font-weight: bold;" ';
		$row_style = isset($argv["rowstyle"]) ? ' style="'.$argv["rowstyle"].'" ' : '';
		$cell_style = isset($argv["cellstyle"]) ? ' style="'.$argv["cellstyle"].'" ' : '';

		# Create Table Header
		$output .= '<table border=1 cellspacing=0 cellpadding=3'.$table_style.'>';
		$output .= '<tr'.$h_row_style.'>';
		$line = $result->fetchObject();
		foreach ($line as $key=>$value) {
			$output .= '<th'.$h_cell_style.'>'.$key.'</th>';
		}
		$output .= '</tr>';
		
		# Create Table Data Rows
		do {
			$output .= '<tr'.$row_style.'>';
			foreach ($line as $value){
			$output .= '<td'.$cell_style.'>'.$value.'</td>';
		
			}
			$output .= '</tr>';
		} while (($line = $result->fetchObject()) !== FALSE);
		# End of Table Tag
		$output .= '</table>';
	}
	
	
	if (isset($argv["cache"]) && $argv["cache"] == 'off') {
		$parser->disableCache();
	} elseif (isset($argv["cache"]) && $argv["cache"] == 'manual') {
		if (!isset($argv["inline"])) {
			$refresh_url = preg_replace('/(.*?)&action=[^&]*(.*)/i', '$1$2', $_SERVER['REQUEST_URI']).
				'&action=purge';
			$output .= '<a href="'.$refresh_url.'"><small>Refresh</small></a>';
		} 
	} elseif (isset($argv["inline"])) {
		$parser->disableCache();
	}	

	if ($wgOut->getPageTitle() != 'SQL2Wiki' && isset($argv["expand"]) && $argv["expand"] == 'true') {
		$output = $parser->recursiveTagParse($output);
	}

	@$dbObj->close();
	return $output;
}

function renderPLSQL( $input, $argv, &$parser ) {
	global $wgDBtype;
        global $wgOut;
	
	$db = $argv["database"];
	
	if (strtolower($wgDBtype) != 'oracle' && strtolower($wgDBtype) != 'oracless')
		return '<b>This function is available only for Oracle and OracleSS DB class.</b>';	

	$dbms_output = isset($argv["dbmsoutput"]) ? $argv["dbmsoutput"] : false;

	if (isset($argv["preexpand"]) && $argv["preexpand"] == 'true') {
		$input = $parser->recursiveTagParse($input);
	}

        if (!SQL2Wiki_execute($db,$input,$dbms_output,$dbObj,$result,$output,$error))
           return $error;
         
	$wrapper = isset($argv["wrapper"]) ? $argv["wrapper"] : '';
	if ($wrapper != '') {
		$other_params = '';
		foreach($argv as $key=>$value)
			if (!in_array($key, array('database', 'wrapper', 'quiet', 'cache', 'expand', 'preexpand', 'dbmsoutput')))
				$other_params .= ' '.$key.'="'.trim($value, '"').'"';
		
		$output = '<'.$wrapper.$other_params.'>'.$output.'</'.$wrapper.'>';
	}

	if (!isset($argv["cache"]) || $argv["cache"] != 'on') {
		$parser->disableCache();
	}	
	
	if ($wgOut->getPageTitle() != 'SQL2Wiki' && isset($argv["expand"]) && $argv["expand"] == 'true' ) {
		$output = $parser->recursiveTagParse($output);
	}

	@$dbObj->close();
	return $output;
}

*/
