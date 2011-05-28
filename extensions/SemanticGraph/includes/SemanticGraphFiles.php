<?php
Class graphfile {

	var $filename;
	var $filepath;
	var $name;
	
	function __construct($f, $t) {
		global $wgUploadDirectory, $wgServer;
		global $wgUploadPath;
		$dir = $wgUploadDirectory."/".trim($t);
	        if ( ! is_dir( $dir ) ) { mkdir( $dir, 0777 ); }
		$this->filename = $dir."/".$f;
		$this->name = $f;
		$this->filepath = $wgServer.$wgUploadPath."/".trim($t)."/".$f;
	}
	
	function renderGraphFromMM( $mm, $width="100%", $height = "450") {
        global $wgSemanticGraphSettings;     
        
        $src = $this->filename . '.mm';
        $handle = fopen($src, "w");
        fwrite($handle, $mm);
        fclose($handle);
        
		$output = '<APPLET CODE="freemind.main.FreeMindApplet.class" ARCHIVE="'.$wgSemanticGraphSettings->mmAppletPath.'" WIDTH="'.$width.'" HEIGHT="'.$height.'">';
		$output .='<PARAM NAME="browsemode_initial_map" VALUE="'.$this->filepath.'.mm"/>';
		$output .='<PARAM NAME="selection_method" VALUE="delayed"/>';
        	$output .='<PARAM NAME="type" VALUE="application/x-java-applet;version=1.4"/>';
		$output .='<PARAM NAME="scriptable" VALUE="false"/>';
		$output .='<PARAM NAME="modes" VALUE="freemind.modes.browsemode.BrowseMode"/>';
		$output .='<PARAM NAME="initial_mode" VALUE="Browse"/>';
		$output .='</APPLET><br/><a href="'.$this->filepath.'.mm">Click to download...</a>';
		//return $output;
		//testing
		return $output;
	}

	function renderGraphFromDot( $timelinesrc, $engine, $width, $height, $svgview = 'false', $boxresize = "none", $zoom = "tofit" ) {
		//adapted from renderGraphviz function: http://www.mediawiki.org/wiki/Extension:GraphViz
        global $wgSemanticGraphSettings;
        global $wgServer;
        
        $src = $this->filename . '.dot';
        $imgn = $this->filename . '.png';
        $mapn = $this->filename . '.map';
	$svg = $this->filename . '.svg';

        $handle = fopen($src, "w");
        fwrite($handle, $timelinesrc);
        fclose($handle);
        
        if ($engine == 'dot') {
        	$cmd = $wgSemanticGraphSettings->dotCommand;
        } elseif ($engine == 'neato') {
        	$cmd = $wgSemanticGraphSettings->neatoCommand;
        } elseif ($engine == 'fdp') {
        	$cmd = $wgSemanticGraphSettings->fdpCommand;
        } elseif ($engine == 'circo') {
        	$cmd = $wgSemanticGraphSettings->circoCommand;
        } elseif ($engine == 'twopi') {
        	$cmd = $wgSemanticGraphSettings->twopiCommand;
        }

	if ($svgview == 'false') {
        
        	$cmdline    = wfEscapeShellArg($cmd).' -Tpng   -o '.wfEscapeShellArg($imgn).' '.wfEscapeShellArg($src);
        	$cmdlinemap = wfEscapeShellArg($cmd).' -Tcmapx -o '.wfEscapeShellArg($mapn).' '.wfEscapeShellArg($src);
        	@$ret = shell_exec($cmdline);
        	@$ret = shell_exec($cmdlinemap);
        
	        
               	@$txt = file_get_contents( $mapn );
               	$txt .= '<img src="'.$this->filepath.'.png" usemap="#'.$this->name.'" />';
               	$txt .= '<br/><a href="'.$this->filepath.'.dot">Click to download dot source...</a>';
                
        } else {
        	$cmdlinesvg = wfEscapeShellArg($cmd).' -Tsvg -o '.wfEscapeShellArg($svg).' '.wfEscapeShellArg($src);
        	@$ret = shell_exec($cmdlinesvg);
	
        	// PLACEHOLDER CODE FOR BIGPIC EXTENSION
        	
        	//$txt = "<html><head></head><body>"
		$txt = efBigSvg($svg, $width, $height, $boxresize, $zoom);
		//$txt .= '</body></html>';
		
		$handle = fopen($svg, "w");
        	fwrite($handle, $txt);
        	fclose($handle);
		//$iframe='<object data="'.$this->filepath.'.svg" type="image/svg+xml" width="'.$boxwidth.'" height="'.$boxheight.'">'
		//	.'<embed src="'.$this->filepath.'.svg" type="image/svg+xml"  width="'.$boxwidth.'" height="'.$boxheight.'"/></object>'; - DOESN't WORK
		//$iframe='<img src="'.$this->filepath.'.svg" alt="no svg viewer available - try firefox"/>'; - DOESN'T WORK
		$iframe = '<IFRAME src="'.$this->filepath.'.svg" width="'.$width.'px" height="'.$height.'px" scrolling="no" frameborder=1>'
		.'[Your user agent does not support frames or is currently configured not to display frames. However, you may visit'
		.'<A href="'.$this->filepath.'.svg">the related document.</A></IFRAME>';
		$iframe .= '<br/><a href="'.$this->filepath.'.dot">Click to download dot source...</a>';

	return $iframe;
		
        }
        
        
        
		
        return $txt;
        }
        
	
	function renderGraphFromHGraph($hgtext, $width = "800", $height = "600") {
			
        global $wgSemanticGraphSettings;     
        
        $src = $this->filename . '.xml';
        $handle = fopen($src, "w");
        fwrite($handle, $hgtext);
        fclose($handle);
        
        //hypergraph is very broken - this is a hack
        //we have to put all the files in exactly the same place.
        $output = '<html><head></head><body><applet code="hypergraph.applications.hexplorer.HExplorerApplet.class" ';
	$output .= 'archive="'.$wgSemanticGraphSettings->hypergraphAppletPath.'" ';
	$output .= 'width="'.$width.'" height="'.$height.'">';
	$output .= '<param name="file" value="'.$this->name.'.xml">';
	$output .='</applet></body></html>';

	$src = $this->filename . '.html';
        $handle = fopen($src, "w");
        fwrite($handle, $output);
        fclose($handle);
        
        $h = $height+50;
        
        $iframe = '<IFRAME src="'.$this->filepath.'.html" width="100%" height="'.$h.'"scrolling="auto" frameborder="0">'
		.'[Your user agent does not support frames or is currently configured not to display frames. However, you may visit'
		.'<A href="'.$this->filepath.'.html">the related document.</A></IFRAME>';

	return $iframe;	
	}
}

?>