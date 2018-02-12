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
	'createDirs' => 'uploads/tx_hwtimporthandler, uploads/tx_hwtimporthandler/backups, uploads/tx_hwtimporthandler/imports',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'version' => '0.0.6',
	'constraints' => array(
		'depends' => array(
			'typo3' => '7.6.0-8.7.99',
            'php' => '5.4.0-7.2.99',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
    'autoload' => [
        'psr-4' => array(
            "Hwt\\HwtImporthandler\\" => "Classes/"
        )
    ],
);