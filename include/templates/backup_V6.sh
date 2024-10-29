#!/bin/bash

STARTTIME=`date +%s`																					#	Compteur pour determiner la duree de l'operation												#

SERVEUR="#EOBU_NOMSERVEUR_EOBU#"															#	Nom du serveur, pour les messages erreurs ou autres											#
REPBACKUP=#EOBU_REPBACKUP_EOBU#
REPLOG=#EOBU_REPLOG_EOBU#
LISTEUSERS=${1-#EOBU_LISTEUSER_EOBU#}													#		Si on ne passe pas un nom de fichier en parametre alors on prend le parametre par defaut	#
REPLOGCLIENT=#EOBU_REPCLIENT_EOBU#

EMAIL=(#EOBU_REPORT_EMAIL_EOBU#)			#	Listes de emails devant recevoir les rapports (separes par un "ESPACE")	#

if [ ! -d $REPLOG/ ] >/dev/null
	then
	echo "Le dossier $REPLOG est manquant"
	mkdir -p $REPLOG
	echo "Le dossier $REPLOG est cree"
fi

if [ ! -d $REPLOGCLIENT/ ] >/dev/null
	then
	echo "Le dossier $REPLOGCLIENT est manquant"
	mkdir -p $REPLOGCLIENT
	echo "Le dossier $REPLOGCLIENT est cree"
fi

if [ ! -d $REPBACKUP/ ] >/dev/null
	then
	echo "Le dossier $REPBACKUP/est manquant"
	mkdir -p $REPBACKUP
	echo "Le dossier $REPBACKUP/ est cree"
fi

if [ ! -f $LISTEUSERS ] >/dev/null
then
	echo "Le fichier $LISTEUSERS est manquant,"
	echo "ce script va definir les utilisateurs à sauvegarder"
	echo "Je vous conseil d'aller verifier le contenu de ce fichier pour être sur que le"
	echo "contenu vous convienne."
	echo "Au prochain lancement, le script sauvegardera le contenu du fichier $LISTEUSERS"
	cd /home/ && ls#EOBU_EXCLUDEDDIR_EOBU#> $LISTEUSERS

	for i in ${EMAIL[@]}; do
		echo "Le fichier contenant la liste des domaines a sauvegarder $LISTEUSERS n existe pas $DATE" | mail -s "$SERVEUR liste inexistante $DATE" ${i}
	done
exit 0
fi

# Ajoute une ligne de separation dans les scripts de log permettant de lire et d'afficher plus facilement les resultats
echo -*-*-*-*-*-*-*-*-*-*-*-*-*-*-*- >> $REPLOG/backup_log.txt

### Log DATE du debut de backup ###
DATE=`date +%Y-%m-%d_%H:%M:%S`
echo $DATE debut des sauvegardes >> $REPLOG/backup_log.txt
echo "" >> $REPLOG/mail_backup.txt
echo "" >> $REPLOG/mail_backup.txt
echo $DATE Debut de la sauvegarde >> $REPLOG/mail_backup.txt

### Backup unitaire des fichiers sources de chaque client sur le serveur. Dans le dossier $REPBACKUP/NOMCLIENT ###
for USER in $(cat $LISTEUSERS); do
	sleep ${2-#EOBU_INTERVAL_BETWEEN_DOMAIN#}
	BACKUPDATE=`date +%Y%m%d-%H%M%S`
	STARTTIME_USER=`date +%s`
	TOPATSTART=`cat /proc/loadavg`

	wget -q -O - "#EOBU_DIR_TO_EXEPHP#/eobackup_log.php?domain_name=$USER&date=$BACKUPDATE&time=start&top=$TOPATSTART" >/dev/null 

	# Verification de l'existence du dossier recevant les sauvegardes des scripts des domaines
	if [ ! -d $REPBACKUP/$USER/$BACKUPDATE"_"$USER/script/ ] >/dev/null 
	then
		CHECK_DATE=`date +%Y-%m-%d_%H:%M:%S`
		echo $CHECK_DATE Le dossier $REPBACKUP/$USER/$BACKUPDATE"_"$USER/script/ est manquant >> $REPLOG/backup_log.txt
		mkdir -p $REPBACKUP/$USER/$BACKUPDATE"_"$USER/script
		CREATE_DATE=`date +%Y-%m-%d_%H:%M:%S`
		echo $CREATE_DATE Le dossier $REPBACKUP/$USER/$BACKUPDATE"_"$USER/script/ est cree >> $REPLOG/backup_log.txt
	fi

	# Verification de l'existence du dossier recevant les sauvegardes des base de donnees des domaines
	if [ ! -d $REPBACKUP/$USER/$BACKUPDATE"_"$USER/dump/ ] >/dev/null 
	then
		CHECK_DATE=`date +%Y-%m-%d_%H:%M:%S`
		echo $CHECK_DATE Le dossier $REPBACKUP/$USER/$BACKUPDATE"_"$USER/dump/ est manquant >> $REPLOG/backup_log.txt
		mkdir -p $REPBACKUP/$USER/$BACKUPDATE"_"$USER/dump
		CREATE_DATE=`date +%Y-%m-%d_%H:%M:%S`
		echo $CREATE_DATE Le dossier $REPBACKUP/$USER/$BACKUPDATE"_"$USER/dump/ est cree >> $REPLOG/backup_log.txt
	fi

	# Verification de l'existence du dossier contenant les logs des sauvegardes pour chaque utilisateur
	if [ ! -d $REPLOGCLIENT/$USER/ ] >/dev/null 
	then
		CHECK_DATE=`date +%Y-%m-%d_%H:%M:%S`
		echo $CHECK_DATE Le dossier $REPLOGCLIENT/$USER/ est manquant >> $REPLOG/backup_log.txt
		mkdir -p $REPLOGCLIENT/$USER
		CREATE_DATE=`date +%Y-%m-%d_%H:%M:%S`
		echo $CREATE_DATE Le dossier $REPLOGCLIENT/$USER/ est cree >> $REPLOG/backup_log.txt
	fi

	# Ajoute une ligne de separation dans les scripts de log permettant de lire et d'afficher plus facilement les resultats
	echo -*-*-*-*-*-*-*-*-*-*-*-*-*-*-*- >> $REPLOGCLIENT/$USER/backup_log_$USER.txt

	BACKUPSTATE="INIT"
	RSYNCORIWEIGHT="0"
	RSYNCBACKUPWEIGHT="0"
	#	On verifie l'existence du dossier contenant les scripts à sauvegarder
	if [ ! -d /home/$USER ] >/dev/null
	then
		CHECK_DATE=`date +%Y-%m-%d_%H:%M:%S`
		echo $CHECK_DATE Le domaine $USER est introuvable >> $REPLOG/backup_log.txt
		echo $CHECK_DATE Le domaine $USER est introuvable >> $REPLOGCLIENT/$USER/backup_log_$USER.txt
		BACKUPSTATE="ERREUR_DOSSIER"
		echo $CHECK_DATE Le domaine $USER est introuvable >> $REPLOG/mail_backup.txt
	else
		#	Sauvegarde des scripts du domaine
		rsync -avc --include=".htaccess" --delete-after /home/$USER $REPBACKUP/$USER/$BACKUPDATE"_"$USER/script

		TOTALTIME_USER_SEC=$((`date +%s`-$STARTTIME_USER))
		TOTALTIME_USER=$(($TOTALTIME_USER_SEC/60))
		RESULT=$?
		DATE=`date +%Y-%m-%d_%H:%M:%S`
		if [ $RESULT = 0 ]; then
			echo "$DATE Sauvegarde des fichiers de $USER effectuee Status: OK Duree de la backup: $TOTALTIME_USER minutes ($TOTALTIME_USER_SEC sec)" >> $REPLOGCLIENT/$USER/backup_log_$USER.txt
			echo "$DATE Sauvegarde des fichiers de $USER effectuee Status: OK Duree de la backup: $TOTALTIME_USER minutes ($TOTALTIME_USER_SEC sec)" >> $REPLOG/backup_log.txt
			BACKUPSTATE="OK"
			echo "$DATE Sauvegarde des fichiers de $USER effectuee Status: OK Duree de la backup: $TOTALTIME_USER minutes ($TOTALTIME_USER_SEC sec)" >> $REPLOG/mail_backup.txt
		else
			for i in ${EMAIL[@]}; do
				echo "$DATE Erreur sauvegarde des fichiers de $USER Erreur $RESULT" | mail -s "Sauvegarde serveur $SERVEUR : Erreur sauvegarde des fichiers le $DATE pour $USER" ${i}
			done
			echo $DATE Sauvegarde des fichiers de $USER Status: ERREUR - $RESULT >> $REPLOG/backup_log.txt
			echo $DATE Sauvegarde des fichiers de $USER Status: ERREUR - $RESULT >> $REPLOGCLIENT/$USER/backup_log_$USER.txt
			BACKUPSTATE="ERREUR_$RESULT"
			echo $DATE Sauvegarde des fichiers de $USER Status: ERREUR - $RESULT >> $REPLOG/mail_backup.txt
		fi

		RSYNCORIWEIGHT=`du -hs /home/$USER`
		RSYNCBACKUPWEIGHT=`du -hs $REPBACKUP/$USER/$BACKUPDATE"_"$USER/script`
	fi

	BACKUPSTATEBDD="INIT"
	DBORIWEIGHT="0"
	DBBACKUPWEIGHT="0"
	#	On verifie que le domaine courant possede bien une base de donnees
	if [ ! -d /home/mysql/$USER ] >/dev/null
	then
		CHECK_DATE=`date +%Y-%m-%d_%H:%M:%S`
		echo $CHECK_DATE Le domaine $USER ne possede pas de base de donnees >> $REPLOG/backup_log.txt
		echo $CHECK_DATE Le domaine $USER ne possede pas de base de donnees >> $REPLOGCLIENT/$USER/backup_log_$USER.txt
		BACKUPSTATEBDD="ERREUR_DOSSIER"
		echo $CHECK_DATE Le domaine $USER ne possede pas de base de donnees >> $REPLOG/mail_backup.txt
	else
		#	Sauvegarde de la base de donnees associee au domaine 
		mysqldump -B --user=#EOBU_BDDUSERNAME_EOBU# --password=#EOBU_BDDUSERPASS_EOBU# --host=localhost $USER > $REPBACKUP/$USER/$BACKUPDATE"_"$USER/dump/$BACKUPDATE"_"$USER.sql

		TOTALTIME_USER_SEC=$((`date +%s`-$STARTTIME_USER))
		TOTALTIME_USER=$(($TOTALTIME_USER_SEC/60))
		RESULT=$?
		DATE=`date +%Y-%m-%d_%H:%M:%S`
		if [ $RESULT = 0 ]; then
			echo "$DATE Sauvegarde de la base de donnees de $USER effectue Status: OK Duree de la backup: $TOTALTIME_USER minutes ($TOTALTIME_USER_SEC sec)" >> $REPLOGCLIENT/$USER/backup_log_$USER.txt
			echo "$DATE Sauvegarde de la base de donnees de $USER effectue Status: OK Duree de la backup: $TOTALTIME_USER minutes ($TOTALTIME_USER_SEC sec)" >> $REPLOG/backup_log.txt
			BACKUPSTATEBDD="OK"
			echo "$DATE Sauvegarde de la base de donnees de $USER effectue Status: OK Duree de la backup: $TOTALTIME_USER minutes ($TOTALTIME_USER_SEC sec)" >> $REPLOG/mail_backup.txt
		else
			for i in ${EMAIL[@]}; do
				echo "$DATE Erreur dump de la base de donnees de $USER Erreur $RESULT" | mail -s "Sauvegarde serveur $SERVEUR : Erreur dump de la base de donnees le $DATE pour $USER" ${i}
			done
			echo $DATE Sauvegarde de la bdd $USER Status: ERREUR - $RESULT >> $REPLOG/backup_log.txt
			echo $DATE Sauvegarde de la bdd $USER Status: ERREUR - $RESULT >> $REPLOGCLIENT/$USER/backup_log_$USER.txt
			BACKUPSTATEBDD="ERREUR_$RESULT"
			echo $DATE Sauvegarde de la bdd $USER Status: ERREUR - $RESULT >> $REPLOG/mail_backup.txt
		fi

		DBORIWEIGHT=`du -hs /home/mysql/$USER`
		DBBACKUPWEIGHT=`du -hs $REPBACKUP/$USER/$BACKUPDATE"_"$USER/dump`
	fi

	###	Log la taille des differents dossier apres la sauvegarde
	echo $RSYNCORIWEIGHT >> $REPLOGCLIENT/$USER/backup_weight_log_$USER.txt
	echo $RSYNCBACKUPWEIGHT >> $REPLOGCLIENT/$USER/backup_weight_log_$USER.txt
	echo $DBORIWEIGHT >> $REPLOGCLIENT/$USER/backup_weight_log_$USER.txt
	echo $DBBACKUPWEIGHT >> $REPLOGCLIENT/$USER/backup_weight_log_$USER.txt

	TOPATEND=`cat /proc/loadavg`

	wget -q -O - "#EOBU_DIR_TO_EXEPHP#/eobackup_log.php?domain_name=$USER&date=$BACKUPDATE&time=end&status_script=$BACKUPSTATE&status_bdd=$BACKUPSTATEBDD&top=$TOPATEND" >/dev/null 

	if [ -f $REPLOGCLIENT/$USER/backup_weight_log_$USER.txt ] >/dev/null
	then
		###	Supprime le fichier contenant les tailles des différents dossiers
		rm $REPLOGCLIENT/$USER/backup_weight_log_$USER.txt
	fi

	chown -R #EOBU_BDDUSERNAME_EOBU#:users $REPBACKUP/$USER/$BACKUPDATE"_"$USER/
done

### Calcul du temps de l'operation converti en minute ###
TOTALTIME_SEC=$((`date +%s`-$STARTTIME))
TOTALTIME=$(($TOTALTIME_SEC/60))

### Envoie par mail confirmation du bon deroulement de la backup ###
DATE=`date +%Y-%m-%d_%H:%M:%S`
echo $DATE Fin de la sauvegarde du serveur $SERVEUR >> $REPLOG/mail_backup.txt
for i in ${EMAIL[@]}; do
	echo "Sauvegarde serveur $SERVEUR : Fin de sauvegarde le $DATE Duree: $TOTALTIME minutes ($TOTALTIME_SEC sec) `cat $REPLOG/mail_backup.txt`" | mail -s "Sauvegarde serveur $SERVEUR : Fin $DATE Duree: $TOTALTIME minutes ($TOTALTIME_SEC sec)" ${i}
done

### Supprime le fichier contenant le log a envoyer par mail
rm $REPLOG/mail_backup.txt

### Log du bon deroulement de la backup ###
DATE=`date +%Y-%m-%d_%H:%M:%S`
echo "$DATE Sauvegarde serveur $SERVEUR : Fin de sauvegarde le $DATE Duree: $TOTALTIME minutes ($TOTALTIME_SEC sec)" >> $REPLOG/backup_log.txt

exit