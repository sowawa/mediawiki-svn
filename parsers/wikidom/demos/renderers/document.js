{
	"blocks": [
		{
			"type": "heading",
			"level": 2,
			"line": {
				"text": "This is a heading",
				"annotations": [
					{
						"type": "italic",
						"range": {
							"offset": 10,
							"length": 7
						}
					}
				]
			}
		},
		{
			"type": "rule"
		},
		{
			"type": "comment",
			"text": "Hello wild world of wikitext!"
		},
		{
			"type": "table",
			"attributes": {
				"class": "wikitable",
				"width": "50%"
			},
			"rows": [
				[
					{
						"type": "heading",
						"document": {
							"blocks": [
								{
									"type": "paragraph",
									"lines": [
										{
											"text": "This is a table heading"
										}
									]
								}
							]
						}
					}
				],
				[
					{
						"type": "data",
						"document": {
							"blocks": [
								{
									"type": "paragraph",
									"lines": [
										{
											"text": "This is a table cell"
										}
									]
								},
								{
									"type": "paragraph",
									"lines": [
										{
											"text": "This is another paragraph in a table cell"
										}
									]
								}
							]
						}
					}
				]
			]
		},
		{
			"type": "paragraph",
			"lines": [
				{
					"text": "This is a test paragraph!",
					"annotations": [
						{
							"type": "italic",
							"range": {
								"offset": 0,
								"length": 4
							}
						},
						{
							"type": "xlink",
							"range": {
								"offset": 8,
								"length": 6
							},
							"data": {
								"url": "http://www.a.com"
							}
						},
						{
							"type": "bold",
							"range": {
								"offset": 10,
								"length": 4
							}
						}
					]
				},
				{
					"text": "Paragraphs can have more than one line.",
					"annotations": [
						{
							"type": "italic",
							"range": {
								"offset": 11,
								"length": 3
							}
						},
						{
							"type": "bold",
							"range": {
								"offset": 20,
								"length": 4
							}
						}
					]
				}
			]
		},
		{
			"type": "paragraph",
			"lines": [
				{
					"text": "Documents can have one or more blocks.",
					"annotations": [
						{
							"type": "bold",
							"range": {
								"offset": 0,
								"length": 9
							}
						}
					]
				}
			]
		},
		{
			"type": "list",
			"style": "number",
			"items": [
				{
					"line": {
						"text": "First item"
					},
					"lists": [
						{
							"type": "list",
							"style": "bullet",
							"items": [
								{
									"line": {
										"text": "First sub-item"
									}
								},
								{
									"line": {
										"text": "Second sub-item"
									}
								},
								{
									"line": {
										"text": "Third sub-item"
									}
								}
							]
						}
					]
				},
				{
					"line": {
						"text": "Second item",
						"annotations": [
							{
								"type": "italic",
								"range": {
									"offset": 0,
									"length": 6
								}
							}
						]
					}
				},
				{
					"line": {
						"text": "Third item",
						"annotations": [
							{
								"type": "bold",
								"range": {
									"offset": 0,
									"length": 5
								}
							}
						]
					}
				},
				{
					"line": {
						"text": "Fourth item",
						"annotations": [
							{
								"type": "ilink",
								"range": {
									"offset": 7,
									"length": 4
								},
								"data": {
									"title": "User:JohnDoe"
								}
							}
						]
					}
				}
			]
		}
	]
}