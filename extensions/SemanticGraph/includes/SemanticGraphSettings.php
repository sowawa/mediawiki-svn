<?php
class SemanticGraphSettings {
	public $dotCommand;
	public $neatoCommand;
	public $mmAppletPath;
	public $hypergraphAppletPath;
	public $options;
	public $colourlist;
	public $lastError;
	public $template;
	public $maxToDispUnfolded;
	public $dummyCategoryLinkProperty;
	public $dummyWikiLinkProperty;

	function __construct() {
		global $wgServer, $wgScriptPath;
		if ( ! (stristr (PHP_OS, 'WIN' ) === FALSE) ) {
			$this->dotCommand = 'C:/Program Files/Graphviz/bin/dot.exe';
				$this->neatoCommand = 'C:/Program Files/Graphviz/bin/dot.exe';
		} else {
			$this->dotCommand = '/usr/bin/dot';
			$this->neatoCommand = '/usr/bin/neato';
			$this->circoCommand = '/usr/bin/circo';
			$this->fdpCommand = '/usr/bin/fdp';
			$this->twopiCommand = '/usr/bin/twopi';
		}
		$this->mmAppletPath = $wgServer.$wgScriptPath.'/extensions/SemanticGraph/libs/freemindbrowser.jar';
		$this->hypergraphAppletPath = 'hyperapplet.jar';
		$this->maxToDispUnfolded = 10;
		$this->dummyCategoryLinkProperty = "SG category of";
		$this->dummyWikiLinkProperty = "SG linked to";

		$this->colourlist = array ('black', 'blue', 'cyan', 'darkGray', 'gray', 'green', 'lightGray', 'magenta', 'orange', 'pink', 'red', 'white', 'yellow');
		$commonhelp = array (
			'property' => 'the property type to traverse, can be one or many, if this is empty the tree will be built from category and wiki links.'
				.' Using the dummy property: '.$this->dummyCategoryLinkProperty.' will result in a category graph.'
				.' Using the dummy property: '.$this->dummyWikiLinkProperty.' will result in a graph of all wiki links.',
			'editform' => 'a form name that can be used to edit red links (i.e. links to pages which dont exist, without "Form:"',
			'editquery' => 'a query string that will be passed to semantic forms, seperator is ampersand',
			'boxresize' => 'this only does anything if both width and height are specified when it will shrink the box until its aspect ration is the same as the image',
			'zoom' => 'this only does anything if a box is not resized to the image aspect ratio. Values of tofit or topage will fit zoom an image into a box either to fit both dimensions (topage) or by one dimension (tofit). Also can take a numeric value without percent sign as a percentage to zoom the image',
			);
		$this->options = array (
			'smm' => array (
				'resource' => array ('type' => 'page', 'check' => array('mand','someexist'),
					'help' => 'the root node of the mind map'),
				'property' => array ('type' => 'page', 'list' => true, 'action' => array('toarray','nsproperty'), 'check' => 'someexist', 'default' => $this->dummyCategoryLinkProperty.",".$this->dummyWikiLinkProperty,
					'help' => $commonhelp['property']),
				'depth' => array ('type' => 'num', 'default' => 5),
				'height' => array ('type' => 'string', 'default' => 450),
				'editform' => array ('type' => 'string', 'default' => false,
					'help' => $commonhelp['editform']),
				'editquery' => array ('type' => 'string', 'default' => false,
					'help' => $commonhelp['editquery']),
				'fold' => array ('type' => 'string', 'default' => 'large', 'values' => array('level1', 'level2', 'level3', 'none', 'large'),
					'help' => 'folding of generated map: by default branches with more than 10 items are folded')
			),
			'sgraph' => array (
				'resource' => array ('type' => 'page', 'list' => true, 'action' => 'toarray', 'check' => array('mand','someexist'),
					'help' => 'the initial nodes of the graph, can be one or many'),
				'property' => array ('type' => 'page', 'list' => true, 'action' => array('toarray','nsproperty'), 'check' => 'someexist', 'default' => $this->dummyCategoryLinkProperty.",".$this->dummyWikiLinkProperty,
					'help' => $commonhelp['property']),
				'depth' => array ('type' => 'num', 'default' => 3,
					'help' => 'deep graphs can be expensive'),
				'engine' => array ('type' => 'string', 'default' => 'dot', 'values' => array('dot','neato','circo','twopi','fdp')),
				'editform' => array ('type' => 'string', 'default' => false,
					'help' => $commonhelp['editform']),
				'editquery' => array ('type' => 'string', 'default' => false,
					'help' => $commonhelp['editquery']),
				'dotoptions' => array ('type' => 'string', 'default' => "rankdir=LR, ranksep=1;\nnode [fontsize=10, fontname=Helvetica];\n",
					'help' => 'a dot string which is inserted at the top of the graph'),
				'width' => array ('type' => 'string', 'default' => '800'),
				'height' => array ('type' => 'string', 'default' => '600'),
				'svg' => array ('type' => 'string', 'default' => 'false'),
				'boxresize' => array ('type' => 'string' , 'default' => 'none', 'values' => array('none','toimage'),
					'help' => "This option is for svg mode only. ".$commonhelp['boxresize']),
				'zoom' => array ('type' => 'string', 'default' => 'tofit' ,
					'help' => "This option is for svg mode only. ".$commonhelp['zoom']),
			),
			'mm2' => array (
				'image' => array ('type' => 'page', 'action' => 'nsimage', 'check' => array('mand','someexist'),
					'help' => 'a page name in the image namespace that refers to an uploaded freemind map e.g. Image:map.mm, also map.mm should work'),
				'height' => array ('type' => 'string', 'default' => 450),
				'editform' => array ('type' => 'string', 'default' => false,
					'help' => $commonhelp['editform']),
				'editquery' => array ('type' => 'string', 'default' => false,
					'help' => $commonhelp['editquery']),
				'fold' => array ('type' => 'string', 'values' => array('level1', 'level2', 'level3', 'none', 'large'))
			),
			'shypergraph' => array (
				'resource' => array ('type' => 'page','list' => true, 'action' => 'toarray', 'check' => array('mand','someexist'),
					'help' => 'a set of pages that define the initial nodes of the hypergraph. This appears to behave best if it is single'),
				'property' => array ('type' => 'page','list' => true, 'action' => array('toarray','nsproperty'), 'check' => 'someexist',  'default' => $this->dummyCategoryLinkProperty.",".$this->dummyWikiLinkProperty,
					'help' => $commonhelp['property']),
				'colours' => array ('type' => 'string', 'list' => true, 'default' => 'black', 'action' => 'toarray', 'values' => $this->colourlist,
					'help' => 'a list of colours for the property links - not yet implemented'),
				'depth' => array ('type' => 'num', 'default' => 5),
				'width' => array ('type' => 'string', 'default' => 800),
				'height' => array ('type' => 'string', 'default' => 600),
				'editform' => array ('type' => 'string', 'default' => false,
					'help' => $commonhelp['editform']),
				'editquery' => array ('type' => 'string', 'default' => false,
					'help' => $commonhelp['editquery']),
			),
			'bigpic' => array (
				'image' => array ('type' => 'page', 'action' => 'nsimage', 'check' => array('mand','someexist'),
					'help' => 'a reference to an image page (that contains an image file!)'),
				'width' => array ('type' => 'num', 'default' => null),
				'height' => array ('type' => 'num', 'default' => null),
				'boxresize' => array ('type' => 'string' , 'default' => 'none', 'values' => array('none','toimage'),
					'help' => $commonhelp['boxresize']),
				'zoom' => array ('type' => 'string', 'default' => 'tofit' ,
					'help' => $commonhelp['zoom']),
			),
			'locallinks' => array (
				'property' => array ('type' => 'page', 'list' => true, 'action' => array('toarray','nsproperty'), 'check' => 'someexist', 'default' => $this->dummyCategoryLinkProperty.",".$this->dummyWikiLinkProperty,
					'help' => $commonhelp['property']),
				'depth' => array ('type' => 'num', 'default' => 3),
			),

		);
		$this->template = array (
			'dot' => array(
				'preamble' => 'digraph "@NAME@" {'."\n",
				'nodeExist' => "@CHILD_ID@ [label=\"@CHILD_LABEL@\" URL=\"$wgServer@CHILD_LNK@\", fontcolor=\"blue\"];"."\n",
				'nodeNotExist'=> "@CHILD_ID@ [label=\"@CHILD_LABEL@\" URL=\"$wgServer@FORM_LNK@\", fontcolor=\"red\"];"."\n",
				'link' => "@PAR_ID@ -> @CHILD_ID@;"."\n",
				'conclusion' => "}"."\n"
			),
			'hypergraph' => array(
				'preamble' => '<?xml version="1.0" encoding="iso-8859-1"?><!DOCTYPE GraphXML SYSTEM "GraphXML.dtd">'
					.'<GraphXML xmlns:xlink="http://www.w3.org/1999/xlink"><graph id="@NAME@">'
					.'<style><line tag="node" class="main" colour="black"/>'
					.'<line tag="node" class="exist" colour="blue"/>'
					.'<line tag="node" class="noexist" colour="red"/>'
					.'</style>'."\n",
				'nodeRoot' =>  '<node class="main" name="@CHILD_ID@"><label>@CHILD_LABEL@</label><dataref><ref xlink:href="'.$wgServer.'@CHILD_LNK@"/></dataref></node>'."\n",
				'nodeExist' => '<node name="@CHILD_ID@"><label>@CHILD_LABEL@</label><style><line colour="blue"/></style><dataref><ref xlink:href="'.$wgServer.'@CHILD_LNK@"  xlink:show="new"/></dataref></node>'."\n",
				'nodeNotExist'=> '<node name="@CHILD_ID@"><label>@CHILD_LABEL@</label><style><line colour="red"/></style><dataref><ref xlink:href="'.$wgServer.'@FORM_LNK@"  xlink:show="new"/></dataref></node>'."\n",
				'link' => '<edge source="@PAR_ID@" target="@CHILD_ID@"/>'."\n",
				'conclusion' => "</graph></GraphXML>"
			),
			'mm' => array(
				'nodeText' => '@CHILD_LABEL@',
				'nodeID' => '@CHILD_ID@',
				'linkExist' => $wgServer.'@CHILD_LNK@',
				'linkNoExist' => $wgServer.'@FORM_LNK@',
				'existColour' => '#0000ff',
				'notExistColour' => '#ff0000',
			)
		);

	}

	function usage($functn) {
		$t = "<p><b>USAGE: {{#$functn:property=value|property=value1,value2,value3|...}}</b></p><table border='1'><tr><th>property</th><th>type</th><th>list</th><th>values</th><th>checks</th><th>help</th></tr>";
		foreach ($this->options[$functn] as $rowk => $rowv) {
			$t .= '<tr><td>'.$rowk.'</td>';
			$t .= '<td>'.$rowv['type'].'</td>';
			if (isset($rowv['list'])) {
				$t .= '<td>'.$rowv['list'].'</td>';
			} else {
				$t .= '<td></td>';
			}
			if (isset($rowv['values'])) {
				$t .= '<td><ul>';
				foreach((array) $rowv['values'] as $value) {
					$t .= '<li>'.$value.'</li>';
				}
				$t .= '</ul></td>';
			} else {
				$t .= '<td></td>';
			}
			if (isset($rowv['check'])) {
				$t .= '<td><ul>';
				foreach((array) $rowv['check'] as $value) {
					$t .= '<li>'.$value.'</li>';
				}
				$t .= '</ul></td>';
			} else {
				$t .= '<td></td>';
			}
			if (isset($rowv['help'])) {
				$t .= '<td>'.$rowv['help'].'</td>';
			} else {
				$t .= '<td></td>';
			}
			$t .= '</tr>';
		}
		$t .= '</table>';
		return $t;
	}

	function parseOptions( $optarray, $type = 'ERROR' ) {
		global $wgContLang, $wgScriptPath;
		array_shift( $optarray ); //first is &$parser, strip it
		if ( !$optarray ) {
			$this->lastError = 'no options specified for function '.$type;
			return false; //no category specified, return nothing
		}
		if ( !array_key_exists($type, $this->options) ) {
			$this->lastError = 'unsupported function call '.$type;
			return false;
		}

		$argv = array();
		//populate defaults in output array
		foreach ((array) $this->options[$type] as $optionk => $optionv) {
			if (isset($optionv['default'])) {
				$argv[$optionk] = $optionv['default'];
			} // if there is no default do not set it yet as we may need to check whether it exists
		}
		//join flat parameter list into output array
		foreach ((array) $optarray as $p ) {
			if ( preg_match('/^\s*(\S.*?)\s*=\s*(.*?)\s*$/', $p, $m) ) {
				$k = $m[1];
				$v = $m[2];
			}
			else {
				$k = trim($p);
				$v = true;
			}
			$argv[$k] = $v;
		}

		//$argv array is initialised with defaults and options now we need to validate and do any conversions requested
		foreach ((array) $this->options[$type] as $optionk => $optionv) {
			if (isset($argv[$optionk])) {
				//options are now as strings, maybe comma seperated to represent arrays
				//sometimes these will be converted to arrays
				if (!is_array($argv[$optionk])) {
						$argv[$optionk] = array($argv[$optionk]);
				}
				if (!isset($optionv['action'])) $optionv['action']=array();
				if (!isset($optionv['check'])) $optionv['check']=array();
				if (!isset($optionv['list'])) $optionv['list']=false;
				//do actions on output array values
				foreach ((array) $optionv['action'] as $action) {
					switch ($action) {
						case 'toarray':

							//TODO: got to fix this - too abstract & unnecesary use of arrays confusing the life out of me
							$y = array();
							$z = array();
							foreach($argv[$optionk] as $x) {
								$y = preg_split("/\s*,\s*/",$x);
								$z = array_merge($z, $y);
							}
							$argv[$optionk] = $z;
							break; // split on whitespace and comma combinations - converts flat csv into an array
						case 'infermode':
							//HELLO
							//here we do some advanced parsing looking for some assumptions about
							//usually property relationships
							//this assumes that the properties are now an array
							//?property or property implies focus->property->object
							//property? implies object->property->focus
							//?propery? implies either (useful for transitivity)

							//=as assiging a label? do this first if so.
							//*as defining transitivity?
							//?property.?property as defining subrelationships?

							foreach ($argv[$optionk] as &$prop) {
								if (strpos($prop,"?")===false) {
									//this is the default unspecified condition equivalent to a non transitive
									$argv['propertyBehaviour']=array('property'=>$prop,'asDirect'=>true,'asInverse'=>false);
									//TODOTODOTODO
								}
							}
						case 'nsproperty':
						case 'nscategory':
						case 'nsform':
						case 'nsimage':
							if ($action == 'nsproperty') $y= SMW_NS_PROPERTY;
							if ($action == 'nscategory') $y= NS_CATEGORY;
							if ($action == 'nsform') $y= SF_NS_FORM;
							if ($action == 'nsimage') $y= NS_IMAGE;
							$y = $wgContLang->getNsText($y);
							$t = array();
							foreach ($argv[$optionk] as $orig) {
								$x = explode(":", $orig);
								if (count($x) == 1) {
									$t[] = $y.":".$x[0];
								} else {
									if ($x[0] == $y) {
										$t[] = $orig;
									} else {
										$this->lastError = 'option '.$optionk.' should be a '.$y.', not a '.$x[0];
										return false; //wrong namespace
									}
								}

							}
							$argv[$optionk] = $t;
							break;

					}
				}
			}


			//do specific checks
			foreach ((array) $optionv['check'] as $check) {
				switch ($check) {
					case 'mand':
						if (!isset($argv[$optionk])) {
							$this->lastError = 'no option specified for mandatory option '.$optionk;
							return false;
						}
						break;
					case 'someexist':
						if (isset($argv[$optionk])) {
							// this can be null if it is not mandatory and not set
							// but if it is not null and no valid page is found then there is an error.
							// twisted logic.

							$valid = array();
							foreach((array) $argv[$optionk] as $contender) {
								$temp = Title::newFromText($contender);
								if (!is_null($temp) && ($temp->exists() || $temp->getDBkey() == Title::makeTitle(SMW_NS_PROPERTY,$this->dummyCategoryLinkProperty)->getDBkey() || $temp->getDBkey() == Title::makeTitle(SMW_NS_PROPERTY,$this->dummyWikiLinkProperty)->getDBkey() )) {
									// special exceptions for dummy properties introduced to do category and non semantic links
									$valid[] = $contender;
								}
							}
							$argv[$optionk] = $valid;
							if (count($valid) == 0) {
								$this->lastError = 'no valid pages found for option '.$optionk;
								return false;
							}
						}
						break;
				}
			}
			//check values
			if (isset($optionv['values'])) {
				$valid = true;
				foreach((array) $argv[$optionk] as $contender) {
					if (!in_array($contender, $optionv['values'])) {
						$this->lastError = 'unexpected value '.$contender.' for option '.$optionk;
						return false;
					}
				}
			}


			// chack types and reconvert single elements to variables from arrays
			if (isset($argv[$optionk])) {
				$output = null;
				foreach ((array) $argv[$optionk] as $contender) {
					switch ($optionv['type']) {
						case 'page':
							if (($x = Title::newFromText($contender)) == null) {
								$this->lastError = 'invalid title '.$contender.' for option '.$optionk;
								return false;
							}
							$output[] = $x->getPrefixedDBkey();
							break;
						case 'string':
							$output[] = $contender;
							//$output[] = trim(settype($contender, 'string'));
							break;
						case 'num':
							if (!is_numeric($contender)) {
								$this->lastError = 'not a number '.$contender.' for option '.$optionk;
								return false;
							}
							$output[] = $contender;
							break;
					}
				}
				$argv[$optionk] = $output;
			}
			if($optionv['list'] == false && is_array($argv[$optionk])) {
				$argv[$optionk] = $argv[$optionk][0];
				//if this isn't supposed to be a list then the first item is taken and array converted to single variable.
			}
		}
		//ok variable initialised and checked for validity
		//page type variables are held as array of PrefixedDBkeys or a single PrefixedDBkey.

		$name = '';
		if (is_array($argv['resource'])) {
		foreach ((array) $argv['resource'] as $r) {
			$name .= $r;
		}
		} elseif(isset($argv['resource'])) {
			$name .= $argv['resource'];
		} else {
			$name .= "noresource";
		}
		if (is_array($argv['property'])) {
			foreach ((array) $argv['property'] as $p) {
				$name .= $p;
			}
		} elseif (isset($args['property'])) {
			$name .= $args['property'];
		} else {
			$name .= "noproperty";
		}
		$argv['name'] = md5($name);



		//specific form edit link setup
		if ($argv['editform'] != false) {
			$argv['editform'] = $wgScriptPath."/index.php/Special:AddData?form=".$argv['editform']."&target=@CHILD_PAGE@";
			if ($argv['editquery'] != '') {
				$argv['editform'] .= '&'.$argv['editquery'];
				unset($argv['editquery']);
			}
		} else {
			$argv['editform'] = "@CHILD_LNK@?action=edit";
			unset($argv['editquery']);
		}

		$argv['type'] = $type;
		if ($type == 'sgraph') {
			$argv['renderer']='dot';
		}
		if ($type == 'shypergraph') {
			$argv['renderer']='hypergraph';
			$argv['editform'] = str_replace("&","&amp;",$argv['editform']); //better ways to do this
		}
		if ($type == 'smm') {
			$argv['renderer']='mm';
		}
		if ($type == 'mm2') {
			$argv['renderer']='mm';
		}

		return $argv;
	}
}
