/**
 * @param {ParserContext} context
 */
function FakeParser(context) {
    // whee
    this.context = context || {};
}

/**
 * @param {string} text
 * @param {function(tree, error)} callback
 */
FakeParser.prototype.parseToTree = function(text, callback) {
    // quick and crappy :D
    var lines = text.split("\n");
	var blocks = [];
    var matches;
    /**
     * Subparse of inline links within a paragraph etc.
     * @param {string} line
     * @return {object[]} list of content subblocks
     */
    var linksParse = function(line) {
        var bits = line.split('[['),
            parts = [];
        if (bits[0] != '') {
            parts.push({
                type: 'text',
                text: bits[0]
            });
        }
        for (var i = 1; i < bits.length; i++) {
            var bit = bits[i];
            var bracketPos = bit.indexOf(']]');
            if (bracketPos === -1) {
                // not a link oh noooooo
                parts.push({
                    type: 'text',
                    text: '[[' + bit
                });
            } else {
                var link = bit.substr(0, bracketPos);
                var tail = bit.substr(bracketPos + 2);
                var linkbits = link.split('|');
                if (linkbits.length == 1) {
                    parts.push({
                        type: 'link',
                        target: link
                    });
                } else {
                    parts.push({
                        type: 'link',
                        target: linkbits[0],
                        text: linkbits.slice(1).join('|') // @fixme multiples for images etc
                    });
                }
                if (tail !== '') {
                    parts.push({
                        type: 'text',
                        text: tail
                    });
                }
            }
        }
        return parts;
    };
    /**
     * Subparse of all inline stuff within a paragraph etc.
     * @param {string} line
     * @return {object[]} list of content subblocks
     */
    var inlineParse = function(line) {
        var parts = [];
        var bits = line.split('<ref');
        var re = /^([^>]*)>(.*)<\/ref\s*>(.*)/;
        var re2 = /^([^>]*)\/>(.*)/;
        if (bits[0] != '') {
            // text before...
            $.merge(parts, linksParse(bits[0]));
        }
        $.each(bits.slice(1), function(i, bit) {
            var matches;
            var after;
            if ((matches = re.exec(bit)) != null) {
                var params = matches[1], text = matches[2];
                after = matches[3];
                parts.push({
                    type: 'ext',
                    name: 'ref',
                    params: params,
                    content: (text == '') ? [] : linksParse(text)
                });
            } else if ((matches = re2.exec(bit)) != null) {
                var params = matches[1];
                after = matches[2];
                parts.push({
                    type: 'ext',
                    name: 'ref',
                    params: params
                });
            } else {
                after = '<ref' + bit;
            }
            if (after != '') {
                $.merge(parts, linksParse(after));
            }
        });
        return parts;
    };
	$.each(lines, function(i, line) {
		if (line == '') {
			blocks.push({
				type: 'break'
			});
		} else if (matches = /^(={1,6})(.*)\1$/.exec(line)) {
            blocks.push({
                type: 'h',
                level: matches[1].length,
                text: matches[2]
            });
		} else {
            var parts = inlineParse(line);
			blocks.push({
				type: 'para',
				content: parts
			});
		}
	});
	var tree = {
		type: 'page',
		content: blocks
	};
	callback(tree, null);
};

/**
 * @param {object} tree
 * @param {function(tree, error)} callback
 */
FakeParser.prototype.expandTree = function(tree, callback) {
	// no-op!
	callback(tree, null);
};

/**
 * @param {object} tree
 * @param {function(domnode, error)} callback
 * @param {HashMap} inspectorMap
 */
FakeParser.prototype.treeToHtml = function(tree, callback, inspectorMap) {
	var self = this;
    var subParseArray = function(listOfTrees, node) {
		$.each(listOfTrees, function(i, subtree) {
			self.treeToHtml(subtree, function(subnode, err) {
				if (subnode) {
					node.append(subnode);
				}
			}, inspectorMap);
		});
    };
	var node;
	switch (tree.type) {
		case 'page':
			// A sequence of block-level elements...
			var page = $('<div class="parseNode"></div>');
            subParseArray(tree.content, page);
            if (self.context.refs) {
                // We're at the end; drop all the remaining refs!
                subParseArray([{
                    type: 'ext',
                    name: 'references'
                }], page);
            }
            node = page[0];
			break;
		case 'para':
			// A single-line paragraph.
			var para = $('<p class="parseNode"></p>');
            subParseArray(tree.content, para);
            node = para[0];
			break;
		case 'break':
			// Just a stub in the parse tree.
			break;
        case 'text':
            // hack hack
            node = document.createTextNode(tree.text);
            break;
        case 'link':
            var link = $('<a class="parseNode"></a>');
            link.text(tree.text || tree.target);
            link.attr('href', '/wiki/' + tree.target); // hack
            node = link[0];
            break;
        case 'extlink':
            var link = $('<a class="parseNode"></a>');
            link.text(tree.text || tree.target); // fixme? #d links, freelinks etc
            link.attr('href', tree.target); // hack: validate etc
            node = link[0];
            break;
        case 'h':
            var h = $('<h' + tree.level + ' class="parseNode"></h' + tree.level + '>').text(tree.text);
            node = h[0];
            break;
        case 'i':
            var h = $('<i class="parseNode"></i>').text(tree.text); // hack -- use contents[]
            node = h[0];
            break;
        case 'template':
            var t = $('<span class="parseNode template"></span>').text('{{' + tree.target);
            if ('params' in tree) {
                $.each(tree.params, function(i, param) {
                    var str = param.contents;
                    if ('name' in param) {
                        str = param.name + '=' + str;
                    }
                    var p = $('<span></span>').text('|' + str);
                    t.append(p);
                });
            }
            t.append('}}');
            node = t[0];
            break;
        case 'ext':
            if (tree.name == 'ref') {
                // Save the reference for later!
                // @fixme names etc?
                if (self.context.refs === undefined) {
                    self.context.refs = [];
                }
                self.context.refs.push(tree);
                var refNum = self.context.refs.length;
                var ref = $('<span class="ref parseNode">[</span>');
                $('<a></a>')
                    .text(refNum + '')
                    .attr('src', '#ref-' + refNum)
                    .appendTo(ref);
                ref.append(']');
                node = ref[0];
            } else if (tree.name == 'references') {
                // Force inline expansion of references with a given group
                // @fixme support multiple groups etc
                var references = $('<ol class="references parseNode"></ol>');
                var oldRefs = self.context.refs;
                self.context.refs = [];
                $.each(oldRefs, function(i, subtree) {
                    var ref = $('<li class="ref parseNode" id="ref-' + i + '"></li>');
                    if ('content' in subtree) {
                        subParseArray(subtree.content, ref);
                    }
                    references.append(ref);
                });
                node = references[0];
            } else if (tree.name == 'cite') {
                // Kinda like a ref but inline.
                // @fixme validate and output the tag parameters
                var cite = $('<span class="cite parseNode"></span>');
                if ('content' in tree) {
                    subParseArray(tree.content, cite);
                }
                node = cite[0];
            } else {
                // @fixme unrecognized exts should output as text + rendered contents?
        		callback(null, 'Unrecognized extension in parse tree');
                return;
            }
            break;
		default:
			callback(null, 'Unrecognized parse tree node');
            return;
	}
    if (node) {
        if (node.nodeType == 1) {
            $(node).data('parseNode', tree); // assign the node for the tree inspector
            if (inspectorMap) {
                inspectorMap.put(tree, node); // store for reverse lookup
            }
        }
		callback(node);
    } else {
        callback(null); // hmmmm
    }
};

/**
 * Collapse a parse tree back to source, if possible.
 * Ideally should exactly match the original source;
 * at minimum the resulting source should parse into
 * a tree that's identical to the current one.
 *
 * @param {object} tree
 * @param {function(text, error)} callback
 */
FakeParser.prototype.treeToSource = function(tree, callback) {
    var self = this;
    var subParseArray = function(listOfTrees) {
        var str = '';
		$.each(listOfTrees, function(i, subtree) {
			self.treeToSource(subtree, function(substr, err) {
				if (substr) {
                    str += substr;
				}
			});
		});
        return str;
    };
	var src;
	switch (tree.type) {
		case 'page':
            src = subParseArray(tree.content);
			break;
		case 'para':
			// A single-line paragraph.
            src = subParseArray(tree.content) + '\n';
			break;
		case 'break':
            src = '\n';
			break;
        case 'text':
            // In the real world, there might be escaping.
            src = tree.text;
            break;
        case 'link':
            src = '[[';
            src += tree.target;
            if (tree.text) {
                src += '|';
                src += tree.text;
            }
            src += ']]';
            break;
        case 'h':
            stub = '';
            for (var i = 0; i < tree.level; i++) {
                stub += '=';
            }
            src = stub + tree.text + stub + '\n';
            break;
        case 'ext':
            src = '<' + tree.name;
            if (tree.params) {
                src += ' ' + tree.params;
            }
            if ('content' in tree) {
                src += '>';
                src += subParseArray(tree.content);
                src += '</' + tree.name + '>';
            } else {
                src += '/>';
            }
            break;
        case 'template':
            src = '{{' + tree.target;
            if (tree.params) {
                for (var i = 0; i < tree.params.length; i++) {
                    var param = tree.params[i];
                    src += '|';
                    if ('name' in param) {
                        src += param.name + '=';
                    }
                    src += param.contents;
                }
            }
            src += '}}';
            break;
        case 'i':
            src = "''" + tree.text + "''";
            break;
        case 'extlink':
            src = '[' + tree.target + ' ' + tree.text + ']';
            break;
		default:
			callback(null, 'Unrecognized parse tree node');
            return;
	}
    if (src) {
        callback(src);
    } else {
        callback(null); // hmmmm
    }
};
