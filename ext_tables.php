<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

/*
 * Set up backend module
 */
if (TYPO3_MODE === 'BE') {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
        'Hwt.' . $_EXTKEY,
        'web',
        'tx_hwtimporthandler_m1',
        '',
        Array ('Backend' => 'start, configure, import'),
        Array (
            'access' => 'user, group',
            'icon' => 'EXT:' . $_EXTKEY . '/ext_icon_orange.png',
            'labels' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_mod.xml',
        )
    );
}