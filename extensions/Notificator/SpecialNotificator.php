<?php

if ( !defined( 'MEDIAWIKI' ) ) {
	echo "Not a valid entry point";
	exit( 1 );
}

class SpecialNotificator extends SpecialPage {

function __construct() {
	parent::__construct( 'Notificator' );
}

function execute( $par ) {
	global $wgRequest, $wgOut, $wgUser;

	$this->setHeaders();

	# Get request data from, e.g.
	$pageId = $wgRequest->getText( 'pageId' );
	$revId = $wgRequest->getText( 'revId' );
	$receiver = $wgRequest->getText( 'receiver' );

	if ( ! $pageId || ! $revId || ! $receiver ) {
		$output = '<span class="error">' . wfMsg( 'notificator-special-page-accessed-directly' ) . '</span>';
	} else {
		$titleObj = Title::newFromID( $pageId );
		$pageTitle = $titleObj->getFullText();
		$linkToPage = $titleObj->getFullURL();

		if ( ! Notificator::receiverIsValid( $receiver ) ) {
			$output = '<span class="error">' . wfMsg( 'notificator-e-mail-address-invalid' ) . ' ' .
				wfMsg( 'notificator-notification-not-sent' ) . '</span>';
			$output .= Notificator::getReturnToText( $linkToPage, $pageTitle );
			$wgOut->addHTML( $output );
			return;
		}

		$oldRevId = Notificator::getLastNotifiedRevId( $pageId, $revId, $receiver );

		if ( $oldRevId >= 0 ) {
			if ( $oldRevId > 0 ) {
				// Receiver has been notified before - send the diff to the last notified revision
				$mailSubjectPrefix = '[' . wfMsg( 'notificator-change-tag' ) . '] ';

				$wgOut->addModules( 'mediawiki.legacy.diff' );
				$diff = Notificator::getNotificationDiffHtml( $oldRevId, $revId );
				$notificationText = wfMsg( 'notificator-notification-text-changes',
					htmlspecialchars( $wgUser->getName() ), '<a href="' . $linkToPage . '">' .
					$pageTitle . '</a>' ) . '<div style="margin-top: 1em;">' . $diff . '</div>';
			} else {
				// Receiver has never been notified about this page - so don't send a diff, just the link
				$mailSubjectPrefix = '[' . wfMsg( 'notificator-new-tag' ) . '] ';
				$notificationText = wfMsg( 'notificator-notification-text-new',
					htmlspecialchars( $wgUser->getName() ), '<a href="' . $linkToPage . '">' .
					$pageTitle . '</a>' );
			}
			$mailSubject = htmlspecialchars( $mailSubjectPrefix . $pageTitle );

			if ( Notificator::sendNotificationMail( $receiver, $mailSubject, $notificationText ) ) {
				$output = '<strong>' . wfMsg( 'notificator-following-e-mail-sent-to',
					htmlspecialchars( $receiver ) ) . '</strong><div style="margin-top: 1em;"><h3>' .
					wfMsg( 'notificator-subject' ) . ' ' . $mailSubject . '</h3><p>' . $notificationText .
					'</p></div>';
				Notificator::recordNotificationInDatabase( $pageId, $revId, $receiver );
			} else {
				$output = '<span class="error">' . wfMsg( 'notificator-error-sending-e-mail',
					htmlspecialchars( $receiver ) ) . '</span>';
			}
		} elseif ( $oldRevId == -1 ) {
			$output = '<span class="error">' . wfMsg( 'notificator-error-parameter-missing' ) . '</span>';
		} elseif ( $oldRevId == -2 ) {
			$output = '<strong>' . wfMsg( 'notificator-notified-already', htmlspecialchars( $receiver ) ) .
				' ' . wfMsg( 'notificator-notification-not-sent' ) . '</strong>';
		}

		$output .= Notificator::getReturnToText( $linkToPage, $pageTitle );
	}

	$wgOut->addHTML( $output );
}

}
