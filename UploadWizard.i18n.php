<?php
/**
 * Internationalisation for Upload Wizard extension
 *
 * @file
 * @ingroup Extensions
 */

$messages = array();

/** English
 * @author Neil Kandalgaonkar
 */
$messages['en'] = array(
	'uploadwizard' => 'Upload wizard',
	'uploadwizard-desc' => 'Upload wizard, developed for the Multimedia Usability grant',
	'mwe-loading-upwiz' => 'Loading upload wizard',
	'mwe-upwiz-code-unknown' => 'Unknown language',
	'mwe-upwiz-step-file' => '1. Upload your files',
	'mwe-upwiz-step-deeds' => '2. Add licenses',
	'mwe-upwiz-step-details' => '3. Add descriptions',
	'mwe-upwiz-step-thanks' => '4. Use your files',
	'mwe-upwiz-intro' => "Welcome to Wikimedia Commons, a repository of images, sounds, and movies that anyone can freely download and use. Add to humanity's knowledge by uploading files that could be used for an educational purpose.",
	'mwe-upwiz-add-file-n' => 'Add another file',
	'mwe-upwiz-add-file-0' => 'Click here to add a file for upload',
	'mwe-upwiz-browse' => 'Browse...',
	'mwe-upwiz-transported' => 'OK',
	'mwe-upwiz-click-here' => 'Click here to select a file',
	'mwe-upwiz-uploading' => 'uploading...',
	'mwe-upwiz-editing' => 'editing...',
	'mwe-upwiz-remove-upload' => 'Remove this file from the list of files to upload',
	'mwe-upwiz-remove-description' => 'Remove this description',
	'mwe-upwiz-upload' => 'Upload',
	'mwe-upwiz-upload-count' => '$1 of $2 files uploaded',
	'mwe-upwiz-progressbar-uploading' => 'uploading',
	'mwe-upwiz-remaining' => '$1 remaining',
	'mwe-upwiz-deeds-intro' => "Thank you! Now we need to set a license for these files, so everyone can legally view or modify them. First, we will have to know where you got them.",
	'mwe-upwiz-details-intro' => 'Now we need some basic information about the files.',
	'mwe-upwiz-source-ownwork' => 'This file is my own work.',
	'mwe-upwiz-source-ownwork-plural' => 'These files are my own work.',
	'mwe-upwiz-source-ownwork-assert' => 'I, $1, the copyright holder of this work, hereby grant anyone the right to use this work for any purpose, as long as they credit me and share derivative work under the same terms.',
	'mwe-upwiz-source-ownwork-assert-plural' => 'I, $1, the copyright holder of these works, hereby grant anyone the right to use these works for any purpose, as long as they credit me and share derivative work under the same terms.',
	'mwe-upwiz-source-ownwork-assert-custom' => 'I, $1, the copyright holder of this work, hereby publish this work under the following license(s):',
	'mwe-upwiz-source-ownwork-assert-custom-plural' => 'I, $1, the copyright holder of these works, hereby publish these works under the following license(s):',
	'mwe-upwiz-source-ownwork-assert-note' => 'This means you release your work under a double Creative Commons Attribution ShareAlike and GFDL license.',
	'mwe-upwiz-source-permission' => 'Their author gave you explicit permission to upload them',
	'mwe-upwiz-source-thirdparty' => 'This file is not my own work.',
	'mwe-upwiz-source-thirdparty-plural' => 'These files are not my own work.',
	'mwe-upwiz-source-thirdparty-intro' => 'Please enter the address where you found each file.',
	'mwe-upwiz-source-thirdparty-custom-plural-intro' => 'If all files have the same source, author, and copyright status, you may enter them only once for all of them.',
	'mwe-upwiz-source-thirdparty-license' => 'The copyright holder of this work published them under the following license(s):',
	'mwe-upwiz-source-thirdparty-license-plural' => 'The copyright holder of these works published them under the following license(s):',
	'mwe-upwiz-source-thirdparty-accept' => 'OK',
	'mwe-upwiz-source-custom' => 'Did you know? You can <a href="$1">customize</a> the default options you see here.',
	'mwe-upwiz-more-options' => 'more options...',
	'mwe-upwiz-fewer-options' => 'fewer options...',
	'mwe-upwiz-desc' => 'Description in',
	'mwe-upwiz-desc-add-n' => 'add a description in another language',
	'mwe-upwiz-desc-add-0' => 'add a description',
	'mwe-upwiz-title' => 'Title',
	'mwe-upwiz-categories-intro' => 'Help people find your works by adding categories',
	'mwe-upwiz-categories-another' => 'Add other categories',
	'mwe-upwiz-previously-uploaded' => 'This file was previously uploaded to $1 and is already available <a href="$2">here</a>.',
	'mwe-upwiz-about-this-work' => 'About this work',
	'mwe-upwiz-media-type' => 'Media type',
	'mwe-upwiz-date-created' => 'Date created',
	'mwe-upwiz-location' => 'Location',
	'mwe-upwiz-copyright-info' => 'Copyright information',
	'mwe-upwiz-author' => 'Author(s)',
	'mwe-upwiz-license' => 'License',
	'mwe-upwiz-about-format' => 'About the file',
	'mwe-upwiz-autoconverted' => 'This file was automatically converted to the $1 format',
	'mwe-upwiz-filename-tag' => 'File name:',
	'mwe-upwiz-other' => 'Other information',
	'mwe-upwiz-other-prefill' => 'Free wikitext field',
	'mwe-upwiz-showall' => 'show all',
	'mwe-upwiz-source' => 'Source',
	'mwe-upwiz-macro-edit-intro' => 'Choose a license first above, then you can add some descriptions and other information to your uploads.',
	'mwe-upwiz-macro-edit' => 'Update descriptions',
	'mwe-upwiz-thanks-intro' => 'Thanks for uploading your works! You can now use your files on page or link to them from elsewhere on the web.',
	'mwe-upwiz-thanks-link' => 'This file is now available at <b><tt>$1</tt></b>.',
	'mwe-upwiz-thanks-wikitext' => '<b>To use the file</b>, copy this text into a page:',
	'mwe-upwiz-thanks-url' => '<b>To link to it in HTML</b>, copy this HTML code:',
	'mwe-upwiz-upload-error-bad-filename-extension' => 'This wiki does not accept filenames with the extension "$1".',
	'mwe-upwiz-upload-error-duplicate' => 'This file was previously uploaded to this wiki.',
	'mwe-upwiz-upload-error-stashed-anyway' => 'Post anyway?',
	'mwe-upwiz-ok' => 'OK',
	'mwe-upwiz-cancel' => 'Cancel',
	'mwe-upwiz-change' => '(change)',
	'mwe-upwiz-fileexists' => 'A file with this name exists already. Please check <b><tt>$1</tt></b> if you are not sure if you want to replace it.',
	'mwe-upwiz-thumbnail-more' => 'Enlarge',
	'mwe-upwiz-overwrite' => 'Replace the file',
	'mwe-copyright-macro' => 'As above',
	'mwe-copyright-custom' => 'Custom',
	'mwe-upwiz-next' => 'Next',
	'mwe-upwiz-home' => 'Go to Wiki home page',
	'mwe-upwiz-upload-another' => 'Upload more files',
	'mwe-prevent-close' => 'Your files are still uploading. Are you sure you want to navigate away from this page?',
	'mwe-upwiz-files-complete' => 'Your files finished uploading!',
	'mwe-upwiz-deeds-later' => 'Set deeds and licenses for each file individually on the next page',
	'mwe-upwiz-tooltip-author' => 'The name of the person who took the photo, or painted the picture, drew the drawing, etc.',
	'mwe-upwiz-tooltip-source' => 'Where this digital file came from -- could be a URL, or a book or publication',
	'mwe-upwiz-tooltip-sign' => 'You can use your wiki user name or your real name. In both cases, this will be linked to your wiki user page',
	'mwe-upwiz-tooltip-title' => 'A short title for the image. You may use plain language with spaces, but no line breaks. This title must be unlike all other titles in this wiki.',
	'mwe-upwiz-tooltip-description' => 'Briefly describe everything notable about the work. For a photo, mention the main things that are depicted, the occasion or the place.',
	'mwe-upwiz-tooltip-other' => 'Any other information you want to include about this work. You may use wikitext code.',
	'mwe-upwiz-tooltip-more-info' => 'Learn more.',
);


$messages['be-tarask'] = array(
	'mwe-upwiz-fileexists' => 'Файл з такой назвай ужо існуе. Калі ласка, праверце <b><tt>$1</tt></b>, калі Вы ня ўпэўненыя, што жадаеце яго замяніць.',
);

$messages['cs'] = array(
	'mwe-upwiz-fileexists' => 'Soubor s tímto jménem již existuje, prosím podívejte se na <b><tt>$1</tt></b>, pokud nevíte jistě, zda chcete tento soubor nahradit.',
);

$messages['de'] = array(
	'mwe-upwiz-fileexists' => 'Eine Datei mit diesem Namen existiert bereits. Bitte prüfe <b><tt>$1</tt></b>, wenn du dir bei der Änderung nicht sicher bist.',
);

$messages['diq'] = array(
	'mwe-upwiz-fileexists' => 'no name de ca ra yew dosya esta. eke şıma emin niê bıvurni, kerem kerê <b><tt>$1</tt></b> kontrol bıkerê.',
);

$messages['dsb'] = array(
	'mwe-upwiz-fileexists' => 'Dataja z toś tym mjenim južo eksistěrujo. Pšosym skontrolěruj <b><tt>$1</tt></b>, jolic njejsy wěsty, lěc coš ju změniś.',
);

$messages['es'] = array(
	'mwe-upwiz-fileexists' => 'Un archivo con este nombre ya existe. Por favor verifica <b><tt>$1</tt></b> si no est.ás seguro si deseas cambiarlo.',
);

$messages['fr'] = array(
	'mwe-upwiz-fileexists' => 'Un fichier existe déjà sous ce nom. Veuillez vérifier <b><tt>$1</tt></b> si vous n\'êtes pas sûr de vouloir le changer.',
);

$messages['gl'] = array(
	'mwe-upwiz-fileexists' => 'Xa existe un ficheiro con ese nome. Por favor, verifique <b><tt>$1</tt></b> se non está seguro de que quere cambialo.',
);

$messages['gsw'] = array(
	'mwe-upwiz-fileexists' => 'S het scho ne Datei mit däm Name. Bitte prief <b><tt>$1</tt></b>, wänn du nit sicher bisch, eb Du dr Name witt ändere.',
);

$messages['hsb'] = array(
	'mwe-upwiz-fileexists' => 'Dataja z tutym mjenom hižo eksistuje. Prošu skontroluj <b><tt>$1</tt></b>, jeli njesy sej wěsty, hač chceš ju změnić.',
);

$messages['hu'] = array(
	'mwe-upwiz-fileexists' => 'Már létezik ilyen nevű fájl. Ellenőrizd a(z) <b><tt>$1</tt></b> fájlt, ha nem vagy biztos benne, hogy le szeretnéd cserélni.',
);

$messages['ia'] = array(
	'mwe-upwiz-fileexists' => 'Un file con iste nomine ja existe. Per favor verifica <b><tt>$1</tt></b> si tu non es secur de voler cambiar lo.',
);

$messages['id'] = array(
	'mwe-upwiz-fileexists' => 'Suatu berkas dengan nama tersebut telah ada. Tolong cek <b><tt>$1</tt></b> jika Anda tidak yakin untuk mengubahnya.',
);

$messages['ja'] = array(
	'mwe-upwiz-fileexists' => '同名のファイルが既に存在しています。上書きしてよいかわからない場合は <b><tt>$1</tt></b> を確認してください。',
);

$messages['ksh'] = array(
	'mwe-upwiz-fileexists' => 'En Dattei met dämm Name jidd_et ald. Beß esu joot un donn <b><tt>$1</tt></b> prööfe, wann De Der nit sescher beß, of De jät ändere wells.',
);

$messages['lb'] = array(
	'mwe-upwiz-fileexists' => 'E Fichier mat dësem Numm gëtt et schonn. Kuckt w.e.g. op <b><tt>$1</tt></b> no wann Dir net sécher sidd ob Dir en ännere wëllt.',
);

$messages['ml'] = array(
	'mwe-upwiz-fileexists' => 'ഇതേ പേരിൽ ഒരു പ്രമാണം നിലവിലുണ്ട്. അതിൽ മാറ്റം വരുത്തണോ എന്നു താങ്കൾക്ക് ഉറപ്പില്ലങ്കിൽ ദയവായി <b><tt>$1</tt></b> കാണുക.',
);

$messages['nl'] = array(
	'mwe-upwiz-fileexists' => 'Er bestaat al een bestand met deze naam. Controleer <b><tt>$1</tt></b> als u niet zeker weet of u het huidige bestand wilt overschrijven.',
);

$messages['oc'] = array(
	'mwe-upwiz-fileexists' => 'Un fichièr amb aqueste nom existís ja. Mercé de verificar <b><tt>$1</tt></b> se sètz pas segur que lo volètz cambiar.',
);

$messages['pl'] = array(
	'mwe-upwiz-fileexists' => 'Plik o tej nazwie już istnieje. Sprawdź <b><tt>$1</tt></b> jeśli nie jesteś pewien czy chcesz go zastąpić.',
);

$messages['pt'] = array(
	'mwe-upwiz-fileexists' => 'Já existe um ficheiro com este nome. Por favor, verifique <b><tt>$1</tt></b> se não tem a certeza de que deseja alterá-lo.',
);

$messages['ru'] = array(
	'mwe-upwiz-fileexists' => 'Файл с этим именем уже существует. Пожалуйста, проверьте <b><tt>$1</tt></b>, если вы не уверены, что хотите заменить его.',
);

$messages['sk'] = array(
	'mwe-upwiz-fileexists' => 'Súbor s týmto názvom už existuje. Prosím, skontrolujte <b><tt>$1</tt></b> ak si nie ste istý, či ho chcete zmeniť.',
);

$messages['tr'] = array(
	'mwe-upwiz-fileexists' => 'Bu isimde bir dosya zaten mevcut. Değiştirmek istediğinize emin değilseniz lütfen <b><tt>$1</tt></b> kontrol edin.',
);

$messages['vi'] = array(
	'mwe-upwiz-fileexists' => 'Một tập tin với tên này đã tồn tại, xin hãy kiểm tra lại <b><tt>$1</tt></b> nếu bạn không chắc bạn có muốn thay đổi nó hay không.',
);



