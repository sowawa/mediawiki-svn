<?php
Class renderer {
	
	var $renderer;
	var $template; 
	var $lastSize;
	
	function __construct($r) {
	 	global $wgSemanticGraphSettings;
	 	$this->renderer = $r;
	 	$this->template = $wgSemanticGraphSettings->template[$r];
	 }
	
	 function enter($type, $args) {
	 	$temtext = $this->template[$type];
	 	if (array_key_exists('name', $args) && $args['name'] != '') {
	 		$temtext = str_replace("@NAME@",$args['name'],$temtext);
	 	}
		if (array_key_exists('editform', $args) && $args['editform'] != '') {
	 		$temtext = str_replace("@FORM_LNK@",$args['editform'],$temtext);
	 	}
	 	if (array_key_exists('fold', $args) && $args['fold'] != '') {
	 		$temtext = str_replace("@FOLDED@",$args['fold'],$temtext);
	 	}
	 	
	 	if (array_key_exists('child', $args) && $args['child'] != '') {
	 		$temtext = str_replace("@CHILD_ID@",$this->makeStringSafe($args['child']->getDBkey()),$temtext);
	 		$temtext = str_replace("@CHILD_LNK@",$args['child']->getLocalURL(),$temtext);
	 		$temtext = str_replace("@CHILD_LABEL@",$this->prettyText($args['child']->getPrefixedText()),$temtext);
			$temtext = str_replace("@CHILD_PAGE@",str_replace(' ',"_",$args['child']->getText()),$temtext);
			$temtext = str_replace("@CHILD@",$args['child']->getText(),$temtext);
	 	}
	 	if (isset($args['parent']) && $args['parent'] != '') {
	 		$temtext = str_replace("@PAR_ID@",$this->makeStringSafe($args['parent']->getDBkey()),$temtext);
			$temtext = str_replace("@PARENT@",$args['parent']->getText(),$temtext);
	 	}
	 	return $temtext;
	 }

	 function makeStringSafe($storagename) {
	 	$storagename = str_replace("&",'_amp_',$storagename);
	 	$storagename = str_replace("#",'_shrp_',$storagename);
	 	$storagename = str_replace("/",'_fs_',$storagename);
	 	$storagename = str_replace("\\",'_bs_',$storagename);
	 	$storagename = str_replace(" ",'__',$storagename);
	 	$storagename = str_replace(":",'_col_',$storagename);
	 	$storagename = str_replace(";",'_sem_',$storagename);
	 	$storagename = str_replace("'",'_apos_',$storagename);
	 	$storagename = str_replace("\"",'_quot_',$storagename);
	 	$storagename = str_replace("(",'_opbra_',$storagename);
	 	$storagename = str_replace(")",'_clbra_',$storagename);
	 	return $storagename;
	 }
	 
	 function prettyText($str) {
	 	if ($this->renderer == "hypergraph") {
	 		$str = str_replace('<','&lt;',$str);
	 		$str = str_replace('>','&gt;',$str);
	 		$str = str_replace("'",'&apos;',$str);
	 		$str = str_replace("\"",'&quot;',$str);
			$str = preg_replace('|(&)(\s*\b\w+\b(?!;))|','$1amp;$2',$str);
	 	}
	 	if ($this->renderer == "dot") {
	 		$newstr = '';
	 		foreach (explode(" ",$str) as $word) {
	 			$newstr .= $word;
	 			if ((strlen($newstr) - strrpos($newstr, '\n'))>10) {
	 				$newstr .= '\n';
	 			} else {
	 				$newstr .= " ";
	 			}
	 		}
	 		$str = trim($newstr);
	 		$str = str_replace("\"",'\"',$str);
	 		$str = str_replace("\(",'\(',$str);
	 		$str = str_replace("\)",'\)',$str);
	 	}
	 	return $str;
	 }

	 function removeDuplicates($str) {
	 	
	 	$r = '';
	 	foreach (explode("\n", $str) as $line) {
	 		if ($line != '') {
	 			if (strpos($r, $line) === false) {
	 				$r .= $line."\n";
	 			}
	 		}
	 	}
	 	return $r;
	 }
}
?>