<?php
/**
 * Internationalisation file for extension Maintenance.
 *
 * @addtogroup Extensions
 */

$messages = array();

/** English
 * @author Ryan Schmidt
 */
$messages['en'] = array(
	'maintenance'                       => "Run maintenance scripts",
	'maintenance-desc'                  => '[[Special:Maintenance|Web interface]] for various maintenance scripts',
	'maintenance-backlink'              => "Back to script selection",
	'maintenance-header'                => "Please select a script below to execute.
Descriptions are next to each script",
	'maintenance-changePassword-desc'   => "Change a user's password",
	'maintenance-createAndPromote-desc' => "Create a user and promote to sysop status",
	'maintenance-deleteBatch-desc'      => "Mass-delete pages",
	'maintenance-deleteRevision-desc'   => "Remove revisions from the database",
	'maintenance-initEditCount-desc'    => "Recalculate the edit counts of users",
	'maintenance-initStats-desc'        => "Recalculate site statistics",
	'maintenance-moveBatch-desc'        => "Mass-move pages",
	'maintenance-runJobs-desc'          => "Run jobs in the job queue",
	'maintenance-showJobs-desc'         => "Show a list of jobs pending in the job queue",
	'maintenance-stats-desc'            => "Show Memcached statistics",
	'maintenance-changePassword'        => "Use this form to change a user's password",
	'maintenance-createAndPromote'      => "Use this form to create a new user and promote it to sysop.
Check the bureaucrat box if you wish to promote to Bureaucrat as well",
	'maintenance-deleteBatch'           => "Use this form to mass-delete pages.
Put only one page per line",
	'maintenance-deleteRevision'        => "Use this form to mass-delete revisions.
Put only one revision number per line",
	'maintenance-initEditCount'         => "",
	'maintenance-initStats'             => "Use this form to recalculate site statistics, specifiying if you want to recalculate page views as well",
	'maintenance-moveBatch'             => "Use this form to mass-move pages.
Each line should specify target page and destination page separated by a pipe",
	'maintenance-runJobs'               => "",
	'maintenance-showJobs'              => "",
	'maintenance-stats'                 => "",
	'maintenance-invalidtype'           => "Invalid type!",
	'maintenance-name'                  => "Username",
	'maintenance-password'              => "Password",
	'maintenance-bureaucrat'            => "Promote user to bureaucrat status",
	'maintenance-reason'                => "Reason",
	'maintenance-update'                => "Use UPDATE when updating a table? Unchecked uses DELETE/INSERT instead.",
	'maintenance-noviews'               => "Check this to prevent updating the number of pageviews",
	'maintenance-confirm'               => "Confirm",
	'maintenance-invalidname'           => "Invalid username!",
	'maintenance-success'               => "$1 ran successfully!",
	'maintenance-userexists'            => "User already exists!",
	'maintenance-invalidtitle'          => "Invalid title \"$1\"!",
	'maintenance-titlenoexist'          => "Title specified (\"$1\") does not exist!",
	'maintenance-failed'                => "FAILED",
	'maintenance-deleted'               => "DELETED",
	'maintenance-revdelete'             => "Deleting revisions $1 from wiki $2",
	'maintenance-revnotfound'           => "Revision $1 not found!",
	'maintenance-stats-edits'           => "Number of edits: $1",
	'maintenance-stats-articles'        => "Number of pages in the main namespace: $1",
	'maintenance-stats-pages'           => "Number of pages: $1",
	'maintenance-stats-users'           => "Number of users: $1",
	'maintenance-stats-admins'          => "Number of admins: $1",
	'maintenance-stats-images'          => "Number of files: $1",
	'maintenance-stats-views'           => "Number of pageviews: $1",
	'maintenance-stats-update'          => "Updating database...",
	'maintenance-move'                  => "Moving $1 to $2...",
	'maintenance-movefail'              => "Error encountered while moving: $1.
Aborting move",
	'maintenance-error'                 => "Error: $1",
	'maintenance-memc-fake'             => "You are running FakeMemCachedClient. No statistics can be provided",
	'maintenance-memc-requests'         => "Requests",
	'maintenance-memc-withsession'      => "with session:",
	'maintenance-memc-withoutsession'   => "without session:",
	'maintenance-memc-total'            => "total:",
	'maintenance-memc-parsercache'      => "Parser Cache",
	'maintenance-memc-hits'             => "hits:",
	'maintenance-memc-invalid'          => "invalid:",
	'maintenance-memc-expired'          => "expired:",
	'maintenance-memc-absent'           => "absent:",
	'maintenance-memc-stub'             => "stub threshold:",
	'maintenance-memc-imagecache'       => "Image Cache",
	'maintenance-memc-misses'           => "misses:",
	'maintenance-memc-updates'          => "updates:",
	'maintenance-memc-uncacheable'      => "uncacheable:",
	'maintenance-memc-diffcache'        => "Diff Cache",
);

/** Bulgarian (Български)
 * @author DCLXVI
 */
$messages['bg'] = array(
	'maintenance-desc'                  => '[[Special:Maintenance|Уеб интерфейс]] за различни скриптове за поддръжка',
	'maintenance-changePassword-desc'   => 'Променяне на потребителска парола',
	'maintenance-createAndPromote-desc' => 'Създаване на потребител и даване на администраторски права',
	'maintenance-deleteBatch-desc'      => 'Масово изтриване на страници',
	'maintenance-deleteRevision-desc'   => 'Премахване на версии от базата от данни',
	'maintenance-initStats-desc'        => 'Опресняване на статистиките на сайта',
	'maintenance-moveBatch-desc'        => 'Масово преместване на страници',
	'maintenance-changePassword'        => 'Формулярът по-долу се използва за промяна на паролата на потребител',
	'maintenance-name'                  => 'Потребителско име',
	'maintenance-password'              => 'Парола',
	'maintenance-bureaucrat'            => 'Предоставяне на права на бюрократ',
	'maintenance-reason'                => 'Причина',
	'maintenance-confirm'               => 'Потвърждаване',
	'maintenance-invalidname'           => 'Невалидно потребителско име!',
	'maintenance-userexists'            => 'Този потребител вече съществува!',
	'maintenance-invalidtitle'          => 'Невалидно заглавие „$1“!',
	'maintenance-titlenoexist'          => 'Посоченото заглавие („$1“) не съществува!',
	'maintenance-stats-edits'           => 'Брой редакции: $1',
	'maintenance-stats-articles'        => 'Брой страници в основното именно пространство: $1',
	'maintenance-stats-pages'           => 'Брой страници: $1',
	'maintenance-stats-users'           => 'Брой потребители: $1',
	'maintenance-stats-admins'          => 'Брой администратори: $1',
	'maintenance-stats-images'          => 'Брой файлове: $1',
	'maintenance-stats-views'           => 'Брой прегледи на страниците: $1',
	'maintenance-stats-update'          => 'Обновяване на базата от данни...',
	'maintenance-error'                 => 'Грешка: $1',
	'maintenance-memc-requests'         => 'Заявки',
	'maintenance-memc-total'            => 'общо:',
);

/** Finnish (Suomi)
 * @author Crt
 * @author Nike
 */
$messages['fi'] = array(
	'maintenance-changePassword-desc'   => 'Muuta käyttäjän salasana',
	'maintenance-createAndPromote-desc' => 'Luo käyttäjä ja lisää ylläpitäjäksi',
	'maintenance-deleteBatch-desc'      => 'Massapoista sivuja',
	'maintenance-deleteRevision-desc'   => 'Poista versioita tietokannasta',
	'maintenance-initEditCount-desc'    => 'Laske uudelleen käyttäjien muokkausmäärät',
	'maintenance-initStats-desc'        => 'Laske sivuston tilastot uudelleen',
	'maintenance-moveBatch-desc'        => 'Massasiirrä sivuja',
	'maintenance-runJobs-desc'          => 'Aja työt ohjelmiston ylläpitotyöjonosta',
	'maintenance-changePassword'        => 'Vaihda käyttäjän salasana tällä lomakkeella',
	'maintenance-name'                  => 'Käyttäjätunnus',
	'maintenance-password'              => 'Salasana',
	'maintenance-reason'                => 'Syy',
	'maintenance-confirm'               => 'Vahvista',
	'maintenance-invalidname'           => 'Virheellinen käyttäjätunnus.',
	'maintenance-success'               => '$1 ajettiin onnistuneesti.',
	'maintenance-userexists'            => 'Käyttäjä on jo olemassa.',
	'maintenance-failed'                => 'EPÄONNISTUI',
	'maintenance-deleted'               => 'POISTETTU',
	'maintenance-revnotfound'           => 'Versiota $1 ei löydy.',
	'maintenance-stats-edits'           => 'Muokkauksia yhteensä: $1',
	'maintenance-stats-pages'           => 'Sivuja yhteensä: $1',
	'maintenance-stats-users'           => 'Käyttäjiä yhteensä: $1',
	'maintenance-stats-admins'          => 'Ylläpitäjiä yhteensä: $1',
	'maintenance-stats-images'          => 'Tiedostoja yhteensä: $1',
	'maintenance-stats-views'           => 'Sivuja näytetty yhteensä: $1',
	'maintenance-stats-update'          => 'Päivitetään tietokantaa...',
	'maintenance-move'                  => 'Siirretään $1 nimelle $2...',
	'maintenance-error'                 => 'Virhe: $1',
);

/** French (Français)
 * @author Grondin
 */
$messages['fr'] = array(
	'maintenance'                       => 'Lancer les scripts de maintenance',
	'maintenance-desc'                  => '[[Special:Maintenance|Interface Web]] pour les différents scripts de maintenance',
	'maintenance-backlink'              => 'Retour vers la sélection du script',
	'maintenance-header'                => 'Veuillez sélectionnez, ci-dessous, un script à exécuter.
Les descriptions sont à la suite de chacun de ceux-ci.',
	'maintenance-changePassword-desc'   => 'Cchanger le mot de passe d’un utilisateur',
	'maintenance-createAndPromote-desc' => 'Créer un utilisateur et promouvoir au statut d’administrateur',
	'maintenance-deleteBatch-desc'      => 'Suppression en masse des pages',
	'maintenance-deleteRevision-desc'   => 'Enlever les versions de la base de donnée',
	'maintenance-initEditCount-desc'    => 'Recalculer les compteurs d’éditions des utilisateurs',
	'maintenance-initStats-desc'        => 'Recalculer les statistiques du site',
	'maintenance-moveBatch-desc'        => 'Renommage en masse des pages',
	'maintenance-runJobs-desc'          => 'Lancer les tâches dans la liste de celles à accomplir',
	'maintenance-showJobs-desc'         => 'Affichier une liste des tâches en cours dans la liste de celles à accomplir',
	'maintenance-stats-desc'            => 'Afficher les statistiques de la mémoire-cache',
	'maintenance-changePassword'        => 'Utiliser ce formulaire pour changer le mot de passe d’un utilisateur',
	'maintenance-createAndPromote'      => 'Utiliser ce formulaire pour créer un nouvel utilisateur et pour le promouvoir administrateur.
Cocher la case bureaucrate si vous désirez lui conférer aussi ce statut.',
	'maintenance-deleteBatch'           => 'Utilisez ce formulaire pour supprimer en masse des pages/
Indiquer une seule page par ligne',
	'maintenance-deleteRevision'        => 'Utilisez ce formulaire pour supprimer en masse les versions.
Indiquez une seule version par ligne',
	'maintenance-initStats'             => 'Utilisez ce formulaire pour recalculer les statistiques du site, en indiquant, le cas échéant, si vous désirez le recalcul du nombre de visites par page.',
	'maintenance-moveBatch'             => 'Utilisez ce formulaire pour déplacer en masse les pages.
Chaque ligne devra indiquer la page d’origine et celle de destination lesquelles devront être séparées par un « <nowiki>|</nowiki> »',
	'maintenance-invalidtype'           => 'Type incorrect !',
	'maintenance-name'                  => 'Nom d’utilisateur',
	'maintenance-password'              => 'Mot de passe',
	'maintenance-bureaucrat'            => 'Promouvoir l’utilisateur au statut de bureaucrate',
	'maintenance-reason'                => 'Motif',
	'maintenance-update'                => "Utiliser UPDATE lors de la mise à jour d'une table ? Décochez l'usage DELETE/INSERT au lieu de cela.",
	'maintenance-noviews'               => 'Cocher ceci pour éviter la mise à jour du nombre de visites des pages.',
	'maintenance-confirm'               => 'Confirmer',
	'maintenance-invalidname'           => 'Nom d’utilisateur incorrect !',
	'maintenance-success'               => '$1 a tourné avec succès !',
	'maintenance-userexists'            => 'L’utilisateur existe déjà !',
	'maintenance-invalidtitle'          => 'Titre incorrect « $1 » !',
	'maintenance-titlenoexist'          => 'Le titre indiqué (« $1 ») n’existe pas !',
	'maintenance-failed'                => 'ÉCHEC',
	'maintenance-deleted'               => 'SUPPRIMÉ',
	'maintenance-revdelete'             => 'Suppression des versions $1 depuis le wiki $2',
	'maintenance-revnotfound'           => 'Version $1 introuvable !',
	'maintenance-stats-edits'           => 'Nombre d’éditions : $1',
	'maintenance-stats-articles'        => 'Nombre de pages dans le même espace : $1',
	'maintenance-stats-pages'           => 'Nombre de pages : $1',
	'maintenance-stats-users'           => 'Nombre d’utilisateurs : $1',
	'maintenance-stats-admins'          => 'Nombre d’administrateurs : $1',
	'maintenance-stats-images'          => 'Nombre de fichiers : $1',
	'maintenance-stats-views'           => 'Nombre de pages visitées : $1',
	'maintenance-stats-update'          => 'Mise à jour de la base de donnée…',
	'maintenance-move'                  => 'Déplacement de $1 vers $2…',
	'maintenance-movefail'              => 'Erreur a été relevée lors du renommage : $1.
Arrêt du déplacement.',
	'maintenance-error'                 => 'Erreur : $1',
	'maintenance-memc-fake'             => 'Vous être en train de lancer FakeMemCachedClient. Aucune statistique ne sera fournie.',
	'maintenance-memc-requests'         => 'Requêtes',
	'maintenance-memc-withsession'      => 'avec la session :',
	'maintenance-memc-withoutsession'   => 'sans la session :',
	'maintenance-memc-total'            => 'total :',
	'maintenance-memc-parsercache'      => 'Cache parseur',
	'maintenance-memc-hits'             => 'clics :',
	'maintenance-memc-invalid'          => 'incorrects :',
	'maintenance-memc-expired'          => 'expirés :',
	'maintenance-memc-absent'           => 'absents :',
	'maintenance-memc-stub'             => 'seuil de départ :',
	'maintenance-memc-imagecache'       => 'Cache image',
	'maintenance-memc-misses'           => 'perdus :',
	'maintenance-memc-updates'          => 'mis à jour :',
	'maintenance-memc-uncacheable'      => 'hors cache :',
	'maintenance-memc-diffcache'        => 'Cache des diff',
);

/** Korean (한국어)
 * @author Ficell
 */
$messages['ko'] = array(
	'maintenance-password' => '비밀번호',
);

/** Luxembourgish (Lëtzebuergesch)
 * @author Robby
 */
$messages['lb'] = array(
	'maintenance'                     => 'Maintenance-Skripten ausféieren',
	'maintenance-changePassword-desc' => 'Engem Benotzer säi Passwuert änneren',
	'maintenance-initStats-desc'      => "D'Statistike vum Site neiberechnen",
	'maintenance-showJobs-desc'       => "Weis d'Lësccht vun den Aarbechten déi nach an der ''Queue'' stinn",
	'maintenance-changePassword'      => "Dëse Formulaire benotze fir engem Benotzer säi Passwuert z'änneren",
	'maintenance-name'                => 'Benotzernumm',
	'maintenance-password'            => 'Passwuert',
	'maintenance-bureaucrat'          => 'Engem Benotzer de Bürokrate-Status ginn',
	'maintenance-reason'              => 'Grond',
	'maintenance-confirm'             => 'Confirméieren',
	'maintenance-invalidname'         => 'Ongëltege Benotzernumm!',
	'maintenance-userexists'          => 'De Benotzer gëtt et schonn!',
	'maintenance-invalidtitle'        => 'Ongëltegen Titel "$1"!',
	'maintenance-deleted'             => 'GELÄSCHT',
	'maintenance-revdelete'           => 'Làsche vun de Versioune(n) $1 vun der Wiki $2',
	'maintenance-revnotfound'         => "D'Versioun $1 gouf net fonnt!",
	'maintenance-stats-edits'         => 'Zuel vun den Ännerungen: $1',
	'maintenance-stats-articles'      => 'Zuel vun de Säiten am Haaptnummraum: $1',
	'maintenance-stats-pages'         => 'Zuel vun de Säiten: $1',
	'maintenance-stats-users'         => 'Zuel vun de Benotzer: $1',
	'maintenance-stats-admins'        => 'Zuel vun den Administrateuren: $1',
	'maintenance-stats-images'        => 'Zuel vun de Fichieren: $1',
	'maintenance-stats-views'         => 'Zuel vun de besichte Säiten: $1',
	'maintenance-stats-update'        => "D'Datebank gëtt aktualiséiert ...",
	'maintenance-move'                => '$1 gëtt op $2 geréckelt ...',
	'maintenance-error'               => 'Feeler: $1',
	'maintenance-memc-requests'       => 'Ufroen',
	'maintenance-memc-total'          => 'Total:',
	'maintenance-memc-absent'         => 'net do:',
);

/** Malayalam (മലയാളം)
 * @author Shijualex
 */
$messages['ml'] = array(
	'maintenance-confirm'    => 'സ്ഥിരീകരിക്കുക',
	'maintenance-memc-total' => 'മൊത്തം:',
);

/** Marathi (मराठी)
 * @author Kaustubh
 */
$messages['mr'] = array(
	'maintenance'                       => 'व्यवस्थापन स्क्रीप्ट्स चालवा',
	'maintenance-desc'                  => 'वेगवेगळ्या व्यवस्थापन स्क्रीप्ट्स करिता [[Special:Maintenance|वेब इंटरफेस]]',
	'maintenance-backlink'              => 'स्क्रीप्ट निवडीकडे परत चला',
	'maintenance-header'                => 'चालविण्यासाठी खालील एक स्क्रीप्ट निवडा.
प्रत्येक स्क्रीप्टच्या पुढे माहिती दिलेली आहे',
	'maintenance-changePassword-desc'   => 'एखाद्या सदस्याचा परवलीचा शब्द बदला',
	'maintenance-createAndPromote-desc' => 'एक सदस्य तयार करा व त्याला प्रबंधक बनवा',
	'maintenance-deleteBatch-desc'      => 'खूप पाने एकत्र वगळा',
	'maintenance-deleteRevision-desc'   => 'डाटाबेस मधून आवृत्त्या वगळा',
	'maintenance-initEditCount-desc'    => 'सदस्यांची योगदान संख्या पुन्हा मोजा',
	'maintenance-initStats-desc'        => 'सांख्यिकी पुन्हा मोजा',
	'maintenance-moveBatch-desc'        => 'खूप पाने एकत्र स्थानांतरीत करा',
	'maintenance-runJobs-desc'          => 'कार्य रांगेतील कार्ये करा',
	'maintenance-showJobs-desc'         => 'कार्य रांगेतील पूर्ण न झालेल्या कार्यांची यादी दाखवा',
	'maintenance-stats-desc'            => 'Memcached सांख्यिकी दाखवा',
	'maintenance-changePassword'        => 'हा अर्ज एखाद्या सदस्याचा परवलीचा शब्द बदलण्यासाठी वापरा',
	'maintenance-createAndPromote'      => 'हा अर्ज एखादा नवीन सदस्य बनवून त्याला प्रबंधक करण्यासाठी वापरा.
सदस्याला अधिकारी बनविण्यासाठी अधिकारी बॉक्समध्ये सुद्धा टिचकी द्या',
	'maintenance-deleteBatch'           => 'हा अर्ज एकाच वेळी अनेक पाने वगळण्यासाठी वापरा.
एका ओळीवर एकच पान लिहा',
	'maintenance-deleteRevision'        => 'हा अर्ज अनेक आवृत्त्या एकाचवेळी वगळण्यासाठी वापरा.
एका ओळीवर एकच आवृत्ती लिहा',
	'maintenance-initStats'             => 'हा अर्ज सांख्यिकी पुन्हा मोजण्यासाठी वापरा, त्यामध्ये तुम्ही पान बघण्याची सांख्यिकी सुद्धा पुन्हा मोजू शकता',
	'maintenance-moveBatch'             => 'हा अर्ज एकाचवेळी अनेक पाने स्थानांतरीत करण्यासाठी वापरा.
प्रत्येक ओळीवर स्रोत पान व लक्ष्य पान पाईप चिन्ह वापरून लिहा',
	'maintenance-invalidtype'           => 'चुकीचा प्रकार!',
	'maintenance-name'                  => 'सदस्यनाव',
	'maintenance-password'              => 'परवलीचा शब्द',
	'maintenance-bureaucrat'            => 'सदस्याला अधिकारीपद द्या',
	'maintenance-reason'                => 'कारण',
	'maintenance-userexists'            => 'सदस्य अगोदरच अस्तित्वात आहे!',
	'maintenance-invalidtitle'          => 'चुकीचे शीर्षक "$1"!',
	'maintenance-titlenoexist'          => 'दिलेले शीर्षक ("$1") अस्तित्वात नाही!',
	'maintenance-deleted'               => 'वगळले',
	'maintenance-stats-edits'           => 'संपादनांची संख्या: $1',
	'maintenance-stats-pages'           => 'पृष्ठ संख्या: $1',
	'maintenance-stats-users'           => 'सदस्य संख्या: $1',
);

/** Dutch (Nederlands)
 * @author Siebrand
 */
$messages['nl'] = array(
	'maintenance'                       => 'Beheerscripts uitvoeren',
	'maintenance-desc'                  => '[[Special:Maintenance|Webinterface]] voor een aantal beheerscripts',
	'maintenance-backlink'              => 'Naar scripselectie terugkeren',
	'maintenance-header'                => 'Selecteer hieronder een uit te voeren script.
Beschrijvingen staan naast de scripts',
	'maintenance-changePassword-desc'   => 'Wachtwoord van een gebruiker wijzigen',
	'maintenance-createAndPromote-desc' => 'Een nieuwe gebruiker aanmaken en deze beheerder maken',
	'maintenance-deleteBatch-desc'      => "Pagina's en masse verwijderen",
	'maintenance-deleteRevision-desc'   => 'Versies uit de database verwijderen',
	'maintenance-initEditCount-desc'    => 'Aantal bewerkingen van gebruikers herberekenen',
	'maintenance-initStats-desc'        => 'Sitestatistieken herberekenen',
	'maintenance-moveBatch-desc'        => "Pagina's en masse hernoemen",
	'maintenance-runJobs-desc'          => 'Taken uit de jobqueue uitvoeren',
	'maintenance-showJobs-desc'         => 'Openstaande taken in de jobqueue bekijken',
	'maintenance-stats-desc'            => 'Memcachedstatistieken bekijken',
	'maintenance-changePassword'        => 'Gebruik dit formulier om het wachtwoord van een gebruiker te wijzigen',
	'maintenance-createAndPromote'      => "Gebruik dit formulier om een gebruiker aan te maken en deze beheerder te maken.
Vink het vakje 'bureaucraat' aan om de gebruik ook bureacraat te maken",
	'maintenance-deleteBatch'           => "Gebruik dit formulier om en masse pagina's te verwijderen.
Geef op iedere regel een paginanaam op",
	'maintenance-deleteRevision'        => 'Gebruik dit formulier om en masse versie te verwijderen.
Geef op iedere regel een paginanaam op',
	'maintenance-initStats'             => 'Gebruik dit formulier de sitestatistieken opnieuw te berekenen. Geef daarbij aan of u de tellingen van het aantal keren dat een pagina is bekeken ook wilt bijwerken',
	'maintenance-moveBatch'             => 'Gebruik dit formulier om en masse pagina\'s te hernoemen.
Iedere regel moet een doelpagina en een bestemmingspagina bevatten, gescheiden door een pipe-teken ("|")',
	'maintenance-invalidtype'           => 'Ongeldig type!',
	'maintenance-name'                  => 'Gebruiker',
	'maintenance-password'              => 'Wachtwoord',
	'maintenance-bureaucrat'            => 'De gebruiker bureaucraat maken',
	'maintenance-reason'                => 'Reden',
	'maintenance-update'                => 'Gebruik UPDATE als u een tabel wilt bijwerken? Unchecked gebruiker in plaats daarvan DELETE/INSERT.',
	'maintenance-noviews'               => 'Vink dit aan om te voorkomen dat het aantal keren dat een pagina is bekeken wordt bijgewerkt',
	'maintenance-confirm'               => 'Bevestigen',
	'maintenance-invalidname'           => 'Ongeldige gebruikersnaam!',
	'maintenance-success'               => '$1 is uitgevoerd!',
	'maintenance-userexists'            => 'De gebruiker bestaat al!',
	'maintenance-invalidtitle'          => 'Ongeldige paginanaam "$1"!',
	'maintenance-titlenoexist'          => 'De opgegeven pagina ("$1") bestaat niet!',
	'maintenance-failed'                => 'MISLUKT',
	'maintenance-deleted'               => 'VERWIJDERD',
	'maintenance-revdelete'             => 'Bezig met het verwijderen van versies $1 van wiki $2',
	'maintenance-revnotfound'           => 'Versie $1 niet gevonden!',
	'maintenance-stats-edits'           => 'Aantal bewerkingen: $1',
	'maintenance-stats-articles'        => "Aantal pagina's in de hoofdnaamruimte: $1",
	'maintenance-stats-pages'           => "Aantal pagina's: $1",
	'maintenance-stats-users'           => 'Aantal gebruikers: $1',
	'maintenance-stats-admins'          => 'Aantal beheerders: $1',
	'maintenance-stats-images'          => 'Aantal bestanden: $1',
	'maintenance-stats-views'           => "Aantal bekeken pagina's: $1",
	'maintenance-stats-update'          => 'Bezig met het bijwerken van de database...',
	'maintenance-move'                  => 'Bezig met het hernoemen van $1 naar $2...',
	'maintenance-movefail'              => 'Er is een fout opgetreden bij het hernoemen: $1.
Hernoemen is afgebroken',
	'maintenance-error'                 => 'Fout: $1',
	'maintenance-memc-fake'             => 'U maakt gebruik van FakeMemCachedClient. Het is niet mogelijk statistieken te berekenen',
	'maintenance-memc-requests'         => 'Verzoeken',
	'maintenance-memc-withsession'      => 'met sessie:',
	'maintenance-memc-withoutsession'   => 'zonder sessie:',
	'maintenance-memc-total'            => 'totaal:',
	'maintenance-memc-parsercache'      => 'Parsercache',
	'maintenance-memc-hits'             => 'hits:',
	'maintenance-memc-invalid'          => 'ongeldig:',
	'maintenance-memc-expired'          => 'verlopen:',
	'maintenance-memc-absent'           => 'afwezig:',
	'maintenance-memc-stub'             => 'stubdrempelwaarde:',
	'maintenance-memc-imagecache'       => 'Beeldencache',
	'maintenance-memc-misses'           => 'gemist:',
	'maintenance-memc-updates'          => 'bijgewerkt:',
	'maintenance-memc-uncacheable'      => 'kan niet gecached worden:',
	'maintenance-memc-diffcache'        => 'Diff cache',
);

/** Norwegian (bokmål)‬ (‪Norsk (bokmål)‬)
 * @author Jon Harald Søby
 */
$messages['no'] = array(
	'maintenance'                       => 'Kjør vedlikeholdsskript',
	'maintenance-desc'                  => '[[Special:Maintenance|Nettgrensesnitt]] for ulike vedlikeholdsskript',
	'maintenance-backlink'              => 'Tilbake til skriptvalget',
	'maintenance-header'                => 'Velg et skript å utføre nedenfor.
Beskrivelser gis ved siden av hvert skript.',
	'maintenance-changePassword-desc'   => 'Endre en brukers passord',
	'maintenance-createAndPromote-desc' => 'Opprett en bruker og gjør til administrator',
	'maintenance-deleteBatch-desc'      => 'Slett mange sider',
	'maintenance-deleteRevision-desc'   => 'Fjern revisjoner fra databasen',
	'maintenance-initEditCount-desc'    => 'Regne om redigeringstelleren for brukere',
	'maintenance-initStats-desc'        => 'Regne om sidestatistikken',
	'maintenance-moveBatch-desc'        => 'Flytte mange sider',
	'maintenance-runJobs-desc'          => 'Kjøre jobber i jobbkøen',
	'maintenance-showJobs-desc'         => 'Vise en liste over jobber som venter i jobbkøen',
	'maintenance-stats-desc'            => 'Vis mellomlagret statistikk',
	'maintenance-changePassword'        => 'Bruk dette skjemaet for å endre en brukers passord',
	'maintenance-createAndPromote'      => 'Bruk dette skjemaet for å opprette en ny bruker og gjøre den til administrator.
Kryss av i byråkratboksen om du ønsker å gjøre den til byråkrat også',
	'maintenance-deleteBatch'           => 'Bruk dette skjemaet for å slette mange sider på én gang.
Skriv én sidetittel per rad',
	'maintenance-deleteRevision'        => 'Bruk dette skjemaet for å slette mange revisjoner på én gang.
Skriv ett revisjonsnummer per rad',
	'maintenance-initStats'             => 'Bruk dette skjemaet for å regne ut sidestatistikken på nytt, spesielt om du vil regne ut sidevisninger på nytt',
	'maintenance-moveBatch'             => 'Bruk dette skjemaet for å flytte mange sider på én gang.
Hver linje bør oppgi kildeside og målside adskilt med strek (|)',
	'maintenance-invalidtype'           => 'Ugyldig type!',
	'maintenance-name'                  => 'Brukernavn',
	'maintenance-password'              => 'Passord',
	'maintenance-bureaucrat'            => 'Forfrem en bruker til byråkrat',
	'maintenance-reason'                => 'Årsak',
	'maintenance-update'                => 'Bruk UPDATE under oppdatering av tabell? Om uavkrysset brukes DELETE/INSERT i stedet.',
	'maintenance-noviews'               => 'Kryss av her for ikke å oppdatere sidevisninger',
	'maintenance-confirm'               => 'Bekreft',
	'maintenance-invalidname'           => 'Ugyldig brukernavn.',
	'maintenance-success'               => '$1 ble gjennomført uten uhell.',
	'maintenance-userexists'            => 'Brukeren finnes allerede.',
	'maintenance-invalidtitle'          => 'Ugyldig tittel «$1».',
	'maintenance-titlenoexist'          => 'Den oppgitte tittelen («$1») finnes ikke.',
	'maintenance-failed'                => 'MISLYKTES',
	'maintenance-deleted'               => 'SLETTET',
	'maintenance-revdelete'             => 'Sletter revisjonene $1 fra wikien $2',
	'maintenance-revnotfound'           => 'Revisjon $1 ikke funnet.',
	'maintenance-stats-edits'           => 'Antal redigeringer: $1',
	'maintenance-stats-articles'        => 'Antall sider i hovednavnerommet: $1',
	'maintenance-stats-pages'           => 'Antall sider: $1',
	'maintenance-stats-users'           => 'Antall brukere: $1',
	'maintenance-stats-admins'          => 'Antall administratorer: $1',
	'maintenance-stats-images'          => 'Antall filer: $1',
	'maintenance-stats-views'           => 'Antall sidevisninger: $1',
	'maintenance-stats-update'          => 'Oppdaterer database …',
	'maintenance-move'                  => 'Flytter $1 til $2 …',
	'maintenance-movefail'              => 'Feil oppsto under flytting: $1.
Avbryter flytting',
	'maintenance-error'                 => 'Feil: $1',
	'maintenance-memc-fake'             => 'Du kjører en FakeMemCachedClient. Ingen statistikk kan oppgis.',
	'maintenance-memc-requests'         => 'Forespørsler',
	'maintenance-memc-withsession'      => 'med sesjon:',
	'maintenance-memc-withoutsession'   => 'uten sesjon:',
	'maintenance-memc-total'            => 'totalt:',
	'maintenance-memc-parsercache'      => 'Parsermellomlager',
	'maintenance-memc-hits'             => 'treff:',
	'maintenance-memc-invalid'          => 'ugyldig:',
	'maintenance-memc-expired'          => 'utgikk:',
	'maintenance-memc-absent'           => 'ikke til stede:',
	'maintenance-memc-stub'             => 'stubbgrense:',
	'maintenance-memc-imagecache'       => 'Bildemellomlager',
	'maintenance-memc-misses'           => 'bom:',
	'maintenance-memc-updates'          => 'Oppdateringer:',
	'maintenance-memc-uncacheable'      => 'Kan ikke mellomlagres:',
	'maintenance-memc-diffcache'        => 'Forskjellsmellomlager',
);

/** Swedish (Svenska)
 * @author M.M.S.
 */
$messages['sv'] = array(
	'maintenance'                       => 'Kör underhållsskripter',
	'maintenance-desc'                  => '[[Special:Maintenance|Webbgränssnitt]] för olika underhållsskripter',
	'maintenance-backlink'              => 'Tillbaka till skriptvalet',
	'maintenance-header'                => 'Var god ange ett skript nedan till att exekvera.
Beskrivningar finns brevid varje skript',
	'maintenance-changePassword-desc'   => 'Ändra en användares lösenord',
	'maintenance-createAndPromote-desc' => 'Skapa en användare och befodra till administratör',
	'maintenance-deleteBatch-desc'      => 'Mass-radera sidor',
	'maintenance-deleteRevision-desc'   => 'Ta bort versioner från databasen',
	'maintenance-initEditCount-desc'    => 'Omräkna redigeringräkningarna för användare',
	'maintenance-initStats-desc'        => 'Omräkna sajtstatistiken',
	'maintenance-moveBatch-desc'        => 'Mass-flytta sidor',
	'maintenance-runJobs-desc'          => 'Köra jobb i jobbkön',
	'maintenance-showJobs-desc'         => 'Visa en lista över jobb som ligger i jobbkön',
	'maintenance-stats-desc'            => 'Visa mellanlagrad statistik',
	'maintenance-changePassword'        => 'Använd detta formulär för att ändra en användares lösenord',
	'maintenance-createAndPromote'      => 'Använd detta formulär för att skapa en ny användare och befodra den till administratör.
Kryssa i byråkratruta om du vill befodra den till byråkrat istället',
	'maintenance-deleteBatch'           => 'Använd detta formulär för att mass-radera sidor.
Skriv endast in en sida per rad',
	'maintenance-deleteRevision'        => 'Använd detta formulär för att mass-radera versioner.
Skriv endast in en version per rad',
	'maintenance-initStats'             => 'Använd detta formulär för att räkna om sajtens statistik, speciellt om du vill räkna om sidvisningar',
	'maintenance-moveBatch'             => 'Använd detta formulär för att mass-flytta sidor.
Varje rad specifierar den nuvarande sidan och destinationssidan separerade med ett lodrätt streck (|)',
	'maintenance-invalidtype'           => 'Ogiltig typ!',
	'maintenance-name'                  => 'Användarnamn',
	'maintenance-password'              => 'Lösenord',
	'maintenance-bureaucrat'            => 'Befodra en användare till en byråkrat',
	'maintenance-reason'                => 'Anledning',
	'maintenance-update'                => 'Använd UPPDATERA när du uppdaterar en tabell? Okryssade använder RADERA/INFOGA istället.',
	'maintenance-noviews'               => 'Kolla det här för att förhindra uppdatering av sidvisningar',
	'maintenance-confirm'               => 'Bekräfta',
	'maintenance-invalidname'           => 'Ogiltigt användarnamn!',
	'maintenance-success'               => '$1 kördes lyckat!',
	'maintenance-userexists'            => 'Användaren existerar redan!',
	'maintenance-invalidtitle'          => 'Ogiltig titel "$1"!',
	'maintenance-titlenoexist'          => 'Titeln som specifierades ("$1") finns inte!',
	'maintenance-failed'                => 'MISSLYCKAD',
	'maintenance-deleted'               => 'RADERAD',
	'maintenance-revdelete'             => 'Raderar versioner $1 från wiki $2',
	'maintenance-revnotfound'           => 'Versionen $1 hittades inte!',
	'maintenance-stats-edits'           => 'Antal redigeringar: $1',
	'maintenance-stats-articles'        => 'Antal sidor i huvudnamnrymden: $1',
	'maintenance-stats-pages'           => 'Antal sidor: $1',
	'maintenance-stats-users'           => 'Antal användare: $1',
	'maintenance-stats-admins'          => 'Antal administratörer: $1',
	'maintenance-stats-images'          => 'Antal filer: $1',
	'maintenance-stats-views'           => 'Antal sidvisningar: $1',
	'maintenance-stats-update'          => 'Uppdaterar databasen...',
	'maintenance-move'                  => 'Flyttar $1 till $2...',
	'maintenance-movefail'              => 'Ett fel uppstod medan flyttningen: $1.
Avbryt flyttning',
	'maintenance-error'                 => 'Fel: $1',
);

/** Vietnamese (Tiếng Việt)
 * @author Vinhtantran
 */
$messages['vi'] = array(
	'maintenance'                       => 'Chạy mã bảo trì',
	'maintenance-desc'                  => '[[Special:Maintenance|Giao diện web]] dành cho các loại mã bảo trì khác nhau',
	'maintenance-backlink'              => 'Quay lại lựa chọn mã',
	'maintenance-header'                => 'Xin hãy chọn một đoạn mã ở dưới để thực thi.
Mô tả nằm ở bên cạnh mỗi đoạn mã',
	'maintenance-changePassword-desc'   => 'Thay đổi mật khẩu của thành viên',
	'maintenance-createAndPromote-desc' => 'Tạo một thành viên và phong cho thành viên này thành sysop',
	'maintenance-deleteBatch-desc'      => 'Xóa trang hàng loạt',
	'maintenance-deleteRevision-desc'   => 'Xóa một phiên bản ra khỏi cơ sở dữ liệu',
	'maintenance-initEditCount-desc'    => 'Tính toán lại số lần sửa đổi của thành viên',
	'maintenance-initStats-desc'        => 'Tính toán lại các thống kê của trang',
	'maintenance-moveBatch-desc'        => 'Di chuyển trang hàng loạt',
	'maintenance-runJobs-desc'          => 'Chạy các tác vụ trong hàng đợi công việc',
	'maintenance-showJobs-desc'         => 'Hiển thị danh sách các công việc đang chờ đợi trong hàng đợi việc',
	'maintenance-stats-desc'            => 'Hiển thị thống kê được lưu vào bộ đệm',
	'maintenance-changePassword'        => 'Sử dụng mẫu này để thay đổi mật khẩu của thành viên',
	'maintenance-createAndPromote'      => 'Sử dụng mẫu này để tạo ra thành viên mới và phong cho thành viên này cờ sysop.
Chọn vào ô hành chính viên nếu bạn cũng muốn phong thành Hành chính viên',
	'maintenance-deleteBatch'           => 'Sử dụng mẫu này để xóa trang hàng loạt.
Chỉ ghi mỗi dòng một trang',
	'maintenance-deleteRevision'        => 'Sử dụng mẫu này để xóa phiên bản hàng loạt.
Chỉ ghi mỗi dòng một phiên bản',
	'maintenance-initStats'             => 'Sử dụng mẫu này để tính lại các thống kê của trang, hãy chỉ rõ nếu bạn cũng muốn tính lại số lần xem trang',
	'maintenance-moveBatch'             => 'Sử dụng mẫu này để di chuyển trang hàng loạt.
Mỗi dòng nên ghi rõ trang nguồn và trang đích, cách nhau bằng dấu sọc đứng',
	'maintenance-invalidtype'           => 'Kiểu không hợp lệ!',
	'maintenance-name'                  => 'Tên người dùng',
	'maintenance-password'              => 'Mật khẩu',
	'maintenance-bureaucrat'            => 'Thăng người này làm hành chính viên',
	'maintenance-reason'                => 'Lý do',
	'maintenance-update'                => 'Có sử dụng UPDATE khi cập nhật một bảng? Thay vào đó hãy bỏ chọn cách dùng DELETE/INSERT.',
	'maintenance-noviews'               => 'Chọn cái này để ngăn cập nhật số lần xem trang',
	'maintenance-confirm'               => 'Xác nhận',
	'maintenance-invalidname'           => 'Tên người dùng không hợp lệ!',
	'maintenance-success'               => '$1 đã chạy thành công!',
	'maintenance-userexists'            => 'Người dùng đã tồn tại!',
	'maintenance-invalidtitle'          => 'Tựa đề “$1” không hợp lệ!',
	'maintenance-titlenoexist'          => 'Tựa đề chỉ định (“$1”) không tồn tại!',
	'maintenance-failed'                => 'THẤT BẠI',
	'maintenance-deleted'               => 'ĐÃ XÓA',
	'maintenance-revdelete'             => 'Đang xóa phiên bản $1 từ wiki $2',
	'maintenance-revnotfound'           => 'Không tìm thấy phiên bản $1!',
	'maintenance-stats-edits'           => 'Số lần sửa đổi: $1',
	'maintenance-stats-articles'        => 'Số trang trong không gian tên chính: $1',
	'maintenance-stats-pages'           => 'Số trang: $1',
	'maintenance-stats-users'           => 'Số người dùng: $1',
	'maintenance-stats-admins'          => 'Số quản lý: $1',
	'maintenance-stats-images'          => 'Số tập tin: $1',
	'maintenance-stats-views'           => 'Số lần xem trang: $1',
	'maintenance-stats-update'          => 'Đang cập nhật cơ sở dữ liệu...',
	'maintenance-move'                  => 'Đang di chuyển $1 sang $2...',
	'maintenance-movefail'              => 'Gặp lỗi khi di chuyển: $1.
Hủy di chuyển',
	'maintenance-error'                 => 'Lỗi: $1',
	'maintenance-memc-fake'             => 'Bạn đang chạy FakeMemCachedClient. Không có thống kê nào',
	'maintenance-memc-requests'         => 'Yêu cầu',
	'maintenance-memc-withsession'      => 'với phiên:',
	'maintenance-memc-withoutsession'   => 'không có phiên',
	'maintenance-memc-total'            => 'tổng cộng:',
	'maintenance-memc-parsercache'      => 'Bộ đệm Phân tích cú pháp',
	'maintenance-memc-hits'             => 'số hit:',
	'maintenance-memc-invalid'          => 'không hợp lệ:',
	'maintenance-memc-expired'          => 'hết hạn:',
	'maintenance-memc-absent'           => 'thiếu:',
	'maintenance-memc-stub'             => 'ngưỡng sơ khai:',
	'maintenance-memc-imagecache'       => 'Bộ đệm Hình ảnh',
	'maintenance-memc-misses'           => 'số miss:',
	'maintenance-memc-updates'          => 'số cập nhật:',
	'maintenance-memc-uncacheable'      => 'không thể lưu đệm:',
	'maintenance-memc-diffcache'        => 'Khác nhau Bộ đệm',
);

