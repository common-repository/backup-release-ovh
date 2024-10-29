<?php
/**
* Plugin database structure management
* 
* Define database structure
* @author Eoxia <dev@eoxia.com>
* @version 0.1
* @package backup_release_ovh
* @subpackage librairies
*/

	global $wpdb;

	$eobackup_update_way = array();
	$eobackup_table = array();
	$eobackup_old_table = array();
	$eobackup_table_change = array();
	$eobackup_table_structure_change = array();

{/*	Tables version 0	*/
	$eobackup_db_version = 0;
	$eobackup_update_way[$eobackup_db_version] = 'versionned';

	{/*	TABLE EOBU_DBT_DOMAIN	*/
		$t = EOBU_DBT_DOMAIN;
		$eobackup_table[$eobackup_db_version][$t] = 
"CREATE TABLE {$t} (
  `id` int(10) NOT NULL auto_increment,
  `status` enum('valid','moderated','deleted','excluded') collate utf8_unicode_ci NOT NULL default 'valid',
	`creation_date` datetime NOT NULL,
  `last_update_date` datetime NOT NULL,
	`last_backup` date NOT NULL,
	`next_backup` date NOT NULL,
	`start_date` date NOT NULL,
  `name` char(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `status` (`status`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
	}

	{/*	TABLE EOBU_DBT_CALENDAR	*/
		$t = EOBU_DBT_CALENDAR;
		$eobackup_table[$eobackup_db_version][$t] = 
"CREATE TABLE {$t} (
  `id` int(10) NOT NULL auto_increment,
  `status` enum('valid','moderated','deleted') collate utf8_unicode_ci NOT NULL default 'valid',
	`creation_date` datetime NOT NULL,
  `last_update_date` datetime NOT NULL,
	`backup_type` enum('standard', 'special', 'manual') collate utf8_unicode_ci NOT NULL default 'standard',
  `db_backup_type` enum('none','dump','dumpandsync') collate utf8_unicode_ci NOT NULL default 'dump',
	`domain_id` int(10) unsigned NOT NULL,
	`interval_type` enum('day', 'week', 'month', 'year') collate utf8_unicode_ci NOT NULL default 'week',
	`interval` int(10) unsigned NOT NULL default '1',
	`backup_total_nb` int(10) unsigned NOT NULL default '5',
  `start_date` date NOT NULL,
	`special_interval` char(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `domain_bu_type` (`domain_id`,`backup_type`),
  KEY `status` (`status`),
  KEY `backup_type` (`backup_type`),
  KEY `domain_id` (`domain_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
	}

	{/*	TABLE EOBU_DBT_DOMAIN_HISTORY	*/
		$t = EOBU_DBT_DOMAIN_HISTORY;
		$eobackup_table[$eobackup_db_version][$t] = 
"CREATE TABLE {$t} (
  `id` int(10) NOT NULL auto_increment,
  `status` enum('valid','moderated','deleted') collate utf8_unicode_ci NOT NULL default 'valid',
  `creation_date` datetime NOT NULL,
  `last_update_date` datetime NOT NULL,
  `history_type` enum('backup','cleanup','restore') collate utf8_unicode_ci NOT NULL default 'backup',
  `parent_backup_id` int(10) unsigned NOT NULL,
  `domain_id` int(10) unsigned NOT NULL,
  `start` datetime NOT NULL,
  `end` datetime NOT NULL,
  `file_status` char(15) collate utf8_unicode_ci NOT NULL,
  `db_status` char(15) collate utf8_unicode_ci NOT NULL,
  `result` char(255) collate utf8_unicode_ci NOT NULL,
  `log` longtext collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `status` (`status`),
  KEY `history_type` (`history_type`),
  KEY `domain_id` (`domain_id`),
  KEY `parent_backup_id` (`parent_backup_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
	}
}

{/*	Tables version 1	*/
	$eobackup_db_version = 1;
	$eobackup_update_way[$eobackup_db_version] = 'full';
}