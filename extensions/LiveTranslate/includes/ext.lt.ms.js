/**
 * JavasSript for the Microsoft translation functionality in the Live Translate extension.
 * @see http://www.mediawiki.org/wiki/Extension:Live_Translate
 * 
 * @licence GNU GPL v3 or later
 * @author Jeroen De Dauw <jeroendedauw at gmail dot com>
 */

(function( $ ){ window.translationService = function() {
	
	var self = this;
	
	this.done = function( targetLang ){};
	
	/**
	 * Translates a single DOM element using Microsoft Translate.
	 * Loops through child elements and recursively calls itself to translate these.
	 * 
	 * @param {jQuery} element
	 * @param {string} sourceLang
	 * @param {string} targetLang
	 */
	this.translateElement = function( element, sourceLang, targetLang ) {
		
	};
	
}; })( jQuery );