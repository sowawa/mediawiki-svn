<?php
/** Aramaic (ܐܪܡܝܐ)
 *
 * @ingroup Language
 * @file
 *
 * @author 334a
 * @author A2raya07
 * @author Basharh
 * @author The Thadman
 */

$rtl = true;

$defaultUserOptionOverrides = array(
	# Swap sidebar to right side by default
	'quickbar' => 2,
);

$messages = array(
# User preference toggles
'tog-hideminor' => 'ܛܫܝ ܫ̈ܚܠܦܬܐ ܙܥܘܪ̈ܬܐ ܒܫ̈ܚܠܦܬܐ ܚ̈ܕܬܬܐ',

# Dates
'sunday'       => 'ܚܕܒܫܒܐ',
'monday'       => 'ܬܪܝܢܒܫܒܐ',
'tuesday'      => 'ܬܠܬܒܫܒܐ',
'wednesday'    => 'ܐܪܒܥܒܫܒܐ',
'thursday'     => 'ܚܡܫܒܫܒܐ',
'friday'       => 'ܥܪܘܒܬܐ',
'saturday'     => 'ܫܒܬܐ',
'sun'          => 'ܚܕܒܫܒܐ',
'mon'          => 'ܬܪܝܢܒܫܒܐ',
'tue'          => 'ܬܠܬܒܫܒܐ',
'wed'          => 'ܐܪܒܥܒܫܒܐ',
'thu'          => 'ܚܡܫܒܫܒܐ',
'fri'          => 'ܥܪܘܒܬܐ',
'sat'          => 'ܫܒܬܐ',
'january'      => 'ܟܢܘܢ ܬܪܝܢܐ',
'february'     => 'ܫܒܛ',
'march'        => 'ܐܕܪ',
'april'        => 'ܢܝܣܢ',
'may_long'     => 'ܐܝܪ',
'june'         => 'ܚܙܝܪܢ',
'july'         => 'ܬܡܘܙ',
'august'       => 'ܐܒ',
'september'    => 'ܐܝܠܘܠ',
'october'      => 'ܬܫܪܝܢ ܩܕܝܡ',
'november'     => 'ܬܫܪܝܢ ܬܪܝܢܐ',
'december'     => 'ܟܢܘܢ ܩܕܝܡ',
'january-gen'  => 'ܟܢܘܢ ܬܪܝܢܐ',
'february-gen' => 'ܫܒܛ',
'jan'          => 'ܟܢܘܢ ܒ',
'feb'          => 'ܫܒܛ',
'mar'          => 'ܐܕܪ',
'apr'          => 'ܢܝܣܢ',
'may'          => 'ܐܝܪ',
'jun'          => 'ܚܙܝܪܢ',
'jul'          => 'ܬܡܘܙ',
'aug'          => 'ܐܒ',
'sep'          => 'ܐܝܠܘܠ',
'oct'          => 'ܬܫܪܝܢ ܐ',
'nov'          => 'ܬܫܪܝܢ ܒ',
'dec'          => 'ܟܢܘܢ ܐ',

'article'        => 'ܡܓܠܬܐ',
'newwindow'      => '(ܦܬܚ ܒܟܘܬܐ ܚܕܬܐ)',
'cancel'         => 'ܒܛܘܠ',
'qbpageoptions'  => 'ܗܕܐ ܦܐܬܐ',
'qbmyoptions'    => 'ܓܒܝ̈ܘܬܝ',
'qbspecialpages' => 'ܦܐܬܘܬܐ ܪܫܝܬܐ',
'moredotdotdot'  => '...ܝܬܝܪ̈ܐ',
'mypage'         => 'ܦܐܬܝ',
'mytalk'         => 'ܕܘܪܫܝ',
'navigation'     => 'ܐܠܦܪܘܬܐ',
'and'            => '&#32;ܘ',

'errorpagetitle'    => 'ܦܘܕܐ',
'help'              => 'ܥܘܕܪܢܐ',
'search'            => 'ܒܨܐ',
'searchbutton'      => 'ܒܨܝ',
'go'                => 'ܙܠ',
'searcharticle'     => 'ܙܠ',
'history'           => 'ܬܫܥܝܬܐ ܕܦܐܬܐ',
'history_short'     => 'ܬܫܥܝܬܐ',
'printableversion'  => 'ܨܚܚܐ ܡܬܛܒܥܢܐ',
'permalink'         => 'ܐܣܘܪܐ ܦܝܘܫܐ',
'print'             => 'ܛܒܘܥ',
'edit'              => 'ܫܚܠܦ',
'create'            => 'ܒܪܝ',
'editthispage'      => 'ܫܚܠܦ ܗܕܐ ܦܐܬܐ',
'create-this-page'  => 'ܒܪܝ ܗܕܐ ܦܐܬܐ',
'delete'            => 'ܫܘܦ',
'deletethispage'    => 'ܫܘܦ ܗܕܐ ܦܐܬܐ',
'protect'           => 'ܚܡܝ',
'protectthispage'   => 'ܚܡܝ ܗܕܐ ܦܐܬܐ',
'unprotect'         => 'ܠܐ ܚܡܝ',
'unprotectthispage' => 'ܠܐ ܚܡܝ ܗܕܐ ܦܐܬܐ',
'newpage'           => 'ܦܐܬܐ ܚܕܬܐ',
'talkpage'          => 'ܕܪܘܫ ܗܕܐ ܦܐܬܐ',
'specialpage'       => 'ܦܐܬܐ ܕܝܠܢܝܬܐ',
'talk'              => 'ܕܘܪܫܐ',
'toolbox'           => 'ܣܢܕܘܩܐ ܕܡܐܢ̈ܐ',
'imagepage'         => 'ܚܙܝ ܦܐܬܐ ܕܠܦܦܐ',
'mediawikipage'     => 'ܚܙܝ ܦܐܬܐ ܕܐܓܪܬܐ',
'templatepage'      => 'ܚܙܝ ܦܐܬܐ ܕܩܠܒܐ',
'viewhelppage'      => 'ܚܙܝ ܦܐܬܐ ܕܥܘܕܪܢܐ',
'viewtalkpage'      => 'ܚܙܝ ܕܘܪܫܐ',
'otherlanguages'    => 'ܠܫܢ̈ܐ ܐܚܪ̈ܢܐ',
'protectedpage'     => 'ܦܐܬܐ ܚܡܝܬܐ',
'jumptonavigation'  => 'ܐܠܦܪܘܬܐ',
'jumptosearch'      => 'ܒܨܐ',

# All link text and link target definitions of links into project namespace that get used by other message strings, with the exception of user group pages (see grouppage) and the disambiguation template definition (see disambiguations).
'currentevents'        => 'ܓܕ̈ܫܐ ܗܫ̈ܝܐ',
'currentevents-url'    => 'Project:ܓܕ̈ܫܐ ܗܫܝܐ',
'edithelp'             => 'ܥܘܕܪܢܐ ܠܫܘܚܠܦܐ',
'mainpage'             => 'ܦܐܬܐ ܪܫܝܬܐ',
'mainpage-description' => 'ܦܐܬܐ ܪܫܝܬܐ',
'portal'               => 'ܬܪܥܐ ܕܟܢܫܐ',
'portal-url'           => 'Project:ܬܪܥܐ ܕܟܢܫܐ',

'ok'                  => 'ܛܒ',
'newmessageslink'     => 'ܐܓܪ̈ܬܐ ܚܕ̈ܬܬܐ',
'newmessagesdifflink' => 'ܫܚܠܦܬܐ ܐܚܪܝܬܐ',
'editsection'         => 'ܫܚܠܦ',
'editold'             => 'ܫܚܠܦ',
'viewsourceold'       => 'ܚܙܝ ܥܩܪܐ',
'viewsourcelink'      => 'ܚܙܝ ܥܩܪܐ',
'toc'                 => 'ܚ̈ܒܝܫܬܐ',
'showtoc'             => 'ܚܘܝ',
'hidetoc'             => 'ܛܫܝ',

# Short words for each namespace, by default used in the namespace tab in monobook
'nstab-main'      => 'ܦܐܬܐ',
'nstab-mediawiki' => 'ܐܓܪܬܐ',
'nstab-template'  => 'ܩܠܒܐ',
'nstab-help'      => 'ܦܐܬܐ ܕܥܘܕܪܢܐ',
'nstab-category'  => 'ܣܕܪܐ',

# General errors
'error'      => 'ܦܘܕܐ',
'viewsource' => 'ܚܙܝ ܥܩܪܐ',

# Login and logout pages
'login'          => 'ܥܘܠ',
'userlogin'      => 'ܥܘܠ \\ ܒܪܝ ܫܡܐ',
'logout'         => 'ܦܠܛ',
'userlogout'     => 'ܦܠܘܛ',
'yourrealname'   => ':ܫܡܐ ܫܪܝܪܐ',
'yourlanguage'   => ':ܠܫܢܐ',
'mailmypassword' => 'ܚܕܬܐ password ܫܕܪ ܠܝ',

# Edit pages
'summary'      => 'ܟܪܝܘܬܐ ܕܫܘܚܠܦܐ',
'minoredit'    => 'ܗܢܐ ܗܘ ܫܚܠܦܬܐ ܙܥܘܪܬܐ',
'watchthis'    => 'ܕܘܩ ܦܐܬܐ ܗܕܐ',
'savearticle'  => 'ܫܚܠܦ ܦܐܬܐ',
'preview'      => 'ܚܝܪܬܐ ܩܕܡܝܬܐ',
'showpreview'  => 'ܚܘܝ ܚܝܪܬܐ ܩܕܡܝܬܐ',
'showdiff'     => 'ܚܘܝ ܫܘ̈ܚܠܦܐ',
'loginreqlink' => 'ܥܘܠ',
'newarticle'   => '(ܚܕܬܐ)',
'yourtext'     => 'ܟܬܒܝ̈ܟ',

# History pages
'historyempty' => '(ܣܦܝܩܐ)',

# Revision deletion
'rev-delundel' => 'ܚܘܝ/ܛܫܝ',

# Preferences page
'mypreferences' => 'ܦܪ̈ܝܫܘܝܬܝ',
'math'          => 'ܡܬܡܐܛܝܩܘܬܐ',
'prefs-rc'      => 'ܫܚ̈ܠܦܬܐ ܚܕ̈ܬܬܐ',
'saveprefs'     => 'ܚܡܝ',

# Recent changes
'recentchanges' => 'ܫܚ̈ܠܦܬܐ ܚܕ̈ܬܬܐ',

# Recent changes linked
'recentchangeslinked' => 'ܫܚܠ̈ܦܐ ܕܡܝܐ',

# Upload
'upload'   => 'ܐܛܥܢ ܠܦܦܐ',
'filename' => 'ܫܡܐ ܕܠܦܦܐ',

# Special:ListFiles
'listfiles_name' => 'ܫܡܐ',

# Random page
'randompage' => 'ܡ̈ܓܠܬܐ ܚܘ̈ܝܚܐ',

# Miscellaneous special pages
'move' => 'ܫܢܝ',

# Book sources
'booksources'    => 'ܙܠ',
'booksources-go' => 'ܙܠ',

# Special:AllPages
'allpages'       => 'ܟܠ ܦܐܬܐ',
'allarticles'    => 'ܟܠ ܡ̈ܓܠܐ',
'allpagessubmit' => 'ܙܠ',

# E-mail user
'emailfrom' => 'ܡܢ',
'emailto'   => 'ܥܠ',

# Watchlist
'watchlist'     => 'ܕܘܩܘܬܝ',
'mywatchlist'   => 'ܕܘܩܘܬܝ',
'watch'         => 'ܕܘܩ',
'watchthispage' => 'ܕܘܩ ܗܕܐ ܦܐܬܐ',
'unwatch'       => 'ܠܐ ܕܘܩ',

# Restrictions (nouns)
'restriction-edit' => 'ܫܚܠܦ',
'restriction-move' => 'ܫܢܝ',

# Namespace form on various pages
'blanknamespace' => '(ܪܫܝܐ)',

# Contributions
'mycontris' => 'ܫ̈ܘܬܦܘܝܬܝ',

# What links here
'whatlinkshere' => 'ܡܐ ܐܣܪ ܠܟܐ؟',

# Namespace 8 related
'allmessagesname' => 'ܫܡܐ',

# Tooltip help for the actions
'tooltip-t-print' => 'ܨܚܚܐ ܡܬܛܒܥܢܐ ܕܗܕܐ ܦܐܬܐ',

# Special:NewFiles
'ilsubmit' => 'ܒܨܝ',

# 'all' in various places, this might be different for inflected languages
'recentchangesall' => 'ܟܠ',
'namespacesall'    => 'ܟܠ',

# action=purge
'confirm_purge_button' => 'ܛܒ',

# Multipage image navigation
'imgmultigo' => 'ܙܠ!',

# Table pager
'table_pager_limit_submit' => 'ܙܠ',

# Watchlist editor
'watchlistedit-raw-submit' => 'ܚܕܬ ܕܘܩܘܬܐ',

# Special:FilePath
'filepath'        => 'ܫܒܝܠܐ ܕܠܦܦܐ',
'filepath-page'   => 'ܠܦܦܐ',
'filepath-submit' => 'ܫܒܝܠܐ',

# Special:SpecialPages
'specialpages' => 'ܦܐܬܘ̈ܬܐ ܕܝܠܢܝ̈ܬܐ',

);
