/**
 * 
 * @param lines {Array} List of line objects
 * @returns {ParagraphBlock}
 */
function ParagraphBlock( lines ) {
	Block.call( this );
	this.lines = lines || [];
	this.metrics = [];
}

/**
 * Inserts content into a block at an offset.
 * 
 * @param offset {Integer} Position to insert content at
 * @param content {Object} Content to insert
 */
Block.prototype.insertContent = function( offset, content ) {
	var lineOffset = 0;
	for ( var i = 0; i < this.lines.length; i++ ) {
		if ( this.lines[i].text.length < offset - lineOffset ) {
			this.lines[i].text = this.lines[i].text.substring( 0, offset - lineOffset )
				+ content
				+ this.lines[i].text.substring( offset - lineOffset )
			break;
		}
		lineOffset += line.text.length;
	}
};

/**
 * Deletes content in a block within a range.
 * 
 * @param offset {Integer} Position to start removing content from
 * @param length {Integer} Length of content to remove
 */
Block.prototype.deleteContent = function( offset, length ) {
	var start,
		end,
		from,
		to,
		line,
		lineOffset;
	for ( var i = 0; i < this.lines.length; i++ ) {
		line = this.lines[i];
		start = offset - lineOffset;
		end = start + length;
		if ( start >= 0 && start < line.text.length) {
			from = {
				'line': line,
				'index': i,
				'offset': start
			};
		}
		if ( end >= 0 && end < line.text.length) {
			to = {
				'line': line,
				'index': i,
				'offset': end
			};
		}
		lineOffset += line.text.length;
	}
	if ( from.index === to.index ) {
		from.line.text = from.line.text.substring( 0, from.line.offset )
			+ from.line.text.substring( to.line.offset );
	} else {
		// Replace "from" line with remaining content of "from" and "to" lines
		from.line.text = from.line.text.substring( 0, from.line.offset )
			+ to.line.text.substring( to.line.offset );
		// Remove lines after "from" up to and including "to"
		this.lines = this.lines.splice( from.index + 1, to.index - from.index );
	}
};

/**
 * Renders content into a container.
 * 
 * @param $container {jQuery Selection} Container to render into
 */
Block.prototype.renderContent = function( $container ) {
	var flow = new TextFlow(),
		text = [];
	for ( var i = 0; i < this.lines.length; i++ ) {
		text.push( this.lines[i].text );
	}
	this.metrics = flow.render( $container, text.join( '\n' ) );
};

/**
 * Gets the location of a position.
 * 
 * @param position {Position} Position to translate
 */
Block.prototype.getLocation = function( position ) {
	throw 'Block.getLocation not implemented in this subclass.';
};

/**
 * Gets the position of a location.
 * 
 * @param location {Location} Location to translate
 */
Block.prototype.getPosition = function( location ) {
	throw 'Block.getPosition not implemented in this subclass.';
};

extend( ParagraphBlock, Block );
