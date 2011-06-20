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
    var target = $('<div id="mw-parser-popup" style="position: relative; z-index: 9999; overflow: auto; background: white"><div class="editor" style="line-height: 1.5em; top: 0px; left: 0px; right: 0px; bottom: 0px; border: 1px solid gray">' + inside + '</div></div>').insertAfter(box);
    $('#wpTextbox1').css('display', 'none');

    onResize = function() {
        target.width(box.width())
            .height(box.height());
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
            match(this, function(node, other) {
                $(node).addClass('parseNodeHighlight');
                $(other).addClass('parseNodeHighlight');
            });
        }).delegate('.parseNode', 'mouseleave', function(event) {
            match(this, function(node, other) {
                $(node).removeClass('parseNodeHighlight');
                $(other).removeClass('parseNodeHighlight');
            });
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
    detail = detail || '';
    modes[className] = {
    	label: className,
		desc: '<p>Showing the page rendered with ' + className + '.</p>' + detail,
        render: function(src, dest) {
            var parser = new parserClass();
            parser.parseToTree(src, function(tree, err) {
                parser.treeToHtml(tree, function(node, err) {
                    dest.append(node);
                });
            });
        }
	};
	modes[className + '-tree'] = {
		label: className + ' tree',
		desc: '<p>Showing the page broken down to parse tree with ' + className + '.</p>' + detail,
        render: function(src, dest) {
            var parser = new parserClass();
            parser.parseToTree(src, function(tree, err) {
				$(dest).nodeTree( tree );
            });
        }
	};
	modes[className + '-roundtrip'] = {
		label: className + ' round-trip',
		desc: '<p>Showing the page as parsed, then returned to source via ' + className + '.</p>' + detail,
        render: function(src, dest) {
            var parser = new parserClass();
            parser.parseToTree(src, function(tree, err) {
                parser.treeToSource(tree, function(src2, err) {
                    var target = $('<div style="white-space: pre-wrap; font-family: monospace">').appendTo(dest);
                    target.html(diffString(src, src2));
                });
            });
        }
	};
	modes[className + '-inspect'] = {
		label: className + ' inspect',
		desc: '<p>Shows ' + className + '\'s HTML output and parse tree side-by-side.</p>' + detail,
        render: function(src, dest) {
            var parser = new parserClass();
            var treeMap = new HashMap(), renderMap = new HashMap();
            parser.parseToTree(src, function(tree, err) {
                var target = makeInspectorColumns(dest);
                var left = target.find('.left'), right = target.find('.right');
				left.nodeTree( tree, function( node, el ) {
					treeMap.put( node, el );
				});
                parser.treeToHtml(tree, function(node, err) {
                    right.append(node);
                    setupInspector(left, right, treeMap, renderMap);
                }, renderMap);
            });
        }
	};
};

$(document).ready( function() {
    /* Start trying to add items... */
    var editor = $('#wpTextbox1');
    if (editor.length > 0 && typeof $.fn.wikiEditor === 'function') {
        //$('#wpTextbox1').bind('wikiEditor-toolbar-buildSection-main', function() {
        var modes = {
            'source': {
                label: 'Source',
                desc: 'Showing the page\'s original wikitext source code, as you are used to editing it.',
                render: false
            }
        };
        addParserModes(modes, MediaWikiParser, 'MediaWikiParser');
        addParserModes(modes, FakeParser, 'FakeParser');
        addParserModes(modes, PegParser, 'PegParser', '<p>Peg-based parser plus FakeParser\'s output. <a href="http://pegjs.majda.cz/documentation">pegjs documentation</a>; edit and reselect to reparse with updated parser</p>');

        window.setTimeout(function() {
            // Great, now let's hook the booklet buttons... (explicit callbacks would be better)
            var hook = function(key, callback) {
                // using live since they haven't been created yet...
                // 'mouseup' as a hack since the upstream click handler cancels other event handlers
                $('#wikiEditor-ui-toolbar .sections .section-parser .index div[rel=' + key + ']').live('mouseup', callback);
            };
            var pages = {};
            $.each(modes, function(name, mode) {
                pages[name] = {
					'layout': 'table',
					'label': mode.label,
					'rows': [
						{
							'desc': { 'html': mode.desc }
						}
					]
				};
                var render = mode.render;
                hook(name, function() {
                    $('#pegparser-source').hide(); // it'll reshow; others won't need it
                    if (mode.render) {
                        var target = makeMagicBox('');
                        var src = $('#wpTextbox1').val();
                        var dest = target.find('div');
                        render(src, dest);
                    } else {
                        $('#mw-parser-popup').remove();
                        onResize = null;
                        $('#wpTextbox1').css('display', 'block');
                    }
                });
            });
            editor.wikiEditor( 'addToToolbar', {
                'sections': {
                    'parser': {
                        'label': 'Parser',
            			'type': 'booklet',
						'pages': pages
					}
				}
            } );

        }, 500 );
    } else {
        mw.log('No wiki editor');
    }
});

})(jQuery);
