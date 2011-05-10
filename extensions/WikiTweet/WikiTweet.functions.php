<?php
if ( !defined( 'MEDIAWIKI' ) )
	die();
class WikiTweetFunctions {
	public static function sendWithPear($mailer, $dest, $headers, $body)
	{
		$mailResult = $mailer->send($dest, $headers, $body);
		if( PEAR::isError( $mailResult ) ) {
			return $mailResult->getMessage();
		} else {
			return true;
		}
	}
	public static function send( $to, $from, $subject, $body, $replyto=null )
	{
		$wgOutputEncoding = 'UTF-8';
		$wgEnotifImpersonal = false;
		$wgErrorString = '';
		$wgEnotifMaxRecips = 500;
		include('WikiTweet.config.php');
		if (is_array( $wgWikiTweet )) {
			require_once( 'Mail.php' );
			$msgid = str_replace(" ", "_", microtime());
			if (function_exists('posix_getpid'))
				$msgid .= '.' . posix_getpid();
			if (is_array($to)) {
				$dest = array();
				foreach ($to as $u)
					$dest[] = $u;
			} else
				$dest = $to;
			$headers['From'] = $from;
			if ($wgEnotifImpersonal)
				$headers['To'] = 'undisclosed-recipients:;';
			else
				$headers['To'] = $to;
			if ( $replyto ) {
				$headers['Reply-To'] = $replyto->toString();
			}
			$headers['Subject'] = $subject;
			$headers['Date'] = date( 'r' );
			$headers['MIME-Version'] = '1.0';
			$headers['Content-type'] = 'text/plain; charset='.$wgOutputEncoding;
			$headers['Content-transfer-encoding'] = '8bit';
			$headers['Message-ID'] = "<$msgid@" . $wgWikiTweet['SMTP']['IDHost'] . '>'; // FIXME
			$headers['X-Mailer'] = 'MediaWiki mailer';
			// Create the mail object using the Mail::factory method
			$mail_object =& Mail::factory('smtp', $wgWikiTweet['SMTP']);
			if( PEAR::isError( $mail_object ) ) {
				wfDebug( "PEAR::Mail factory failed: " . $mail_object->getMessage() . "\n" );
				return new WikiError( $mail_object->getMessage() );
			}
			$chunks = array_chunk( (array)$dest, $wgEnotifMaxRecips );
			foreach ($chunks as $chunk) {
				$e = WikiTweetFunctions::sendWithPear($mail_object, $chunk, $headers, $body);
				if( $e != true)
					return $e;
			}
		} 
	}
	
	public static function str_global_replace( $i__string, $i__array )
	{
		$o__string = $i__string ;
		foreach($i__array as $l__key => $l__item)
		{
			$o__string = str_replace($l__key, $l__value, $o__string );
		}
		return $o__string;
	}
	public static function Convert_Date ( $i__old_date )
	{
		// function to convert a date in a countdown
		$l__nber_seconds = 0 ;
		$l__nber_minutes = 0 ;
		$l__nber_hours   = 0 ;
		$l__diff_date    = 0 ;
		
		
		$l__diff_date = time() - $i__old_date ;
		if ( $l__diff_date < 0 )
		{
			// theorically impossible
			$result = wfMsg ( 'wikitweet-inthefuture' ) ;
		}
		elseif ( $l__diff_date < 10 )
		{
			// less than 10 seconds is "few"
			$result = wfMsg ( 'wikitweet-fewsecondsago' ) ;
		}
		elseif ( $l__diff_date >= 60 )
		{
			// real conversion
			$l__nber_seconds = $l__diff_date % 60 ;
			$l__new_diff = $l__diff_date - $l__nber_seconds ;
			$l__nber_minutes = $l__new_diff / 60 ;
			if ($l__nber_minutes >= 60)
			{
				$l__old_nber_minutes = $l__nber_minutes ;
				$l__nber_minutes = $l__nber_minutes % 60 ;
				$l__new_nber_minutes = $l__old_nber_minutes - $l__nber_minutes ;
				$l__nber_hours = $l__new_nber_minutes / 60 ;
			}
		}
		else
		{
			$l__nber_seconds = $l__diff_date ;
		}
		//plurals
		$seconds = ( $l__nber_seconds == 1 ) ? wfMsg( 'wikitweet-second' ) : wfMsg( 'wikitweet-seconds' ) ;
		$minutes = ( $l__nber_minutes == 1 ) ? wfMsg( 'wikitweet-minute' ) : wfMsg( 'wikitweet-minutes' ) ;
		$hours   = ( $l__nber_hours == 1 )   ? wfMsg( 'wikitweet-hour' )   : wfMsg( 'wikitweet-hours' )   ;
		
		if ($l__nber_hours !=0 && $l__nber_minutes != 0 )
		{
			$result = str_replace( "%time%" , "$l__nber_hours $hours $l__nber_minutes $minutes" , wfMsg( 'wikitweet-timeago' ) ); // "%time% ago"
		}
		elseif (($l__nber_hours != 0 && $l__nber_minutes == 0 ) || ($l__nber_hours >= 5 ) )
		{
			$result = str_replace( "%time%" , "$l__nber_hours $hours" , wfMsg( 'wikitweet-timeago' ) ); // "%time% ago"
		}
		elseif (($l__nber_minutes >= 5) || ($l__nber_minutes != 0 && $l__nber_seconds == 0 ) )
		{
			$result = str_replace( "%time%" , "$l__nber_minutes $minutes" , wfMsg( 'wikitweet-timeago' ) ); // "%time% ago"
		}
		elseif ($l__nber_minutes != 0 && $l__nber_seconds != 0 )
		{
			$result = str_replace( "%time%" , "$l__nber_minutes $minutes $l__nber_seconds $seconds" , wfMsg( 'wikitweet-timeago' ) ); // "%time% ago"
		}
		elseif ($l__nber_seconds !=0 )
		{
			$result = str_replace( "%time%" , "$l__nber_seconds $seconds" , wfMsg( 'wikitweet-timeago' ) ); // "%time% ago"
		}
	return $result ; // a string
	}
}
