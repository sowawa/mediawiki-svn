<?php
/**
 * Internationalisation file for LinkSearch extension.
 *
 * @package MediaWiki
 * @subpackage Extensions
*/

$wgLinkSearchMessages = array();

$wgLinkSearchMessages['en'] = array(
	'linksearch'       => 'Search web links',
	'linksearch-pat'   => 'Search pattern:',
	'linksearch-ns'    => 'Namespace:',
	'linksearch-ok'    => 'Search',
	'linksearch-text'  => 'Wildcards such as "*.wikipedia.org" may be used.<br />Supported protocols: <tt>$1</tt>',
	'linksearch-line'  => '$1 linked from $2',
	'linksearch-error' => 'Wildcards may appear only at the start of the hostname.',
);
$wgLinkSearchMessages['cs'] = array(
	'linksearch'       => 'Hledání externích odkazů',
	'linksearch-text'  => 'Lze používat zástupné znaky, např. „*.wikipedia.org“.',
	'linksearch-line'  => '$2 odkazuje na $1',
	'linksearch-error' => 'Zástupné znaky lze použít jen na začátku doménového jména.',
);
$wgLinkSearchMessages['de'] = array(
	'linksearch'       => 'Weblink-Suche',
	'linksearch-pat'   => 'Suchmuster:',
	'linksearch-ns'    => 'Namensraum:',
	'linksearch-ok'    => 'Suche',
	'linksearch-text'  => 'Diese Spezialseite ermöglicht die Suche nach Seiten, in denen bestimmte Weblinks enthalten sind. Dabei können Wildcards wie beispielsweise <tt>*.example.com</tt> benutzt werden.<br />Unterstützte Protokolle: <tt>$1</tt>',
	'linksearch-line'  => '$1 ist verlinkt von $2',
	'linksearch-error' => 'Wildcards können nur am Anfang der URL verwendet werden.',
);
$wgLinkSearchMessages['fi'] = array(
	'linksearch'       => 'Etsi ulkoisia linkkejä',
	'linksearch-text'  => 'Asteriskia (*) voi käyttää jokerimerkkinä, esimerkiksi ”*.wikipedia.org”.',
	'linksearch-line'  => '$1 on linkitetty sivulta $2',
	'linksearch-error' => 'Jokerimerkkiä voi käyttää ainoastaan osoitteen alussa.',
);
$wgLinkSearchMessages['he'] = array(
	'linksearch'       => 'חיפוש קישורים חיצוניים',
	'linksearch-pat'   => 'קישור לחיפוש:',
	'linksearch-ns'    => 'מרחב שם:',
	'linksearch-ok'    => 'חיפוש',
	'linksearch-text'  => 'ניתן להשתמש בתווים כללים, לדוגמה "‎*.wikipedia.org".<br />פרוטוקולים נתמכים: <tt>$1</tt>',
	'linksearch-line'  => '$1 מקושר מהדף $2',
	'linksearch-error' => 'תווים כלליים יכולים להופיע רק בתחילת שם השרת.',
);
$wgLinkSearchMessages['hu'] = array(
	'linksearch'       => 'Keresés külső hivatkozások szerint',
	'linksearch-text'  => 'A helyettesítő karaktereket is lehet használni, például "*.wikipedia.org".',
	'linksearch-line'  => '$1 hivatkozva innen: $2',
	'linksearch-error' => 'Helyettesítő karakterek csak a cím elején szerepelhetnek.',
);
$wgLinkSearchMessages['id'] = array(
	'linksearch'       => 'Pencarian pranala luar',
	'linksearch-pat'   => 'Pola pencarian:',
	'linksearch-ns'    => 'Ruang nama:',
	'linksearch-ok'    => 'Cari',
	'linksearch-text'  => 'Bentuk pencarian \'\'wildcards\'\' seperti "*.wikipedia.org" dapat digunakan.<br />Protokol yang didukung: <tt>$1</tt>',
	'linksearch-line'  => '$1 terpaut dari $2',
	'linksearch-error' => '\'\'Wildcards\'\' hanya dapat digunakan di bagian awal dari nama host.'
);
$wgLinkSearchMessages['it'] = array(
	'linksearch'       => 'Ricerca collegamenti esterni',
	'linksearch-text'  => 'È possibile fare uso di metacaratteri, ad es. "*.example.org".',
	'linksearch-line'  => '$1 presente nella pagina $2',
	'linksearch-error' => 'I metacaratteri possono essere usati solo all\'inizio del nome dell\'host.',
);
$wgLinkSearchMessages['ja'] = array(
	'linksearch'       => '外部リンクの検索',
	'linksearch-pat'   => '検索パターン:',
	'linksearch-ns'    => '名前空間:',
	'linksearch-ok'    => '検索',
	'linksearch-text'  => '"*.wikipedia.org" のようにワイルドカードを使うことができます。<br />対応プロトコル: <tt>$1</tt>',
	'linksearch-line'  => '$1 が $2 からリンクされています',
	'linksearch-error' => 'ワイルドカードはホスト名の先頭でのみ使用できます。',
);
$wgLinkSearchMessages['kk-kz'] = array(
	'linksearch'       => 'Еренсілтемелерін іздеу',
	'linksearch-pat'   => 'Іздеу шарты:',
	'linksearch-ns'    => 'Есім аясы:',
	'linksearch-ok'    => 'Іздеу',
	'linksearch-text'  => '«*.wikipedia.org» атауына ұқсасты бәдел нышандарды қолдануға болады. ',
	'linksearch-line'  => '$2 дегеннен $1 сілтеген',
	'linksearch-error' => 'Бәдел нышандар тек сервер жайы атауының бастауында болуы мүмкін.',
);
$wgLinkSearchMessages['kk-tr'] = array(
	'linksearch'       => 'Erensiltemelerin izdew',
	'linksearch-pat'   => 'İzdew şartı:',
	'linksearch-ns'    => 'Esim ayası:',
	'linksearch-ok'    => 'İzdew',
	'linksearch-text'  => '«*.wikipedia.org» atawına uqsastı bädel nışandardı qoldanwğa boladı. ',
	'linksearch-line'  => '$2 degennen $1 siltegen',
	'linksearch-error' => 'Bädel nışandar tek server jaýı atawınıñ bastawında bolwı mümkin.',
);
$wgLinkSearchMessages['kk-cn'] = array(
	'linksearch'       => 'ەرەنسٸلتەمەلەرٸن ٸزدەۋ',
	'linksearch-pat'   => 'ٸزدەۋ شارتى:',
	'linksearch-ns'    => 'ەسٸم اياسى:',
	'linksearch-ok'    => 'ٸزدەۋ',
	'linksearch-text'  => '«*.wikipedia.org» اتاۋىنا ۇقساستى بٵدەل نىشانداردى قولدانۋعا بولادى. ',
	'linksearch-line'  => '$2 دەگەننەن $1 سٸلتەگەن',
	'linksearch-error' => 'بٵدەل نىشاندار تەك سەرۆەر جايى اتاۋىنىڭ باستاۋىندا بولۋى مٷمكٸن.',
);
$wgLinkSearchMessages['kk'] = $wgLinkSearchMessages['kk-kz'];
$wgLinkSearchMessages['nl'] = array(
	'linksearch'       => 'Zoek externe links',
	'linksearch-text'  => 'Wildcards zoals "*.wikipedia.org" of "*.org" zijn toegestaan.',
	'linksearch-line'  => '$1 gelinkt vanaf $2',
	'linksearch-error' => 'Wildcards zijn alleen toegestaan aan het begin van een hostnaam.'
);
$wgLinkSearchMessages['pt'] = array(
	'linksearch'       => 'Procurar por links da web',
	'linksearch-pat'   => 'Padrão de procura:',
	'linksearch-ns'    => 'Espaço nominal:',
	'linksearch-ok'    => 'Pesquisar',
	'linksearch-text'  => 'É possível utilizar "caracteres mágicos" como em "*.wikipedia.org".<br />Protocolos suportados: <tt>$1</tt>',
	'linksearch-line'  => '$1 está lincado em $2',
	'linksearch-error' => '"Caracteres mágicos" (wildcards) podem ser utilizados apenas no início do endereço.',
);
// Brazillian portuguese inherits portuguese.
$wgLinkSearchMessages['pt-br'] = $wgLinkSearchMessages['pt'];

$wgLinkSearchMessages['fr'] = array(
	'linksearch'	   => 'Rechercher des liens internet',
	'linksearch-text'  => 'Cette page spéciale permet de rechercher les pages dans lesquelles un lien externe apparaît.<br />Des caractères « jokers » peuvent être utilisés, par exemple "*.wikipedia.org".',
	'linksearch-line'  => '$1 avec un lien à partir de $2',
	'linksearch-error' => 'Les caractères « jokers » ne peuvent être utilisés qu’au début du nom de domaine.'
);
$wgLinkSearchMessages['ru'] = array(
	'linksearch'       => 'Поиск внешних ссылок',
	'linksearch-text'  => 'Можно использовать подстановочные символы, например: «*.wikipedia.org».',
	'linksearch-line'  => 'Из $2 ссылка на $1',
	'linksearch-error' => 'Подстановочные символы могут использоваться только в начале адресов.',
);
$wgLinkSearchMessages['sk'] = array(
	'linksearch'       => 'Hľadať webové odkazy',
	'linksearch-text'  => 'Je možné použiť zástupné znaky ako "*.wikipedia.org".',
	'linksearch-line'  => 'Na $1 odkazuje $2',
	'linksearch-error' => 'Zástupné znaky je možné použiť iba na začiatku názvu domény.',
);
$wgLinkSearchMessages['sr'] = array(
	'linksearch'	   => 'Претрага интернет веза',
	'linksearch-text'  => 'Џокери као што су "*.wikipedia.org" могу да се користе.',
	'linksearch-line'  => '$1 повезана са $2',
	'linksearch-error' => 'Џокери могу да се појављују само на почетку домена.'
);
$wgLinkSearchMessages['zh-cn'] = array(
	'linksearch'       => '搜索网页链接',
    'linksearch-pat'   => '搜索网址:',
    'linksearch-ns'    => '名字空间:',
    'linksearch-ok'    => '搜索',
	'linksearch-text'  => '可以使用类似"*.wikipedia.org"的通配符。',
	'linksearch-line'  => '$1 链自 $2',
	'linksearch-error' => '通配符仅可在主机名称的开头使用。',
);
$wgLinkSearchMessages['zh-tw'] = array(
	'linksearch'       => '搜尋網頁連結',
    'linksearch-pat'   => '搜尋網址:',
    'linksearch-ns'    => '名稱空間:',
    'linksearch-ok'    => '搜尋',
	'linksearch-text'  => '可以使用類似"*.wikipedia.org"的萬用字元。',
	'linksearch-line'  => '$1 連自 $2',
	'linksearch-error' => '萬用字元僅可在主機名稱的開頭使用。',
);
$wgLinkSearchMessages['zh-yue'] = array(
	'linksearch'       => '搜尋網頁連結',
	'linksearch-text'  => '可以用類似"*.wikipedia.org"嘅萬用字元。',
	'linksearch-line'  => '$1 連自 $2',
	'linksearch-error' => '萬用字元只可以響主機名嘅開頭度用。',
);
$wgLinkSearchMessages['zh-hk'] = $wgLinkSearchMessages['zh-tw'];
$wgLinkSearchMessages['zh-sg'] = $wgLinkSearchMessages['zh-cn'];
?>
