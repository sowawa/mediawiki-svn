<?php

/**
 * Internationalisation file for the Username Blacklist extension
 *
 * @author Rob Church <robchur@gmail.com>
 * @addtogroup Extensions
 */

function efUsernameBlacklistMessages( $single = false ) {
	$messages = array(

/* English (Rob Church) */
'en' => array(
'blacklistedusername' => 'Blacklisted username',
'blacklistedusernametext' => 'The user name you have chosen matches the [[MediaWiki:Usernameblacklist|
list of blacklisted usernames]]. Please choose another name.',
),

/* German (Raymond) */
'de' => array(
'blacklistedusername' => 'Benutzername auf der Sperrliste',
'blacklistedusernametext' => 'Der gewählte Benutzername steht auf der [[MediaWiki:Usernameblacklist|Liste der gesperrten Benutzernamen]]. Bitte einen anderen wählen.',
),

/* French */
'fr' => array(
'blacklistedusername' => 'Noms d’utilisateurs en liste noire',
'blacklistedusernametext' => 'Le nom d’utilisateur que vous avez choisi se trouve sur la 
[[MediaWiki:Usernameblacklist|liste des noms interdits]]. Veuillez choisir un autre nom.',
),

/* Indonesian (Ivan Lanin) */
'id' => array(
'blacklistedusername' => 'Daftar hitam nama pengguna',
'blacklistedusernametext' => 'Nama pengguna yang Anda pilih berada dalam [[MediaWiki:Usernameblacklist|
daftar hitam nama pengguna]]. Harap pilih nama lain.',
),

/* Italian (BrokenArrow) */
'it' => array(
'blacklistedusername' => 'Nome utente non consentito',
'blacklistedusernametext' => 'Il nome utente scelto è inserito nella [[MediaWiki:Usernameblacklist|lista dei nomi non consentiti]]. Si prega di scegliere un altro nome.',
),

/* nld / Dutch (Siebrand Mazeland) */
'nl' => array(
'blacklistedusername' => 'Gebruikersnaam op zwarte lijst',
'blacklistedusernametext' => 'De gebruikersnaam die u heeft gekozen staat op de [[MediaWiki:Usernameblacklist|
zwarte lijst van gebruikersnamen]]. Kies alstublieft een andere naam.',
),

/* Occitan (Cedric31) */
'oc' => array(
'blacklistedusername' => 'Noms d’utilizaires en lista negra',
'blacklistedusernametext' => 'Lo nom d’utilizaire qu\'avètz causit se tròba sus la [[MediaWiki:Usernameblacklist|lista dels noms interdiches]]. Causissètz un autre nom.',
),

/* Slovak (helix84) */
'sk' => array(
'blacklistedusername' => 'Používateľské meno na čiernej listine',
'blacklistedusernametext' => 'Používateľské meno, ktoré ste si zvolili sa nachádza na [[MediaWiki:Usernameblacklist|
čiernej listine používateľských mien]]. Prosím, zvoľte si iné.',
),

/* Chinese (China) (Shinjiman) */
'zh-cn' => array(
'blacklistedusername' => '列入黑名单的用户名',
'blacklistedusernametext' => '您所选择的用户名是与[[MediaWiki:Usernameblacklist|用户名黑名单列表]]匹配。请选择另一个名称。',
),

/* Chinese (Taiwan) (Shinjiman) */
'zh-tw' => array(
'blacklistedusername' => '列入黑名單的用戶名',
'blacklistedusernametext' => '您所選擇的用戶名是與[[MediaWiki:Usernameblacklist|用戶名黑名單列表]]符合。請選擇另一個名稱。',
),

/* Cantonese (Shinjiman) */
'zh-yue' => array(
'blacklistedusername' => '列入黑名單嘅用戶名',
'blacklistedusernametext' => '你所揀嘅用戶名係同[[MediaWiki:Usernameblacklist|用戶名黑名單一覽]]符合。請揀過另一個名喇。',
),

	);

	/* Chinese (Hong Kong), inherited from Chinese (Taiwan) */
	$messages['zh-hk'] = $messages['zh-tw'];
	/* Chinese (Singapore), inherited from Chinese (China) */
	$messages['zh-sg'] = $messages['zh-cn'];

	return $single ? $messages['en'] : $messages;
}

?>
