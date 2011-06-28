(function($){

	$.webfonts = {

		/* Version number */
		oldconfig: false,
		version: "0.1.2",
		set: function( font ) {
			if ( font === "none" ) {
				$.webfonts.reset();
				return;
			}

			var config = mw.config.get( "wgWebFonts" );
			if ( !font in config.fonts ) {
				console.log( "Requested unknown font", font );
				return;
			} else {
				config = config.fonts[font];
			}

			var styleString =
				"<style type='text/css'>\n@font-face {\n"
				+ "\tfont-family: '"+font+"';\n";
			if ( 'eot' in config ) {
				styleString += "\tsrc: url('"+config.eot+"');\n";
			}
			//If the font is present locally, use it.
			styleString += "\tsrc: local('"+ font +"'),";

			if ( 'woff' in config ) {
				styleString += "\t\turl('"+config.woff+"') format('woff'),";
			}
			if ( 'svg' in config ) {
				styleString += "\t\turl('"+config.svg+"#"+font+"') format('svg'),";
			}
			if ( 'ttf' in config ) {
				styleString += "\t\turl('"+config.ttf+"') format('truetype');\n";
			}

			styleString += "\tfont-weight: normal;\n}\n</style>\n";
			$(styleString).appendTo("head" );

			//save the current font and its size. Used for reset.
			if ( !$.webfonts.oldconfig ) {
				$.webfonts.oldconfig = {
					"font-family": $("body").css('font-family'),
					"font-size":   $("body").css('font-size')
				}
			}

			//Set the font, fallback fonts
			$("body").css('font-family',  font +", Helvetica, Arial, sans-serif");
			//we need to change the fonts of Input and Select explicitly.
			$("input").css('font-family',  font +", Helvetica, Arial, sans-serif");
			$("select").css('font-family',  font +", Helvetica, Arial, sans-serif");

			//scale the font of the page. Scale is in percentage.
			// For example scale = 1.2 means  scale the font by 120 percentage
			if ( 'scale' in config ) {
				$.webfonts.scale("body", config.scale);
			}

			if ( 'normalization' in config ) {
					$(document).ready(function() {
						$.webfonts.normalize(config.normalization);
				});
			}
			//set the font option in cookie
			$.cookie( 'webfonts-font', font, { 'path': '/', 'expires': 30 } );
		},

		/**
		 * Reset the font with old configuration
		 */
		reset: function(){
			$("body").css('font-family', $.webfonts.oldconfig["font-family"]);
			//we need to reset the fonts of Input and Select explicitly.
			$("input").css('font-family', $.webfonts.oldconfig["font-family"]);
			$("select").css('font-family', $.webfonts.oldconfig["font-family"]);
			//reset the font size from old configuration
			$("body").css('font-size', $.webfonts.oldconfig["font-size"]);
			//remove the cookie
			$.cookie( 'webfonts-font', 'none' );
		},

		/**
		 * Scale the font of the page by given percentage
		 * @param selecter CSS selector
		 * @param  percentage of scale. eg 1.2 for 120% scale
		 */
		scale : function (selecter, percentage){
			  $(selecter).each(function(i) {
				  var currentSize = parseInt($(this).css("font-size"));
				  $(this).css("font-size", Math.round( currentSize * percentage));
			});
		},

		/**
		 * Does a find replace of string on the page.
		 * @param normalization_rules hashmap of replacement rules.
		 */
		normalize: function(normalization_rules){
			$.each(normalization_rules, function(search, replace) {
				var search_pattern = new RegExp(search,"g");
				return $("*").each(function(){
				var node = this.firstChild,
				  val,
				  new_val;
				if ( node ) {
				  do {
					if ( node.nodeType === 3 ) {
					  val = node.nodeValue;
					  new_val = val.replace(search_pattern, replace );
					  if ( new_val !== val ) {
						  node.nodeValue = new_val;
					  }
					}
				  } while ( node = node.nextSibling );
				}
			  });
			});
		},
                 
                /**
                 * Setup the font selection menu. 
                 * It also apply the font from cookie, if any.
                 */
		setup: function() {
			var haveSchemes = false;
			var config = mw.config.get( "wgWebFontsAvailable" );
			// Build font dropdown
			$fontsmenu = $( '<ul />' ).attr('id','webfonts-fontsmenu');
			for ( var scheme in config ) {
				$fontlink = $( '<input>' )
					.attr("type","radio")
					.attr("name","font")
					.attr("id","webfont-"+config[scheme])
					.attr("value",config[scheme] );
					
				$fontlabel =  $( '<label />' )
					.attr("for","webfont-"+config[scheme])
					.append( $fontlink )
					.append( config[scheme] )
							
				$fontmenuitem = $( '<li />' )
					.val( config[scheme] )
					.append( $fontlabel )
					

				haveSchemes = true;
				//some closure trick :)
				(function (font) {
					$fontlink.click( function( event ) {
						$.webfonts.set( font );
					})
				}) (config[scheme]);

				$fontsmenu.append($fontmenuitem);
			}
			$resetlink = $( '<input />' )
					.attr("type","radio")
					.attr("name","font")
					.attr("value","webfont-none")
					.attr("id","webfont-none")
					.click( function( event ) {
						$.webfonts.set( 'none');
					});
					
			$resetlabel =  $( '<label />' )
					.attr("for","webfont-none")
					.append( $resetlink )
					.append( mw.msg("webfonts-reset"));
					
			$resetlinkitem = $( '<li />' )
				.val( 'none')
				.append( $resetlabel )	

				
			$fontsmenu.append($resetlinkitem);

			if ( !haveSchemes ) {
				// No schemes available, don't show the tool
				return;
			}

			var $menudiv = $( '<div />' ).attr('id','webfonts-fonts')
			.addClass( 'menu' )
			.append( $fontsmenu )
			.append();

			var $div = $( '<div />' ).attr('id','webfonts-menu')
			.addClass( 'webfontMenu' )
			.append( "<a href='#'>"+ mw.msg("webfonts-load")+"</a>")
			.append( $menudiv );

			//this is the fonts link
			var $li = $( '<li />' ).attr('id','pt-webfont')
			.append( $div );

			//add to the left of top personal links
			$($( '#p-personal ul' )[0]).prepend( $li );

			//see if there is a font in cookie
			cookie_font = $.cookie('webfonts-font');
			if(cookie_font == null){
				$.webfonts.set( config[0]);
				//mark it as checked
				$('#webfont-'+config[0]).attr('checked', 'checked');
			}
			else{
				if (cookie_font !=='none'){
					$.webfonts.set( cookie_font);
					//mark it as checked
					$('#webfont-'+cookie_font).attr('checked', 'checked');
				}
			}
		}

	}

	$( document ).ready( function() {
		$.webfonts.setup();
	} );

})(jQuery);
