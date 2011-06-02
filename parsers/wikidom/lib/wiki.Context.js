wiki.Context = function( options ) {

	/* Private Members */

	// Constants are used for behavioral switches, or other non-parametric inclusions
	var constants = options && options.constants ? options.constants : {};
	// Parameters are used during transclusion for template expansion
	var parameters = options && options.parameters ? options.parameters : {};

	/* Methods */

	/**
	 * Checks if a page exists, used for rendering "new" links.
	 * 
	 * This method delegates to the pageExists option passed through the constructor. If no such
	 * option was given, this method will always return false.
	 * 
	 * @param name String: Page namespace
	 * @param name String: Page title
	 * @return Boolean: True if page exists
	 */
	this.pageExists = function( namespace, title ) {
		return typeof options.pageExists === 'function'
			? options.pageExists( namespace, title ) : false;
	};

	/**
	 * Gets the Document Object Model of a page.
	 * 
	 * This method delegates to the getPageDom option passed through the constructor. If no such
	 * option was given, this method will always return null.
	 * 
	 * @param name String: Page namespace
	 * @param name String: Page title
	 * @return Object: Page DOM (document object)
	 */
	this.getPageDom = function( namespace, title ) {
		return typeof options.getPageDom === 'function'
			? options.getPageDom( namespace, title ) : null;
	};

	/**
	 * Sets a constant.
	 * 
	 * @param name String: Constant name
	 * @param value String: Constant value
	 */
	this.setConstant = function( name, value ) {
		constants[name] = value;
	};

	/**
	 * Gets a constant.
	 * 
	 * @param name String: Constant name
	 * @return String: Constant value
	 */
	this.getConstant = function( name ) {
		return name in constants ? constants[name] : null;
	};

	/**
	 * Sets a parameter.
	 * 
	 * @param name String: Parameter name
	 * @param value Object: Parameter value (document object)
	 */
	this.setParameter = function( name, value ) {
		parameters[name] = value;
	};

	/**
	 * Gets a parameter.
	 * 
	 * @param name String: Parameter name
	 * @return Object: Parameter value (document object)
	 */
	this.getParameter = function( name ) {
		return name in parameters ? parameters[name] : null;
	};
};
