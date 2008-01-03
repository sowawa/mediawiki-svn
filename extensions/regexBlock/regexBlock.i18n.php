<?php
/**
 * Internationalisation file for extension regexBlock.
 *
 * @addtogroup Extensions
*/

$messages = array();

$messages ['en'] = array(
	'regexblock' => 'RegexBlock',
	'regexblock-page-title' => 'Regular Expression Name Block',
	'regexblockstats' => 'Regex Block Statistics',
	'regexblock-reason-ip' => 'This IP address is prevented from editing due to vandalism or other disruption by you or by someone who shares your IP address. If you believe this is in error, please $1' ,
	'regexblock-reason-name' => 'This username is prevented from editing due to vandalism or other disruption. If you believe this is in error, please $1',
	'regexblock-reason-regex' => 'This username is prevented from editing due to vandalism or other disruption by a user with a similar name. Please create an alternate user name or $1 about the problem',
	'regexblock-help' => 'Use the form below to block write access from a specific IP address or username. This should be done only only to prevent vandalism, and in accordance with policy. \'\'This page will allow you to block even non-existing users, and will also block users with names similar to given, i.e. « Test » will be blocked along with « Test 2 » etc. You can also block full IP addresses, meaning that no one logging from them will be able to edit pages. Note: partial IP addresses will be treated by usernames in determining blocking.  If no reason is specified, a default generic reason will be used.\'\'',
	'regexblock-page-title-1' => 'Block address using regular expressions',
	'regexblock-unblock-success' => 'Unblock succeeded',
	'regexblock-unblock-log' => 'User name or IP address \'\'\'$1\'\'\' has been unblocked.',
	'regexblock-unblock-error' => 'Error unblocking $1. Probably there is no such user.',
	'regexblock-form-username' => 'IP Adress or username:',
	'regexblock-form-reason' => 'Reason:',
	'regexblock-form-expiry' => 'Expiry:&#160;',
	'regexblock-form-match' => 'Exact match',
	'regexblock-form-account-block' => 'Block creation of new accounts',
	'regexblock-form-submit' => 'Block&nbsp;this&nbsp;User',
	'regexblock-block-log' => 'User name or IP address \'\'\'$1\'\'\' has been blocked.',
	'regexblock-block-success' => 'Block succedeed',
	'regexblock-form-submit-empty' => 'Give a user name or an IP address to block.',
	'regexblock-form-submit-regex' => 'Invalid regular expression.',
	'regexblock-form-submit-expiry' => 'Please specify an expiration period.',
	'regexblock-already-blocked' => '$1 is already blocked.',
	'regexblock-stats-title' => 'Regex Block Stats',
	'regexblock-stats-username' => 'For $1',
	'regexblock-stats-times' => 'was blocked on',
	'regexblock-stats-logging' => 'logging from address',
	'regexblock-currently-blocked' => 'Currently blocked addresses:',
	'regexblock-view-blocked' => 'View blocked by:',
	'regexblock-view-all' => 'All',
	'regexblock-view-go' => 'Go',
	'regexblock-view-match' => '(exact match)',
	'regexblock-view-regex' => '(regex match)',
	'regexblock-view-account' => '(account creation block)',
	'regexblock-view-reason' => 'reason: $1',
	'regexblock-view-reason-default' => 'generic reason',
	'regexblock-view-block-infinite' => 'permanent block',
	'regexblock-view-block-temporary' => 'expires on ',
	'regexblock-view-block-expired' => 'EXPIRED on ',
	'regexblock-view-block-by' => 'blocked by ',
	'regexblock-view-block-unblock' => 'unblock',
	'regexblock-view-stats' => '(stats)',
	'regexblock-view-empty' => 'The list of blocked names and addresses is empty.',
	'regexblock-view-time' => 'on $1',
);

$messages['ar'] = array(
	'regexblock' => 'منع ريجيكس',
	'regexblock-page-title' => 'منع الاسم بواسطة تعبير منتظم',
	'regexblockstats' => 'إحصاءات منع الريجيكس',
	'regexblock-reason-ip' => 'عنوان الأيبي هذا ممنوع نتيجة للتخريب أو إساءة أخرى بواسطتك أو بواسطة شخص يشارك في عنوان الأيبي الخاص بك. لو كنت تعتقد أن هذا خطأ، من فضلك $1',
	'regexblock-reason-name' => 'اسم المستخدم هذا ممنوع من التحرير نتيجة للتخريب أو إساءة أخرى. لو كنت تعتقد أن هذا خطأ، من فضلك $1',
	'regexblock-reason-regex' => 'اسم المستخدم هذا ممنوع من التحرير نتيجة للتخريب أو إساءة أخرى بواسطة مستخدم باسم مشابه. من فضلك أنشيء اسم مستخدم بديل أو $1 حول المشكلة',
	'regexblock-help' => 'استخدم الاستمارة بالأسفل لمنع التحرير من عنوان أيبي أو اسم مستخدم محدد. هذا ينبغي أن يتم فقط لمنع التخريب، وبالتوافق مع السياسة. \'\'هذه الصفحة ستسمح لك بمنع حتى المستخدمين غير الموجودين، وستمنع أيضا المستخدمين بأسماء مشابهة للمعطاة،أي أن « Test » سيتم منعها بالإضافة إلى « Test 2 »إلى آخره. يمكنك أيضا منع عناوين أيبي كاملة، مما يعني أنه لا أحد مسجلا للدخول منها سيمكنه تعديل الصفحات. ملاحظة: عناوين الأيبي الجزئية سيتم معاملتها بواسطة أسماء مستخدمين في تحديد المنع.  لو لم يتم تحديد سبب، سيتم استخدام سبب افتراضي تلقائي.\'\'',
	'regexblock-page-title-1' => 'منع عنوان باستخدام تعبيرات منتظمة',
	'regexblock-unblock-success' => 'رفع المنع نجح',
	'regexblock-unblock-log' => 'اسم المستخدم أو عنوان الأيبي \'\'\'$1\'\'\' تم رفع المنع عنه.',
	'regexblock-unblock-error' => 'خطأ أثناء رفع المنع عن $1. على الأرجح لا يوجد مستخدم بهذا الاسم.',
	'regexblock-form-username' => 'عنوان الأيبي أو اسم المستخدم:',
	'regexblock-form-reason' => 'السبب:',
	'regexblock-form-expiry' => 'الانتهاء:&#160;',
	'regexblock-form-match' => 'تطابق تام',
	'regexblock-form-account-block' => 'منع إنشاء الحسابات الجديدة',
	'regexblock-form-submit' => 'منع&nbsp;هذا&nbsp;المستخدم',
	'regexblock-block-log' => 'اسم المستخدم أو عنوان الأيبي \'\'\'$1\'\'\' تم منعه.',
	'regexblock-block-success' => 'المنع نجح',
	'regexblock-form-submit-empty' => 'أعط اسم مستخدم أو عنوان أيبي للمنع.',
	'regexblock-form-submit-regex' => 'تعبير منتظم غير صحيح.',
	'regexblock-form-submit-expiry' => 'من فضلك حدد تاريخ انتهاء.',
	'regexblock-already-blocked' => '$1 ممنوع بالفعل.',
	'regexblock-stats-title' => 'إحصاءات منع الريجيكس',
	'regexblock-stats-username' => 'ل$1',
	'regexblock-stats-times' => 'تم منعه في',
	'regexblock-stats-logging' => 'دخول من العنوان',
	'regexblock-currently-blocked' => 'العناوين الممنوعة حاليا:',
	'regexblock-view-blocked' => 'عرض الممنوع بواسطة:',
	'regexblock-view-all' => 'الكل',
	'regexblock-view-go' => 'اذهب',
	'regexblock-view-match' => '(تطابق تام)',
	'regexblock-view-regex' => '(تطابق ريجيكس)',
	'regexblock-view-account' => '(منع إنشاء حساب)',
	'regexblock-view-reason' => 'السبب: $1',
	'regexblock-view-reason-default' => 'سبب تلقائي',
	'regexblock-view-block-infinite' => 'منع دائم',
	'regexblock-view-block-temporary' => 'ينتهي في',
	'regexblock-view-block-expired' => 'انتهى في',
	'regexblock-view-block-by' => 'ممنوع بواسطة',
	'regexblock-view-block-unblock' => 'رفع المنع',
	'regexblock-view-stats' => '(إحصاءات)',
	'regexblock-view-empty' => 'قائمة الأسماء والعناوين الممنوعة فارغة.',
	'regexblock-view-time' => 'في $1',
);

/** Bulgarian (Български)
 * @author DCLXVI
 */
$messages['bg'] = array(
	'regexblock-form-username'        => 'IP адрес или потребителско име:',
	'regexblock-form-reason'          => 'Причина:',
	'regexblock-form-match'           => 'Пълно съвпадение',
	'regexblock-stats-username'       => 'За $1',
	'regexblock-currently-blocked'    => 'Текущо блокирани адреси:',
	'regexblock-view-all'             => 'Всички',
	'regexblock-view-reason'          => 'причина: $1',
	'regexblock-view-block-temporary' => 'изтича на',
	'regexblock-view-block-by'        => 'блокиран от',
	'regexblock-view-stats'           => '(статистика)',
);

/** Greek (Ελληνικά)
 * @author Consta
 */
$messages['el'] = array(
	'regexblock-form-reason'    => 'Λόγος:',
	'regexblock-stats-username' => 'Για $1',
	'regexblock-view-reason'    => 'Λόγος: $1',
);

$messages ['fr'] = array(
	'regexblock' => 'Expressions régulières pour bloquer un utilisateur ou une IP',
	'regexblock-page-title' => 'Blocage d’un nom par une expression régulière',
	'regexblockstats' => 'Statistiques sur les blocages par expressions régulières',
	'regexblock-reason-ip' => 'Cette adresse IP est écartée de toute édition pour cause de vandalisme ou autres faits analogues par vous ou quelqu’un d’autre partageant votre adresse IP. Si vous êtes persuadé qu’il s’agit d’une erreur, $1',
	'regexblock-reason-name' => 'Cet utilisateur est écarté de toute édition pour cause de vandalisme ou autres faits analogues. Si vous êtes persuadé qu’il s’agit d’une erreur, $1.',
	'regexblock-reason-regex' => 'Cet utilisateur est écarté de toute édition pour cause de vandalisme ou autres faits analogues par un utilisateur ayant un nom similaire. Veuillez créer un autre compte ou $1 pour signaler le problème.',
	'regexblock-help' => 'Utilisez le formulaire ci-dessous pour bloquer l’accès en écriture une adresse IP ou un nom d’utilisateur. Ceci doit être fait uniquement pour éviter tout vandalisme et conformément aux règles prescrites sur le projet. \'\'Cette page vous autorise même à bloquer des utilisateurs non enregistrés et permet aussi de bloquer des utilisateur présentant des noms similaires. Par exemple, « Test » sera bloquée en même temps que « Test 2 » etc. Vous pouvez aussi bloquer des adresses IP entières, ce qui signifie que personne travaillant depuis elles ne pourra éditer des pages. Note : des adresses IP partielles seront considérées comme des noms d’utilisateur lors du blocage. Si aucun motif n’est indiqué en commentaire, un motif par défaut sera indiqué.\'\'',
	'regexblock-page-title-1' => 'Blocage d’une adresse utilisant une expression régulière',
	'regexblock-unblock-success' =>'Le déblocage a réussi',
	'regexblock-unblock-log' => 'L’utilisateur ou l’adresse IP \'\'\'$1\'\'\' a été débloqué.',
	'regexblock-unblock-error' => 'Erreur de déblocage de $1. L’utilisateur n’existe probablemement pas.',
	'regexblock-form-username' => 'Adresse IP ou Utilisateur :',
	'regexblock-form-reason' => 'Motif :',
	'regexblock-form-expiry' => 'Expiration :&#160;',
	'regexblock-form-match' => 'Terme exact',
	'regexblock-form-account-block' => 'Interdire la création d’un nouveau compte.',
	'regexblock-form-submit' => 'Bloquer&nbsp;cet&nbsp;Utilisateur',
	'regexblock-block-log' => 'L’Utilisateur ou l’adresse IP \'\'\'$1\'\'\' a été bloqué.',
	'regexblock-block-success' => 'Le blocage a réussi',
	'regexblock-form-submit-empty' => 'Indiquez un nom d’utilisateur ou une adresse IP à bloquer.',
	'regexblock-form-submit-regex' => 'Expression régulière incorrecte.',
	'regexblock-form-submit-expiry' => 'Précisez une période d’expiration.',
	'regexblock-already-blocked' => '$1 est déjà bloqué.',
	'regexblock-stats-title' => 'Statistiques des blocages par expressions régulières',
	'regexblock-stats-username' => 'Pour $1',
	'regexblock-stats-times' => 'a été bloqué le',
	'regexblock-stats-logging' => 'enregistré depuis l’adresse',
	'regexblock-currently-blocked' => 'Adresses actuellement bloquées :',
	'regexblock-view-blocked' => 'Voir les blocages par :',
	'regexblock-view-all' => 'Tous',
	'regexblock-view-go' => 'Lancer',
	'regexblock-view-match' => '(terme exact)',
	'regexblock-view-regex' => '(expression régulière)',
	'regexblock-view-account' => '(création des comptes bloquée)',
	'regexblock-view-reason' => 'motif : $1',
	'regexblock-view-reason-default' => 'aucun motif indiqué',
	'regexblock-view-block-infinite' => 'blocage permanent',
	'regexblock-view-block-temporary' => 'expire le ',
	'regexblock-view-block-expired' => 'EXPIRÉ le ',
	'regexblock-view-block-by' => 'bloqué par ',
	'regexblock-view-block-unblock' => 'débloquer',
	'regexblock-view-stats' => '(statistiques)',
	'regexblock-view-empty' => 'La liste des utilisateurs et des adresses IP bloqués est vide.',
	'regexblock-view-time' => 'le $1',
);

/** Galician (Galego)
 * @author Alma
 * @author Xosé
 */
$messages['gl'] = array(
	'regexblock-reason-ip'            => 'A este enderezo IP estalle prohibido editar debido a vandalismo ou outras actividades negativas realizadas por vostede ou por alguén que comparte o seu enderezo IP. Se pensa que se trata dun erro, $1',
	'regexblock-reason-name'          => 'A este nome de usuario estalle prohibido editar debido a vandalismo ou outras actividades negativas. Se pensa que se trata dun erro, $1',
	'regexblock-reason-regex'         => 'A este nome de usuario prohíbeselle editar debido a vandalismo ou outras actividades negativas por parte dun usuario cun nome semellante. Cree un nome de usuario diferente ou $1 sobre o problema',
	'regexblock-help'                 => "Use o formulario seguinte para bloquear o acceso de escritura desde un determinado enderezo IP ou nome de usuario. Esto debería facerse só para previr vandalismo, e de conformidade coa política. \"Esta páxina lle permitirá bloquear incluso os non usuarios existentes, e tamén cos nomes dos usuarios bloqueados similares aos dados, é dicir,«Test» se bloqueará xunto con « Test 2 », etc. Tamén pode bloquear enderezos IP completos, no sentido de que ninguén rexistrado nos mesmos será capaz de editar páxinas. Nota: os enderezos IP parciais serán tratados polos nomes de usuarios na determinación do bloqueo. Se non se especifica a razón, será usado por defecto un motivo xenérico.''",
	'regexblock-page-title-1'         => 'Bloquear enderezos usando expresións regulares',
	'regexblock-unblock-success'      => 'O desbloqueo foi un éxito',
	'regexblock-unblock-log'          => "O nome de Usuario ou o enderezo IP '''$1''' foi desbloqueado.",
	'regexblock-unblock-error'        => 'Erro desbloqueando $1. Probabelmente non existe tal usuario.',
	'regexblock-form-username'        => 'Enderezo IP ou nome de usuario:',
	'regexblock-form-reason'          => 'Razón:',
	'regexblock-form-expiry'          => 'Expiración:&#160;',
	'regexblock-form-match'           => 'Procura exacta',
	'regexblock-form-account-block'   => 'Bloqueada a creación de novas contas',
	'regexblock-form-submit'          => 'Bloqueado&nbsp;este&nbsp;Usuario',
	'regexblock-block-log'            => "O nome de usuario ou o enderezo IP '''$1''' foi bloqueado.",
	'regexblock-block-success'        => 'Bloqueo con éxito',
	'regexblock-form-submit-empty'    => 'Dar un nome de usuario ou un enderezo IP para bloquear.',
	'regexblock-form-submit-regex'    => 'Expresión regular non válida.',
	'regexblock-form-submit-expiry'   => 'Especifique un período de expiración.',
	'regexblock-already-blocked'      => '$1 está aínda bloqueado.',
	'regexblock-stats-username'       => 'Para $1',
	'regexblock-stats-times'          => 'foi bloqueado en',
	'regexblock-stats-logging'        => 'rexistrarse desde o enderezo',
	'regexblock-currently-blocked'    => 'Enderezos actualmente bloqueados:',
	'regexblock-view-blocked'         => 'Ver bloqueado por:',
	'regexblock-view-all'             => 'Todo',
	'regexblock-view-go'              => 'Adiante',
	'regexblock-view-match'           => '(procura exacta)',
	'regexblock-view-account'         => '(bloqueo de creación de contas)',
	'regexblock-view-reason'          => 'razón: $1',
	'regexblock-view-reason-default'  => 'razón xenérica',
	'regexblock-view-block-infinite'  => 'bloqueo permanente',
	'regexblock-view-block-temporary' => 'expira o',
	'regexblock-view-block-expired'   => 'EXPIRADO o',
	'regexblock-view-block-by'        => 'bloqueado por',
	'regexblock-view-block-unblock'   => 'desbloquear',
	'regexblock-view-stats'           => '(estatísticas)',
	'regexblock-view-empty'           => 'A listaxe dos nomes e enderezos bloqueados está baleira.',
	'regexblock-view-time'            => 'en $1',
);

/** Croatian (Hrvatski)
 * @author Dnik
 * @author SpeedyGonsales
 */
$messages['hr'] = array(
	'regexblock'                      => 'Blokiranje pomoću regularnih izraza',
	'regexblock-page-title'           => 'Blokiranje pomoću regularnih izraza',
	'regexblockstats'                 => 'Statistika blokiranja regularnim izrazima',
	'regexblock-reason-ip'            => 'Ova IP adresa je blokirana (tj. nemoguće je uređivati stranice) zbog vandalizma ili nekog drugog vašeg prekršaja (ili nekog s kim dijelite IP adresu). Ukoliko mislite da je posrijedi greška, molimo $1',
	'regexblock-reason-name'          => 'Ovo suradničko ime je blokirano (tj. spriječeno mu je uređivanje članaka) zbog vandalizma ili nekog drugog prekršaja. Ukoliko mislite da se radi o grešci, molimo $1',
	'regexblock-reason-regex'         => 'Ovo suradničko ime je blokirano (tj. spriječeno mu je uređivanje članaka) zbog vandalizma ili nekog drugog prekršaja suradnika s istim (ili sličnem) imenom. Ukoliko mislite da se radi o grešci, molimo $1',
	'regexblock-help'                 => "Rabite donju formu za blokiranje određenih IP adresa ili suradnika. TO treba činiti samo radi sprječavanja vandalizama, u skladu s pravilima.

''Ova stranica omogućava vam blokiranje suradničkih imena prema uzorku (postojećih i novih), npr. ako blokirate « Test 2», blokirat ćete i « Test » itd. Možete također blokirati IP adrese, što znači da nitko tko se prijavi s njih neće moći uređivati. Napomena: djelomične IP adrese bit će analizirane prema suradničkim imenima u određivanju trajanja bloka. Ukoliko razlog nije dan, bit će navedeno generičko objašnjenje.''",
	'regexblock-page-title-1'         => 'Blokiraj adresu koristeći regularni izraz',
	'regexblock-unblock-success'      => 'Deblokiranje uspjelo',
	'regexblock-unblock-log'          => "Suradnik ili IP adresa '''$1''' je deblokiran.",
	'regexblock-unblock-error'        => 'Greška prilikom deblokiranja $1. Taj suradnik vjerojatno ne postoji.',
	'regexblock-form-username'        => 'IP-adresa ili ime suradnika:',
	'regexblock-form-reason'          => 'Razlog:',
	'regexblock-form-expiry'          => 'Istek bloka:&#160;',
	'regexblock-form-match'           => 'Točno podudaranje',
	'regexblock-form-account-block'   => 'Blokiraj stvaranje novih računa',
	'regexblock-form-submit'          => 'Blokiraj&nbsp;ovog&nbsp;suradnika',
	'regexblock-block-log'            => "Suradnik ili IP-adresa '''$1''' su blokirani.",
	'regexblock-block-success'        => 'Blokiranje uspjelo',
	'regexblock-form-submit-empty'    => 'Unesite ime suradnika ili IP-adresu za blokiranje.',
	'regexblock-form-submit-regex'    => 'Pogrešan regularni izraz.',
	'regexblock-form-submit-expiry'   => 'Molimo odredite razdoblje isteka.',
	'regexblock-already-blocked'      => '$1 je već blokiran.',
	'regexblock-stats-title'          => 'Statistika blokiranja reg. izrazima',
	'regexblock-stats-username'       => 'Za $1',
	'regexblock-stats-times'          => 'je blokiran u',
	'regexblock-stats-logging'        => 'prijava s adrese',
	'regexblock-currently-blocked'    => 'Trenutno blokirane adrese:',
	'regexblock-view-blocked'         => 'Pregled po onom tko je blokirao:',
	'regexblock-view-all'             => 'Svi',
	'regexblock-view-go'              => 'Kreni',
	'regexblock-view-match'           => '(točno podudaranje)',
	'regexblock-view-regex'           => '(podudaranje reg. izrazom)',
	'regexblock-view-account'         => '(blokiranje otvaranja računa)',
	'regexblock-view-reason'          => 'razlog: $1',
	'regexblock-view-reason-default'  => 'uobičajeni razlog',
	'regexblock-view-block-infinite'  => 'trajna blokada',
	'regexblock-view-block-temporary' => 'ističe u',
	'regexblock-view-block-expired'   => 'ISTEKLO u',
	'regexblock-view-block-by'        => 'blokiran od',
	'regexblock-view-block-unblock'   => 'deblokiraj',
	'regexblock-view-stats'           => '(statistika)',
	'regexblock-view-empty'           => 'Popis blokiranih imena i adresa je prazan.',
	'regexblock-view-time'            => 'u $1',
);

$messages['hsb'] = array(
	'regexblock-page-title' => 'Blokowanje mjenow regularnych wurazow',
	'regexblockstats' => 'Regex Block Statistika',
	'regexblock-reason-ip' => 'Tuta IP-adresa so dla wandalizma abo mylenje přez tebje abo někoho druheho, kiž IP-adresu z tobu dźěli, za wobdźěłowanje zawěra. Jeli mysliš, zo to je zmylk, prošu $1',
	'regexblock-reason-name' => 'Tute wužiwarske mjeno so dla wandalizma abo druheho mylenja za wobdźěłowanje zawěra. Jerli mysliš, zo to je zmylk, prošu $1',
	'regexblock-reason-regex' => 'Tute wužiwarske mjeno so dla wandalizma abo druheho mylenja přez wužiwarja z podobnym mjenom zawěra. Prošu wutwor druhe wužiwarske mjeno abo $1 wo tutym problemje',
	'regexblock-help' => 'Wužij formular deleka, zo by pisanski přistup ze specifiskeje adresy abo wužiwarskeho mjena blokował. To měło so jenož činić, zo by wandalizmej zadźěwało a wotpowědujo prawidłam. \'\'Tuta strona budźe će dowoleć, samo njeeksistowacych wužiwarjow blokować a budźe tež wužiwarjow z mjenom, kotrež je datemu podobne, blokować, t.r. "test" budźe so runje tak blokować kaž "test 2" atd. Móžeš dospołne OP-adresy blokować, zo by něchtó, kiž so z nich přizjewja, strony wobdźěłać móhł. Kedźbu: dźělne IP-adresy so přez wužiwarske mjeno wužiwaja, zo by blokowanje postajiło. Jeli přičina njeje podata, budźe so powšitkowna přičina wužiwać.\'\'',
	'regexblock-page-title-1' => 'Adresu z pomocu regularnych wurazow blokować',
	'regexblock-unblock-success' => 'Wotblokowanje wuspěšne',
	'regexblock-unblock-log' => 'Wužiwarske mjeno abo IP-adresa \'\'\'$1\'\'\' wotblokowana.',
	'regexblock-unblock-error' => 'Zmylk při wotblokowanju $1. Najskerje tajki wužiwar njeje.',
	'regexblock-form-username' => 'IP-adresa abo wužiwarske mjeno:',
	'regexblock-form-reason' => 'Přičina:',
	'regexblock-form-expiry' => 'Spadnjenje:&#160;',
	'regexblock-form-match' => 'Eksaktny wotpowědnik',
	'regexblock-form-account-block' => 'Wutworjenje nowych kontow blokować',
	'regexblock-form-submit' => 'Tutoho&nbsp;wužiwarja&nbsp;blokować',
	'regexblock-block-log' => 'Wužiwarske mjeno abo IP-adresa \'\'\'$1\'\'\' je so blokowało/blokowała.',
	'regexblock-block-success' => 'Blokowanje wuspěšne',
	'regexblock-form-submit-empty' => 'Podaj wužiwarske mjeno abo IP-adresu za blokowanje.',
	'regexblock-form-submit-regex' => 'Njepłaćiwy regularny wuraz.',
	'regexblock-form-submit-expiry' => 'Podaj prošu periodu spadnjenja.',
	'regexblock-already-blocked' => '$1 je hižo zablokowany.',
	'regexblock-stats-title' => 'Regex Block Statistiske podaća',
	'regexblock-stats-username' => 'Za $1',
	'regexblock-stats-times' => 'bu blokowane',
	'regexblock-stats-logging' => 'protokolowanje z adresy',
	'regexblock-currently-blocked' => 'Tuchwilu zablokowane adresy:',
	'regexblock-view-blocked' => 'Wobhladanje zablokowane wot:',
	'regexblock-view-all' => 'Wšě',
	'regexblock-view-go' => 'Dźi',
	'regexblock-view-match' => '(eksaktny wotpowědnik)',
	'regexblock-view-regex' => '(regularny wuraz wotpowědnik)',
	'regexblock-view-account' => '(wutworjenje konta blokować)',
	'regexblock-view-reason' => 'přičina: $1',
	'regexblock-view-reason-default' => 'powšitkowna přičina',
	'regexblock-view-block-infinite' => 'trajne blokowanje',
	'regexblock-view-block-temporary' => 'spadnje',
	'regexblock-view-block-expired' => 'SPADNJENY',
	'regexblock-view-block-by' => 'zablokowany wot',
	'regexblock-view-block-unblock' => 'wotblokować',
	'regexblock-view-stats' => '(statistiske podaća)',
	'regexblock-view-empty' => 'Lisćina zablokowanych mjenow a adresow je prózdna.',
	'regexblock-view-time' => '$1',
);

/** Dutch (Nederlands)
 * @author SPQRobin
 * @author Siebrand
 */
$messages['nl'] = array(
	'regexblock'                      => 'RegexBlokkeren',
	'regexblock-page-title'           => 'Namen blokkeren met reguliere uitdrukkingen',
	'regexblockstats'                 => 'Statistieken van regex-blokkeren',
	'regexblock-reason-ip'            => 'Dit IP-adres is door u of door iemand met hetzelfde IP-adres geblokkeerd van bewerken door vandalisme of een andere reden. Als u gelooft dat dit een fout is, gelieve $1',
	'regexblock-reason-name'          => 'Deze gebruikersnaam is geblokkeerd van bewerken door vandalisme of een andere reden. Als u gelooft dat dit een fout is, gelieve $1',
	'regexblock-reason-regex'         => 'Deze gebruikersnaam is door een gebruiker met dezelfde naam geblokkeerd van bewerken door vandalisme of een andere reden. Gelieve een andere gebruikersnaam te kiezen of $1 over het probleem',
	'regexblock-help'                 => "Gebruik het onderstaande formulier om schrijftoegang voor een IP-adres of gebruiker te ontzeggen. Dit hoort eigenlijk alleen te gebeuren om vandalisme te voorkomen, en dient in overeenstemming te zijn met het beleid. ''Deze pagina staat u zelf toe om gebruikers die nog niet bestaan te blokkeren. Daarnaast worden ook gebruikers met gelijkende namen geblokkeerd. \"Test\" wordt samen met \"Test 2\", enzovoort geblokkeerd. U kunt ook een IP-adres blokkeren, wat betekent dat niemand van dat IP-adres pagina's kan bewerken. Opmerking: IP-adressen worden behandeld als gebruikersnamen bij het bepalen van blokkades. Als er geen reden is opgegeven, dan wordt er een standaardreden gebruikt.''",
	'regexblock-page-title-1'         => 'IP-adres blokkeren met behulp van reguliere uitdrukkingen',
	'regexblock-unblock-success'      => 'Het deblokkeren is gelukt',
	'regexblock-unblock-log'          => "Gebruikersnaam of IP-adres '''$1''' zijn gedeblokkeerd.",
	'regexblock-unblock-error'        => 'Een fout bij het deblokkeren van $1. Waarschijnlijk bestaat er geen gebruiker met die naam.',
	'regexblock-form-username'        => 'IP-adres of gebruikersnaam:',
	'regexblock-form-reason'          => 'Reden:',
	'regexblock-form-expiry'          => 'Verloopt:&#160;',
	'regexblock-form-match'           => 'Voldoet precies',
	'regexblock-form-account-block'   => 'Het aanmaken van nieuwe gebruikers blokkeren',
	'regexblock-form-submit'          => 'Deze&nbsp;gebruiker&nbsp;blokkeren',
	'regexblock-block-log'            => "Gebruikersnaam of IP-adres '''$1''' is geblokkeerd.",
	'regexblock-block-success'        => 'Het blokkeren is gelukt',
	'regexblock-form-submit-empty'    => 'Geef een gebruikersnaam of een IP-adres om te blokkeren.',
	'regexblock-form-submit-regex'    => 'Ongeldige reguliere uitdrukking.',
	'regexblock-form-submit-expiry'   => 'Geef alstublieft een verlooptermijn op.',
	'regexblock-already-blocked'      => '$1 is al geblokkeerd.',
	'regexblock-stats-title'          => 'Regex Block statistieken',
	'regexblock-stats-username'       => 'Voor $1',
	'regexblock-stats-times'          => 'is geblokkeerd op',
	'regexblock-stats-logging'        => 'aangemeld van IP-adres',
	'regexblock-currently-blocked'    => 'Op dit moment geblokkeerde IP-adressen:',
	'regexblock-view-blocked'         => 'Toon blokkades door:',
	'regexblock-view-all'             => 'Alles',
	'regexblock-view-go'              => 'Gaan',
	'regexblock-view-match'           => '(voldoet precies)',
	'regexblock-view-regex'           => '(voldoet aan regex)',
	'regexblock-view-account'         => '(blokkade aanmaken gebruikers)',
	'regexblock-view-reason'          => 'reden: $1',
	'regexblock-view-reason-default'  => 'algemene reden',
	'regexblock-view-block-infinite'  => 'permanente blokkade',
	'regexblock-view-block-temporary' => 'verloopt op',
	'regexblock-view-block-expired'   => 'VERLOPEN op',
	'regexblock-view-block-by'        => 'geblokkeerd door',
	'regexblock-view-block-unblock'   => 'deblokkeren',
	'regexblock-view-stats'           => '(statistieken)',
	'regexblock-view-empty'           => 'De lijst van geblokkeerde namen en IP-adressen is leeg.',
	'regexblock-view-time'            => 'op $1',
);

/** Occitan (Occitan)
 * @author Cedric31
 */
$messages['oc'] = array(
	'regexblock'                      => 'Expressions regularas per blocar un utilizaire o una IP',
	'regexblock-page-title'           => 'Blocatge d’un nom per una expression regulara',
	'regexblockstats'                 => 'Estatisticas suls blocatges per expressions regularas',
	'regexblock-reason-ip'            => 'Aquesta adreça IP es apartat de tota edicon per causa de vandalisme o autres faches analògs per vos o qualqu’un d’autre partejant vòstra adreça IP. Se sètz persuadit(-ida) que s’agís d’una error, $1',
	'regexblock-reason-name'          => 'Aqueste utilizaire es apartat de tota edicion per causa de vandalisme o autres faches analògs. Se sètz persuadit(-ida) que s’agís d’una error, $1.',
	'regexblock-reason-regex'         => "Aqueste utilizaire es apartat de tota edicion per causa de vandalisme o autres faches analògs per un utilizaire qu'a un nom similar. Creatz un autre compte o $1 per senhalar lo problèma.",
	'regexblock-help'                 => "Utilizatz lo formulari çaijós per blocar l’accès en escritura una adreça IP o un nom d’utilizaire. Aquò deu èsser fach unicament per evitar tot vandalisme e conformadament a las règlas prescrichas sul projècte. ''Aquesta pagina vos autoriza quitament a blocar d'utilizaires pas enregistrats e permet tanben de blocar d'utilizaires que presentan de noms similars. Per exemple, « Tèst » serà blocada al meteis temps que « Tèst 2 » etc. Tanben podètz blocar d'adreças IP entièras, çò que significa que degun que trabalha pas dempuèi elas poirà pas editar de paginas. Nòta : d'adreças IP parcialas seràn consideradas coma de noms d’utilizaire al moment del blocatge. Se cap de motiu es pas indicat en comentari, un motiu per defaut serà indicat.''",
	'regexblock-page-title-1'         => 'Blocatge d’una adreça utilizant una expression regulara',
	'regexblock-unblock-success'      => 'Lo desblocatge a capitat',
	'regexblock-unblock-log'          => "L’utilizaire o l’adreça IP '''$1''' es estat desblocat.",
	'regexblock-unblock-error'        => 'Error de deblocatge de $1. L’utilizaire existís probablament pas.',
	'regexblock-form-username'        => 'Adreça IP o Utilizaire :',
	'regexblock-form-reason'          => 'Motiu :',
	'regexblock-form-expiry'          => 'Expiracion :&#160;',
	'regexblock-form-match'           => 'Tèrme exacte',
	'regexblock-form-account-block'   => 'Interdire la creacion d’un compte novèl.',
	'regexblock-form-submit'          => 'Blocar&nbsp;aqueste&nbsp;Utilizaire',
	'regexblock-block-log'            => "L’Utilizaire o l’adreça IP '''$1''' es estat blocat.",
	'regexblock-block-success'        => 'Lo blocatge a capitat',
	'regexblock-form-submit-empty'    => 'Indicatz un nom d’utilizaire o una adreça IP de blocar.',
	'regexblock-form-submit-regex'    => 'Expression regulara incorrècta.',
	'regexblock-form-submit-expiry'   => 'Precisatz un periòde d’expiracion.',
	'regexblock-already-blocked'      => '$1 ja es blocat.',
	'regexblock-stats-title'          => 'Estatisticas dels blocatges per expressions regularas',
	'regexblock-stats-username'       => 'Per $1',
	'regexblock-stats-times'          => 'es estat blocat lo',
	'regexblock-stats-logging'        => 'enregistrat dempuèi l’adreça',
	'regexblock-currently-blocked'    => 'Adreças actualament blocadas :',
	'regexblock-view-blocked'         => 'Veire los blocatges per :',
	'regexblock-view-all'             => 'Totes',
	'regexblock-view-go'              => 'Amodar',
	'regexblock-view-match'           => '(tèrme exacte)',
	'regexblock-view-regex'           => '(expression regulara)',
	'regexblock-view-account'         => '(creacion dels comptes blocada)',
	'regexblock-view-reason'          => 'motiu : $1',
	'regexblock-view-reason-default'  => 'cap de motiu indicat',
	'regexblock-view-block-infinite'  => 'blocatge permanent',
	'regexblock-view-block-temporary' => 'expira lo',
	'regexblock-view-block-expired'   => 'EXPIRAT lo',
	'regexblock-view-block-by'        => 'blocat per',
	'regexblock-view-block-unblock'   => 'desblocar',
	'regexblock-view-stats'           => '(estatisticas)',
	'regexblock-view-empty'           => 'La lista dels utilizaires e de las adreças IP blocats es voida.',
	'regexblock-view-time'            => 'lo $1',
);
