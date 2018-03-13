<?php

########################################################################
# Extension Manager/Repository config file for ext: "cablan_tca_helper"
#
# Auto generated 06-04-2009 13:29
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'TCA Marker Helper',
	'description' => 'Plugin and service which helps to substitute markers for other extensions',
	'category' => 'services',
	'author' => 'Martin-Pierre Frenette',
	'author_email' => 'typo3@cablan.net',
	'shy' => '',
	'dependencies' => 'tt_news',
	'conflicts' => '',
	'priority' => '',
	'module' => '',
	'state' => 'alpha',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 1,
	'lockType' => '',
	'author_company' => '',
	'version' => '0.0.0',
	'constraints' => array(
		'depends' => array(
			'tt_news' => '',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:7:{s:9:"ChangeLog";s:4:"a25a";s:10:"README.txt";s:4:"ee2d";s:12:"ext_icon.gif";s:4:"1bdc";s:14:"ext_tables.php";s:4:"063e";s:36:"sv1/class.tx_cablantcahelper_sv1.php";s:4:"f1f1";s:19:"doc/wizard_form.dat";s:4:"6a8a";s:20:"doc/wizard_form.html";s:4:"c7c1";}',
);

?>