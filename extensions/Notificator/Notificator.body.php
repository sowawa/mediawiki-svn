<?php

if (!defined('MEDIAWIKI')) {
	echo "Not a valid entry point";
	exit(1);
}

class Notificator {

public static function notificator_Render($parser, $receiver = '', $receiverLabel = '') {
	global $wgScript, $wgTitle;

	if(! $receiverLabel) {
		$receiverLabel = $receiver;
	}

	// Check that the database table is in place
	if(! Notificator::checkDatabaseTableExists()) {
		$output = '<span class="error">' . wfMsg('notificator-db-table-does-not-exist') . '</span>';
		return array($output, 'noparse' => true, 'isHTML' => true);
	}

	// Check whether the parameter is a valid e-mail address or not
	if($receiver && Notificator::checkEmailAddress($receiver)) {
		// Valid e-mail address available, so just show a button
		$output = '<form action="' . $wgScript . '/Special:Notificator" method="post" enctype="multipart/form-data"> 
<input type="hidden" name="pageId" value="' . $wgTitle->getArticleID() . '" />
<input type="hidden" name="revId" value="' . $wgTitle->getLatestRevID() . '" />
<input type="hidden" name="receiver" value="' . $receiver . '" />
<input type="submit" value="' . wfMsg('notify-address-or-name', htmlspecialchars($receiverLabel)) . '" />
</form>';
	} else {
		// No valid e-mail address available, show text entry field and button
		$output = '<form action="' . $wgScript . '/Special:Notificator" method="post" enctype="multipart/form-data"> 
<input type="hidden" name="pageId" value="' . $wgTitle->getArticleID() . '" />
<input type="hidden" name="revId" value="' . $wgTitle->getLatestRevID() . '" />
<input type="text" name="receiver" value="' . wfMsg('e-mail-address') . '" onfocus="if (this.value == \'' . wfMsg('e-mail-address') . '\') {this.value=\'\'}" />
<input type="submit" value="' . wfMsg('notify') . '" />
</form>';
	}

	return $parser->insertStripItem($output, $parser->mStripState);
}

private function checkDatabaseTableExists() {
	$dbr = wfGetDB(DB_SLAVE);
	$res = $dbr->tableExists('notificator');
	return $res;
}

private function getDiffCss() {
	$ret = '';
	$file = fopen(dirname(__FILE__) . '/diff-in-mail.css', 'r');
	while(!feof($file)) {
		$ret = $ret . fgets($file, 4096);
	}
	fclose ($file);
	return $ret;
}

public static function checkEmailAddress($string) {
// from http://www.linuxjournal.com/article/9585
	$isValid = true;
	$atIndex = strrpos($string, "@");
	if (is_bool($atIndex) && !$atIndex) {
		$isValid = false;
	} else {
		$domain = substr($string, $atIndex+1);
		$local = substr($string, 0, $atIndex);
		$localLen = strlen($local);
		$domainLen = strlen($domain);
		if ($localLen < 1 || $localLen > 64) {
			// local part length exceeded
			$isValid = false;
		}
		else if ($domainLen < 1 || $domainLen > 255) {
			// domain part length exceeded
			$isValid = false;
		}
		else if ($local[0] == '.' || $local[$localLen-1] == '.') {
			// local part starts or ends with '.'
			$isValid = false;
		}
		else if (preg_match('/\\.\\./', $local)) {
			// local part has two consecutive dots
			$isValid = false;
		}
		else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain)) {
			// character not valid in domain part
			$isValid = false;
		}
		else if (preg_match('/\\.\\./', $domain)) {
			// domain part has two consecutive dots
			$isValid = false;
		}
		else if (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\","",$local))) {
			// character not valid in local part unless 
			// local part is quoted
			if (!preg_match('/^"(\\\\"|[^"])+"$/', str_replace("\\\\","",$local))) {
				$isValid = false;
			}
		}
		if ($isValid && !(checkdnsrr($domain,"MX") || checkdnsrr($domain,"A"))) {
			// domain not found in DNS
			$isValid = false;
		}
	}
	return $isValid;
}

public static function getLastNotifiedRevId($pageId, $revId, $receiver) {
	// Returns -1 if any parameter is missing
	// Returns -2 if the database revId is the same as the given revId (= notified already)
	// Returns revId from the database - if there is no record, return 0

	if (! $pageId || ! $revId || ! $receiver) {
		return -1;
	}

	// Get $oldRevId from database
	$dbr = wfGetDB(DB_SLAVE);
	$res = $dbr->select('notificator',                    // table
                      'rev_id',                         // vars (columns of the table)
                      array('page_id' => (int)$pageId, 'receiver_email' => $receiver)  // conds
	                   );

	$row = $dbr->fetchRow($res);

	$oldRevId = $row[rev_id];

	if(! $oldRevId) {
		$oldRevId = 0;
	} elseif ($oldRevId == $revId) {
		$oldRevId = -2;
	}

	return $oldRevId;
}

public static function getNotificationDiffHtml($oldRevId, $revId) {
	$oldRevisionObj = Revision::newFromId($oldRevId);
	$newRevisionObj = Revision::newFromId($revId);

	if($oldRevisionObj->getTitle() != $newRevisionObj->getTitle()) {
		return '<span class="error">' . wfMsg('revs-not-from-same-title') . '</span>';
	}

	$titleObj = $oldRevisionObj->getTitle();

	$differenceEngineObj = new DifferenceEngine($titleObj, $oldRevId, $revId);

	$notificationDiffHtml = '<style media="screen" type="text/css">' . Notificator::getDiffCss() . '</style><table class="diff">
<col class="diff-marker" />
<col class="diff-content" />
<col class="diff-marker" />
<col class="diff-content" />
' . $differenceEngineObj->getDiffBody() . '
</table>';

	return $notificationDiffHtml;
}

public static function sendNotificationMail($receiver, $mailSubject, $notificationText) {
	global $ngFromAddress;
	$headers = 'From: ' . $ngFromAddress . "\r\n" .
             'X-Mailer: PHP/' . phpversion() . "\r\n" .
             'MIME-Version: 1.0' . "\r\n" .
             'Content-type: text/html; charset=utf-8' . "\r\n";
	$encodedMailSubject = mb_encode_mimeheader($mailSubject,"UTF-8", "B", "\n");

	return mail($receiver, $encodedMailSubject, $notificationText, $headers);
}

public static function recordNotificationInDatabase($pageId, $revId, $receiver) {
	$lastNotifiedRevId = Notificator::getLastNotifiedRevId($pageId, $revId, $receiver);
	if($lastNotifiedRevId > 0) {
		$dbw = wfGetDB(DB_MASTER);
		$res = $dbw->update('notificator',                    // table
                        array('rev_id' => (int)$revId),                         // vars (columns of the table)
                        array('page_id' => (int)$pageId, 'receiver_email' => $receiver)  // conds
	                     );
		return $res;
	} elseif($lastNotifiedRevId == 0) {
		$dbw = wfGetDB(DB_MASTER);
		$res = $dbw->insert('notificator',                    // table
                        array('page_id' => (int)$pageId,
                              'rev_id' => (int)$revId,
                              'receiver_email' => $receiver) // "$a"
	                     );
		return $res;
	} elseif($lastNotifiedRevId < 0) {
		return false;
	}
}

public static function getReturnToText($linkToPage, $pageTitle) {
	return '<p style="margin-top: 2em;">' . wfMsg('return-to') . ' <a href="' . $linkToPage . '">' . $pageTitle . '</a>.';
}

}
