$( document ).ready( function() {
	var content = $( '#api-sandbox-content' );
	if ( !content.length ) {
		return;
	}
	content.show();	

	var action = $( '#api-sandbox-action' );
	var prop = $( '#api-sandbox-prop' );
	var propRow = $( '#api-sandbox-prop-row' );
	var help = $( '#api-sandbox-help' );
	var further = $( '#api-sandbox-further-inputs' );
	var actionCache = [];
	var propCache = [];

	function isset( x ) {
		return typeof x != 'undefined';
	}

	function showLoading( element ) {
		element.html( mw.msg( 'apisb-loading' ) ); // @todo:
	}

	function showLoadError( element ) {
		element.html( '<span style="error">' + mw.msg( 'apisb-load-error' ) + '</span>' );
	}

	function parseParamInfo( data ) {
		further.text( '' );
		if ( !isset( data.paraminfo ) 
			|| ( !isset( data.paraminfo.modules ) && !isset( data.paraminfo.querymodules ) )
			)
		{
			showLoadError( further );
			return;
		}
		if ( isset( data.paraminfo.modules ) ) {
			actionCache[data.paraminfo.modules[0].name] = data.paraminfo.modules[0];
			createInputs( actionCache[data.paraminfo.modules[0].name] );
		} else {
			propCache[data.paraminfo.querymodules[0].name] = data.paraminfo.querymodules[0];
			createInputs( propCache[data.paraminfo.querymodules[0].name] );
		}
		
	}

	function getQueryInfo( action, prop ) {
		var isQuery = action == 'query';
		if ( action == '-' || ( isQuery && prop == '-' ) ) {
			return;
		}
		var cached;
		if ( isQuery ) {
			cached = propCache[prop];
		} else {
			cached = actionCache[action];
		}
		if ( typeof cached != 'object' ) { // stupid FF adds watch() everywhere
			showLoading( further );
			var data = {
				format: 'json',
				action: 'paraminfo',
			};
			if (isQuery ) {
				data.querymodules = prop;
			} else {
				data.modules = action;
			}
			$.getJSON(
				mw.config.get( 'wgScriptPath' ) + '/api' + mw.config.get( 'wgScriptExtension' ),
				data,
				parseParamInfo
			);
		} else {
			createInputs( cached );
		}
	}

	function createInputs( info ) {
		help.text( info.description );
		var s = '<table class="api-sandbox-options">\n<tbody>';
		for ( var i = 0; i < info.parameters.length; i++ ) {
			var param = info.parameters[i];
			var name = info.prefix + param.name;
			var desc = mw.html.escape( param.description );
			if ( desc.indexOf( '\n ' ) >= 0 ) {
				desc = desc.replace( /^(.*?)((?:\n\s+[^\n]*)+)(.*?)$/m, '$1<ul>$2</ul>$3' );
				desc = desc.replace( /\n\s+([^\n]*)/g, '\n<li>$1</li>' );
			}
			desc = desc.replace( /\n(?!<)/, '\n<br/>' );

			s += '<tr><td class="api-sandbox-label"><label for="param-' + name + '">' + name + '=</label></td>'
				+ '<td class="api-sandbox-value">' + input( param, name )
				+ '</td><td>' + desc + '</td></tr>';
		}
		s += '\n</tbody>\n</table>\n';
		further.html( s );
	}

	function input( param, name ) {
		var s = param.type;
		var value = '';
		switch ( param.type ) {
			case 'limit':
				value = 10;
			case 'integer':
			case 'string':
			case 'user':
				s = '<input class="api-sandbox-input" id="param-' + name + '" value="' + value + '"/>';
				break;
			case 'bool':
			case 'boolean':
				s = '<input id="param-' + name + '" type="checkbox"/>';
				break;
			default:
				if ( typeof param.type == 'object' ) {
					var id = 'param-' + name;
					var attributes = { 'id': id };
					if ( isset( param.multi ) ) {
						attributes.multiple = 'multiple';
						s = select( param.type, attributes, false );
					} else {
						s = select( param.type, attributes, true );
					}
				}
		}
		return s;
	}

	function select( values, attributes, selected ) {
		var s = '<select class="api-sandbox-input"';
		if ( isset( attributes.multiple ) ) {
			s += ' size="' + values.length + '"';
		}
		for ( var a in attributes ) {
			s += ' ' + a + '="' + attributes[a] + '"';
		}
		s += '>';
		if ( typeof selected != 'array' ) {
			if ( selected ) {
				s += '\n<option value="" selected="selected">' + mw.msg( 'apisb-select-value' ) + '</option>';
			}
			selected = [];
		}
		for ( var i = 0; i < values.length; i++ ) {
			s += '\n<option value="' + values[i] + '"';
			if ( $.inArray( values[i], selected ) >= 0 ) {
				s += ' selected="selected"';
			}
			s += '>' + values[i] + '</option>';
		}
		s += '\n</select>';
		return s;
	}
	
	function updateBasics() {
		var a = action.val();
		var p = prop.val();
		var isQuery = a == 'query';
		if ( isQuery ) {
			propRow.show();
		} else {
			propRow.hide();
		}
		further.text( '' );
		help.text( '' );
		getQueryInfo( a, p );
	}

	action.change( updateBasics );
	prop.change( updateBasics );
	

});