<?php 
	/**
	 * Do not edit this file instead use LocalSettings.php and 
	 * $wgMwEmbedModuleConfig[ {configuration name} ] = value; format
	 */

	return array( 
		// If the Timed Text interface should be displayed:
		// 'always' Displays link and call to contribute always
		// 'auto' Looks for child timed text elements or "apiTitleKey" & load interface
		// 'off' Does not display the timed text interface
		"TimedText.ShowInterface" => "auto",

		/**
		* If the "add timed text" link / interface should be exposed 
		* allows usere to upload text files to the wiki
		*/
		'TimedText.ShowAddTextLink' => false,

		// If the link to request a transcript should be shown on video files
		'TimedText.ShowRequestTranscript' => false,
	
		// The category for listing videos that need transcription:
		'TimedText.NeedsTranscriptCategory' => 'Videos needing subtitles'
	);