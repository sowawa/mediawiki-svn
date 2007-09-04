<?php
/*
 * Created on 17/07/2007
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
?>

<?php

/*
 * Created on August 16, 2007
 *
 * API for MediaWiki 1.8+
 *
 * Copyright (C) 2007 Iker Labarga <Firstname><Lastname>@gmail.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 * http://www.gnu.org/copyleft/gpl.html
 */

if (!defined('MEDIAWIKI')) {
    // Eclipse helper - will be ignored in production
    require_once ("ApiBase.php");
}

/**
 * A query module to list all external URLs found on a given set of pages.
 *
 * @addtogroup API
 */
class ApiEditPage extends ApiBase {
	//----------------------------------------
	//**** APIEDITPAGE CONSTANTS (2xx) value ****
	const BAD_LGTOKEN 						= 001;
	const BAD_EDITTOKEN						= 002;
	const NO_POST_REQUEST					= 003;
	const AS_SUCCESS_UPDATE					= 200;
	const AS_SUCCESS_NEW_ARTICLE			= 201;
	const AS_HOOK_ERROR 					= 210;
	const AS_FILTERING						= 211;
	const AS_HOOK_ERROR_EXPECTED				= 212;
	const AS_BLOCKED_PAGE_FOR_USER			= 215;
	const AS_CONTENT_TOO_BIG				= 216;
	const AS_USER_CANNOT_EDIT				= 217;
	const AS_READ_ONLY_PAGE_ANON			= 218;
	const AS_READ_ONLY_PAGE_LOGGED			= 219;
	const AS_READ_ONLY_PAGE					= 220;
	const AS_RATE_LIMITED					= 221;
	const AS_ARTICLE_WAS_DELETED			= 222;
	const AS_NO_CREATE_PERMISSION			= 223;
	const AS_BLANK_ARTICLE					= 224;
	const AS_CONFLICT_DETECTED				= 225;
	const AS_SUMMARY_NEEDED					= 226;
	const AS_TEXTBOX_EMPTY					= 228;
	const AS_MAX_ARTICLE_SIZE_EXCEDED		= 229;
	const AS_OK								= 230;
 	const AS_END							= 231;
 	const AS_SPAM_ERROR						= 232;
	//----------------------------------------

    public function __construct($query, $moduleName) {
        parent :: __construct($query, $moduleName, 'ep');
    }

    public function execute() {
        global $wgUser, $wgRequest;
				$title = $text = $summary = $edittime = $lgtoken = $userid = $tokenid = $value = null;

		if( session_id() == '' ) {
			wfSetupSession();
    }

		extract($this->extractRequestParams());
		// Ensure the correct timestamp format
		$edittime =eregi_replace("[-,a-z,:]","",$edittime);
    $object_title = Title::newFromDBkey($title);
		$myArticle = new Article($object_title);

        // User creation since UserID number
		if ($userid != 0){
        	$myUser = new User();
			$myUser->setID($userid);
			$myUser->loadFromId();
			$myUser->setCookies();
		 	$wgUser = $myUser;

		 	if ($lgtoken != $_SESSION['wsToken']){
				$value = BAD_LGTOKEN;
      }
		}

		if ($value != 'BAD_LGTOKEN'){
    	$md5 = $wgUser->editToken();
      // This is only to fast testing. So must be cleanned before a Release
      $tokenid = $md5;

			$params = new FauxRequest(array (
	        	'wpTitle' 		=> $title,
	        	'wpTextbox1' 	=> $text,
	        	'wpSummary'		=> $summary,
	        	'wpEdittime'	=> $edittime,
	        	'wplgToken' 	=> $lgtoken,
	        	'wpUserID'		=> $userid,
	        	'wpEditToken'	=> $tokenid
	    ));

      // APiEditPage only accepts POST requests
			if (!$_SERVER['REQUEST_METHOD']){
      	$value = 'NO_POST_REQUEST';
      }

      else{
      	$params->wasPosted = true;
     		if ($md5 != $tokenid){
					$value = BAD_EDITTOKEN;
      	}

      	else {
					$editForm = new EditPage($myArticle);
					$editForm->mTitle = $object_title;
					$editForm->importFormData($params);

					$value=$editForm->attemptSave();
				}
    	}
		}
		switch ($value){
			case self::AS_END:
				$result['result'] = 'COnflict detected';
				break;

			case self::AS_SUCCESS_UPDATE:
				$result['result'] 		= 'Success';
		       	$result['title']		= $editForm->mTitle;
		       	$result['id']			= $myArticle->getID();
		       	$result['revid']		= $myArticle->getRevIdFetched();
		       	$rtext['content']		= $editForm->textbox1;
		       	break;

			case self::AS_MAX_ARTICLE_SIZE_EXCEDED:
				$result['result'] = 'Article too long';
		          break;

			case self::AS_TEXTBOX_EMPTY:
				$result['result'] = 'Blank edition';
				break;

			case self::AS_SUMMARY_NEEDED:
				$result['result'] = 'Summary is mandatory';
				break;

			case self::AS_CONFLICT_DETECTED:
				$result['result'] = 'Conflict detected';
				break;

			case self::AS_SUCCESS_NEW_ARTICLE:
				$result['result'] 		= 'Success';
		       	$result['title']		= $editForm->mTitle;
		       	$result['id']			= $myArticle->getID();
		       	$result['revid']		= $myArticle->getRevIdFetched();
		       	$rtext['content']		= $editForm->textbox1;
		       	break;

	 		case self::AS_BLANK_ARTICLE:
			 	$result['result'] = 'Blank article';
			 	break;

		 	case self::AS_NO_CREATE_PERMISSION;
				$result['result'] = 'No create permission';
				break;

		 	case self::AS_ARTICLE_WAS_DELETED:
			 	$result['result'] = 'Article was deleted before';
			 	break;

			case self::AS_RATE_LIMITED:
			 	$result['result'] = 'Rate limit excedeed';
			 	break;

		 	case self::AS_READ_ONLY_PAGE:
			 	$result['result'] = 'Read only page';
			 	break;

		 	case self::AS_READ_ONLY_PAGE_LOGGED:
				$result['result'] = 'Read only allowed';
				break;

			case self::AS_READ_ONLY_PAGE_ANON:
				$result['result'] = 'Read only allowed';
				break;

			case self::AS_CONTENT_TOO_BIG:
				$result['result'] = 'Article too long';
				break;

			case self::AS_BLOCKED_PAGE_FOR_USER:
				$result['result'] = 'Blocked page for the user';
				break;

			case self::AS_HOOK_ERROR:
				$result['result'] = 'Hook error detected';
				break;

			case self::AS_SPAM_ERROR:
				$result['result'] = 'Spam error detected';
				break;

			case self::AS_FILTERING:
				$result['result'] = 'Filtering not passed';
				break;

			case self::AS_HOOK_ERROR_EXPECTED:
				$result['result'] = 'Hook error detected';
				break;

			case self::NO_POST_REQUEST:
				$result['result'] = 'Error.Only POST requests are allowed';
				break;

			case 'BAD_LGTOKEN':
				$result['result'] = "Error.Login token is wrong";
				break;

			case 'BAD_EDITTOKEN':
				$result['result'] = "Error.Edit token is wrong";
				break;

			default :
                $result['result'] = 'Invalid';
                break;
		}

		$this->getResult()->addValue(null, 'editpage', $result);
    	if (isset ($rtext['content'])) $this->getResult()->addValue('text', 'content', $rtext);

    }

    protected function getAllowedParams() {
        return array (
			'title' => array(
                ApiBase :: PARAM_TYPE => 'string'
            ),
		    'text' => array(
                ApiBase :: PARAM_TYPE => 'string'
            ),
            'summary' => array(
                ApiBase :: PARAM_TYPE => 'string'
            ),
            'userid' => array(
                ApiBase :: PARAM_TYPE => 'string'
            ),
            'edittime' => array(
                ApiBase :: PARAM_TYPE => 'string'
            ),
            'lgtoken' => array(
                ApiBase :: PARAM_TYPE => 'string'
            ),
            'tokenid' => array(
                ApiBase :: PARAM_TYPE => 'string'
            ),
       );
    }

    protected function getDescription() {
   		return array (
            'title'			=> 'Title of article',
            'text' 			=> 'text of article',
            'summary'		=> 'Summary of article',
			'userid'		=> 'ID of the user',
			'edittime'		=> 'Timestamp of base revision edited',
			'lgtoken'		=> 'Login token of the user'

        );
    }

    protected function getExamples() {
        return array (
                "Edit a page (anonimous user):",
                "    api.php?action=edit&eptitle=Test&epsummary=test%20summary&eptext=article%20content&epedittime=20070824123454&eptokenid=+%5C"
            );
    }

    public function getVersion() {
        return __CLASS__ . ': $Id: ApiEditPage.php 22289 2007-08-16 13:27:44Z ilabarg1 $';
    }
}
?>
