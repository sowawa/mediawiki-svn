<?php

if( !defined( 'MEDIAWIKI' ) )
	die( 'Not an entry point.' );

class SpecialNuke extends SpecialPage {
	function __construct() {
		parent::__construct( 'Nuke', 'nuke' );
	}

	function execute( $par ){
		global $wgUser, $wgRequest;

		if( !$this->userCanExecute( $wgUser ) ){
			$this->displayRestrictionError();
			return;
		}

		$this->setHeaders();
		$this->outputHeader();

		$target = $wgRequest->getText( 'target', $par );

		// Normalise name
		if ( $target !== '' ) {
			$user = User::newFromName( $target );
			if ( $user ) $target = $user->getName();
		}

		$reason = $wgRequest->getText( 'wpReason',
			wfMsgForContent( 'nuke-defaultreason', $target ) );
		$posted = $wgRequest->wasPosted() &&
			$wgUser->matchEditToken( $wgRequest->getVal( 'wpEditToken' ) );
		if( $posted ) {
			$pages = $wgRequest->getArray( 'pages' );
			if( $pages ) {
				return $this->doDelete( $pages, $reason );
			}
		}
		if( $target != '' ) {
			$this->listForm( $target, $reason );
		} else {
			$this->promptForm();
		}
	}

	/**
	 * Prompt for a username or IP address
	 */
	function promptForm() {
		global $wgOut;

		$input = Xml::input( 'target', 40 );
		$submit = Xml::submitButton( wfMsg( 'nuke-submit-user' ) );

		$wgOut->addWikiMsg( 'nuke-tools' );
		$wgOut->addHTML(
			Xml::openElement( 'form', array(
				'action' => $this->getTitle()->getLocalURL( 'action=submit' ),
				'method' => 'post' )
			) . "$input\n$submit\n"
		);

		$wgOut->addHTML( "</form>" );
	}

	/**
	 * Display list of pages to delete
	 */
	function listForm( $username, $reason ) {
		global $wgUser, $wgOut, $wgLang;

		$pages = $this->getNewPages( $username );

		if( count( $pages ) == 0 ) {
			$wgOut->addWikiMsg( 'nuke-nopages', $username );
			return $this->promptForm();
		}
		$wgOut->addWikiMsg( 'nuke-list', $username );

		$nuke = $this->getTitle();
		$submit = Xml::submitButton( wfMsg( 'nuke-submit-delete' ) );

		$script = <<<JAVASCRIPT
<script type="text/javascript">
function selectPages( bool ) {
	var inputs = document.getElementsByTagName("input");
	for (i = 0; i < inputs.length; i++) {
		if (inputs[i].type == "checkbox") {
			inputs[i].checked = bool;
		}
	}
}
</script>
JAVASCRIPT;
		$wgOut->addScript( $script );

		$wgOut->addHTML(
			Xml::openElement( 'form', array(
				'action' => $nuke->getLocalURL( 'action=delete' ),
				'method' => 'post',
				'name' => 'nukelist')
			) .
			Html::hidden( 'wpEditToken', $wgUser->editToken() ) .
			Xml::tags( 'p',
				null,
				Xml::inputLabel(
					wfMsg( 'deletecomment' ), 'wpReason', 'wpReason', 60, $reason
				)
			)
		);

		// Select: All, None
		$links = array();
		$links[] = '<a href="#" onclick="selectPages( true ); return false;">' . 
			wfMsg( 'powersearch-toggleall' ) . '</a>';
		$links[] = '<a href="#" onclick="selectPages( false ); return false;">' . 
			wfMsg( 'powersearch-togglenone' ) . '</a>';
		$wgOut->addHTML(
			Xml::tags( 'p',
				null,
				wfMsg( 'nuke-select', $wgLang->commaList( $links ) )
			)
		);

		// Delete button
		$wgOut->addHTML(
			Xml::submitButton( wfMsg( 'nuke-submit-delete' ) )
		);

		$wgOut->addHTML( '<ul>' );

		$sk = $wgUser->getSkin();
		foreach( $pages as $info ) {
			list( $title, $edits ) = $info;
			$image = $title->getNamespace() == NS_IMAGE ? wfLocalFile( $title ) : false;
			$thumb = $image && $image->exists() ? $image->transform( array( 'width' => 120, 'height' => 120 ), 0 ) : false;

			$changes = wfMsgExt( 'nchanges', 'parsemag', $wgLang->formatNum( $edits ) );
			
			$wgOut->addHTML( '<li>' .
				Xml::check( 'pages[]', true,
					array( 'value' =>  $title->getPrefixedDbKey() )
				) .
				'&#160;' .
				( $thumb ? $thumb->toHtml( array( 'desc-link' => true ) ) : '' ) .
				$sk->makeKnownLinkObj( $title ) .
				'&#160;(' .
				$sk->makeKnownLinkObj( $title, $changes, 'action=history' ) .
				")</li>\n" );
		}
		$wgOut->addHTML(
			"</ul>\n" .
			Xml::submitButton( wfMsg( 'nuke-submit-delete' ) ) .
			"</form>"
		);
	}

	function getNewPages( $username ) {
		$dbr = wfGetDB( DB_SLAVE );
		$result = $dbr->select( 'recentchanges',
			array( 'rc_namespace', 'rc_title', 'rc_timestamp', 'COUNT(*) AS edits' ),
			array(
				'rc_user_text' => $username,
				"(rc_new = 1) OR (rc_log_type = 'upload' AND rc_log_action = 'upload')"
			),
			__METHOD__,
			array(
				'ORDER BY' => 'rc_timestamp DESC',
				'GROUP BY' => 'rc_namespace, rc_title'
			)
		);
		$pages = array();
		foreach ( $result as $row ) {
			$pages[] = array( Title::makeTitle( $row->rc_namespace, $row->rc_title ), $row->edits );
		}
		$dbr->freeResult( $result );
		return $pages;
	}

	function doDelete( $pages, $reason ) {
		foreach( $pages as $page ) {
			$title = Title::newFromURL( $page );
			$file = $title->getNamespace() == NS_IMAGE ? wfLocalFile( $title ) : false;
			if ( $file ) {
				$oldimage = null; // Must be passed by reference
				FileDeleteForm::doDelete( $title, $file, $oldimage, $reason, false );								
			} else {
				$article = new Article( $title );
				$article->doDelete( $reason );
			}
		}
	}
}