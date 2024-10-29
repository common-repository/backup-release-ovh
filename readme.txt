=== Backup release ovh ===
Contributors: Eoxia
Tags: ovh, release 2, release, backup, sauvegarde
Donate link: http://www.eoxia.com/
Requires at least: 3.2.1
Tested up to: 3.3.1
Stable tag: 0.2

Plugin de gestion des sauvegardes sur un serveur d&eacute;di&eacute;s ovh avec planification/rotation/restauration des sauvegardes

== Description ==

Avant de pouvoir utiliser le plugin vous devrez effectuer les r&eacute;glages, une fois ces r&eacute;glages effectu&eacute;s vous pourrez g&eacute;n&eacute;rer les fichiers "sh" permettant d'effectuer les sauvegardes. Vous devrez envoyer ces fichiers dans les dossiers que vous aurez d&eacute;finis, puis d&eacute;finir une tache cron qui permettra d'automatiser le lancement des sauvegardes (la commande &agrave; lancer est indiqu&eacute;e dans la page des options) 

== Installation ==

L'installation du plugin peut se faire de 2 fa&ccedil;ons :

* M&eacute;thode 1

1. T&eacute;l&eacute;chargez `backup_release_ovh_VX.X.X.zip`
2. Uploader le dossier `backup_release_ovh` dans le r&eacute;pertoire `/wp-content/plugins/`
3. Activer le plugin dans le menu `Extensions` de Wordpress

* M&eacute;thode 2

1. Rechercher le plugin "Backup release ovh" &agrave; partir du menu "Extensions" de Wordpress
2. Lancer l'installation du plugin


== Frequently Asked Questions ==

=	Un message m'indique qu'il existe une diff&eacute;rence entre les diff&eacute;rentes dates du serveur =

Ce message ne vous emp&ecirc;che pas d'utiliser le plugin, simplement il se peut que les dates enregistr&eacute;es ne soient pas coh&eacute;rentes. Vous pouvez d&eacute;j&agrave; v&eacute;rifier la configuration de wordpress dans l'onglet "R&eacute;glages >> G&eacute;n&eacute;ral -> Fuseau horaire". Si cela n'est pas suffisant alors vous devrez v&eacute;rifier votre serveur.

= La taille de la base de donn&eacute;es apr&egrave;s la sauvegarde est inf&eacute;rieure &agrave; la base de donn&eacute;es originale =

La sauvegarde de la base de donn&eacute;es est effectu&eacute;e par un dump mysql, le r&eacute;sultat est donc un fichier poss&eacute;dant l'extension sql. La taille affich&eacute;e pour la base de donn&eacute;es originale est la taille du dossier pr&eacute;sent sur le serveur. La comparaison ne peut &ecirc;tre faite directement, mais l'affichage de ces informations permet de v&eacute;rifier qu'il y a bien eu une sauvegarde.

= La page historique n'affiche aucun r&eacute;sultat alors qu'une sauvegarde a &eacute;t&eacute; effectu&eacute;e aujourd'hui =

Cette page liste les sauvegardes ant&eacute;rieures &agrave; la date du jour uniquement. Pour les sauvegardes effectu&eacute;es le jour m&ecirc;me, il faut aller sur la page r&eacute;sum&eacute;.


== Screenshots ==

1. Page de configuration du plugin (Partie 1 / 3)
2. Page de configuration du plugin (Partie 2 / 3)
3. Page de configuration du plugin (Partie 3 / 3)
4. Page de pr&eacute;sentation des domaines pr&eacute;sents sur le serveur avec l'url vers le site et le type de site (uniquement wordpress et magento en version 0.1)
5. Interface pour la configuration des domaines
6. Interface d'&eacute;dition de la configuration pour un domaine sp&eacute;cifique
7. Interface de listing des sauvegardes effectu&eacute;es pour le domaine s&eacute;lectionn&eacute;
8. Interface de listing des restaurations effectu&eacute;es pour le domaine s&eacute;lectionn&eacute;
9. R&eacute;sum&eacute; des sauvegardes effectu&eacute;es le jour m&ecirc;me

== Changelog ==


= Version 0.2 =

Corrections

* ST169 - Correction du nettoyage qui ne r&eacute;cup&eacute;rait pas la bonne variable pour l'identifiant du domaine &agrave; nettoyer 


= Version 0.1 =

* R&eacute;glages - D&eacute;finition des dossiers contenant les scripts de lancement des sauvegardes (*.sh et *.php)
* R&eacute;glages - D&eacute;finition des dossiers recevant les diff&eacute;rents &eacute;l&eacute;ments d&eacute;coulant des sauvegardes (Dossier de sauvegarde, logs)
* R&eacute;glages - Choix des dossiers &agrave; exclure automatiquement des sauvegardes
* R&eacute;glages - Choix des adresses emails devant recevoir les rapports des sauvegardes
* R&eacute;glages - D&eacute;finition de la temporisation entre la sauvegarde de chaque domaine
* R&eacute;sum&eacute; - Visualisation des sauvegardes effectu&eacute;es le jour m&ecirc;me (Liste des domaines &agrave; sauvegarder et R&eacute;sum&eacute; de la sauvegarde si elle a d&eacute;j&agrave; eu lieu)
* R&eacute;sum&eacute; - Visualisation des logs de la sauvegarde g&eacute;n&eacute;rale
* R&eacute;sum&eacute; - Visualisation de la liste des domaines sauvegard&eacute;s lors de la sauvegarde g&eacute;n&eacute;rale
* Domaines - Configuration de la p&eacute;riodicit&eacute; pour chaque domaine (ind&eacute;pendamment ou par lot)
* Domaines - Configuration de la p&eacute;riodicit&eacute; de la rotation des sauvegardes pour chaque domaine (Permet de d&eacute;finir combien de sauvegarde on veut conserver pour chaque domaine)
* Domaines - Visualisation du type de "moteur" pour le site (uniquement wordpress et magento en version 0.1)
* Domaines - Possibilit&eacute; d'exclure des domaines temporairement
* Domaines - Possibilit&eacute; de visualiser l'historique des sauvegardes pour chaque domaine
* Domaines - Possibilit&eacute; de visualiser l'historique des restauration pour chaque domaine (uniquement wordpress en version 0.1)
* Domaines - Affichage des informations concernant la sauvegarde: Taille des dossiers au moment de la sauvegarde (originaux et sauvegarde); Informations concernant la charge du serveur au moment de la sauvegarde
* Domaines - Visualisation des logs pour chaque sauvegarde et restauration
* Historique - Visualisation de l'historique des sauvegardes effectu&eacute;es (La sauvegarde du jour est exclue de cette liste)



==	Am&eacute;liorations futures ==

* Effectuer une sauvegarde manuelle d'un domaine en particulier &agrave; un moment donn&eacute;
* Restaurer les sauvegardes des sites effectu&eacute;s sous un autre moteur que wordpress
* Sauvegarder des dossiers syst&egrave;me (configurations apache/php/mysql)


== Upgrade Notice ==

= 0.1 =
Premi&egrave;re version du plugin

== Contactez l'auteur ==

dev@eoxia.com