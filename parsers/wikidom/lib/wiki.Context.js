wiki.Context = function() {

	/* Private Members */

	var params = {};

	/* Methods */

	this.pageExists = function( namespace, title ) {
		return false;
	};

	this.getPageDom = function( namespace, title ) {
		return '';
	};

	this.setParam = function( name, document ) {
		params[name] = document;
	};

	this.getParam = function( name ) {
		return name in params ? params[name] : null;
	};
};
