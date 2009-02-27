<?php

class AbuseFilterVariableHolder {
	var $mVars = array();
	
	function setVar( $variable, $datum ) {
		$variable = strtolower( $variable );
		if (!( $datum instanceof AFPData || $datum instanceof AFComputedVariable ) ) {
			$datum = AFPData::newFromPHPVar( $datum );
		}
		
		$this->mVars[$variable] = $datum;
	}
	
	function setLazyLoadVar( $variable, $method, $parameters ) {
		$placeholder = new AFComputedVariable( $method, $parameters );
		$this->setVar( $variable, $placeholder );
	}
	
	function getVar( $variable ) {
		$variable = strtolower( $variable );
		if ( isset($this->mVars[$variable]) ) {
		
			if ($this->mVars[$variable] instanceof AFComputedVariable) {
				$value = $this->mVars[$variable]->compute( $this );
				$this->setVar( $variable, $value );
				
				return $value;
			} elseif ($this->mVars[$variable] instanceof AFPData)
				return $this->mVars[$variable];
		} else {
			return new AFPData();
		}
	}
	
	static function merge( /* ... */ ) {
		$newHolder = new AbuseFilterVariableHolder;
		
		foreach( func_get_args() as $addHolder ) {
			$newHolder->addHolder( $addHolder );
		}
		
		return $newHolder;
	}
	
	function addHolder( $addHolder ) {
		$this->mVars = array_merge( $this->mVars, $addHolder->mVars );
	}
	
	function exportAllVars() {
		$allVarNames = array_keys( $this->mVars );
		$exported = array();
		
		foreach( $allVarNames as $varName ) {
			$exported[$varName] = $this->getVar( $varName )->toString();
		}
		return $exported;
	}
	
}

class AFComputedVariable {
	var $mMethod, $mParameters;
	static $userCache = array();
	static $articleCache = array();
	
	function __construct( $method, $parameters ) {
		$this->mMethod = $method;
		$this->mParameters = $parameters;
	}
	
	/** It's like Article::prepareTextForEdit, but not for editing (old wikitext usually) */
	function parseNonEditWikitext( $wikitext, $article ) {
		static $cache = array();
		
		$cacheKey = md5($wikitext).':'.$article->mTitle->getPrefixedText();
		
		if ( isset( $cache[$cacheKey] ) )
			return $cache[$cacheKey];
			
		global $wgParser;
		$edit = (object)array();
		$options = new ParserOptions;
		$options->setTidy( true );
		$edit->output = $wgParser->parse( $wikitext, $article->mTitle, $options );
		$cache[$cacheKey] = $edit;
		
		return $edit;
	}
	
	static function userObjectFromName( $username ) {
		if ( isset( self::$userCache[$username] ) )
			return self::$userCache[$username];
			
		wfDebug( "Couldn't find user $username in cache\n" );
			
		return self::$userCache[$username] = User::newFromName( $username );
	}
	
	static function articleFromTitle( $namespace, $title ) {
		if ( isset( self::$articleCache["$namespace:$title"] ) )
			return self::$articleCache["$namespace:$title"];
		
		$t = Title::makeTitle( $namespace, $title );
		self::$articleCache["$namespace:$title"] = new Article( $t );
		
		return self::$articleCache["$namespace:$title"];
	}
	
	function compute( $vars ) {
		$parameters = $this->mParameters;
		$result = null;
		switch( $this->mMethod ) {
			case 'diff':
				$text1Var = $parameters['oldtext-var'];
				$text2Var = $parameters['newtext-var'];
				$text1 = $vars->getVar( $text1Var )->toString();
				$text2 = $vars->getVar( $text2Var )->toString();
				$result = wfDiff( $text1, $text2 );
				$result = trim( str_replace( '\No newline at end of file', '', $result ) );
				break;
			case 'diff-split':
				$diff = $vars->getVar( $parameters['diff-var'] )->toString();
				$line_prefix = $parameters['line-prefix'];
				$diff_lines = explode( "\n", $diff );
				$interest_lines = array();
				foreach( $diff_lines as $line ) {
					if (strpos( $line, $line_prefix )===0) {
						$interest_lines[] = substr( $line, strlen($line_prefix) );
					}
				}
				$result = implode( "\n", $interest_lines );
				break;
			case 'links-from-wikitext':
				$article = self::articleFromTitle( $parameters['namespace'], $parameters['title'] );
				$textVar = $parameters['text-var'];
				
				$new_text = $vars->getVar( $textVar )->toString();
				$editInfo = $article->prepareTextForEdit( $new_text );
				$links = array_keys( $editInfo->output->getExternalLinks() );
				$result = implode( "\n", $links );
				break;
			case 'links-from-wikitext-nonedit':
				$article = self::articleFromTitle( $parameters['namespace'], $parameters['title'] );
				$textVar = $parameters['text-var'];
				
				$wikitext = $vars->getVar( $textVar )->toString();
				$editInfo = $this->parseNonEditWikitext( $wikitext, $article );
				$links = array_keys( $editInfo->output->getExternalLinks() );
				$result = implode( "\n", $links );
				break;
			case 'link-diff-added':
				$oldLinkVar = $parameters['oldlink-var'];
				$newLinkVar = $parameters['newlink-var'];
				
				$oldLinks = $vars->getVar( $oldLinkVar )->toString();
				$newLinks = $vars->getVar( $newLinkVar )->toString();
				
				$oldLinks = explode( "\n", $oldLinks );
				$newLinks = explode( "\n", $newLinks );
				
				$added = array_diff( $newLinks, $oldLinks );
				$result = implode( "\n", $added );
				break;
			case 'link-diff-removed':
				$oldLinkVar = $parameters['oldlink-var'];
				$newLinkVar = $parameters['newlink-var'];
				
				$oldLinks = $vars->getVar( $oldLinkVar )->toString();
				$newLinks = $vars->getVar( $newLinkVar )->toString();
				
				$oldLinks = explode( "\n", $oldLinks );
				$newLinks = explode( "\n", $newLinks );
				
				$removed = array_diff( $oldLinks, $newLinks );
				$result = implode( "\n", $removed );
				break;
			case 'parse-wikitext':
				$article = self::articleFromTitle( $parameters['namespace'], $parameters['title'] );
				$textVar = $parameters['wikitext-var'];
				
				$new_text = $vars->getVar( $textVar )->toString();
				$editInfo = $article->prepareTextForEdit( $new_text );
				$newHTML = $editInfo->output->getText();
				// Kill the PP limit comments. Ideally we'd just remove these by not setting the 
				// parser option, but then we can't share a parse operation with the edit, which is bad.
				$result = preg_replace( '/<!--\s*NewPP limit report[^>]*-->\s*$/si', '', $newHTML );
				break;
			case 'parse-wikitext-nonedit':
				$article = self::articleFromTitle( $parameters['namespace'], $parameters['title'] );
				$textVar = $parameters['wikitext-var'];
				
				$text = $vars->getVar( $textVar )->toString();
				$editInfo = $this->parseNonEditWIkitext( $text, $article );
				
				$result = $editInfo->output->getText();
				break;
			case 'strip-html':
				$htmlVar = $parameters['html-var'];
				$html = $vars->getVar( $htmlVar )->toString();
				$result = preg_replace( '/<[^>]+>/', '', $html );
				break;
			case 'load-recent-authors':
				$cutOff = $parameters['cutoff'];
				$title = Title::makeTitle( $parameters['namespace'], $parameters['title'] );
				
				$dbr = wfGetDB( DB_SLAVE );
				$res = $dbr->select( 'revision', 'distinct rev_user_text',
					array(
						'rev_page' => $title->getArticleId(),
						'rev_timestamp<'.$dbr->addQuotes( $dbr->timestamp( $cutOff ) ) ),
						__METHOD__,
					array( 'ORDER BY' => 'rev_timestamp DESC', 'LIMIT' => 10 ) );
					
				$users = array();
				while ($user = $dbr->fetchRow($res)) {
					$users[] = $user[0];
				}
				$result = implode( ",", $users );
				break;
			case 'get-page-restrictions':
				$action = $parameters['action'];
				$title = Title::makeTitle( $parameters['namespace'], $parameters['title'] );
				
				$rights = $title->getRestrictions( $action );
				$rights = count($rights) ? $rights : array();
				$result = implode(',', $rights );
				break;
			case 'simple-user-accessor':
				$user = $parameters['user'];
				$method = $parameters['method'];
				
				if (!$user) {
					throw new MWException( "No user parameter given." );
				}
				
				$obj = self::userObjectFromName( $user );
				
				if (!$obj) {
					throw new MWException( "Invalid username $user" );
				}
				
				$result = call_user_func( array($obj, $method) );
				break;
			case 'user-age':
				$user = $parameters['user'];
				$asOf = $parameters['asof'];
				$obj = self::userObjectFromName( $user );
				$registration = $obj->getRegistration();
				$result =
					wfTimestamp( TS_UNIX, $asOf) - wfTimestampOrNull( TS_UNIX, $registration );
				break;
			case 'user-groups':
				$user = $parameters['user'];
				$obj = self::userObjectFromName( $user );
				$result = implode(',', $obj->getEffectiveGroups() );
				break;
			case 'length':
				$s = $vars->getVar( $parameters['length-var'] )->toString();
				$result = strlen( $s );
				break;
			case 'subtract':
				$v1 = $vars->getVar( $parameters['val1-var'] )->toFloat();
				$v2 = $vars->getVar( $parameters['val2-var'] )->toFloat();
				$result = $v1 - $v2;
				break;
			case 'revision-text-by-id':
				$rev = Revision::newFromId( $parameters['revid'] );
				$result = $rev->getText();
				break;
			case 'revision-text-by-timestamp':
				$timestamp = $parameters['timestamp'];
				$title = Title::makeTitle( $parameters['namespace'], $parameters['title'] );
				$dbr = wfGetDB( DB_SLAVE );
				
				$rev = Revision::loadFromTimestamp( $dbr, $title, $timestamp );

				if ($rev)
					$result = $rev->getText();
				else
					$result = '';
				break;
			default:
				throw new AFPException( "Unknown variable compute type ".$this->mMethod );
		}
		
		return AFPData::newFromPHPVar( $result );
	}
}