<?php

if (!defined( 'MEDIAWIKI' ))
	die();

class AbuseFilterViewEdit extends AbuseFilterView {

	function __construct( $page, $params ) {
		parent::__construct( $page, $params );
		$this->mFilter = $page->mFilter;
		$this->mHistoryID = $page->mHistoryID;
	}

	function show( ) {
		global $wgRequest, $wgUser, $wgOut;
		
		$filter = $this->mFilter;
		$history_id = $this->mHistoryID;
		$this->mSkin = $wgUser->getSkin();

		$editToken = $wgRequest->getVal( 'wpEditToken' );
		$didEdit = $this->canEdit() && $wgUser->matchEditToken( $editToken, array( 'abusefilter', $filter ) );

		if ($didEdit) {
			// Check syntax
			$syntaxerr = AbuseFilter::checkSyntax( $wgRequest->getVal( 'wpFilterRules' ) );
			if ($syntaxerr !== true ) {
				$wgOut->addHTML( $this->buildFilterEditor( wfMsgExt( 'abusefilter-edit-badsyntax', array( 'parseinline' ), array( $syntaxerr ) ), $filter, $history_id ) );
			}

			$dbw = wfGetDB( DB_MASTER );

			list ($newRow, $actions) = $this->loadRequest($filter);

			$differences = AbuseFilter::compareVersions( array($newRow, $actions), array( $newRow->mOriginalRow, $newRow->mOriginalActions ) );

			unset( $newRow->mOriginalRow );
			unset( $newRow->mOriginalActions );

			if (!count($differences)) {
				$wgOut->redirect( $this->getTitle()->getLocalURL() );
				return;
			}

			$newRow = get_object_vars($newRow); // Convert from object to array

			// Set last modifier.
			$newRow['af_timestamp'] = $dbw->timestamp( wfTimestampNow() );
			$newRow['af_user'] = $wgUser->getId();
			$newRow['af_user_text'] = $wgUser->getName();

			$dbw->begin();

			// Insert MAIN row.
			if ($filter == 'new') {
				$new_id = $dbw->nextSequenceValue( 'abuse_filter_af_id_seq' );
				$is_new = true;
			} else {
				$new_id = $this->mFilter;
				$is_new = false;
			}

			// Reset throttled marker, if we're re-enabling it.
			$newRow['af_throttled'] = $newRow['af_throttled'] && !$newRow['af_enabled'];
			$newRow['af_id'] = $new_id; // ID.

			$dbw->replace( 'abuse_filter', array( 'af_id' ), $newRow, __METHOD__ );

			if ($is_new) {
				$new_id = $dbw->insertId();
			}

			// Actions
			global $wgAbuseFilterAvailableActions;
			$deadActions = array();
			$actionsRows = array();
			foreach( $wgAbuseFilterAvailableActions as $action ) {
				// Check if it's set
				$enabled = isset($actions[$action]) && (bool)$actions[$action];

				if ($enabled) {
					$parameters = $actions[$action]['parameters'];

					$thisRow = array( 'afa_filter' => $new_id, 'afa_consequence' => $action, 'afa_parameters' => implode( "\n", $parameters ) );
					$actionsRows[] = $thisRow;
				} else {
					$deadActions[] = $action;
				}
			}

			// Create a history row
			$afh_row = array();

			foreach( AbuseFilter::$history_mappings as $af_col => $afh_col ) {
				$afh_row[$afh_col] = $newRow[$af_col];
			}

			// Actions
			$displayActions = array();
			foreach( $actions as $action ) {
				$displayActions[$action['action']] = $action['parameters'];
			}
			$afh_row['afh_actions'] = serialize($displayActions);

			$afh_row['afh_changed_fields'] = implode( ',', $differences );

			// Flags
			$flags = array();
			if ($newRow['af_hidden'])
				$flags[] = 'hidden';
			if ($newRow['af_enabled'])
				$flags[] = 'enabled';
			if ($newRow['af_deleted'])
				$flags[] = 'deleted';

			$afh_row['afh_flags'] = implode( ",", $flags );

			$afh_row['afh_filter'] = $new_id;

			// Do the update
			$dbw->insert( 'abuse_filter_history', $afh_row, __METHOD__ );
			$dbw->delete( 'abuse_filter_action', array( 'afa_filter' => $filter ), __METHOD__ );
			$dbw->insert( 'abuse_filter_action', $actionsRows, __METHOD__ );

			$dbw->commit();

			global $wgOut;

			$wgOut->redirect( $this->getTitle()->getLocalURL( 'result=success&changedfilter='.$new_id ) );
		} else {
			if ($history_id) {
				$wgOut->addWikiMsg( 'abusefilter-edit-oldwarning', $this->mHistoryID, $this->mFilter );
			}
			
			$wgOut->addHTML( $this->buildFilterEditor( null, $this->mFilter, $history_id ) );
			
			if ($history_id) {
				$wgOut->addWikiMsg( 'abusefilter-edit-oldwarning', $this->mHistoryID, $this->mFilter );
			}
		}
	}

	function buildFilterEditor( $error, $filter, $history_id=null ) {
		if( $filter === null ) {
			return false;
		}

		// Build the edit form
		global $wgOut,$wgLang,$wgUser;
		$sk = $this->mSkin;

		// Load from request OR database.
		list ($row, $actions) = $this->loadRequest($filter, $history_id);

		if( !$row ) {
			$wgOut->addWikiMsg( 'abusefilter-edit-badfilter' );
			$wgOut->addHTML( $sk->link( $this->getTitle(), wfMsg( 'abusefilter-return' ) ) );
			return;
		}

		$wgOut->setSubtitle( wfMsg( 'abusefilter-edit-subtitle', $filter, $history_id ) );

		// Hide hidden filters.
		if (isset($row->af_hidden) && $row->af_hidden && !$this->canEdit()) {
			return wfMsg( 'abusefilter-edit-denied' );
		}

		$output = '';
		if ($error) {
			$wgOut->addHTML( "<span class=\"error\">$error</span>" );
		}

		$wgOut->addHTML( $sk->link( $this->getTitle(), wfMsg( 'abusefilter-history-backlist' ) ) );

		$fields = array();

		$fields['abusefilter-edit-id'] = $this->mFilter == 'new' ? wfMsg( 'abusefilter-edit-new' ) : $filter;
		$fields['abusefilter-edit-description'] = Xml::input( 'wpFilterDescription', 45, isset( $row->af_public_comments ) ? $row->af_public_comments : '' );

		// Hit count display
		if( !empty($row->af_hit_count) ){
			$count = (int)$row->af_hit_count;
			$count_display = wfMsgExt( 'abusefilter-hitcount', array( 'parseinline' ),
				$wgLang->formatNum( $count )
			);
			$hitCount = $sk->makeKnownLinkObj( SpecialPage::getTitleFor( 'AbuseLog' ), $count_display, 'wpSearchFilter='.$row->af_id );

			$fields['abusefilter-edit-hitcount'] = $hitCount;
		}

		if ($filter !== 'new') {
			// Statistics
			global $wgMemc, $wgLang;
			$matches_count = $wgMemc->get( AbuseFilter::filterMatchesKey( $filter ) );
			$total = $wgMemc->get( AbuseFilter::filterUsedKey() );

			if ($total > 0) {
				$matches_percent = sprintf( '%.2f', 100 * $matches_count / $total );
				$fields['abusefilter-edit-status-label'] =
					wfMsgExt( 'abusefilter-edit-status', array( 'parsemag', 'escape' ),
						$wgLang->formatNum($total),
						$wgLang->formatNum($matches_count),
						$wgLang->formatNum($matches_percent)
					);
			}
		}

		$fields['abusefilter-edit-rules'] = AbuseFilter::buildEditBox($row->af_pattern);
		$fields['abusefilter-edit-notes'] = Xml::textarea( 'wpFilterNotes', ( isset( $row->af_comments ) ? $row->af_comments."\n" : "\n" ) );

		// Build checkboxen
		$checkboxes = array( 'hidden', 'enabled', 'deleted' );
		$flags = '';

		if (isset($row->af_throttled) && $row->af_throttled) {
			global $wgAbuseFilterEmergencyDisableThreshold;
			$threshold_percent = sprintf( '%.2f', $wgAbuseFilterEmergencyDisableThreshold * 100 );
			$flags .= $wgOut->parse( wfMsg( 'abusefilter-edit-throttled', $wgLang->formatNum( $threshold_percent ) ) );
		}

		foreach( $checkboxes as $checkboxId ) {
			$message = "abusefilter-edit-$checkboxId";
			$dbField = "af_$checkboxId";
			$postVar = "wpFilter".ucfirst($checkboxId);

			$checkbox = Xml::checkLabel( wfMsg( $message ), $postVar, $postVar, isset( $row->$dbField ) ? $row->$dbField : false );
			$checkbox = Xml::tags( 'p', null, $checkbox );
			$flags .= $checkbox;
		}
		$fields['abusefilter-edit-flags'] = $flags;

		if ($filter != 'new') {
			// Last modification details
			$user = $sk->userLink( $row->af_user, $row->af_user_text ) . $sk->userToolLinks( $row->af_user, $row->af_user_text );
			$fields['abusefilter-edit-lastmod'] = wfMsgExt( 'abusefilter-edit-lastmod-text', array( 'parseinline', 'replaceafter' ), array( $wgLang->timeanddate( $row->af_timestamp ), $user ) );
			$history_display = wfMsgExt( 'abusefilter-edit-viewhistory', array( 'parseinline' ) );
			$fields['abusefilter-edit-history'] = $sk->makeKnownLinkObj( $this->getTitle( 'history/'.$filter ), $history_display );
		}

		$form = Xml::buildForm( $fields );
		$form = Xml::fieldset( wfMsg( 'abusefilter-edit-main' ), $form );
		$form .= Xml::fieldset( wfMsg( 'abusefilter-edit-consequences' ), $this->buildConsequenceEditor( $row, $actions ) );

		if ($this->canEdit()) {
			$form .= Xml::submitButton( wfMsg( 'abusefilter-edit-save' ) );
			$form .= Xml::hidden( 'wpEditToken', $wgUser->editToken( array( 'abusefilter', $filter )) );
		}

		$form = Xml::tags( 'form', array( 'action' => $this->getTitle( $filter )->getFullURL(), 'method' => 'POST' ), $form );

		$output .= $form;

		return $output;
	}

	function buildConsequenceEditor( $row, $actions ) {
		global $wgAbuseFilterAvailableActions;
		$setActions = array();
		foreach( $wgAbuseFilterAvailableActions as $action ) {
			$setActions[$action] = array_key_exists( $action, $actions );
		}

		$output = '';

		foreach( $wgAbuseFilterAvailableActions as $action ) {
			$output .= $this->buildConsequenceSelector( $action, $setActions[$action], @$actions[$action]['parameters'] );
		}

		return $output;
	}

	function buildConsequenceSelector( $action, $set, $parameters ) {
		global $wgAbuseFilterAvailableActions;

		if ( !in_array( $action, $wgAbuseFilterAvailableActions ) ) {
			return;
		}
		
		switch( $action ) {
			case 'throttle':
				$throttleSettings = Xml::checkLabel( wfMsg( 'abusefilter-edit-action-throttle' ), 'wpFilterActionThrottle', 'wpFilterActionThrottle', $set );
				$throttleFields = array();

				if ($set) {
					array_shift( $parameters );
					$throttleRate = explode(',', $parameters[0]);
					$throttleCount = $throttleRate[0];
					$throttlePeriod = $throttleRate[1];

					$throttleGroups = implode("\n", array_slice($parameters, 1 ) );
				} else {
					$throttleCount = 3;
					$throttlePeriod = 60;

					$throttleGroups = "user\n";
				}

				$throttleFields['abusefilter-edit-throttle-count'] = Xml::input( 'wpFilterThrottleCount', 20, $throttleCount );
				$throttleFields['abusefilter-edit-throttle-period'] = wfMsgExt( 'abusefilter-edit-throttle-seconds', array( 'parseinline', 'replaceafter' ), array(Xml::input( 'wpFilterThrottlePeriod', 20, $throttlePeriod )  ) );
				$throttleFields['abusefilter-edit-throttle-groups'] = Xml::textarea( 'wpFilterThrottleGroups', $throttleGroups."\n" );
				$throttleSettings .= Xml::buildForm( $throttleFields );
				return Xml::tags( 'p', null, $throttleSettings );
			case 'flag':
				$checkbox = Xml::checkLabel( wfMsg( 'abusefilter-edit-action-flag' ), 'wpFilterActionFlag', 'wpFilterActionFlag', true, array( 'disabled' => '1' ) );
				return Xml::tags( 'p', null, $checkbox );
			case 'warn':
				$output = '';
				$checkbox = Xml::checkLabel( wfMsg( 'abusefilter-edit-action-warn' ), 'wpFilterActionWarn', 'wpFilterActionWarn', $set );
				$output .= Xml::tags( 'p', null, $checkbox );

				$warnMsg = empty($set) ? 'abusefilter-warning' : $parameters[0];
				$warnFields['abusefilter-edit-warn-message'] = Xml::input( 'wpFilterWarnMessage', 45, $warnMsg );
				$output .= Xml::tags( 'p', null, Xml::buildForm( $warnFields ) );
				return $output;
				// Commented out to avoid trunk changes for now.
// 			case 'tag':
// 				if ($set) {
// 					$tags = $parameters;
// 				} else {
// 					$tags = array();
// 				}
// 				$output = '';
// 
// 				$checkbox = Xml::checkLabel( wfMsg('abusefilter-edit-action-tag'), 'wpFilterActionTag', 'wpFilterActionTag', $set );
// 				$output .= Xml::tags( 'p', null, $checkbox );
// 
// 				$tagFields['abusefilter-edit-tag-tag'] = Xml::textarea( 'wpFilterTags', implode( "\n", $tags ) );
// 				$output .= Xml::tags( 'p', null, Xml::buildForm( $tagFields ) );
// 				return $output;
			default:
				$message = 'abusefilter-edit-action-'.$action;
				$form_field = 'wpFilterAction' . ucfirst($action);
				$status = $set;

				$thisAction = Xml::checkLabel( wfMsg( $message ), $form_field, $form_field, $status );
				$thisAction = Xml::tags( 'p', null, $thisAction );
				return $thisAction;
		}
	}

	function loadFilterData( $id ) {

		if ($id == 'new') {
			$obj = new stdClass;
			$obj->af_pattern = '';
			$obj->af_enabled = 1;
			$obj->af_hidden = 0;
			return array( $obj, array() );
		}

		$dbr = wfGetDB( DB_SLAVE );

		// Load the main row
		$row = $dbr->selectRow( 'abuse_filter', '*', array( 'af_id' => $id ), __METHOD__ );

		if (!isset($row) || !isset($row->af_id) || !$row->af_id)
			return null;

		// Load the actions
		$actions = array();
		$res = $dbr->select( 'abuse_filter_action', '*', array( 'afa_filter' => $id), __METHOD__ );
		while ( $actionRow = $dbr->fetchObject( $res ) ) {
			$thisAction = array();
			$thisAction['action'] = $actionRow->afa_consequence;
			$thisAction['parameters'] = explode( "\n", $actionRow->afa_parameters );

			$actions[$actionRow->afa_consequence] = $thisAction;
		}

		return array( $row, $actions );
	}

	function loadRequest( $filter, $history_id = null ) {
		static $row = null;
		static $actions = null;
		global $wgRequest;

		if (!is_null($actions) && !is_null($row)) {
			return array($row,$actions);
		} elseif ($wgRequest->wasPosted()) {
			## Nothing, we do it all later
		} elseif ( $history_id ) {
			return $this->loadHistoryItem( $history_id );
		} else {
			return $this->loadFilterData( $filter );
		}

		// We need some details like last editor
		list($row,$origActions) = $this->loadFilterData( $filter );

		$row->mOriginalRow = clone $row;
		$row->mOriginalActions = $origActions;

		$textLoads = array( 'af_public_comments' => 'wpFilterDescription', 'af_pattern' => 'wpFilterRules', 'af_comments' => 'wpFilterNotes' );

		foreach( $textLoads as $col => $field ) {
			$row->$col = trim($wgRequest->getVal( $field ));
		}

		$row->af_deleted = $wgRequest->getBool( 'wpFilterDeleted' );
		$row->af_enabled = $wgRequest->getBool( 'wpFilterEnabled' ) && !$row->af_deleted;
		$row->af_hidden = $wgRequest->getBool( 'wpFilterHidden' );

		// Actions
		global $wgAbuseFilterAvailableActions;
		$actions = array();
		foreach( $wgAbuseFilterAvailableActions as $action ) {
			// Check if it's set
			$enabled = $wgRequest->getBool( 'wpFilterAction'.ucfirst($action) );

			if ($enabled) {
				$parameters = array();

				if ($action == 'throttle') {
					// We need to load the parameters
					$throttleCount = $wgRequest->getIntOrNull( 'wpFilterThrottleCount' );
					$throttlePeriod = $wgRequest->getIntOrNull( 'wpFilterThrottlePeriod' );
					$throttleGroups = explode("\n", trim( $wgRequest->getText( 'wpFilterThrottleGroups' ) ) );

					$parameters[0] = $this->mFilter; // For now, anyway
					$parameters[1] = "$throttleCount,$throttlePeriod";
					$parameters = array_merge( $parameters, $throttleGroups );
				} elseif ($action == 'warn') {
					$parameters[0] = $wgRequest->getVal( 'wpFilterWarnMessage' );
				} elseif ($action == 'tag') {
					$parameters = explode("\n", $wgRequest->getText( 'wpFilterTags' ) );
				}

				$thisAction = array( 'action' => $action, 'parameters' => $parameters );
				$actions[$action] = $thisAction;
			}
		}

		return array( $row, $actions );
	}

	function loadHistoryItem( $id ) {
		$dbr = wfGetDB( DB_SLAVE );

		// Load the row.
		$row = $dbr->selectRow( 'abuse_filter_history', '*', array( 'afh_id' => $id ), __METHOD__ );

		return AbuseFilter::translateFromHistory( $row );
	}
}