/**
 * MediaWiki:Gadget-ParserPopups.js
 * Brion Vibber <brion @ pobox.com>
 * 2011-05-02
 *
 * Initial steps on some experiments to flip between various parsing methods to
 * compare source, parse trees, and outcomes.
 *
 * Adds a fold-out section in the editor (using enhanced toolbar) to swap view of:
 * - Source (your regular editable text)
 * - MediaWiki parser (parsed page as full HTML)
 * - Preprocessor tree (tree view of XML preprocessor tree; shows limited pre-parsing breakdown)
 * - FakeParser (a very primitive parser class in this gadget)
 * - FakeParser's parse tree
 * - FakeParser's output and parse tree side-by-side.
 *
 * The parsed views update to match the current editor state when you bump over to them.
 * In side-by-side view, matching items are highlighted on the two sides, and clicking
 * will scroll the related side into view if needed.
 */
(function( $ ) {


var onResize = null;
$(window).resize(function() {
	if (onResize) {
		onResize();
	}
});
$('.mw-pp-node').live('click', function() {
	var ul = $(this.parentNode).find('ul:first');
	if (ul.is(":hidden")) {
		ul.slideDown();
	} else {
		ul.slideUp();
	}
});

var makeMagicBox = function(inside) {
	$('#mw-parser-popup').remove();
	// line-height is needed to compensate for oddity in WikiEditor extension, which zeroes the line-height on a parent container
	var box = $('#wpTextbox1');
	var target = $('<div id="mw-parser-popup" style="width: 100%; overflow-y: auto; background: white"><div class="editor" style="line-height: 1.5em; top: 0px; left: 0px; right: 0px; bottom: 0px; border: 1px solid gray">' + inside + '</div></div>').insertAfter(box);
	$('#wpTextbox1').css('display', 'none');

	onResize = function() {
		//target.width(box.width())
		//    .height(box.height());
		target.height(box.height());
	};
	onResize();
	return target;
};

/**
 * Create two scrollable columns for an 'inspector' display.
 * @param {jQuery} dest -- jquery obj to receive the target
 * @return {jQuery}
 */
var makeInspectorColumns = function(dest) {
	var h = $('#wpTextbox1').height(); // hack
	var target = $(
		'<table style="width: 100%; height: ' + h + 'px">' +
		'<tr>' +
		'<td width="50%"><div class="left" style="overflow:auto; ' +
			'height: ' + h + 'px"></div></td>' +
		'<td width="50%"><div class="right" style="overflow:auto; ' +
			'height: ' + h + 'px"></div></td>' +
		'</tr>' +
		'</table>').appendTo(dest);
	return target;
};

/**
 * Set up 'inspector' events to highlight elements with matching parseNode data properties
 * between the given two sections.
 *
 * @param {jQuery} left
 * @param {jQUery} right
 */
var setupInspector = function(left, right, leftMap, rightMap) {
	var makeMagic = function(a, b, inspectorMap) {
		var match = function(aNode, callback) {
			var treeNode = $(aNode).data('parseNode');
			var bNode = treeNode && inspectorMap.get(treeNode);
			callback(aNode, bNode);
		};
		a.delegate('.parseNode', 'mouseenter', function(event) {
			$('.parseNodeHighlight').removeClass('parseNodeHighlight');
			match(this, function(node, other) {
				$(node).addClass('parseNodeHighlight');
				$(other).addClass('parseNodeHighlight');
			});
			event.preventDefault();
			return false;
		}).delegate('.parseNode', 'mouseleave', function(event) {
			$('.parseNodeHighlight').removeClass('parseNodeHighlight');
			event.preventDefault();
			return false;
		}).delegate('.parseNode', 'click', function(event) {
			match(this, function(node, other) {
				if (other) {
					// try to scroll the other into view. how... feasible is this? :DD
					var visibleStart = b.scrollTop();
					var visibleEnd = visibleStart + b.height();
					var otherStart = visibleStart + $(other).position().top;
					var otherEnd = otherStart + $(other).height();
					if (otherStart > visibleEnd) {
						b.scrollTop(otherStart);
					} else if (otherEnd < visibleStart) {
						b.scrollTop(otherStart);
					}
					event.preventDefault();
					return false;
				}
			});
		});
	};
	makeMagic(left, right, rightMap);
	makeMagic(right, left, leftMap);
};

var addParserModes = function(modes, parserClass, className, detail) {
	modes[className] = {
		'label': className,
		'action': {
			'type': 'callback',
			'execute': function( context ) {
				context.parserPlayground.parser = new parserClass();
				context.parserPlayground.fn.initDisplay();
			}
		}
	};
};

$(document).ready( function() {
	/* Start trying to add items... */
	var editor = $('#wpTextbox1');
	if (editor.length > 0 && typeof $.fn.wikiEditor === 'function') {
		//$('#wpTextbox1').bind('wikiEditor-toolbar-buildSection-main', function() {
		var listItems = {
			'sourceView': {
				'label': 'Source',
				'action': {
					'type': 'callback',
					'execute': function( context ) {
						context.parserPlayground.parser = undefined;
						context.parserPlayground.tree = undefined;
						context.parserPlayground.fn.hide();
					}
				}
			}
		};
		addParserModes(listItems, MediaWikiParser, 'MediaWikiParser');
		addParserModes(listItems, FakeParser, 'FakeParser');
		addParserModes(listItems, PegParser, 'PegParser', '<p>Peg-based parser plus FakeParser\'s output. <a href="http://pegjs.majda.cz/documentation">pegjs documentation</a>; edit and reselect to reparse with updated parser</p>');

		window.setTimeout(function() {
			var context = editor.data('wikiEditor-context');
			context.parserPlayground = {
				parser: new FakeParser(),
				tree: undefined,
				useInspector: false,
				fn: {
					initDisplay: function() {
						if (context.$parserContainer) {
							context.parserPlayground.fn.hide();
						}
						var $target = makeMagicBox('');
						$('#mw-parser-inspector').remove();
						var $inspector = $('<div id="mw-parser-inspector" style="position: relative; width: 100%; overflow-y: auto; height: 200px"></div>');
						$inspector.insertAfter($target);
						if (!context.parserPlayground.useInspector) {
							$inspector.hide();
						}

						context.$parserContainer = $target;
						context.$parserInspector = $inspector;

						var src = $('#wpTextbox1').val();
						var $dest = $target.find('div');

						var parser = context.parserPlayground.parser;
						var treeMap = context.parserPlayground.treeMap = new HashMap(),
							renderMap = new HashMap();
						parser.parseToTree(src, function(tree, err) {
							context.parserPlayground.tree = tree;
							if (context.parserPlayground.useInspector) {
								$inspector.nodeTree( tree, function( node, el ) {
									treeMap.put( node, el );
								});
							}
							parser.treeToHtml(tree, function(node, err) {
								$dest.append(node);
								context.parserPlayground.fn.setupEditor($target);
								setupInspector($target, $inspector, renderMap, treeMap);
							}, renderMap);
						});
					},
					hide: function() {
						$('#pegparser-source').hide(); // it'll reshow; others won't need it
						context.$iframe = undefined;
						context.$parserContainer.remove();
						context.$parserContainer = undefined;
						context.$parserInspector.remove();
						context.$parserInspector = undefined;
						context.$textarea.show();
					},
					toggleInspector: function() {
						if (context.parserPlayground.useInspector) {
							context.parserPlayground.useInspector = false;
							context.$parserInspector.hide();
						} else if ( context.parserPlayground.parser ) {
							context.parserPlayground.useInspector = true;
							context.$parserInspector.empty().show();
							context.$parserInspector.nodeTree( context.parserPlayground.tree, function( node, el ) {
								context.parserPlayground.treeMap.put( node, el );
							});
						}
						var target = 'img.tool[rel=inspector]';
						var $img = context.modules.toolbar.$toolbar.find( target );
						$img.attr('src', context.parserPlayground.fn.inspectorToolbarIcon());
					},
					inspectorToolbarIcon: function() {
						// When loaded as a gadget, one may need to override the wiki's own assets path.
						var iconPath = mw.config.get('wgParserPlaygroundAssetsPath', mw.config.get('wgExtensionAssetsPath')) + '/ParserPlayground/modules/images/';
						return iconPath + (context.parserPlayground.useInspector ? 'inspector-active.png' : 'inspector.png');
					},
					setupEditor: function($target) {
						$target.delegate('.parseNode', 'click', function(event) {
							if (context.parserPlayground.useInspector) {
								return true;
							}
							var node = $(this).data('parseNode');
							if ( node ) {
								// Ok, not 100% kosher right now but... :D
								var parser = context.parserPlayground.parser;
								parser.treeToSource(node, function(src, err) {
									alert( src );
								});
								event.preventDefault();
								return false;
							}
						});
					}
				}
			}
			editor.wikiEditor( 'addToToolbar', {
				'sections': {
					'richedit': {
						'label': 'Rich editor',
						'type': 'toolbar',
						'groups': {
							'mode': {
								'tools': {
									'mode': {
										'label': 'Mode',
										'type': 'select',
										'list': listItems
									},
									'inspector': {
										'label': 'Toggle inspector',
										'type': 'button',
										'icon': context.parserPlayground.fn.inspectorToolbarIcon(),
										'action': {
											'type': 'callback',
											'execute': context.parserPlayground.fn.toggleInspector
										}
									}
								}
							}
						}
					}
				}
			} );
		}, 500 );
	} else {
		mw.log('No wiki editor');
	}
});

})(jQuery);
