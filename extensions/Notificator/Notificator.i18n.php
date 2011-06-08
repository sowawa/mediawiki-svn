<?php

$aliases = array();
$messages = array();

$aliases['en'] = array(
	'Notificator' => array( 'Notificator' ),
);

$messages['en'] = array(
	'notificator' => 'Notificator',
	'notificator-description' => 'Notifies someone by e-mail about changes to a page when a button on that page gets clicked.',
	'notificator-db-table-does-not-exist' => 'Database table \'notificator\' does not exist. The update.php maintenance script needs to be run before the Notificator extension can be used.',
	'e-mail-address' => 'e-mail address',
	'notify' => 'Notify', // Button label
	'notify-address-or-name' => 'Notify $1', // Button label
	'revs-not-from-same-title' => 'Revision IDs are not from the same title/article', // Unlikely to be seen by the user
	'return-to' => 'Return to', // Return to <link> - at the bottom of the special page
	'special-page-accessed-directly' => 'This special page cannot be accessed directly. It is intended to be used through a Notificator button.',
	'e-mail-address-invalid' => 'The provided e-mail address is invalid.',
	'notification-not-sent' => 'Notification <em>not</em> sent.',
	'change-tag' => 'change',
	'new-tag' => 'new',
	'notification-text-changes' => '$1 wants to notify you about the following changes to $2:',
	'notification-text-new' => '$1 wants to notify you about $2.',
	'following-e-mail-sent-to' => 'The following e-mail has been sent to <em>$1</em>:',
	'subject' => 'Subject:', // The subject line of an e-mail
	'error-sending-e-mail' => 'There was an error when sending the notification e-mail to <em>$1</em>.',
	'error-parameter-missing' => 'Error: Missing parameter.', // Unlikely to be seen by the user
	'notified-already' => '$1 has been notified about this page or page change before.',
);

$messages['de'] = array(
	'notificator' => 'Notificator',
	'notificator-description' => 'Sendet Benachrichtungs-Mails über Seitenänderungen, wenn ein Knopf auf der entsprechenden Seite betätigt wird.',
	'notificator-db-table-does-not-exist' => 'Datenbanktabelle \'notificator\' existiert nicht. Das update.php Maintenance Script muss ausgeführt werden, bevor die Notificator-Extension verwendet werden kann.',
	'e-mail-address' => 'E-Mail-Adresse',
	'notify' => 'Benachrichtigen',
	'notify-address-or-name' => '$1 benachrichtigen',
	'revs-not-from-same-title' => 'Revision-IDs gehören nicht zum selben Titel/Artikel',
	'return-to' => 'Zurück zu',
	'special-page-accessed-directly' => 'Auf diese Spezial-Seite kann nicht direkt zugegriffen werden. Sie sollte über einen Notificator-Knopf verwendet werden.',
	'e-mail-address-invalid' => 'Die angegebene E-Mail-Adresse ist ungültig.',
	'notification-not-sent' => 'Benachrichtigung <em>nicht</em> gesendet.',
	'change-tag' => 'Änderung',
	'new-tag' => 'Neu',
	'notification-text-changes' => '$1 möchte Sie zu folgenden Änderungen an $2 benachrichtigen:',
	'notification-text-new' => '$1 möchte Sie zu $2 benachrichtigen.',
	'following-e-mail-sent-to' => 'Folgende E-Mail wurde an <em>$1</em> gesendet:',
	'subject' => 'Betreff:',
	'error-sending-e-mail' => 'Beim Versenden der Benachrichtigungs-Mail an <em>$1</em> ist ein Fehler aufgetreten.',
	'error-parameter-missing' => 'Fehler: Fehlender Parameter.',
	'notified-already' => '$1 wurde bereits zu dieser Seite oder Seitenänderung benachrichtigt.',
);