<?php
function efBigPic($fileurl, $imgwidth, $imgheight, $boxwidth = null, $boxheight = null, $boxresize = 'none', $zoom = 'tofit') {
	
	global $wgBigPicCounter;
	@$wgBigPicCounter += 1;
	/*
	$fileurl, the URL to the image
	$imgwidth, 
	$imgheight, 
	$boxwidth = null, if null the value is calculated to maintain aspect ratio of image using boxheight.
	$boxheight = null, if null the value is calculated to maintain aspect ratio of image using boxwidth. 
		If both boxwidth and boxheight null will default to size of image 
	$boxresize = 'none'|'toimage', only does anything if both boxwidth and boxheight are specified
	$zoom = 'tofit'|'topage'|numeric value>0, will fit image into box either to fit all (topage) or by width (tofit) or any zoom value
	
	more than one of these functions in a page generates cross talk between script functions and unpredictable effects.
	*/
	
	$x=$imgwidth;
	$y=$imgheight;
	
	list($imgwidth, $imgheight, $boxwidth, $boxheight, $offsetx, $offsety) = boxsizing($imgwidth, $imgheight, $boxwidth, $boxheight, $boxresize, $zoom);
	
	$c = $wgBigPicCounter;
	//clip:rect(0px '.$boxheight.'px '.$boxwidth.'px 0px);
	
	$html = '<div id=maptable style="position:relative;overflow:hidden;height:'.$boxheight.'px;'
		.'width:'.$boxwidth.'px;border:2px inset #aeaeae;">'
		.'<div id=zoom style="position:absolute;top:10px;left:10px;color:yellow;background-color:transparent; z-index:10;">'
		.'<a style="border:1px solid black;background-color:white; text-decoration:none; padding:2px;line-height:1.8em;color:black;" onclick="zoomIn()">Zoom in</a>&nbsp;'
		.'<a style="border:1px solid black;background-color:white; text-decoration:none; padding:2px;line-height:1.8em;color:black;" onclick="zoomOut()">Zoom out</a></div>'
		.'<div id=mover style="position:relative;height:'.$imgheight.'px;width:'.$imgwidth.'px;top:'.$offsety.'px;left:'.$offsetx.'px;" onmousedown="startDrag(event)">'
		.'<img style="height:100%;width:100%" ondragstart="return false" onmousedown="return false" src="'.$fileurl.'" id=earthMap title="" /></div></div>';
	
	$js = "<script type=\"text/javascript\">/*@cc_on @*/
		mapWidth=$imgwidth;
		mapHeight=$imgheight;
		function zoom(factor) {
			var diffWidth=mapWidth;
			mapWidth=mapWidth*factor;
			diffWidth-=mapWidth;
			var diffHeight=mapHeight;
			mapHeight=mapHeight*factor;
			diffHeight-=mapHeight;
			var img=document.getElementById('mover');
			img.style.left=(((img.offsetLeft-$boxwidth/2)*factor)+$boxwidth/2)+'px'
			img.style.width=mapWidth+'px';
			img.style.top=(((img.offsetTop-$boxheight/2)*factor)+$boxheight/2)+'px'
			img.style.height=mapHeight+'px'
		}
		function zoomOut() {
			zoom(0.8);
		}
		function zoomIn() {
			zoom(1.25);
		}
		function getDragParent(el) {
			var oldEl=el;
			while (el) {
				el=el.parentNode;
				if (el.id==\"maptable\" || el.nodeName.toUpperCase()=='BODY') {
					return oldEl;
				}
				oldEl=el;
			}
		}
		var offsetX,offsetY,draggingThing;
		function startDrag(e) {
			draggingThing=document.getElementById('mover'); 
			offsetX=e.clientX-draggingThing.offsetLeft;
			offsetY=e.clientY-draggingThing.offsetTop;
			document.body.onmousemove=moveDrag;
			document.body.onmouseup=endDrag;
			document.onselectstart=nullFunc;
		}
		function nullFunc(e) {
			return false;
		}
		function moveDrag(e) {
			e=e || event;
			if (draggingThing) {
				draggingThing.style.top=(e.clientY-offsetY)+'px';
				draggingThing.style.left=(e.clientX-offsetX)+'px';
				return true;
			}
		}
		function endDrag(e) {
			draggingThing=null;
			document.body.onmousemove=null;
			document.body.onmouseend=null;
			document.onselectstart=null;
		}
		</script>";
	
	return $js."\n".$html;
}

function boxsizing($imgwidth, $imgheight, $boxwidth = null, $boxheight = null, $boxresize = 'none', $zoom = 'tofit') {
	if ($boxwidth == null && $boxheight == null) {
		$boxwidth = $imgwidth;
		$boxheight = $imgheight;
	} else if ($boxwidth == null && $boxheight != null) {
		$boxwidth = $imgwidth * $boxheight/$imgheight;
		$imgwidth = $boxwidth;
		$imgheight = $boxheight;
	} else if ($boxwidth != null && $boxheight == null) {
		$boxheight = $imgheight * $boxwidth/$imgwidth;
		$imgwidth = $boxwidth;
		$imgheight = $boxheight;
	} else if ($boxresize == "toimage") {
		if (($imgwidth/$imgheight) < ($boxwidth/$boxheight)) {
			// img is more portrait oriented than box
			// shrink box width to correct aspect ratio
			$boxwidth = $imgwidth * $boxheight/$imgheight;
		} else {
			// img is more landscape oriented than box
			// shrink box height to corerct aspect ratio
			$boxheight = $imgheight * $boxwidth/$imgwidth;
		}
		$imgwidth = $boxwidth;
		$imgheight = $boxheight;
	} else	{
		$scale = array (($boxwidth/$imgwidth) , ($boxheight/$imgheight));
		if ($zoom == 'topage') {
				//shrink image to fit smallest dimension
				$imgwidth = min($scale) * $imgwidth;
				$imgheight = min($scale) * $imgheight;
		} else if ($zoom == 'tofit') {
				//shrink image to both dimensions
				$imgwidth = max($scale) * $imgwidth;
				$imgheight = max($scale) * $imgheight;
		}
	}
	
	/*
	if ((int) $zoom > 0) {
		//override calculated values if a percentage zoom has been specified
		$imgwidth = $x * $zoom/100;
		$imgheight = $y * $zoom/100;
	}
	*/
		//centre image in box
	$offsetx = ($boxwidth - $imgwidth)/2;
	$offsety = ($boxheight - $imgheight)/2;
	
	$offsetx = floor($offsetx);
	$offsety = floor($offsety);
	$boxwidth = floor($boxwidth);
	$imgwidth = floor($imgwidth);
	$boxheight = floor($boxheight);
	$imgheight = floor($imgheight);
	
	return array($imgwidth, $imgheight, $boxwidth, $boxheight, $offsetx, $offsety);
}

function efBigSvg($svgurl, $boxwidth = null, $boxheight = null, $boxresize = 'none', $zoom = 'tofit') {
	
	/*
	$fileurl, the URL to the image
	$imgwidth, 
	$imgheight, 
	$boxwidth = null, if null the value is calculated to maintain aspect ratio of image using boxheight.
	$boxheight = null, if null the value is calculated to maintain aspect ratio of image using boxwidth. 
		If both boxwidth and boxheight both null will default to size of image. 
	$boxresize = 'none'|'toimage', only does anything if both boxwidth and boxheight are specified when it will resize one dimension
		to maintain the image aspect ratio.
	$zoom = 'tofit'|'topage'|numeric value>0, will fit image into box either to fit all (topage) or by width (tofit) or any zoom value
	
	more than one of these functions in a page generates cross talk between script functions and unpredictable effects.
	*/
	
//echo $svgurl; exit(0);
	
	$svg = new DOMDocument();
	@$str = file_get_contents($svgurl);
	$svg->loadXML($str);
	
	$svgnode = $svg->getElementsByTagName('svg')->item(0);
	//get width and height from svg document - this is expressed in pt and needs to be changed to user units to align to browsers view of scale.
	$imgwidth = (int) $svgnode->getAttribute('width');
	$imgheight = (int) $svgnode->getAttribute('height');

	$x=$imgwidth;
	$y=-$imgheight; //graph viz has origin at bottom left. svg has origin at top left.
	list($imgwidth, $imgheight, $boxwidth, $boxheight, $offsetx, $offsety) = boxsizing($imgwidth, $imgheight, $boxwidth, $boxheight, $boxresize, $zoom);
	$svgnode->setAttribute('width',$boxwidth);
	$svgnode->setAttribute('height',$boxheight);
	$scale = $imgwidth/$x;
	
	$js = ' var fx='.(-$x/2).';
		var fy='.(-$y/2).';
		var scale='.$scale.';
		function zoom(factor) {
			scale = scale*factor;
			document.getElementById(\'mover\').setAttributeNS(null,"transform", translation(fx,fy,scale));
		}
		function zoomOut() {
			zoom(0.8);
		}
		function zoomIn() {
			zoom(1.25);
		}
		function panLeft() {
			pan('.($boxwidth/4).',0);
		}
		function panRight() {
			pan(-'.($boxwidth/4).',0);
		}
		function panUp() {
			pan(0,'.($boxheight/4).');
		}
		function panDown() {
			pan(0,-'.($boxheight/4).');
		}
		function pan(x,y) {
			fx = fx + x/scale;
			fy = fy + y/scale;
			document.getElementById(\'mover\').setAttributeNS(null,"transform", translation(fx,fy,scale));
		}
		function centre() {
			fx = '.(-$x/2).';
			fy = '.(-$y/2).';
			document.getElementById(\'mover\').setAttributeNS(null,"transform", translation(fx,fy,scale));
		}
		var draggingThing;
		var startx;
		var starty;
		function startDrag(evt) {
			draggingThing=document.getElementById(\'mover\');
			startx = evt.screenX;
			starty = evt.screenY;
			draggingThing.onmousemove=moveDrag;
			draggingThing.onmouseup=endDrag;
			draggingThing.onselectstart=nullFunc;
		}
		function nullFunc(evt) {
			return false;
		}
		function moveDrag(evt) {
			return true;
		}
		function endDrag(evt) {
			fx = fx-(startx-evt.screenX)/scale;
			fy = fy-(starty-evt.screenY)/scale;
			draggingThing.setAttributeNS(null,"transform", translation(fx,fy,scale));
			draggingThing=null;
		}
		function translation(x, y, s) {
			return \'translate('.($boxwidth/2).','.($boxheight/2).') scale(\'+s+\') translate(\'+x+\',\'+y+\')\';
		}';
		
	$js=preg_replace('|^\s*\r?\n|m','',$js); //strip whitespace to prevent some spurious wiki parsing
	$js=preg_replace('|^\s*|m','',$js);
	$js=preg_replace('|\s*\r?\n|m','',$js);	
	
	$scriptnode = $svg->createElement('script', $js);
	$scriptnode->setAttribute('type','text/ecmascript');
	

	//clear initial transforms applied by graphviz we will have to reimplement. should probably check here that current assumptions around way graphviz behaves are true.
	$svgnode->removeAttribute('viewBox');
	$svgnode->setAttribute('overflow','hidden'); // this has effect of removing scroll bars in viewing window which is good because we need to be incontrol of position
	//fix svg font-size="7.00" to font-size="7pt" for compatability
	foreach ($svg->getElementsByTagName('text') as $tnode) {
		$pt = $tnode->getAttribute('style');
		$pt = preg_replace('|font-size:([0-9]*)[^;]*|', 'font-size:$1pt', $pt);
		$tnode->setAttribute('style',$pt);
	}
	//export as svgtxt
	
	//fix javascript to interact with svg DOM mover element.
	
	
	
	$transform = 'translate('.($boxwidth/2).','.($boxheight/2).') ';
	//the original scale factor. This is what will be changed with zooming
	$transform .= 'scale('.$scale.') ';
	//initial focus point is centre of original image. This is what is going to be changed with panning
	$transform .= 'translate('.(-$x/2).','.(-$y/2).') ';
	// the processing order of these feels wrong to me - i wonder if this is a bug in firefox.
			
	$graph0 = $svg->getElementsByTagName('g')->item(0);
	$graph0->setAttribute('transform',$transform);
	$graph0->setAttribute('id','mover');
	$graph0->parentNode->insertBefore($scriptnode, $graph0);
	
	foreach($svg->getElementsByTagName('a') as $link) {
		$link->setAttributeNS('http://www.w3.org/1999/xlink','xlink:show','new');
	} // this needs to be done to get round usability iframe behaviour.
	
	$background = $svg->getElementsByTagName('polygon')->item(0);
	$background->setAttribute('onmousedown','startDrag(evt)');
		
	$zoomcontrols = '<g id="zoomcontrols" class="graph">'
		.'<rect id="background" style="fill:none;stroke:black;" x="0" y="0" width="'.$boxwidth.'" height="'.$boxheight.'"/>'
		.'<g id="zoomin" class="node" onclick="zoomIn()"><rect style="fill:#f0f0f0;stroke:black;" x="10" y="10" width="20" height="15"/><text text-anchor="middle" x="20" y="20" style="font-family:Arial;font-size:7pt;fill:black;">in</text></g>'
		.'<g id="zoomout" class="node" onclick="zoomOut()"><rect style="fill:#f0f0f0;stroke:black;" x="40" y="10" width="20" height="15"/><text text-anchor="middle" x="50" y="20"  style="font-family:Arial;font-size:7pt;fill:black;">out</text></g>'
		.'<g id="left" class="node" onclick="panLeft()"><polygon style="fill:#f0f0f0;stroke:black;" points="10,55  20,45 20,65"/></g>'
		.'<g id="right" class="node" onclick="panRight()"><polygon style="fill:#f0f0f0;stroke:black;" points="50,45  60,55 50,65"/></g>'
		.'<g id="up" class="node" onclick="panUp()"><polygon style="fill:#f0f0f0;stroke:black;" points="25,40 35,30 45,40"/></g>'
		.'<g id="down" class="node" onclick="panDown()"><polygon style="fill:#f0f0f0;stroke:black;" points="25,70 45,70 35,80"/></g>'
		.'<g id="centre" class="node" onclick="centre()"><ellipse style="fill:#f0f0f0;stroke:black;" cx="35" cy="55" rx="10" ry="10"/></g>'
		.'</g>';
	
	$svgtxt = $svg->saveXML($svg->documentElement);
	$svgtxt = str_replace("</svg>", $zoomcontrols."</svg>", $svgtxt); //apologies to DOM purists out there.



	
	//return $js;
	return $svgtxt;
}

?>