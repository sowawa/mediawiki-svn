<?php

/**
 * Parser for custom Live Translate translation memory Format (LFT) data.
 *
 * @since 0.4
 *
 * @file LT_LTFParser.php
 * @ingroup LiveTranslate
 *
 * @licence GNU GPL v3
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class LTLTFParser extends LTTMParser {
	
	/**
	 * (non-PHPdoc)
	 * @see LTTMParser::parse()
	 */
	public function parse( $text ) {
		$tm = new LTTranslationMemory();
		
		$translationSets = array();
		
		$lines = explode( "\n", $text );
		$languages = array_map( 'trim', explode( ',', array_shift( $lines ) ) );
		
		foreach ( $lines as $line ) {
			$values = array_map( 'trim', explode( ',', $line ) );
			$tu = new LTTMUnit();
			
			// If there is only one value, interpret it as "should never be translated", and add it for all languages.
			if ( count( $values ) == 1 ) {
				foreach ( $languages as $language ) {
					// Add the translation (or translations) (value, array) of the word in the language (key).
					$tu->addVariants( array( $language => array_map( 'trim', explode( '|', $values[0] ) ) ) );
				}
			}
			else {
				foreach ( $languages as $nr => $language ) {
					if ( array_key_exists( $nr, $values ) ) {
						// Add the translation (or translations) (value, array) of the word in the language (key).
						$tu->addVariants( array( $language => array_map( 'trim', explode( '|', $values[$nr] ) ) ) );
					}
				}
			}
			
			$tm->addTranslationUnit( $tu );
		}
		
		return $tm;
	}	
	
}