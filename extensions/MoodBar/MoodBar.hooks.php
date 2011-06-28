<?php

class MoodBarHooks {
	/**
	 * Adds MoodBar JS to the output if appropriate.
	 */
	public static function onPageDisplay( &$output, &$skin ) {
		if ( self::shouldShowMoodbar( $output, $skin ) ) {
			$output->addModules( array('ext.moodBar') );
		}
		
		return true;
	}
	
	/**
	 * Determines whether or not we should show the MoodBar.
	 */
	public static function shouldShowMoodbar( &$output, &$skin ) {
		// Front-end appends to header elements, which have different
		// locations and IDs in every skin.
		// Untill there is some kind of central registry of element-ids
		// that skins implement, or a fixed name for each of them, just
		// show it in Vector for now.
		if ( $skin->getSkinName() == 'vector' ) {
			return true;
		}
	}
	
	/**
	 * Runs MoodBar schema updates
	 */
	public static function onLoadExtensionSchemaUpdates( $updater = null ) {
		$updater->addExtensionUpdate( array( 'addTable', 'moodbar_feedback',
			dirname(__FILE__).'/sql/MoodBar.sql', true ) );
			
		return true;
	}
}
