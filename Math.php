<?php
#
# Takes LaTeX fragments, sends them to a helper program (texvc) for rendering
# to rasterized PNG and HTML and MathML approximations. An appropriate
# rendering form is picked and returned.
#
# by Tomasz Wegrzanowski, with additions by Brion Vibber (2003, 2004)
#

class MathRenderer {
	var $mode = MW_MATH_MODERN;
	var $tex = "";
	var $inputhash = "";
	var $hash = "";
	var $html = "";
	var $mathml = "";
	var $conservativeness = 0;
	
	function MathRenderer( $tex ) {
		$this->tex = $tex;
 	}
	
	function setOutputMode( $mode ) {
		$this->mode = $mode;
	}

	function render() {
		global $wgMathDirectory, $wgTmpDirectory, $wgInputEncoding;
		global $wgTexvc;
	
		if( $this->mode == MW_MATH_SOURCE ) {
			# No need to render or parse anything more!
			return ('$ '.htmlspecialchars( $this->tex ).' $');
		}
		
		if( !$this->_recall() ) {
			# Ensure that the temp and output directories are available before continuing...
			if( !file_exists( $wgMathDirectory ) ) {
				if( !@mkdir( $wgMathDirectory ) ) {
					return $this->_error( "math_bad_output" );
				}
			} elseif( !is_dir( $wgMathDirectory ) || !is_writable( $wgMathDirectory ) ) {
				return $this->_error( "math_bad_output" );
			}
			if( !file_exists( $wgTmpDirectory ) ) {
				if( !@mkdir( $wgTmpDirectory ) ) {
					return $this->_error( "math_bad_tmpdir" );
				}
			} elseif( !is_dir( $wgTmpDirectory ) || !is_writable( $wgTmpDirectory ) ) {
				return $this->_error( "math_bad_tmpdir" );
			}
			
			if( !is_executable( $wgTexvc ) ) {
				return $this->_error( "math_notexvc" );
			}
			$cmd = $wgTexvc." ".
				escapeshellarg($wgTmpDirectory)." ".
				escapeshellarg($wgMathDirectory)." ".
				escapeshellarg($this->tex)." ".
				escapeshellarg($wgInputEncoding);
			wfDebug( "TeX: $cmd" );
			$contents = `$cmd`;
		
			if (strlen($contents) == 0) {
				return $this->_error( "math_unknown_error" );
			}
			
			$retval = substr ($contents, 0, 1);
			if (($retval == "C") || ($retval == "M") || ($retval == "L")) {
				if ($retval == "C")
					$this->conservativeness = 2;
				else if ($retval == "M")
					$this->conservativeness = 1;
				else
					$this->conservativeness = 0;
				$outdata = substr ($contents, 33);
		
				$i = strpos($outdata, "\000");
		
				$this->html = substr($outdata, 0, $i);
				$this->mathml = substr($outdata, $i+1);
		
				$sql_html = "'".wfStrencode($this->html)."'";
				$sql_mathml = "'".wfStrencode($this->mathml)."'";
			} else if (($retval == "c") || ($retval == "m") || ($retval == "l"))  {
				$this->html = substr ($contents, 33);
				if ($retval == "c")
					$this->conservativeness = 2;
				else if ($retval == "m")
					$this->conservativeness = 1;
				else
					$this->conservativeness = 0;
				$sql_html = "'".wfStrencode($this->html)."'";
				$this->mathml = '';
				$sql_mathml = 'NULL';
			} else if ($retval == "X") {
				$outhtml = '';
				$this->mathml = substr ($contents, 33);
				$sql_html = 'NULL';
				$sql_mathml = "'".wfStrencode($this->mathml)."'";
				$this->conservativeness = 0;
			} else if ($retval == "+") {
				$this->outhtml = '';
				$this->mathml = '';
				$sql_html = 'NULL';
				$sql_mathml = 'NULL';
				$this->conservativeness = 0;
			} else {
				$errbit = htmlspecialchars( substr($contents, 1) );
				switch( $retval ) {
					case "E": return $this->_error( "math_lexing_error", $errbit );
					case "S": return $this->_error( "math_syntax_error", $errbit );
					case "F": return $this->_error( "math_unknown_function", $errbit );
					default:  return $this->_error( "math_unknown_error", $errbit );
				}
			}
		
			$this->hash = substr ($contents, 1, 32);
			if (!preg_match("/^[a-f0-9]{32}$/", $this->hash)) {
				return $this->_error( "math_unknown_error" );
			}
		
			if( !file_exists( "$wgMathDirectory/{$this->hash}.png" ) ) {
				return $this->_error( "math_image_error" );
			}
			
			# Now save it back to the DB:
			$outmd5_sql = wfStrencode(pack("H32", $this->hash));
		
			$md5_sql = wfStrencode( pack("H32", $this->md5) ); # Binary packed, not hex
			$sql = "REPLACE INTO math VALUES ('".$md5_sql."', '".$outmd5_sql."', ".$this->conservativeness.", ".$sql_html.", ".$sql_mathml.")";
			
			$res = wfQuery( $sql, DB_WRITE, "MathRenderer::render" );
			# we don't really care if it fails
		}
		
		return $this->_doRender();
	}
	
	function _error( $msg, $append = "" ) {
		$mf   = htmlspecialchars( wfMsg( "math_failure" ) );
		$munk = htmlspecialchars( wfMsg( "math_unknown_error" ) );
		$errmsg = htmlspecialchars( wfMsg( $msg ) );
		$source = htmlspecialchars($this->tex);
		return "<strong class='error'>$mf ($errmsg$append): $source</strong>\n";
	}
	
	function _recall() {
		global $wgMathDirectory;
		
		$this->md5 = md5( $this->tex );
		
		$md5_sql = wfStrencode( pack("H32", $this->md5) ); # Binary packed, not hex
		$sql = "SELECT math_outputhash,math_html_conservativeness,math_html,math_mathml FROM math WHERE math_inputhash = '$md5_sql'";
		$res = wfQuery( $sql, DB_READ, "MathRenderer::_recall" );
		
		if( $rpage = wfFetchObject ( $res ) ) {
			# Tailing 0x20s can get dropped by the database, add it back on if necessary:
			$xhash = unpack( "H32md5", $rpage->math_outputhash . "                " );
			$this->hash = $xhash ['md5'];
			
			$this->conservativeness = $rpage->math_html_conservativeness;
			$this->html = $rpage->math_html;
			$this->mathml = $rpage->math_mathml;
			
			if( file_exists( "$wgMathDirectory/{$this->hash}.png" ) ) {
				return true;
			}
		}
		
		# Missing from the database and/or the render cache
		return false;
	}

	# Select among PNG, HTML, or MathML output depending on 
	function _doRender() {
		if( $this->mode == MW_MATH_MATHML && $this->mathml != '' ) {
			return "<math xmlns='http://www.w3.org/1998/Math/MathML'>{$this->mathml}</math>";
		}
		if (($this->mode == MW_MATH_PNG) || ($this->html == '') ||
		   (($this->mode == MW_MATH_SIMPLE) && ($this->conservativeness != 2)) ||
		   (($this->mode == MW_MATH_MODERN || $this->mode == MW_MATH_MATHML) && ($this->conservativeness == 0))) {
			return $this->_linkToMathImage();
		} else {
			return $this->html;
		}
	}

	function _linkToMathImage() {
		global $wgMathPath;
		$url = htmlspecialchars( "$wgMathPath/{$this->hash}.png" );
		$alt = trim(str_replace("\n", ' ', htmlspecialchars( $this->tex )));
		return "<img class='tex' src=\"$url\" alt=\"$alt\" />";
	}

}

function renderMath( $tex ) {
	global $wgUser;
	$math = new MathRenderer( $tex );
	$math->setOutputMode( $wgUser->getOption("math"));
	return $math->render();
}

?>
