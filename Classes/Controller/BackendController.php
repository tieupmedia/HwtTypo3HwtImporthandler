<?php

namespace Hwt\HwtImporthandler\Controller;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2015-2016 Heiko Westermann <hwt3@gmx.de>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Controller of backend module m1
 *
 * @package TYPO3
 * @subpackage tx_hwtimporthandler
 * @author Heiko Westermann <hwt3@gmx.de>
 */
class BackendController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

    // TypoScript settings
    protected $settings = array();
    // ModuleTS settings
    protected $moduleSettings = array();
    // id of selected page
    protected $id;
    // info of selected page
    protected $pageinfo;
    // paht to locallang
    protected $locallangPath = 'LLL:EXT:hwt_importhandler/Resources/Private/Language/locallang_mod.xml:';
 
    protected function initializeAction() {
        $this->id = (int)\TYPO3\CMS\Core\Utility\GeneralUtility::_GP('id');
        //$this->pageinfo = \TYPO3\CMS\Backend\Utility\BackendUtility::readPageAccess($this->id, $GLOBALS['BE_USER']->getPagePermsClause(1));
 
        $configurationManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Configuration\\BackendConfigurationManager');
 
        $this->settings = $configurationManager->getConfiguration(
            $this->request->getControllerExtensionName(),
            $this->request->getPluginName()
        );
        
        // get moduleTs
        $this->moduleSettings = $this->getModuleTs();
    }

    /**
     * action
     *
     * @return void
     */
    public function startAction() {
        $this->globals = array(
            'flashMessages' => $this->controllerContext->getFlashMessageQueue(),
            'locallangPath' => $this->locallangPath,
            //'pageId' => $this->id,
            //'moduleSettings' => $this->moduleSettings,
        );


        // add values to view
        $this->view->assignMultiple(array(
            'presets' => $this->getPresets()
        ));
    }

    /**
     * action
     *
     * @return void
     */
    public function configureAction() {
        $this->globals = array(
            'flashMessages' => $this->controllerContext->getFlashMessageQueue(),
            'locallangPath' => $this->locallangPath,
            //'pageId' => $this->id,
            //'moduleSettings' => $this->moduleSettings,
        );

        $preset = false;
        $hasError = false;
        $configurations = array();

        if ($this->request->hasArgument('preset') && $this->request->getArgument('preset')) {
            $preset = $this->request->getArgument('preset');

            // run all configurators
            if (is_array($this->moduleSettings['preset.'][$preset]['configurator.'])) {
                foreach ($this->moduleSettings['preset.'][$preset]['configurator.'] as $configuratorSetting) {
                    $configuratorClass = $configuratorSetting['class'];

                    $configurator = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance($configuratorClass);
                    if ( is_a($configurator, 'Hwt\HwtImporthandler\Configurator\AbstractConfigurator') ) {
                        /*if ( is_array($configuratorSetting['config.']) ) {
                            $configurator->init($configuratorSetting['config.']);
                        }*/
                        $configurator->init($this->globals, $configuratorSetting['config.']);
                        $configurations[] = $configurator->run();
                    }
                    //\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($configurator);
                }
            }
        } else {
            $this->controllerContext->getFlashMessageQueue()->enqueue(
                new \TYPO3\CMS\Core\Messaging\FlashMessage (
                    $GLOBALS['LANG']->sL($this->locallangPath . 'start.errorNoPreset'),
                    $GLOBALS['LANG']->sL($this->locallangPath . 'start.errorNoPresetHeader'),
                    \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR,
                    false
                )
            );
            $hasError = true;
        }


        // add values to view
        $this->view->assignMultiple(array(
            'preset' => $preset,
            'hasError' => $hasError,
            'configurations' => $configurations
        ));
    }
 
    /**
     * action
     *
     * @return void
     */
    public function importAction() {
        $this->globals = array(
            'flashMessages' => $this->controllerContext->getFlashMessageQueue(),
            'locallangPath' => $this->locallangPath,
            //'pageId' => $this->id,
            //'moduleSettings' => $this->moduleSettings,
        );

        $preset = false;
        $continue = $break = false;

        /*
         * Do inport
         * - if it is an initial import call
         * - or if an import should continue
         */
        if ( ( ($this->request->hasArgument('continue') && $this->request->hasArgument('importer')) ||
              $this->request->hasArgument('import') ) && $this->request->hasArgument('preset')
           ) {

            $preset = $this->request->getArgument('preset');
            $enable = false;

            // run all importers
            if (is_array($this->moduleSettings['preset.'][$preset]['importer.'])) {
                foreach ($this->moduleSettings['preset.'][$preset]['importer.'] as $importerKey => $importerSetting) {

                    // enable importing on intial call or if importer to continue matches
                    if ( (!$enable) && 
                         ($this->request->hasArgument('import') || ($this->request->getArgument('importer')==$importerKey)) ) {
                        $enable = true;
                    }

                    if ($enable) {
                        if ($break) {
                            $continue = $importerKey;
                            break;
                        }

                        $importerClass = $importerSetting['class'];

                        $importer = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance($importerClass);
                        if ( is_a($importer, 'Hwt\HwtImporthandler\Importer\AbstractImporter') ) {
                            /*if ( is_array($importerSetting['config.']) ) {
                                $importer->init($importerSetting['config.']);
                            }*/
                            //\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($this->globals);
                            $importer->init($this->globals, $importerSetting['config.']);
                            $importer->run();
                        }
                        if (!$importerSetting['config.']['continue']) {
                            $break = true;
                        }
                    }
                }
            }
        } else {
            $hasError = true;
        }


        // display message
        if (!$this->request->hasArgument('preset')) {
            $this->controllerContext->getFlashMessageQueue()->enqueue(
                new \TYPO3\CMS\Core\Messaging\FlashMessage (
                    $GLOBALS['LANG']->sL($this->locallangPath . 'start.errorNoPreset'),
                    $GLOBALS['LANG']->sL($this->locallangPath . 'start.errorNoPresetHeader'),
                    \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR,
                    false
                )
            );
        }


        // add values to view
        $this->view->assignMultiple(array(
            'hasError' => $hasError,
            'continue' => $continue,
            'preset' => $preset,
        ));
    }



    /*
     * get presets
     * ToDo: make extendable
     */
    protected function getPresets() {
        $presets = false;

        foreach ($this->moduleSettings['preset.'] as $key => $config) {
            $preset = new \stdClass();
            $preset->key = $key;
            $preset->name = $config['name'];
            $presets[] = $preset;
        }
        return $presets;
    }
    
    
    
    /*
     * get settings
     * ToDo: make extendable
     */
    protected function getModuleTs() {
        $return = false;
        $settings = file_get_contents(\TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName('typo3conf/ext/hwt_importhandler/Configuration/Settings/setup.txt'));
        if ($settings) {
            $tsParser =   \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\TypoScript\\Parser\\TypoScriptParser');
            $tsParser->parse($settings);
        }
        return $tsParser->setup['module.']['hwt_importhandler.']['settings.'];
    }
}