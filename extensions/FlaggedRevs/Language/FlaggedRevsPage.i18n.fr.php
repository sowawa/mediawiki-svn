<?php
/** French (Français)
 * @author Sherbrooke
 * @author Dereckson
 * @author Grondin
 * @author Siebrand
 * @author ChrisPtDe
 * @author SPQRobin
 */
$messages = array(
	'editor'                      => 'Contributeur',
	'group-editor'                => 'Contributeurs',
	'group-editor-member'         => 'Contributeur',
	'grouppage-editor'            => '{{ns:project}}:Editor',
	'reviewer'                    => 'Réviseur',
	'group-reviewer'              => 'Réviseurs',
	'group-reviewer-member'       => 'Réviseur',
	'grouppage-reviewer'          => '{{ns:project}}:Reviewer',
	'revreview-current'           => 'Ébauche',
	'tooltip-ca-current'          => "Voir l'ébauche courante de cette page",
	'revreview-edit'              => 'Ébauche de modification',
	'revreview-source'            => "Source de l'ébauche",
	'revreview-stable'            => 'Stable',
	'tooltip-ca-stable'           => 'Voir la version stable de cette page',
	'revreview-oldrating'         => 'Son pointage :',
	'revreview-noflagged'         => "Il n'y a pas de version révisée de cette page, sa [[{{MediaWiki:Validationpage}}|qualité]] est incertaine.",
	'stabilization-tab'           => '(aq)',
	'tooltip-ca-default'          => "Paramètres pour l'assurance-qualité",
	'validationpage'              => "{{ns:help}}:Validation de l'article",
	'revreview-quick-none'        => "'''Courante''' (pas de révisions évaluées)",
	'revreview-quick-see-quality' => "'''Courante'''. [[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} voir révision stable]] ($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} {{plural:$2|modification|modifications}}])",
	'revreview-quick-see-basic'   => "'''Courante'''. [[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} voir versions stables]] ($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} {{plural:$2|changement|changements}}])",
	'revreview-quick-basic'       => "'''[[{{MediaWiki:Validationpage}}|Vue]]'''. [[{{fullurl:{{FULLPAGENAMEE}}|stable=0}} voir révision courante]] ($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} {{plural:$2|modification|modifications}}])",
	'revreview-quick-quality'     => "'''[[{{MediaWiki:Validationpage}}|Qualité]]'''. [[{{fullurl:{{FULLPAGENAMEE}}|stable=0}} voir version courante]] ($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} {{plural:$2|changement|changements}}])",
	'revreview-newest-basic'      => "La [{{fullurl:{{FULLPAGENAMEE}}|stable=1}} dernière version vue] ([{{fullurl:Special:Stableversions|page={{FULLPAGENAMEE}}}} toutes les voir]) était [{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} approuvée] le ''$2''. [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3 {{plural:$3|changement|changements}}] {{plural:$3|demande|demandent}} une révision.",
	'revreview-newest-quality'    => "La [{{fullurl:{{FULLPAGENAMEE}}|stable=1}} dernière version de qualité] ([{{fullurl:Special:Stableversions|page={{FULLPAGENAMEE}}}} toutes les voir]) était [{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} approuvée] le ''$2''. [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3 {{plural:$3|changement|changements}}] {{plural:$3|demande|demandent}} une révision.",
	'revreview-basic'             => "C'est la dernière [[{{MediaWiki:Validationpage}}|version vue]], [{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} approuvée] le ''$2''. L'[{{fullurl:{{FULLPAGENAMEE}}|stable=0}} ébauche] peut être [{{fullurl:{{FULLPAGENAMEE}}|action=edit}} modifiée]; [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} {{PLURAL:$3|$3 changement attend|$3 changements attendent}}] une révision.",
	'revreview-quality'           => "C'est la dernière [[{{MediaWiki:Validationpage}}|version de qualité]], [{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} approuvée] le ''$2''. L'[{{fullurl:{{FULLPAGENAMEE}}|stable=0}} ébauche] peut être [{{fullurl:{{FULLPAGENAMEE}}|action=edit}} modifiée]; [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} {{PLURAL:$3|$3 changement attend|$3 changements attendent}}] une révision.",
	'revreview-static'            => "C'est une [[{{MediaWiki:Validationpage}}|version vue]] de '''[[:$3|$3]]''', [{{fullurl:Special:Log/review|page=$1}} approuvée] le ''$2''.",
	'revreview-note'              => '[[User:$1]] a écrit ces notes de révision :',
	'revreview-update'            => 'Prière de revoir les modifications faites à partir de la dernière version stable. Quelques images ou modèles sont mis à jour :',
	'revreview-update-none'       => 'Prière de revoir les modifications faites à partir de la dernière version stable.',
	'revreview-auto'              => '(automatique)',
	'revreview-auto-w'            => "Vous modifiez une version stable, toute modification sera '''automatiquement révisée'''. Demandez une prévisualisation avant de sauvegarder.",
	'revreview-auto-w-old'        => "Vous modifiez une vieille version, toute modification sera '''automatiquement révisée'''. Demandez une prévisualisation avant de sauvegarder.",
	'revreview-patrolled'         => 'La version sélectionnée de [[:$1|$1]] a été marquée comme patrouillée.',
	'hist-stable'                 => '[Version visualisée]',
	'hist-quality'                => '[qualité de la version]',
	'flaggedrevs'                 => 'Révisions marquées',
	'review-logpage'              => "Journal des révisions de l'article",
	'review-logpagetext'          => "C'est un journal des modifications pour [[{{MediaWiki:Makevalidate-page}}|l'approbation]] des révisions.",
	'review-logentry-app'         => 'Revue [[$1]]',
	'review-logentry-dis'         => 'Version dépréciée de [[$1]]',
	'review-logaction'            => 'Version ID $1',
	'stable-logpage'              => 'Journal des versions stables',
	'stable-logpagetext'          => "C'est le journal des modifications pour les [[{{MediaWiki:Validationpage}}|version stables]] des pages.",
	'stable-logentry'             => 'Les versions stables de [[$1]] sont paramétrées.',
	'stable-logentry2'            => 'Remettre à zéro le journal des versions stables de [[$1]]',
	'revisionreview'              => 'Revoir versions',
	'revreview-main'              => 'Vous devez choisir une version précise pour réviser. Voir [[Special:Unreviewedpages|Version non révisées]] pour une liste de pages.',
	'revreview-selected'          => "Version choisie de '''$1 :'''",
	'revreview-text'              => 'Les versions stables sont choisies par défaut, plutôt que les dernières versions.',
	'revreview-toolow'            => 'Pour les attributs ci-dessous, vous devez donner un pointage plus élevé que « non approuvé » pour que la version soit considérée revue. Pour déprécier une version, mettre tous les champs à « non approuvé ».',
	'revreview-flag'              => 'Évaluer cette version (#$1)',
	'revreview-legend'            => 'Évaluer le contenu de la version',
	'revreview-notes'             => 'Observations et notes à afficher :',
	'revreview-accuracy'          => 'Précision',
	'revreview-accuracy-0'        => 'Non approuvée',
	'revreview-accuracy-1'        => 'Vue',
	'revreview-accuracy-2'        => 'Précis',
	'revreview-accuracy-3'        => 'Bien sourcée',
	'revreview-accuracy-4'        => 'Remarquable',
	'revreview-depth'             => 'Profondeur',
	'revreview-depth-0'           => 'Non approuvée',
	'revreview-depth-1'           => 'De base',
	'revreview-depth-2'           => 'Modéré',
	'revreview-depth-3'           => 'Élevée',
	'revreview-depth-4'           => 'Remarquable',
	'revreview-style'             => 'Lisibilité',
	'revreview-style-0'           => 'Non approuvée',
	'revreview-style-1'           => 'Acceptable',
	'revreview-style-2'           => 'Bonne',
	'revreview-style-3'           => 'Concise',
	'revreview-style-4'           => 'Remarquable',
	'revreview-log'               => 'Commentaire au journal :',
	'revreview-submit'            => 'Sauvegarder revue',
	'revreview-changed'           => "'''L'action demandée n'a pu être accomplie pour cette version.'''
	
Un modèle ou une image peut avoir été demandé alors qu'aucune version précise n'était choisie. Cela peut survenir lorsque qu'un modèle (ou une image) remplace dynamiquement un autre modèle (ou une autre image) selon une variable qui dépend de la version de la page. Rafraîchir la page et reviser à nouveau celle-ci peut corriger ce problème.",
	'stableversions'              => 'Versions stables',
	'stableversions-title'        => 'Versions stables de « $1 »',
	'stableversions-leg1'         => "Dernières révisions revues d'une page",
	'stableversions-page'         => 'Nom de la page :',
	'stableversions-none'         => "« [[:$1]] » n'a pas de versions révisées.",
	'stableversions-list'         => 'La liste qui suit contient des versions de « [[:$1]] » qui ont été révisées :',
	'stableversions-review'       => "Révisée le ''$1'' par $2",
	'review-diff2stable'          => 'Différence entre la dernière version stable et les versions actuelles',
	'unreviewedpages'             => 'Pages non revues',
	'viewunreviewed'              => 'Lister les pages non révisées',
	'unreviewed-outdated'         => 'Afficher les pages qui ont des révisions faites à une version stable.',
	'unreviewed-category'         => 'Catégorie :',
	'unreviewed-diff'             => 'Modifications',
	'unreviewed-list'             => "Cette page liste les articles qui n'ont pas été révisés ou qui ont des révisions non vues.",
	'revreview-visibility'        => 'Cette page contient une [[{{MediaWiki:Validationpage}}|version stable]], qui peut être [{{fullurl:Special:Stabilization|page={{FULLPAGENAMEE}}}} configurée].',
	'stabilization'               => 'Stabilisation de la page',
	'stabilization-text'          => "Changer les paramètres ci-dessous pour ajuster l'affichage et la sélection de la version stable de [[:$1|$1]].",
	'stabilization-perm'          => "Votre compte n'a pas les droits pour changer les paramètres de la version stable. Voici les paramètres courants de [[:$1|$1]] :",
	'stabilization-page'          => 'Nom de la page :',
	'stabilization-leg'           => "Paramétrer la version stable d'une page",
	'stabilization-select'        => 'Comment la version stable est choisie',
	'stabilization-select1'       => 'La dernière version de qualité, sinon la dernière version vue',
	'stabilization-select2'       => 'La dernière révision vue',
	'stabilization-def'           => "Version affichée lors de l'affichage par défaut de la page",
	'stabilization-def1'          => 'La version stable, sinon la version courante',
	'stabilization-def2'          => 'La version courante',
	'stabilization-submit'        => 'Confirmer',
	'stabilization-notexists'     => "Il n'y a pas de page « [[:$1|$1]] », pas de paramétrage possible",
	'stabilization-notcontent'    => 'La page « [[:$1|$1]] » ne peut être révisée, pas de paramétrage possible',
	'stabilization-comment'       => 'Commentaire :',
	'stabilization-expiry'        => 'Expire :',
	'stabilization-sel-short'     => 'Priorité',
	'stabilization-sel-short-0'   => 'Qualité',
	'stabilization-sel-short-1'   => 'Nulle',
	'stabilization-def-short'     => 'Défaut',
	'stabilization-def-short-0'   => 'Courante',
	'stabilization-def-short-1'   => 'Stable',
	'stabilize_expiry_invalid'    => "Date d'expiration invalide.",
	'stabilize_expiry_old'        => "Cette durée d'expiration est déjà écoulée.",
	'stabilize-expiring'          => 'Expire le $1 (UTC)',
	'reviewedpages'               => 'Pages passées en revue',
	'reviewedpages-leg'           => 'Liste des pages passées en revue à un certain niveau',
	'reviewedpages-list'          => 'Les pages suivantes ont été passées en revue au niveau spécifié',
	'reviewedpages-none'          => 'Cette liste est vide',
	'reviewedpages-lev-0'         => 'Vu',
	'reviewedpages-lev-1'         => 'Qualité',
	'reviewedpages-lev-2'         => 'Mis en avant',
	'reviewedpages-all'           => 'versions passées en revue',
	'reviewedpages-best'          => 'Dernière révision la mieux notée',
);

