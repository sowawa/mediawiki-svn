/**
 * 
 * @param blocks {Array} List of blocks
 * @returns {Document}
 */
function Document( blocks ) {
	this.blocks = blocks || [];
	this.width = null;
	this.$ = $( '<div class="editSurface-document"></div>' )
		.data( 'document', this );
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
 * @param block {Block} Block to insert
 * @param before {Block} Block to insert before, if null then block will be inserted at the end
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

Document.prototype.renderBlocks = function() {
	// Remember width, to avoid updates when without width changes
	this.width = this.$.innerWidth();
	// Render blocks
	for ( var i = 0; i < this.blocks.length; i++ ) {
		this.$.append( this.blocks[i].$ );
		this.blocks[i].renderContent();
	}
};

Document.prototype.updateBlocks = function() {
	// Bypass rendering when width has not changed
	var width = this.$.innerWidth();
	if ( this.width === width ) {
		return;
	}
	this.width = width;
	// Render blocks
	var doc;
	this.$.children( '.editSurface-block' ).each( function( i ) {
		$(this).data( 'block' ).renderContent();
	} );
};
