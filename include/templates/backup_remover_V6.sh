#!/bin/bash

STARTTIME=`date +%s`																					#	Compteur pour determiner la duree de l'operation												#

SERVEUR="#EOBU_NOMSERVEUR_EOBU#"															#	Nom du serveur, pour les messages erreurs ou autres											#
REPLOG=#EOBU_REPLOG_EOBU#
REPBACKUP=#EOBU_REPBACKUP_EOBU#
LISTEUSERS=$1

EMAIL=(#EOBU_REPORT_EMAIL_EOBU#)			#	Listes de emails devant recevoir les rapports (separes par un "ESPACE")	#

if [ ! -d $REPLOG/ ] >/dev/null
	then
	echo "Le dossier $REPLOG est manquant"
	mkdir -p $REPLOG
	echo "Le dossier $REPLOG est cree"
fi

# Ajoute une ligne de separation dans les scripts de log permettant de lire et d'afficher plus facilement les resultats
echo -*-*-*-*-*-*-*-*-*-*-*-*-*-*-*- >> $REPLOG/backup_remover_log.txt

#	Si le dossier contenant les sauvegardes existe alors on commence la vérification pour le nettoyage sinon on ne fait rien	#
if [ -d $REPBACKUP/ ] && [ $REPBACKUP/ != / ] && [ $REPBACKUP/ != /bin/ ] && [ $REPBACKUP/ != /boot/ ] && [ $REPBACKUP/ != /dev/ ] && [ $REPBACKUP/ != /etc/ ] && [ $REPBACKUP/ != /home/ ] && [ $REPBACKUP/ != /lib/ ] && [ $REPBACKUP/ != /lib32/ ] && [ $REPBACKUP/ != /lib64/ ] && [ $REPBACKUP/ != /mnt/ ] && [ $REPBACKUP/ != /proc/ ] && [ $REPBACKUP/ != /root/ ] && [ $REPBACKUP/ != /sbin/ ] && [ $REPBACKUP/ != /sys/ ] && [ $REPBACKUP/ != /usr/ ] && [ $REPBACKUP/ != /var/ ] >/dev/null
then
	#	Si le fichier de suppression des anciennes version des sauvegardes n'exite pas on arrete l'execution du script
	if [ ! -f $LISTEUSERS ] >/dev/null
	then
		DATE=`date +%Y-%m-%d_%H:%M:%S`
		echo "$DATE Le fichier contenant la liste des utilisateurs a supprimer ($LISTEUSERS) est introuvable" >> $REPLOG/backup_remover_log.txt
		for i in ${EMAIL[@]}; do
			echo "Le fichier contenant la liste des domaines a supprimer $LISTEUSERS n existe pas $DATE" | mail -s "$SERVEUR liste de nettoyage inexistante $DATE" ${i}
		done

		exit 0
	fi

	### Log debut du nettoyage ###
	DATE=`date +%Y-%m-%d_%H:%M:%S`
	echo $DATE debut nettoyage des sauvegardes >> $REPLOG/backup_remover_log.txt
	echo "" >> $REPLOG/mail_backup.txt
	echo "" >> $REPLOG/mail_backup.txt
	echo $DATE Debut du nettoyage des sauvegardes >> $REPLOG/mail_backup.txt

	### Backup unitaire des fichiers sources de chaque client sur le serveur. Dans le dossier $REPBACKUP/NOMCLIENT ###
	for USER in $(cat $LISTEUSERS); do
		sleep ${2-#EOBU_INTERVAL_BETWEEN_DOMAIN#}
		STARTTIME_USER=`date +%s`
		TOPATSTART=`cat /proc/loadavg`

		wget -q -O - "#EOBU_DIR_TO_EXEPHP#/eobackup_log.php?action=remove_old_backup&domain_name=$USER&time=start&top=$TOPATSTART" >/dev/null 

		BACKUPREMOVESTATE="INIT"
		# Verification de l'existence du dossier recevant les sauvegardes des scripts des domaines
		if [ -d $REPBACKUP/$USER/script/ ] || [ -d $REPBACKUP/$USER/dump/ ] >/dev/null 
		then
			#	Sauvegarde des scripts du domaine
			rm -r $REPBACKUP/$USER/

			TOTALTIME_USER_SEC=$((`date +%s`-$STARTTIME_USER))
			TOTALTIME_USER=$(($TOTALTIME_USER_SEC/60))
			RESULT=$?
			DATE=`date +%Y-%m-%d_%H:%M:%S`
			if [ $RESULT = 0 ]; then
				echo "$DATE Suppression de la sauvegarde $USER effectuee Status: OK Duree du nettoyage: $TOTALTIME_USER minutes ($TOTALTIME_USER_SEC sec)" >> $REPLOG/backup_remover_log.txt
				BACKUPREMOVESTATE="OK"
				echo $DATE Le dossier $USER a ete supprime >> $REPLOG/mail_backup.txt
			else
				for i in ${EMAIL[@]}; do
					echo "Erreur nettoyage du dossier $USER sur disque usb le $DATE. Erreur $RESULT" | mail -s "Nettoyage serveur $SERVEUR : Erreur nettoyage sur le disque usb le $DATE pour $USER" ${i}
				done
				echo $DATE Nettoyage des sauvegardes de $USER Status: ERREUR - $RESULT >> $REPLOG/backup_remover_log.txt
				BACKUPREMOVESTATE="ERREUR_$RESULT"
				echo "$DATE Le dossier $USER n'a pas pu etre supprime Erreur - $RESULT" >> $REPLOG/mail_backup.txt
			fi
		else
			CHECK_DATE=`date +%Y-%m-%d_%H:%M:%S`
			echo $CHECK_DATE Les dossiers $REPBACKUP/$USER/script/ et $REPBACKUP/$USER/dump/ sont manquants. Le nettoyage ne peut avoir lieu >> $REPLOG/backup_remover_log.txt
			echo "$CHECK_DATE Les dossiers $REPBACKUP/$USER/script/ et $REPBACKUP/$USER/dump/ sont manquants. Le nettoyage ne peut avoir lieu" >> $REPLOG/mail_backup.txt
		fi

		wget -q -O - "#EOBU_DIR_TO_EXEPHP#/eobackup_log.php?action=remove_old_backup&domain_name=$USER&time=end&state=$BACKUPREMOVESTATE" >/dev/null 
	done

	### Calcul du temps de l'operation converti en minute ###
	TOTALTIME_SEC=$((`date +%s`-$STARTTIME))
	TOTALTIME=$(($TOTALTIME_SEC/60))

	### Envoie par mail confirmation du bon deroulement de la backup ###
	DATE=`date +%Y-%m-%d_%H:%M:%S`
	echo $DATE Fin du nettoyage des sauvegardes du serveur $SERVEUR >> $REPLOG/mail_backup.txt
	for i in ${EMAIL[@]}; do
		echo "Nettoyage sauvegarde serveur $SERVEUR : Fin du nettoyage des sauvegardes sur le disque usb le $DATE Duree: $TOTALTIME minutes ($TOTALTIME_SEC sec)  `cat $REPLOG/mail_backup.txt`" | mail -s "Nettoyage serveur $SERVEUR : Fin $DATE Duree: $TOTALTIME minutes ($TOTALTIME_SEC sec)" ${i}
	done

	### Supprime le fichier contenant le log a envoyer par mail
	rm $REPLOG/mail_backup.txt

	### Log du bon deroulement de la backup ###
	DATE=`date +%Y-%m-%d_%H:%M:%S`
	echo "$DATE nettoyage des sauvegardes serveur $SERVEUR : Fin du nettoyage des sauvegardes sur le disque usb le $DATE Duree: $TOTALTIME minutes ($TOTALTIME_SEC sec)" >> $REPLOG/backup_remover_log.txt

else
	for i in ${EMAIL[@]}; do
		echo "Nettoyage sauvegarde serveur $SERVEUR : Le dossier contenant les sauvegardes n existe pas" | mail -s "Nettoyage serveur $SERVEUR : Erreur dossier sauvegarde" ${i}
	done

	### Log du bon deroulement de la backup ###
	DATE=`date +%Y-%m-%d_%H:%M:%S`
	echo "$DATE nettoyage des sauvegardes serveur $SERVEUR : Le dossier contenant les sauvegardes n existe pas" >> $REPLOG/backup_remover_log.txt
fi

exit