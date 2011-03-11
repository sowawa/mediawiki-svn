/**
 * JavasSript for Google Maps v3 maps in the Maps extension.
 * @see http://www.mediawiki.org/wiki/Extension:Maps
 * 
 * @author Jeroen De Dauw <jeroendedauw at gmail dot com>
 */

(function( $ ){ $.fn.googlemaps = function( options ) {

	var mapOptions = {
		disableDefaultUI: true,
		mapTypeId: eval( 'google.maps.MapTypeId.' + options.type ),
	};
	this.options = options;
	
	// Map controls
	mapOptions.panControl = $.inArray( 'pan', options.controls ) != -1;
	mapOptions.zoomControl = $.inArray( 'zoom', options.controls ) != -1;
	mapOptions.mapTypeControl = $.inArray( 'type', options.controls ) != -1;
	mapOptions.scaleControl = $.inArray( 'scale', options.controls ) != -1;
	mapOptions.streetViewControl = $.inArray( 'streetview', options.controls ) != -1;

	// Map control styles
	mapOptions.zoomControlOptions = { style: eval( 'google.maps.ZoomControlStyle.' + options.zoomstyle ) }
	mapOptions.mapTypeControlOptions = { style: eval( 'google.maps.MapTypeControlStyle.' + options.typestyle ) }	

	// Create the map.
	var locations = options.locations;

	var map = new google.maps.Map( this.get( 0 ), mapOptions );
	this.map = map;
	
	var markers = [];
	if ( !options.locations ) {
		options.locations = [];
	}
	
	// Add the markers.
	for ( var i = options.locations.length - 1; i >= 0; i-- ) {
		var location = options.locations[i];
		
		var marker = new google.maps.Marker( {
			map: map,
			position: new google.maps.LatLng( location.lat , location.lon ),
			title: location.title
		} );
		
		marker.openWindow = false;
		
		if ( location.text != '' ) {
			marker.text = location.text;
			google.maps.event.addListener( marker, 'click', function() {
				if ( this.openWindow !== false ) {
					this.openWindow.close();
				}
				this.openWindow = new google.maps.InfoWindow( { content: this.text } );
				this.openWindow.closeclick = function() {
					marker.openWindow = false;
				};
				this.openWindow.open( map, this );					
			} );			
		}
		
		markers.push( marker );
	}
	
	// Code to add KML files.
	for ( i = options.kml.length - 1; i >= 0; i-- ) {
		var kmlLayer = new google.maps.KmlLayer( options.kml[i], { map: map } );
	}
	
	for ( i = options.fusiontables.length - 1; i >= 0; i-- ) {
		var ftLayer = new google.maps.FusionTablesLayer( options.fusiontables[i], { map: map } );
	}	
	
	var layerMapping = {
		'traffic': 'new google.maps.TrafficLayer()',
		'bicycling': 'new google.maps.BicyclingLayer()'
	};
	
	for ( i = options.layers.length - 1; i >= 0; i-- ) {
		var layer = eval( layerMapping[options.layers[i]] );
		layer.setMap( map );
	}	
	
	var bounds;
	
	if ( ( options.centre === false || options.zoom === false ) && options.locations.length > 1 ) {
		bounds = new google.maps.LatLngBounds();

		for ( var i = markers.length - 1; i >= 0; i-- ) {
			bounds.extend( markers[i].getPosition() );
		}
		
		map.fitBounds( bounds );
	}
	
	if ( options.zoom !== false ) {
		map.setZoom( options.zoom );
	}
	
	var centre;
	
	if ( options.centre === false ) {
		if ( options.locations.length > 1 ) {
			centre = bounds.getCenter();
		}
		else if ( options.locations.length == 1 ) {
			centre = new google.maps.LatLng( options.locations[0].lat, options.locations[0].lon );
		}
		else {
			centre = new google.maps.LatLng( 0, 0 );
		}
	}
	else {
		centre = new google.maps.LatLng( options.centre.lat, options.centre.lon );
	}
	
	map.setCenter( centre );
	
	if ( options.autoinfowindows ) {
		for ( var i = markers.length - 1; i >= 0; i-- ) {
			markers[i].openWindow = new google.maps.InfoWindow( { content: markers[i].text } );
			markers[i].openWindow.closeclick = function() {
				markers[i].openWindow = false;
			};			
			markers[i].openWindow.open( map, markers[i] );	
		}		
	}
	
	if ( options.resizable ) {
		this.resizable()
	}	

	return this;
	
}; })( jQuery );