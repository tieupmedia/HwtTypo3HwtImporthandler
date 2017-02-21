<?php

namespace Hwt\HwtImporthandler\Controller;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2016 Heiko Westermann <hwt3@gmx.de>
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

    // Module configuration
    protected $configuration;
    // ModuleTS settings
    protected $settings;
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
        $this->configuration = $configurationManager->getConfiguration();
        // get module settings
        $this->settings = $this->configuration['settings'];
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
            //'settings' => $this->settings,
        );

        // if quickstart was configured
        if ( $this->settings['defaultPreset'] ) {
            $configVars = $this->configureAction($this->settings['defaultPreset']);
            // add values to view
            $this->view->assignMultiple($configVars);
        }

        // add values to view
        $this->view->assignMultiple(array(
            'presets' => $this->getPresets()
        ));
    }

    /**
     * action
     *
     * @param string $defaultPreset
     * @return void
     */
    public function configureAction($defaultPreset=false) {
        $this->globals = array(
            'flashMessages' => $this->controllerContext->getFlashMessageQueue(),
            'locallangPath' => $this->locallangPath,
            //'pageId' => $this->id,
            //'settings' => $this->settings,
        );

        $preset = false;
        $hasError = false;
        $configurations = array();

        if ( ($this->request->hasArgument('preset') && ($preset = $this->request->getArgument('preset')) ) || 
             $defaultPreset 
           ) {
            if ( $defaultPreset ) {
                $preset = $defaultPreset;
            }
            //\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($preset);

            // run all configurators
            if ( is_array($this->settings['preset'][$preset]) &&
                 is_array($this->settings['preset'][$preset]['configurator'])
               ) {
                foreach ($this->settings['preset'][$preset]['configurator'] as $configuratorSetting) {
                    $configuratorClass = $configuratorSetting['class'];

                    $configurator = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance($configuratorClass);
                    if ( is_a($configurator, 'Hwt\HwtImporthandler\Configurator\AbstractConfigurator') ) {
                        /*if ( is_array($configuratorSetting['config']) ) {
                            $configurator->init($configuratorSetting['config']);
                        }*/
                        $configurator->init($this->globals, $this->request, $configuratorSetting['config']);
                        $configurations[] = $configurator->run();
                    }
                    //\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($configurator);
                }
            } else if ( !is_array($this->settings['preset'][$preset]) ) {
                $this->controllerContext->getFlashMessageQueue()->enqueue(
                    new \TYPO3\CMS\Core\Messaging\FlashMessage (
                        $GLOBALS['LANG']->sL($this->locallangPath . 'configure.errorPresetNotExist'),
                        $GLOBALS['LANG']->sL($this->locallangPath . 'configure.errorPresetNotExistHeader'),
                        \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR,
                        false
                    )
                );
                $hasError = true;  
            }
        } else {
            $this->controllerContext->getFlashMessageQueue()->enqueue(
                new \TYPO3\CMS\Core\Messaging\FlashMessage (
                    $GLOBALS['LANG']->sL($this->locallangPath . 'configure.errorPresetNotSelected'),
                    $GLOBALS['LANG']->sL($this->locallangPath . 'configure.errorPresetNotSelectedHeader'),
                    \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR,
                    false
                )
            );
            $hasError = true;
        }


        // add values to view
        $viewVariables = array(
            'preset' => $preset,
            'hasError' => $hasError,
            'configurations' => $configurations
        );
        
        if ( !$defaultPreset ) {
            $this->view->assignMultiple($viewVariables);
        } else {
            return $viewVariables;
        }
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
            //'settings' => $this->settings,
        );

        // Reset view params
        $preset = $hasError = false;
        // Init variables for importer controlling
        $continue = $break = false;
        $success = true;


        /*
         * Do import
         * - if it is an initial import call
         * - or if an import should continue
         */
        if ( ( 
                ($this->request->hasArgument('continue') && $this->request->hasArgument('importer')) ||
                $this->request->hasArgument('import') 
             ) && $this->request->hasArgument('preset')
           ) {
                // if correct arguments are submitted

            $preset = $this->request->getArgument('preset');
            
            // disable execution of importers by default
            $enable = false;

            // lopp through all configured importers
            if ( is_array($this->settings['preset'][$preset]['importer']) ) {
                //\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($this->settings['preset'][$preset]['importer']);

                $last = array_pop(array_keys($this->settings['preset'][$preset]['importer']));
                foreach ($this->settings['preset'][$preset]['importer'] as $importerKey => $importerSetting) {

                    // enable importing on first call or if importer param to continue matches the current key
                    if ( (!$enable) && 
                         ($this->request->hasArgument('import') || ($this->request->getArgument('importer')==$importerKey)) ) {
                        $enable = true;
                    }

                    if ($enable) {
                        if ($break) {
                                // if $break was set in last loop, just define with which importer
                                //to go ahead next an exit for this call
                            $continue = $importerKey;
                            break;
                        } else {
                                // run importer regular
                            $importerClass = $importerSetting['class'];

                            $importer = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance($importerClass);
                            if ( is_a($importer, 'Hwt\HwtImporthandler\Importer\AbstractImporter') ) {
                                /*if ( is_array($importerSetting['config']) ) {
                                    $importer->init($importerSetting['config']);
                                }*/
                                //\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($this->globals);
                                $importer->init($this->globals, $this->request, $importerSetting['config']);
                                $success = $importer->run();
                                
                                // if importer run returned false, exit now
                                if (!$success) {
                                    $this->controllerContext->getFlashMessageQueue()->enqueue(
                                        new \TYPO3\CMS\Core\Messaging\FlashMessage (
                                            $GLOBALS['LANG']->sL($this->locallangPath . 'import.errorAborted'),
                                            $GLOBALS['LANG']->sL($this->locallangPath . 'import.errorAbortedHeader'),
                                            \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR,
                                            false
                                        )
                                    );
                                    $hasError = true;
                                    break;
                                }
                            }
                            if (!$importerSetting['config']['continue']) {
                                $break = true;
                            }
                        }
                    }

                    // if last importer is executed without error
                    if ($importerKey === $last) {
                       $this->globals['flashMessages']->enqueue(
                            new \TYPO3\CMS\Core\Messaging\FlashMessage (
                                $GLOBALS['LANG']->sL($this->globals['locallangPath'] . 'import.infoCompleted'),
                                $GLOBALS['LANG']->sL($this->globals['locallangPath'] . 'import.infoCompletedHeader'),
                                \TYPO3\CMS\Core\Messaging\FlashMessage::INFO,
                                false
                            )
                        ); 
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

        foreach ($this->settings['preset'] as $key => $config) {
            $preset = new \stdClass();
            $preset->key = $key;
            $preset->name = $config['name'];
            $presets[] = $preset;
        }
        return $presets;
    }
}