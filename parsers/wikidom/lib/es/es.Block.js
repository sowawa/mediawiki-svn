/**
 * 
 * @returns {Block}
 */
function Block() {
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
	throw 'Block.insertContent not implemented in this subclass.';
};

/**
 * Deletes content in a block within a range.
 * 
 * @param offset {Integer} Position to start removing content from
 * @param length {Integer} Length of content to remove
 */
Block.prototype.deleteContent = function( offset, length ) {
	throw 'Block.deleteContent not implemented in this subclass.';
};

/**
 * Renders content into a container.
 * 
 * @param $container {jQuery Selection} Container to render into
 */
Block.prototype.renderContent = function( $container ) {
	throw 'Block.renderContent not implemented in this subclass.';
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
