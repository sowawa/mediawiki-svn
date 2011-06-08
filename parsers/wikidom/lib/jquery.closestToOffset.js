$.fn.closestToOffset = function( offset ) {
	var $e = null,
		x = offset.left,
		y = offset.top,
		o,
		d,
		dx,
		dy,
		md;
	this.each( function() {
		var o = $(this).offset();
		if ( ( x >= o.left ) && ( x <= o.right ) && ( y >= o.top ) && ( y <= o.bottom ) ) {
			$e = $(this);
			return false;
		}
		var offsets = [
			[o.left, o.top],
			[o.right, o.top],
			[o.left, o.bottom],
			[o.right, o.bottom]
		];
		for ( var i = 0; i < offsets.length; i++ ) {
			dx = offsets[i][0] - x;
			dy = offsets[i][1] - y;
			d = Math.sqrt( ( dx * dx ) + ( dy * dy ) );
			if ( md === undefined || d < md ) {
				md = d;
				$e = $(this);
			}
		}
	} );
	return $e;
};
