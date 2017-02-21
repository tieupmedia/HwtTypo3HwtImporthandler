<?php

########################################################################
# Extension Manager/Repository config file for ext "hwt_importhandler".
#
# Auto generated 23-11-2012 14:26
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'HWT Importhandler',
	'description' => 'Universal importer module for universal and flexible imports.',
	'category' => 'plugin',
	'author' => 'Heiko Westermann',
	'author_email' => 'hwt3@gmx.de',
    'author_company' => 'tie-up media',
	'shy' => '',
	'dependencies' => 'cms',
	'conflicts' => '',
	'priority' => '',
	'module' => '',
	'state' => 'alpha',
	'internal' => '',
	'uploadfolder' => TRUE,
	'createDirs' => 'uploads/tx_hwtimporthandler, uploads/tx_hwtimporthandler/imports',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'version' => '0.0.5',
	'constraints' => array(
		'depends' => array(
			'typo3' => '6.2.0-7.6.99',
            'php' => '5.3.0-7.0.99',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:11:{s:9:"ChangeLog";s:4:"80be";s:12:"ext_icon.gif";s:4:"1bdc";s:14:"ext_tables.php";s:4:"d440";s:10:"README.txt";s:4:"ee2d";s:19:"doc/wizard_form.dat";s:4:"4688";s:20:"doc/wizard_form.html";s:4:"c8db";s:13:"mod1/conf.php";s:4:"691e";s:14:"mod1/index.php";s:4:"bdf5";s:18:"mod1/locallang.xml";s:4:"b5bc";s:22:"mod1/locallang_mod.xml";s:4:"db06";s:19:"mod1/moduleicon.gif";s:4:"8074";}',
);