module( 'Wikidom Serialization' );

function assertSerializations( tests ) {
	var htmlRenderer = new HtmlRenderer();
	var wikitextRenderer = new WikitextRenderer();
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
				'type': 'rule',
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
							'range': {
								'offset': 15,
								'length': 4
							},
							'data': {
								'title': 'Main_Page'
							}
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
							{
								'type': 'bold',
								'range': {
									'offset': 10,
									'length': 4
								}
							},
							{
								'type': 'italic',
								'range': {
									'offset': 19,
									'length': 6
								}
							}
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
	assertSerializations( [] );
} );

// Tables
test( 'Tables', function() {
	assertSerializations( [] );
} );
