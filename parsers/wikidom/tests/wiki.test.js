module( 'Wiki DOM Serialization' );

function assertSerializations( tests ) {
	var htmlRenderer = new wiki.HtmlRenderer();
	var wikitextRenderer = new wiki.WikitextRenderer();
	for ( var i = 0; i < tests.length; i++ ) {
		equals(
			htmlRenderer.render( tests[i].dom ),
			tests[i].html,
			'Serialize ' + tests[i].subject + ' to HTML'
		);
	}
	for ( var i = 0; i < tests.length; i++ ) {
		equals(
			wikitextRenderer.render( tests[i].dom ),
			tests[i].wikitext,
			'Serialize ' + tests[i].subject + ' to Wikitext'
		);
	}
}

test( 'Comments', function() {
	assertSerializations( [
		{
			'subject': 'comment',
			'dom': { 'blocks': [ {
				'type': 'comment',
				'text': 'Hello world!'
			} ] },
			'html': '<!--Hello world!-->',
			'wikitext': '<!--Hello world!-->'
		}
	] );
} );

test( 'Horizontal rules', function() {
	assertSerializations( [
		{
			'subject': 'horizontal rule',
			'dom': { 'blocks': [ {
				'type': 'horizontal-rule',
			} ] },
			'html': '<hr />',
			'wikitext': '----'
		}
	] );
} );

test( 'Headings', function() {
	assertSerializations( [
		{
			'subject': 'level 1 heading',
			'dom': { 'blocks': [ {
				'type': 'heading',
				'level': 1,
				'line': { 'text': 'Heading 1' }
			} ] },
			'html': '<h1>Heading 1</h1>',
			'wikitext': '=Heading 1='
		},
		{
			'subject': 'level 2 heading',
			'dom': { 'blocks': [ {
				'type': 'heading',
				'level': 2,
				'line': { 'text': 'Heading 2' }
			} ] },
			'html': '<h2>Heading 2</h2>',
			'wikitext': '==Heading 2=='
		},
		{
			'subject': 'level 3 heading',
			'dom': { 'blocks': [ {
				'type': 'heading',
				'level': 3,
				'line': { 'text': 'Heading 3' }
			} ] },
			'html': '<h3>Heading 3</h3>',
			'wikitext': '===Heading 3==='
		},
		{
			'subject': 'level 4 heading',
			'dom': { 'blocks': [ {
				'type': 'heading',
				'level': 4,
				'line': { 'text': 'Heading 4' }
			} ] },
			'html': '<h4>Heading 4</h4>',
			'wikitext': '====Heading 4===='
		},
		{
			'subject': 'level 5 heading',
			'dom': { 'blocks': [ {
				'type': 'heading',
				'level': 5,
				'line': { 'text': 'Heading 5' }
			} ] },
			'html': '<h5>Heading 5</h5>',
			'wikitext': '=====Heading 5====='
		},
		{
			'subject': 'level 6 heading',
			'dom': { 'blocks': [ {
				'type': 'heading',
				'level': 6,
				'line': { 'text': 'Heading 6' }
			} ] },
			'html': '<h6>Heading 6</h6>',
			'wikitext': '======Heading 6======'
		},
		{
			'subject': 'level 1 heading with annotated text',
			'dom': { 'blocks': [ {
				'type': 'heading',
				'level': 1,
				'line': {
					'text': 'Heading with a link',
					'annotations': [
						{
							'type': 'ilink',
							'range': { 'offset': 15, 'length': 4 },
							'data': { 'namespace': 'Main', 'title': 'Main_Page' }
						}
					]
				}
			} ] },
			'html': '<h1>Heading with a <a href="/wiki/Main_Page">link</a></h1>',
			'wikitext': '=Heading with a [[Main_Page|link]]='
		},
	] );
} );

test( 'Paragraphs', function() {
	assertSerializations( [
   		{
			'subject': 'paragraph with a single line of plain text',
			'dom': { 'blocks': [ {
				'type': 'paragraph',
				'lines': [ { 'text': 'Line with plain text' } ]
			} ] },
			'html': '<p>Line with plain text</p>',
			'wikitext': 'Line with plain text'
		},
		{
			'subject': 'paragraph with multiple lines of plain text',
			'dom': { 'blocks': [ {
				'type': 'paragraph',
				'lines': [
					{ 'text': 'Line with plain text' },
					{ 'text': 'Line with more plain text' }
				]
			} ] },
			'html': '<p>Line with plain text\nLine with more plain text</p>',
			'wikitext': 'Line with plain text\nLine with more plain text'
		},
		{
			'subject': 'paragraph with a single line of annotated text',
			'dom': { 'blocks': [ {
				'type': 'paragraph',
				'lines': [
					{
						'text': 'Line with bold and italic text',
						'annotations': [
							{ 'type': 'bold', 'range': { 'offset': 10, 'length': 4 } },
							{ 'type': 'italic', 'range': { 'offset': 19, 'length': 6 } }
						]
					}
				]
			} ] },
			'html': '<p>Line with <strong>bold</strong> and <em>italic</em> text</p>',
			'wikitext': 'Line with \'\'\'bold\'\'\' and \'\'italic\'\' text'
		}
   	] );
} );

// Lists
test( 'Lists', function() {
	assertSerializations( [
  		{
			'subject': 'numbered list',
			'dom': { 'blocks': [ {
				'type': 'list',
				'style': 'number',
				'items': [
					{ 'line': { 'text': '1' } },
					{ 'line': { 'text': '2' } },
					{ 'line': { 'text': '3' } }
				]
			} ] },
			'html': '<ol>\n<li>1</li>\n<li>2</li>\n<li>3</li>\n</ol>',
			'wikitext': '# 1\n# 2\n# 3'
		},
  		{
			'subject': 'bulleted list',
			'dom': { 'blocks': [ {
				'type': 'list',
				'style': 'bullet',
				'items': [
					{ 'line': { 'text': '1' } },
					{ 'line': { 'text': '2' } },
					{ 'line': { 'text': '3' } }
				]
			} ] },
			'html': '<ul>\n<li>1</li>\n<li>2</li>\n<li>3</li>\n</ul>',
			'wikitext': '* 1\n* 2\n* 3'
		},
  		{
			'subject': 'mixed-style nested lists',
			'dom': { 'blocks': [ {
				'type': 'list',
				'style': 'bullet',
				'items': [
					{
						'line': { 'text': '1' },
						'lists': [
							{
								'style': 'number',
								'items': [
									{ 'line': { 'text': '1.1' } },
									{ 'line': { 'text': '1.2' } },
									{ 'line': { 'text': '1.3' } }
								]
							}
						]
					},
					{ 'line': { 'text': '2' } }
				]
			} ] },
			'html': '<ul>\n<li>1\n<ol>\n<li>1.1</li>\n<li>1.2</li>\n<li>1.3</li>\n</ol>'
				+ '\n</li>\n<li>2</li>\n</ul>',
			'wikitext': '* 1\n*# 1.1\n*# 1.2\n*# 1.3\n* 2'
		}
	] );
} );

// Tables
test( 'Tables', function() {
	assertSerializations( [
		{
			'subject': 'table with headings and data',
			'dom': { 'blocks': [{
				'type': 'table',
				'rows': [
					[
				        {
							'type': 'heading',
							'document': { 'blocks': [{
								'type': 'paragraph',
								'lines': [{ 'text': 'A' }]
							}] }
						},
				        {
							'type': 'heading',
							'document': { 'blocks': [{
								'type': 'paragraph',
								'lines': [{ 'text': 'B' }]
							}] }
						}
			        ],
					[
				        {
							'type': 'data',
							'document': { 'blocks': [{
								'type': 'paragraph',
								'lines': [{ 'text': '1' }]
							}] }
						},
				        {
							'type': 'data',
							'document': { 'blocks': [{
								'type': 'paragraph',
								'lines': [{ 'text': '2' }]
							}] }
						}
			        ]
				]
			}] },
			'html': '<table>\n<tr>\n<th>A</th>\n<th>B</th>\n</tr>\n<tr>\n'
				+ '<td>1</td>\n<td>2</td>\n</tr>\n</table>',
			'wikitext': '{|\n!A\n!B\n|-\n|1\n|2\n|}'
		},
		{
			'subject': 'table with attributes',
			'dom': { 'blocks': [{
				'type': 'table',
				'attributes': {
					'class': 'wikitable'
				},
				'rows': [
					[
						{
							'type': 'data',
							'attributes': {
								'class': 'abc'
							},
							'document': { 'blocks': [{
								'type': 'paragraph',
								'lines': [{ 'text': 'abc' }]
							}] }
						}
					]
				]
			}] },
			'html': '<table class="wikitable">\n<tr>\n<td class="abc">abc</td>\n</tr>\n</table>',
			'wikitext': '{|class="wikitable"\n|class="abc"|abc\n|}'
		}
	] );
} );

test( 'Transclusion', function() {
	assertSerializations( [
		{
			'subject': 'transclusion',
			'dom': { 'blocks': [ {
				'type': 'transclusion',
				'namespace': 'Template',
				'title': 'Test'
			} ] },
			'html': '<a href="/wiki/Template:Test">Template:Test</a>',
			'wikitext': '{{Test}}'
		}
	] );
} );

test( 'Parameter', function() {
	assertSerializations( [
		{
			'subject': 'transclusion',
			'dom': { 'blocks': [ {
				'type': 'parameter',
				'name': '1'
			} ] },
			'html': '{{{1}}}',
			'wikitext': '{{{1}}}'
		}
	] );
} );
