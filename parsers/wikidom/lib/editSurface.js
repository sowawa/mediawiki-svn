/**
 * Pixel position, a 2D position within a rendered document.
 * 
 * @param x {Integer} Horizontal position
 * @param y {Integer} Vertical position
 * @returns {Position}
 */
function Position( x, y ) {
	this.x = x || 0;
	this.y = y || 0;
}

/**
 * Content location, an offset within a block.
 * 
 * @param block {Block} Location target
 * @param offset {Integer} Location offset
 * @returns {Location}
 */
function Location( block, offset ) {
	this.block = block;
	this.offset = offset || 0;
}

/**
 * Content selection, a pair of locations.
 * 
 * @param from {Location} Starting location
 * @param to {Location} Ending location
 * @returns {Selection}
 */
function Selection( from, to ) {
	this.from = from;
	this.to = to;
}

/**
 * Ensures that "from" is before "to".
 */
Selection.prototype.normalize = function() {
	if ( this.from.block.index() > this.to.block.index()
			|| ( this.from.block.index() === this.to.block.index()
					&& this.from.offset > this.to.offset ) ) {
		var from = sel.from;
		this.from = to;
		this.to = from;
	}
};

/**
 * Gets all blocks selected completely, between from and to.
 * 
 * If from and to are adjacent blocks, or the same block, the result will always be an empty array.
 * 
 * @returns {Array} List of blocks
 */
Selection.prototype.through = function() {
	var through = [];
	if ( this.from !== this.to && this.from.nextBlock() !== this.to ) {
		var next = this.from.nextBlock()
		while ( next && next !== this.to ) {
			through.push( next );
			next = next.nextBlock();
		}
	}
	return through;
};

/**
 * 
 * @param blocks {Array} List of blocks
 * @returns {Document}
 */
function Document( blocks ) {
	this.blocks = blocks || [];
}

/**
 * Gets the first block in the document.
 * 
 * @returns {Block}
 */
Document.prototype.firstBlock = function() {
	return this.blocks.length ? this.blocks[0] : null;
};

/**
 * Gets the last block in the document.
 * 
 * @returns {Block}
 */
Document.prototype.lastBlock = function() {
	return this.blocks.length ? this.blocks[this.blocks.length - 1] : null;
};

/**
 * Adds a block to the end of the document.
 * 
 * @param {Block} Block to append
 */
Document.prototype.appendBlock = function( block ) {
	block.document = this;
	this.blocks.push( block );
};

/**
 * Adds a block to the beginning of the document.
 * 
 * @param {Block} Block to prepend
 */
Document.prototype.prependBlock = function( block ) {
	block.document = this;
	this.blocks.unshift( block );
};

/**
 * Adds a block to the document after an existing block.
 * 
 * @param {Block} Block to insert
 */
Document.prototype.insertBlockBefore = function( block, before ) {
	block.document = this;
	if ( before ) {
		this.blocks.splice( before.index(), 0, block );
	} else {
		this.blocks.push( block );
	}
};

/**
 * Removes a block from the document.
 * 
 * @param {Block} Block to remove
 */
Document.prototype.removeBlock = function( block ) {
	this.blocks.splice( block.index(), 1 );
	block.document = null;
};

/**
 * 
 * @param document
 * @returns {Block}
 */
function Block( document ) {
	this.document = document;
}

/**
 * Gets the index of the block within it's document.
 * 
 * @returns {Integer} Index of block
 */
Block.prototype.index = function() {
	return this.document.blocks.indexOf( this );
};

/**
 * Gets the next block in the document.
 * 
 * @returns {Block|Null} Block directly proceeding this one, or null if none exists
 */
Block.prototype.nextBlock = function() {
	var index = this.index() + 1;
	return this.document.blocks.length < index ? this.document.blocks[index] : null;
};

/**
 * Gets the previous block in the document.
 * 
 * @returns {Block|Null} Block directly preceding this one, or null if none exists
 */
Block.prototype.previousBlock = function() {
	var index = this.index() - 1;
	return index >= 0 ? this.document.blocks[index] : null;
};

/**
 * Inserts content into a block at an offset.
 * 
 * @param offset {Integer} Position to insert content at
 * @param content {Object} Content to insert
 */
Block.prototype.insertContent = function( offset, content ) {
	// block-type dependent implementation
};

/**
 * Deletes content in a block within a range.
 * 
 * @param offset {Integer} Position to start removing content from
 * @param length {Integer} Length of content to remove
 */
Block.prototype.deleteContent = function( offset, length ) {
	// block-type dependent implementation
};

/**
 * Replaces a range of content in a block with new content.
 * 
 * @param offset {Integer} Position to start removing content from
 * @param length {Integer} Length of content to remove
 * @param content {Object} Content to insert
 */
Block.prototype.replaceContent = function( offset, length, content ) {
	// block-type dependent implementation
};

/**
 * 
 * @param $container
 * @param document
 * @returns {Surface}
 */
function Surface( $container, document ) {
	this.$container = $container;
	this.document = document;
}

/**
 * Moves the cursor to a new location.
 * 
 * @param location {Location} Location to move the cursor to
 */
Surface.prototype.setCursor = function( location ) {
	//
};

/**
 * Gets the current location of the cursor.
 * 
 * @returns {Location}
 */
Surface.prototype.getCursor = function() {
	// return location
};

/**
 * Sets the selection to a new range.
 * 
 * @param from {Location} Start of selection
 * @param to {Location} End of selection
 */
Surface.prototype.setSelection = function( from, to ) {
	//
};

/**
 * Sets the selection to a new range.
 * 
 * @param from {Selection} Selection to apply
 */
Surface.prototype.setSelection = function( selection ) {
	//
};

/**
 * Gets the current document selection.
 * 
 * @returns {Selection}
 */
Surface.prototype.getSelection = function() {
	// return selection
};

/**
 * Gets the location of a position.
 * 
 * @param position {Position} Position to translate
 * @returns {Location}
 */
Surface.prototype.getLocation = function( position ) {
	// return location
};

/**
 * Gets the position of a location.
 * 
 * @param location {Location} Location to translate
 * @returns {Position}
 */
Surface.prototype.getPosition = function( location ) {
	// return position
};

/**
 * Moves the cursor to the nearest location directly above the current flowed line.
 */
Surface.prototype.moveCursorUp = function() {
	var location = this.getCursor();
	var below = this.getPosition( location );
	var minDistance;
	while ( location.block.previous() && location.offset > 0 ) {
		location.offset--;
		if ( location.offset === -1 ) {
			location.block = block.previous();
		}
		var above = this.getPosition( location );
		if ( above.y < below.y ) {
			var distance = below.x - above.x;
			if ( minDistance > distance ) {
				location.offset++;
				break;
			} else {
				minDistance = distance;
			}
		}
	}
	this.setCursor( location );
};

/**
 * Moves the cursor to the nearest location directly below the current flowed line.
 */
Surface.prototype.moveCursorDown = function() {
	var location = this.getCursor();
	var above = this.getPosition( location );
	var minDistance;
	while ( location.block.next() && location.offset < location.block.length ) {
		location.offset++;
		if ( location.offset = location.block.length ) {
				location.block = block.next();
		}
		var below = this.getPosition( location );
		if ( above.y < below.y ) {
			var distance = below.x - above.x;
			if ( minDistance > distance ) {
				location.offset++;
				break;
			} else {
				minDistance = distance;
			}
		}
	}
	this.setCursor( location );
};

/**
 * Moves the cursor backward of the current position.
 */
Surface.prototype.moveCursorRight = function() {
	var location = this.getCursor();
	if ( location.block.length < location.offset + 1 ) {
		location.offset++;
	} else {
		var next = location.block.next();
		if ( next ) {
			location.block = next;
			location.offset = 0;
		}
	}
	this.setCursor( location );
};

/**
 * Moves the cursor forward of the current position.
 */
Surface.prototype.moveCursorLeft = function() {
	var location = this.getCursor();
	if ( location.offset > 0 ) {
		location.offset--;
	} else {
		var previous = location.block.previous();
		if ( previous ) {
			location.block = previous;
			location.offset = location.block.length - 1;
		}
	}
	this.setCursor( location );
};

/**
 * Updates the rendered view.
 * 
 * @param from Location: Where to start re-flowing from (optional)
 */
Surface.prototype.reflow = function( from ) {
	//
};
