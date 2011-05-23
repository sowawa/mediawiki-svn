<?php
/*
 (c) Aaron Schulz, Joerg Baach, 2007-2008 GPL
 
 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License along
 with this program; if not, write to the Free Software Foundation, Inc.,
 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 http://www.gnu.org/copyleft/gpl.html
*/

if ( !defined( 'MEDIAWIKI' ) ) {
	echo "FlaggedRevs extension\n";
	exit( 1 );
}

# This messes with dump HTML...
if ( defined( 'MW_HTML_FOR_DUMP' ) ) {
	return;
}

$wgExtensionCredits['specialpage'][] = array(
	'path'           => __FILE__,
	'name'           => 'Flagged Revisions',
	'author'         => array( 'Aaron Schulz', 'Joerg Baach' ),
	'url'            => 'http://www.mediawiki.org/wiki/Extension:FlaggedRevs',
	'descriptionmsg' => 'flaggedrevs-desc',
);

# Load global constants
include_once( "FlaggedRevsDefines.php" );

# ######## Configuration variables ########
# IMPORTANT: DO NOT EDIT THIS FILE
# When configuring globals, set them at LocalSettings.php instead

# This will only distinguish "checked", "quality", and unreviewed
# A small icon will show in the upper right hand corner
$wgSimpleFlaggedRevsUI = true; // @TODO: remove when ready
# For visitors, only show tags/icons for unreviewed/outdated pages
$wgFlaggedRevsLowProfile = true; // @TODO: remove with new icon UI?

# Allowed namespaces of reviewable pages
$wgFlaggedRevsNamespaces = array( NS_MAIN, NS_FILE, NS_TEMPLATE );
# Pages exempt from reviewing. No flagging UI will be shown for them.
$wgFlaggedRevsWhitelist = array();
# $wgFlaggedRevsWhitelist = array( 'Main_Page' );

# Is a "stable version" used as the default display
# version for all pages in reviewable namespaces?
$wgFlaggedRevsOverride = true;
# Below are groups that see the current revision by default.
# This makes editing easier since the users always start off
# viewing the latest version of pages.
$wgFlaggedRevsExceptions = array( 'user' ); // @TODO: remove when ready (and expand pref)

# Auto-review settings for edits/new pages:
# FR_AUTOREVIEW_NONE
#	Don't auto-review any edits or new pages
# FR_AUTOREVIEW_CHANGES
#	Auto-review the following types of edits (to existing pages):
#	(a) changes directly to the stable version by users with 'autoreview'/'bot'
#	(b) reversions to old reviewed versions by users with 'autoreview'/'bot'
# 	(c) self-reversions back to the stable version by any user
# FR_AUTOREVIEW_CREATION
#	Auto-review new pages as minimally "checked"
# FR_AUTOREVIEW_CREATION_AND_CHANGES
#	Combines FR_AUTOREVIEW_CHANGES and FR_AUTOREVIEW_CREATION
$wgFlaggedRevsAutoReview = FR_AUTOREVIEW_CREATION_AND_CHANGES;

# Define the tags we can use to rate an article, number of levels,
# and set the minimum level to have it become a "quality" or "pristine" version.
# NOTE: When setting up new dimensions or levels, you will need to add some
# 		MediaWiki messages for the UI to show properly; any sysop can do this.
$wgFlaggedRevsTags = array(
	'accuracy' => array( 'levels' => 3, 'quality' => 2, 'pristine' => 4 ),
	'depth'    => array( 'levels' => 3, 'quality' => 1, 'pristine' => 4 ),
	'style'    => array( 'levels' => 3, 'quality' => 1, 'pristine' => 4 ),
);
# For each tag, define the highest tag level that is unlocked by
# having certain rights. For example, having 'review' rights may
# allow for "depth" to be rated up to second level.
# NOTE: Users cannot lower revision tags from a level they can't set.
# NOTE: Users with 'validate' (Reviewers) can set all tags to all levels.
$wgFlaggedRevsTagsRestrictions = array(
	'accuracy' => array( 'review' => 1, 'autoreview' => 1 ),
	'depth'	   => array( 'review' => 2, 'autoreview' => 2 ),
	'style'	   => array( 'review' => 3, 'autoreview' => 3 ),
);
# For each tag, what is the highest level that it can be auto-reviewed to?
# $wgFlaggedRevsAutoReview must be enabled for this to apply.
$wgFlaggedRevsTagsAuto = array(
	'accuracy' => 1, 'depth' => 1, 'style' => 1
);

# Restriction levels for 'autoreview'/'review' rights.
# When a level is selected for a page, an edit made by a user
# will not be auto-reviewed if the user lacks the specified permission.
# Levels are set at the Stabilization special page.
$wgFlaggedRevsRestrictionLevels = array( '', 'sysop' );
# Set this to use FlaggedRevs *only* as a protection-like mechanism.
# This will disable Stabilization and show the above restriction levels
# on the protection form of pages. Each level has the stable version shown by default.
# A "none" level will appear in the form as well, to disable the review process.
# Pages will only be reviewable if manually restricted to a level above "none".
$wgFlaggedRevsProtection = false;

# Define our basic reviewer class of established editors (Editors)
$wgGroupPermissions['editor']['review']          = true;
$wgGroupPermissions['editor']['autoreview']      = true;
$wgGroupPermissions['editor']['autoconfirmed']   = true;
$wgGroupPermissions['editor']['unreviewedpages'] = true;
$wgGroupPermissions['editor']['patrolmarks']     = true;

# Define when users get automatically promoted to Editors. Set as false to disable.
# Once users meet these requirements they will be promoted, unless previously demoted.
$wgFlaggedRevsAutopromote = array(
	'days'	              	=> 60, # days since registration
	'edits'	              	=> 250, # total edit count
	'excludeLastDays'     	=> 1, # exclude the last X days of edits from edit counts
	'benchmarks'         	=> 15, # number of "spread out" edits
	'spacing'	        	=> 3, # number of days between these edits (the "spread")
	// Either totalContentEdits reqs OR totalCheckedEdits requirements needed
	'totalContentEdits' 	=> 300, # edits to pages in $wgContentNamespaces
	'totalCheckedEdits' 	=> 200, # edits before the stable version of pages
	'uniqueContentPages'	=> 14, # unique pages in $wgContentNamespaces edited
	'editComments'      	=> 50, # number of manual edit summaries used
	'userpageBytes'     	=> 0, # size of userpage (use 0 to not require a userpage)
	'neverBlocked'      	=> true, # username was never blocked before?
	'maxRevertedEditRatio' 	=> .03, # max fraction of edits reverted via "rollback"/"undo"
);

# Define when users get to have their own edits auto-reviewed. Set to false to disable.
# This can be used for newer, semi-trusted users to improve workflow.
# It is done by granting some users the implicit 'autoreview' group.
$wgFlaggedRevsAutoconfirm = false;
/* (example usage)
$wgFlaggedRevsAutoconfirm = array(
	'days'	              => 30, # days since registration
	'edits'	              => 50, # total edit count
	'benchmarks'          => 7, # number of "spread out" edits
	'spacing'	          => 3, # number of days between these edits (the "spread")
	// Either totalContentEdits reqs OR totalCheckedEdits requirements needed
	'totalContentEdits'   => 150, # $wgContentNamespaces edits OR...
	'totalCheckedEdits'   => 50, # ...Edits before the stable version of pages
	'uniqueContentPages'  => 8, # $wgContentNamespaces unique pages edited
	'editComments'        => 20, # how many edit comments used?
	'email'	              => false, # user must be emailconfirmed?
	'neverBlocked'        => true, # Can users that were blocked be promoted?
);
*/

# Defines extra rights for advanced reviewer class (Reviewers)
$wgGroupPermissions['reviewer']['validate']        = true;
# Let this stand alone just in case...
$wgGroupPermissions['reviewer']['review']          = true;
$wgGroupPermissions['reviewer']['autoreview']      = true;
$wgGroupPermissions['reviewer']['autoconfirmed']   = true;
$wgGroupPermissions['reviewer']['unreviewedpages'] = true;
$wgGroupPermissions['reviewer']['patrolmarks']     = true;

# Sysops have their edits autoreviewed
$wgGroupPermissions['sysop']['autoreview'] = true;
# Stable version selection and default page revision selection can be set per page.
$wgGroupPermissions['sysop']['stablesettings'] = true;
# Sysops can always move stable pages
$wgGroupPermissions['sysop']['movestable'] = true;

# Special:Userrights settings
# # Basic rights for Sysops
$wgAddGroups['sysop'][] = 'editor';
$wgRemoveGroups['sysop'][] = 'editor';
# # Extra ones for Bureaucrats (@TODO: remove this)
$wgAddGroups['bureaucrat'][] = 'reviewer';
$wgRemoveGroups['bureaucrat'][] = 'reviewer';

# How far the logs for overseeing quality revisions and depreciations go
$wgFlaggedRevsOversightAge = 30 * 24 * 3600;

# How long before Special:ValidationStatistics is updated.
# Set to false to disable (perhaps using a cron job instead).
$wgFlaggedRevsStatsAge = 2 * 3600; // 2 hours

# Configurable information to collect and display at Special:ValidationStatistics
$wgFlaggedRevsStats = array(
	'topReviewersCount'		=> 5, # how many top reviewers to list
	'topReviewersHours' 	=> 1, # how many hours of the last reviews to count
);

# How to handle templates and files used in stable versions:
# FR_INCLUDES_CURRENT
#	Always use the current version of templates/files
# FR_INCLUDES_FREEZE
#	Use the version of templates/files that the page used when reviewed
# FR_INCLUDES_STABLE
#	For each template/file, check if a version of it was used when the page was reviewed
#	and	if the template/file itself has a stable version; use the newest those versions
# NOTE: We may have templates that do not have stable version. Also, given situational
# inclusion of templates (e.g. parser functions selecting template X or Y based on date),
# there may also be no "review time version" revision ID for a template used on a page.
# In such cases, we select the current (unreviewed) revision. Likewise for files.
$wgFlaggedRevsHandleIncludes = FR_INCLUDES_STABLE;

# End of configuration variables.
# ########

# Temp var
$wgFlaggedRevsRCCrap = true;
# Patrollable namespaces (overridden by reviewable namespaces) (don't use)
$wgFlaggedRevsPatrolNamespaces = array(); // @TODO: remove when ready

# Bots are granted autoreview via hooks, mark in rights 
# array so that it shows up in sp:ListGroupRights...
$wgGroupPermissions['bot']['autoreview'] = true;

# Lets some users access the review UI and set some flags
$wgAvailableRights[] = 'review'; # review pages to basic levels
$wgAvailableRights[] = 'validate'; # review pages to all levels
$wgAvailableRights[] = 'autoreview'; # auto-review pages on edit (including rollback)
$wgAvailableRights[] = 'autoreviewrestore'; # auto-review on rollback
$wgAvailableRights[] = 'unreviewedpages'; # view the list of unreviewed pages
$wgAvailableRights[] = 'movestable'; # move pages with stable versions
$wgAvailableRights[] = 'stablesettings'; # change page stability settings

$wgExtensionFunctions[] = 'efLoadFlaggedRevs';

$dir = dirname( __FILE__ ) . '/';
$langDir = $dir . 'presentation/language/';

# Load main i18n file and special page alias file
$wgExtensionMessagesFiles['FlaggedRevs'] = $langDir . 'FlaggedRevs.i18n.php';
$wgExtensionAliasesFiles['FlaggedRevs'] = $langDir . 'FlaggedRevs.alias.php';

$accessDir = $dir . 'dataclasses/';
# Utility classes...
$wgAutoloadClasses['FlaggedRevs'] = $accessDir . 'FlaggedRevs.class.php';
$wgAutoloadClasses['FRUserCounters'] = $accessDir . 'FRUserCounters.php';
$wgAutoloadClasses['FRUserActivity'] = $accessDir . 'FRUserActivity.php';
$wgAutoloadClasses['FlaggedPageConfig'] = $accessDir . 'FlaggedPageConfig.php';
$wgAutoloadClasses['FlaggedRevsLog'] = $accessDir . 'FlaggedRevsLog.php';
# Data object classes...
$wgAutoloadClasses['FRExtraCacheUpdate'] = $accessDir . 'FRExtraCacheUpdate.php';
$wgAutoloadClasses['FRExtraCacheUpdateJob'] = $accessDir . 'FRExtraCacheUpdate.php';
$wgAutoloadClasses['FRSquidUpdate'] = $accessDir . 'FRExtraCacheUpdate.php';
$wgAutoloadClasses['FRDependencyUpdate'] = $accessDir . 'FRDependencyUpdate.php';
$wgAutoloadClasses['FRInclusionManager'] = $accessDir . 'FRInclusionManager.php';
$wgAutoloadClasses['FlaggedPage'] = $accessDir . 'FlaggedPage.php';
$wgAutoloadClasses['FlaggedRevision'] = $accessDir . 'FlaggedRevision.php';
$wgAutoloadClasses['FRParserCacheStable'] = $accessDir . 'FRParserCacheStable.php';

# Event handler classes...
$wgAutoloadClasses['FlaggedRevsHooks'] = $dir . 'dataclasses/FlaggedRevs.hooks.php';
$wgAutoloadClasses['FlaggedRevsUIHooks'] = $dir . 'presentation/FlaggedRevsUI.hooks.php';
$wgAutoloadClasses['FlaggedRevsApiHooks'] = $dir . 'api/FlaggedRevsApi.hooks.php';
$wgAutoloadClasses['FlaggedRevsUpdaterHooks'] = $dir . 'schema/FlaggedRevsUpdater.hooks.php';
$wgAutoloadClasses['FlaggedRevsTestHooks'] = $dir . 'tests/FlaggedRevsTest.hooks.php';

# Business object classes
$wgAutoloadClasses['FRGenericSubmitForm'] = $dir . 'business/FRGenericSubmitForm.php';
$wgAutoloadClasses['RevisionReviewForm'] = $dir . 'business/RevisionReviewForm.php';
$wgAutoloadClasses['PageStabilityForm'] = $dir . 'business/PageStabilityForm.php';
$wgAutoloadClasses['PageStabilityGeneralForm'] = $dir . 'business/PageStabilityForm.php';
$wgAutoloadClasses['PageStabilityProtectForm'] = $dir . 'business/PageStabilityForm.php';

# Presentation classes...
$wgAutoloadClasses['FlaggedPageView'] = $dir . 'presentation/FlaggedPageView.php';
$wgAutoloadClasses['FlaggedRevsLogView'] = $dir . 'presentation/FlaggedRevsLogView.php';
$wgAutoloadClasses['FlaggedRevsXML'] = $dir . 'presentation/FlaggedRevsXML.php';
$wgAutoloadClasses['RevisionReviewFormUI'] = $dir . 'presentation/RevisionReviewFormUI.php';
$wgAutoloadClasses['RejectConfirmationFormUI'] = $dir . 'presentation/RejectConfirmationFormUI.php';

$specialActionDir = $dir . 'presentation/specialpages/actions/';
# Load revision review UI
$wgAutoloadClasses['RevisionReview'] = $specialActionDir . 'RevisionReview_body.php';
$wgExtensionMessagesFiles['RevisionReview'] = $langDir . 'RevisionReview.i18n.php';
# Stable version config UI
$wgAutoloadClasses['Stabilization'] = $specialActionDir . 'Stabilization_body.php';
$wgExtensionMessagesFiles['Stabilization'] = $langDir . 'Stabilization.i18n.php';

$specialReportDir = $dir . 'presentation/specialpages/reports/';
# Reviewed versions list
$wgAutoloadClasses['ReviewedVersions'] = $specialReportDir . 'ReviewedVersions_body.php';
$wgExtensionMessagesFiles['ReviewedVersions'] = $langDir . 'ReviewedVersions.i18n.php';
# Unreviewed pages list
$wgAutoloadClasses['UnreviewedPages'] = $specialReportDir . 'UnreviewedPages_body.php';
$wgExtensionMessagesFiles['UnreviewedPages'] = $langDir . 'UnreviewedPages.i18n.php';
$wgSpecialPageGroups['UnreviewedPages'] = 'quality';
# Pages with pending changes list
$wgAutoloadClasses['PendingChanges'] = $specialReportDir . 'PendingChanges_body.php';
$wgExtensionMessagesFiles['PendingChanges'] = $langDir . 'PendingChanges.i18n.php';
$wgSpecialPageGroups['PendingChanges'] = 'quality';
# Pages with tagged pending changes list
$wgAutoloadClasses['ProblemChanges'] = $specialReportDir . 'ProblemChanges_body.php';
$wgExtensionMessagesFiles['ProblemChanges'] = $langDir . 'ProblemChanges.i18n.php';
$wgSpecialPageGroups['ProblemChanges'] = 'quality';
# Reviewed pages list
$wgAutoloadClasses['ReviewedPages'] = $specialReportDir . 'ReviewedPages_body.php';
$wgExtensionMessagesFiles['ReviewedPages'] = $langDir . 'ReviewedPages.i18n.php';
$wgSpecialPageGroups['ReviewedPages'] = 'quality';
# Stable pages list (for protection config)
$wgAutoloadClasses['StablePages'] = $specialReportDir . 'StablePages_body.php';
$wgExtensionMessagesFiles['StablePages'] = $langDir . 'StablePages.i18n.php';
$wgSpecialPageGroups['StablePages'] = 'quality';
# Configured pages list (non-protection config)
$wgAutoloadClasses['ConfiguredPages'] = $specialReportDir . 'ConfiguredPages_body.php';
$wgExtensionMessagesFiles['ConfiguredPages'] = $langDir . 'ConfiguredPages.i18n.php';
$wgSpecialPageGroups['ConfiguredPages'] = 'quality';
# Filterable review log page to oversee reviews
$wgAutoloadClasses['QualityOversight'] = $specialReportDir . 'QualityOversight_body.php';
$wgExtensionMessagesFiles['QualityOversight'] = $langDir . 'QualityOversight.i18n.php';
$wgSpecialPageGroups['QualityOversight'] = 'quality';
# Review statistics
$wgAutoloadClasses['ValidationStatistics'] = $specialReportDir . 'ValidationStatistics_body.php';
$wgExtensionMessagesFiles['ValidationStatistics'] = $langDir . 'ValidationStatistics.i18n.php';
$wgSpecialPageGroups['ValidationStatistics'] = 'quality';

$apiActionDir = $dir . 'api/actions/';
# Page review module for API
$wgAutoloadClasses['ApiReview'] = $apiActionDir . 'ApiReview.php';
$wgAPIModules['review'] = 'ApiReview';
# Stability config module for API
$wgAutoloadClasses['ApiStabilize'] = $apiActionDir . 'ApiStabilize.php';
$wgAutoloadClasses['ApiStabilizeGeneral'] = $apiActionDir . 'ApiStabilize.php';
$wgAutoloadClasses['ApiStabilizeProtect'] = $apiActionDir . 'ApiStabilize.php';

$apiReportDir = $dir . 'api/reports/';
# OldReviewedPages for API
$wgAutoloadClasses['ApiQueryOldreviewedpages'] = $apiReportDir . 'ApiQueryOldreviewedpages.php';
$wgAPIListModules['oldreviewedpages'] = 'ApiQueryOldreviewedpages';
# UnreviewedPages for API
$wgAutoloadClasses['ApiQueryUnreviewedpages'] = $apiReportDir . 'ApiQueryUnreviewedpages.php';
# ReviewedPages for API
$wgAutoloadClasses['ApiQueryReviewedpages'] = $apiReportDir . 'ApiQueryReviewedpages.php';
# ConfiguredPages for API
$wgAutoloadClasses['ApiQueryConfiguredpages'] = $apiReportDir . 'ApiQueryConfiguredpages.php';
# Flag metadata for pages for API
$wgAutoloadClasses['ApiQueryFlagged'] = $apiReportDir . 'ApiQueryFlagged.php';
$wgAPIPropModules['flagged'] = 'ApiQueryFlagged';
# Site flag config for API
$wgAutoloadClasses['ApiFlagConfig'] = $apiReportDir . 'ApiFlagConfig.php';
$wgAPIModules['flagconfig'] = 'ApiFlagConfig';

# Special case cache invalidations
$wgJobClasses['flaggedrevs_CacheUpdate'] = 'FRExtraCacheUpdateJob';

# New user preferences
$wgDefaultUserOptions['flaggedrevssimpleui'] = (int)$wgSimpleFlaggedRevsUI;
$wgDefaultUserOptions['flaggedrevsstable'] = false;
$wgDefaultUserOptions['flaggedrevseditdiffs'] = true;
$wgDefaultUserOptions['flaggedrevsviewdiffs'] = false;

# JS/CSS modules and message bundles used by JS scripts
$localModulePath = dirname( __FILE__ ) . '/presentation/modules/';
$remoteModulePath = 'FlaggedRevs/presentation/modules';
$wgResourceModules['ext.flaggedRevs.basic'] = array(
	'styles' 		=> array( 'flaggedrevs.css' ),
	'localBasePath' => $localModulePath,
	'remoteExtPath' => $remoteModulePath,
);
$wgResourceModules['ext.flaggedRevs.advanced'] = array(
	'scripts' 		=> array( 'flaggedrevs.js' ),
	'messages'      => array(
		'revreview-toggle-show', 'revreview-toggle-hide',
		'revreview-diff-toggle-show', 'revreview-diff-toggle-hide',
		'revreview-log-toggle-show', 'revreview-log-toggle-hide',
		'revreview-log-details-show', 'revreview-log-details-hide'
	),
	'dependencies' 	=> array( 'mediawiki.util' ),
	'localBasePath' => $localModulePath,
	'remoteExtPath' => $remoteModulePath,
);
$wgResourceModules['ext.flaggedRevs.review'] = array(
	'scripts' 		=> array( 'review.js' ),
	'messages'      => array(
		'savearticle', 'tooltip-save', 'accesskey-save',
		'revreview-submitedit', 'revreview-submitedit-title',
		'revreview-submit-review', 'revreview-submit-unreview',
		'revreview-submit-reviewed', 'revreview-submit-unreviewed',
		'revreview-submitting', 'actioncomplete', 'actionfailed'
	),
	'dependencies' 	=> array( 'mediawiki.util' ),
	'localBasePath' => $localModulePath,
	'remoteExtPath' => $remoteModulePath,
);

# ####### EVENT-HANDLER FUNCTIONS  #########

# ######## User interface #########
# Override current revision, add patrol links, set cache...
$wgHooks['ArticleViewHeader'][] = 'FlaggedRevsUIHooks::onArticleViewHeader';
$wgHooks['ImagePageFindFile'][] = 'FlaggedRevsUIHooks::onImagePageFindFile';
# Override redirect behavior...
$wgHooks['InitializeArticleMaybeRedirect'][] = 'FlaggedRevsUIHooks::overrideRedirect';
# Set page view tabs
$wgHooks['SkinTemplateTabs'][] = 'FlaggedRevsUIHooks::onSkinTemplateTabs'; // All skins
$wgHooks['SkinTemplateNavigation'][] = 'FlaggedRevsUIHooks::onSkinTemplateNavigation'; // Vector
# Add notice tags to edit view
$wgHooks['EditPage::showEditForm:initial'][] = 'FlaggedRevsUIHooks::addToEditView';
# Tweak submit button name/title
$wgHooks['EditPageBeforeEditButtons'][] = 'FlaggedRevsUIHooks::onBeforeEditButtons';
# Autoreview information from form
$wgHooks['EditPageBeforeEditChecks'][] = 'FlaggedRevsUIHooks::addReviewCheck';
$wgHooks['EditPage::showEditForm:fields'][] = 'FlaggedRevsUIHooks::addRevisionIDField';
# Add draft link to section edit error
$wgHooks['EditPageNoSuchSection'][] = 'FlaggedRevsUIHooks::onNoSuchSection';
# Add notice tags to history
$wgHooks['PageHistoryBeforeList'][] = 'FlaggedRevsUIHooks::addToHistView';
# Add review form and visiblity settings link
$wgHooks['SkinAfterContent'][] = 'FlaggedRevsUIHooks::onSkinAfterContent';
# Mark items in page history
$wgHooks['PageHistoryPager::getQueryInfo'][] = 'FlaggedRevsUIHooks::addToHistQuery';
$wgHooks['PageHistoryLineEnding'][] = 'FlaggedRevsUIHooks::addToHistLine';
$wgHooks['LocalFile::getHistory'][] = 'FlaggedRevsUIHooks::addToFileHistQuery';
$wgHooks['ImagePageFileHistoryLine'][] = 'FlaggedRevsUIHooks::addToFileHistLine';
# Mark items in RC
$wgHooks['SpecialRecentChangesQuery'][] = 'FlaggedRevsUIHooks::addToRCQuery';
$wgHooks['SpecialWatchlistQuery'][] = 'FlaggedRevsUIHooks::addToWatchlistQuery';
$wgHooks['ChangesListInsertArticleLink'][] = 'FlaggedRevsUIHooks::addToChangeListLine';
# Page review on edit
$wgHooks['ArticleUpdateBeforeRedirect'][] = 'FlaggedRevsUIHooks::injectPostEditURLParams';
# Diff-to-stable
$wgHooks['DiffViewHeader'][] = 'FlaggedRevsUIHooks::onDiffViewHeader';
# Add diff=review url param alias
$wgHooks['NewDifferenceEngine'][] = 'FlaggedRevsUIHooks::checkDiffUrl';
# Local user account preference
$wgHooks['GetPreferences'][] = 'FlaggedRevsUIHooks::onGetPreferences';
# Show unreviewed pages links
$wgHooks['CategoryPageView'][] = 'FlaggedRevsUIHooks::onCategoryPageView';
# Review/stability log links
$wgHooks['LogLine'][] = 'FlaggedRevsUIHooks::logLineLinks';

# Add review notice, backlog notices and CSS/JS and set robots
$wgHooks['BeforePageDisplay'][] = 'FlaggedRevsUIHooks::onBeforePageDisplay';
# Add global JS vars
$wgHooks['MakeGlobalVariablesScript'][] = 'FlaggedRevsUIHooks::injectGlobalJSVars';

# Add flagging data to ApiQueryRevisions
$wgHooks['APIGetAllowedParams'][] = 'FlaggedRevsApiHooks::addApiRevisionParams';
$wgHooks['APIQueryAfterExecute'][] = 'FlaggedRevsApiHooks::addApiRevisionData';
# ########

# ######## Parser #########
# Parser hooks, selects the desired images/templates
$wgHooks['BeforeParserFetchTemplateAndtitle'][] = 'FlaggedRevsHooks::parserFetchStableTemplate';
$wgHooks['BeforeParserFetchFileAndTitle'][] = 'FlaggedRevsHooks::parserFetchStableFile';
# ########

# ######## DB write operations #########
# Autopromote Editors
$wgHooks['ArticleSaveComplete'][] = 'FlaggedRevsHooks::onArticleSaveComplete';
# Auto-reviewing
$wgHooks['RecentChange_save'][] = 'FlaggedRevsHooks::autoMarkPatrolled';
$wgHooks['NewRevisionFromEditComplete'][] = 'FlaggedRevsHooks::maybeMakeEditReviewed';
# Null edit review via checkbox
$wgHooks['ArticleSaveComplete'][] = 'FlaggedRevsHooks::maybeNullEditReview';
# Disable auto-promotion for demoted users
$wgHooks['UserRights'][] = 'FlaggedRevsHooks::recordDemote';
# User edit tallies
$wgHooks['ArticleRollbackComplete'][] = 'FlaggedRevsHooks::incrementRollbacks';
$wgHooks['NewRevisionFromEditComplete'][] = 'FlaggedRevsHooks::incrementReverts';
# Update fr_page_id and tracking rows on revision restore and merge
$wgHooks['ArticleRevisionUndeleted'][] = 'FlaggedRevsHooks::onRevisionRestore';
$wgHooks['ArticleMergeComplete'][] = 'FlaggedRevsHooks::onArticleMergeComplete';

# Update tracking rows and cache on page changes (@TODO: this sucks):
# Article edit/create
$wgHooks['ArticleEditUpdates'][] = 'FlaggedRevsHooks::onArticleEditUpdates';
# Article delete/restore
$wgHooks['ArticleDeleteComplete'][] = 'FlaggedRevsHooks::onArticleDelete';
$wgHooks['ArticleUndelete'][] = 'FlaggedRevsHooks::onArticleUndelete';
# Revision delete/restore
$wgHooks['ArticleRevisionVisibilitySet'][] = 'FlaggedRevsHooks::onRevisionDelete';
# Article move
$wgHooks['TitleMoveComplete'][] = 'FlaggedRevsHooks::onTitleMoveComplete';
# File upload
$wgHooks['FileUpload'][] = 'FlaggedRevsHooks::onFileUpload';
# ########

# ######## Other #########
# Determine what pages can be moved and patrolled
$wgHooks['getUserPermissionsErrors'][] = 'FlaggedRevsHooks::onUserCan';
# Implicit autoreview rights group
$wgHooks['AutopromoteCondition'][] = 'FlaggedRevsHooks::checkAutoPromoteCond';

# Check if a page is currently being reviewed
$wgHooks['MediaWikiPerformAction'][] = 'FlaggedRevsUIHooks::onMediaWikiPerformAction';

# Actually register special pages
$wgHooks['SpecialPage_initList'][] = 'FlaggedRevsUIHooks::defineSpecialPages';

# Stable dump hook
$wgHooks['WikiExporter::dumpStableQuery'][] = 'FlaggedRevsHooks::stableDumpQuery';

# GNSM category hooks
$wgHooks['GoogleNewsSitemap::Query'][] = 'FlaggedRevsHooks::gnsmQueryModifier';

# Duplicate flagged* tables in parserTests.php
$wgHooks['ParserTestTables'][] = 'FlaggedRevsTestHooks::onParserTestTables';
# Integration tests
$wgHooks['UnitTestsList'][] = 'FlaggedRevsTestHooks::getUnitTests';

# Database schema changes
$wgHooks['LoadExtensionSchemaUpdates'][] = 'FlaggedRevsUpdaterHooks::addSchemaUpdates';

# Performance Don't show content on diff
$wgHooks['ArticleContentOnDiff'][] = 'FlaggedRevsUIHooks::onArticleContentOnDiff';

# ########

function efSetFlaggedRevsConditionalHooks() {
	global $wgHooks;
	# Mark items in user contribs
	if ( !FlaggedRevs::useOnlyIfProtected() ) {
		$wgHooks['ContribsPager::getQueryInfo'][] = 'FlaggedRevsUIHooks::addToContribsQuery';
		$wgHooks['ContributionsLineEnding'][] = 'FlaggedRevsUIHooks::addToContribsLine';
	}
	if ( FlaggedRevs::useProtectionLevels() ) {
		# Add protection form field
		$wgHooks['ProtectionForm::buildForm'][] = 'FlaggedRevsUIHooks::onProtectionForm';
		$wgHooks['ProtectionForm::showLogExtract'][] = 'FlaggedRevsUIHooks::insertStabilityLog';
		# Save stability settings
		$wgHooks['ProtectionForm::save'][] = 'FlaggedRevsUIHooks::onProtectionSave';
		# Parser stuff
		$wgHooks['ParserFirstCallInit'][] = 'FlaggedRevsHooks::onParserFirstCallInit';
		$wgHooks['LanguageGetMagic'][] = 'FlaggedRevsHooks::onLanguageGetMagic';
		$wgHooks['ParserGetVariableValueSwitch'][] = 'FlaggedRevsHooks::onParserGetVariableValueSwitch';
		$wgHooks['MagicWordwgVariableIDs'][] = 'FlaggedRevsHooks::onMagicWordwgVariableIDs';
	}
	# Give bots the 'autoreview' right (here so it triggers after CentralAuth)
	# @TODO: better way to ensure hook order
	$wgHooks['UserGetRights'][] = 'FlaggedRevsHooks::onUserGetRights';
}

# ####### END HOOK TRIGGERED FUNCTIONS  #########

function efLoadFlaggedRevs() {
	global $wgFlaggedRevsRCCrap, $wgUseRCPatrol, $wgFlaggedRevsNamespaces;
	if ( $wgFlaggedRevsRCCrap ) {
		# If patrolling is already on, then we know that it 
		# was intended to have all namespaces patrollable.
		if ( $wgUseRCPatrol ) {
			global $wgFlaggedRevsPatrolNamespaces;
			$wgFlaggedRevsPatrolNamespaces = MWNamespace::getValidNamespaces();
		}
		/* TODO: decouple from rc patrol */
		# Check if FlaggedRevs is enabled by default for pages...
		if ( $wgFlaggedRevsNamespaces && !FlaggedRevs::useOnlyIfProtected() ) {
			# Use RC Patrolling to check for vandalism.
			# Edits to reviewable pages must be flagged to be patrolled.
			$wgUseRCPatrol = true;
		}
	}
	global $wgFlaggedRevsAutoconfirm, $wgAutopromote;
	# $wgFlaggedRevsAutoconfirm is now a wrapper around $wgAutopromote
	if ( is_array( $wgFlaggedRevsAutoconfirm ) ) {
		$wgAutopromote['autoreview'] = array( '&', // AND
			array( APCOND_AGE, $wgFlaggedRevsAutoconfirm['days']*86400 ),
			array( APCOND_EDITCOUNT, $wgFlaggedRevsAutoconfirm['edits'] ),
			array( APCOND_FR_EDITSUMMARYCOUNT, $wgFlaggedRevsAutoconfirm['editComments'] ),
			array( APCOND_FR_UNIQUEPAGECOUNT, $wgFlaggedRevsAutoconfirm['uniqueContentPages'] ),
			array( APCOND_FR_EDITSPACING,
				$wgFlaggedRevsAutoconfirm['spacing'], $wgFlaggedRevsAutoconfirm['benchmarks'] ),
			array( '|', // OR
				array( APCOND_FR_CONTENTEDITCOUNT, $wgFlaggedRevsAutoconfirm['totalContentEdits'] ),
				array( APCOND_FR_CHECKEDEDITCOUNT, $wgFlaggedRevsAutoconfirm['totalCheckedEdits'] )
			)
		);
		if ( $wgFlaggedRevsAutoconfirm['email'] ) {
			$wgAutopromote['autoreview'][] = array( APCOND_EMAILCONFIRMED );
		}
		if ( $wgFlaggedRevsAutoconfirm['neverBlocked'] ) {
			$wgAutopromote['autoreview'][] = array( APCOND_FR_NEVERBOCKED );
		}
	}
	# Conditional API modules
	efSetFlaggedRevsConditionalAPIModules();
	# Load hooks that aren't always set
	efSetFlaggedRevsConditionalHooks();
	# Remove conditionally applicable rights
	efSetFlaggedRevsConditionalRights();
	# Defaults for user preferences
	efSetFlaggedRevsConditionalPreferences();
}

function efSetFlaggedRevsConditionalAPIModules() {
	global $wgAPIModules, $wgAPIListModules;
	if ( FlaggedRevs::useOnlyIfProtected() ) {
		$wgAPIModules['stabilize'] = 'ApiStabilizeProtect';
	} else {
		$wgAPIModules['stabilize'] = 'ApiStabilizeGeneral';
		$wgAPIListModules['reviewedpages'] = 'ApiQueryReviewedpages';
		$wgAPIListModules['unreviewedpages'] = 'ApiQueryUnreviewedpages';
		$wgAPIListModules['configuredpages'] = 'ApiQueryConfiguredpages';
	}
}

function efSetFlaggedRevsConditionalRights() {
	global $wgGroupPermissions, $wgImplicitGroups, $wgFlaggedRevsAutoconfirm;
	if ( FlaggedRevs::useOnlyIfProtected() ) {
		// Removes sp:ListGroupRights cruft
		if ( isset( $wgGroupPermissions['editor'] ) ) {
			unset( $wgGroupPermissions['editor']['unreviewedpages'] );
		}
		if ( isset( $wgGroupPermissions['reviewer'] ) ) {
			unset( $wgGroupPermissions['reviewer']['unreviewedpages'] );
		}
	}
	if ( !empty( $wgFlaggedRevsAutoconfirm ) ) {
		# Implicit autoreview group
		$wgGroupPermissions['autoreview']['autoreview'] = true;
		# Don't show the 'autoreview' group everywhere
		$wgImplicitGroups[] = 'autoreview';
	}
}

function efSetFlaggedRevsConditionalPreferences() {
	global $wgDefaultUserOptions, $wgSimpleFlaggedRevsUI;
	$wgDefaultUserOptions['flaggedrevssimpleui'] = (int)$wgSimpleFlaggedRevsUI;
}

# Add review log
$wgLogTypes[] = 'review';
$wgFilterLogTypes['review'] = true;
$wgLogNames['review'] = 'review-logpage';
$wgLogHeaders['review'] = 'review-logpagetext';
# Various actions are used for log filtering ...
$wgLogActions['review/approve']  = 'review-logentry-app'; // checked (again)
$wgLogActions['review/approve2']  = 'review-logentry-app'; // quality (again)
$wgLogActions['review/approve-i']  = 'review-logentry-app'; // checked (first time)
$wgLogActions['review/approve2-i']  = 'review-logentry-app'; // quality (first time)
$wgLogActions['review/approve-a']  = 'review-logentry-app'; // checked (auto)
$wgLogActions['review/approve2-a']  = 'review-logentry-app'; // quality (auto)
$wgLogActions['review/approve-ia']  = 'review-logentry-app'; // checked (initial & auto)
$wgLogActions['review/approve2-ia']  = 'review-logentry-app'; // quality (initial & auto)
$wgLogActions['review/unapprove'] = 'review-logentry-dis'; // was checked
$wgLogActions['review/unapprove2'] = 'review-logentry-dis'; // was quality

# Add stable version log
$wgLogTypes[] = 'stable';
$wgLogNames['stable'] = 'stable-logpage';
$wgLogHeaders['stable'] = 'stable-logpagetext';
$wgLogActionsHandlers['stable/config'] = 'FlaggedRevsLogView::stabilityLogText'; // customize
$wgLogActionsHandlers['stable/modify'] = 'FlaggedRevsLogView::stabilityLogText'; // re-customize
$wgLogActionsHandlers['stable/reset'] = 'FlaggedRevsLogView::stabilityLogText'; // reset

# AJAX functions
$wgAjaxExportList[] = 'RevisionReview::AjaxReview';
$wgAjaxExportList[] = 'FlaggedPageView::AjaxBuildDiffHeaderItems';

# Cache update
$wgSpecialPageCacheUpdates[] = 'efFlaggedRevsUnreviewedPagesUpdate';

function efFlaggedRevsUnreviewedPagesUpdate() {
	$base = dirname( __FILE__ );
	require_once( "$base/maintenance/updateQueryCache.inc" );
	update_flaggedrevs_querycache();
	require_once( "$base/maintenance/updateStats.inc" );
	update_flaggedrevs_stats();
}

# B/C ...
$wgLogActions['rights/erevoke']  = 'rights-editor-revoke';
