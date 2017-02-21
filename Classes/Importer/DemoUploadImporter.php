<?php

namespace Hwt\HwtImporthandler\Importer;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2016-2017 Heiko Westermann <hwt3@gmx.de>
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
 * Demo importer
 *
 * @package TYPO3
 * @subpackage tx_hwtimporthandler
 * @author Heiko Westermann <hwt3@gmx.de>
 */

class DemoUploadImporter extends AbstractImporter {
    public function run() {
        $hasError = false;
        
        // ToDo: Validate configuration
        if ( $this->request->hasArgument('localfile') ) {
            $this->globals['flashMessages']->enqueue(
                new \TYPO3\CMS\Core\Messaging\FlashMessage (
                    $GLOBALS['LANG']->sL($this->request->getArgument('localfile') . ' in ' . $this->settings['localPath']),
                    $GLOBALS['LANG']->sL($this->globals['locallangPath'] . 'import.noticeImportedLocalFileHeader'),
                    \TYPO3\CMS\Core\Messaging\FlashMessage::INFO,
                    false
                )
            );    
        }

        // Upload file if requested
        if (
            is_array($_FILES['tx_hwtimporthandler_web_hwtimporthandlertxhwtimporthandlerm1']) && 
            $_FILES['tx_hwtimporthandler_web_hwtimporthandlertxhwtimporthandlerm1']['name']['file']
           ) {
            $uploader = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Hwt\\HwtImporthandler\\Uploader\\FileUploader');
            $uploader->setUploadDir($this->settings['uploadPath']);
            $fileName = $uploader->run();

            if ($fileName) {
                $this->globals['flashMessages']->enqueue(
                    new \TYPO3\CMS\Core\Messaging\FlashMessage (
                        $GLOBALS['LANG']->sL($fileName . ' in ' . $this->settings['uploadPath']),
                        $GLOBALS['LANG']->sL($this->globals['locallangPath'] . 'import.noticeImportedFileHeader'),
                        \TYPO3\CMS\Core\Messaging\FlashMessage::NOTICE,
                        false
                    )
                );
                $success = true;
            }
        } else {
            $this->globals['flashMessages']->enqueue(
                new \TYPO3\CMS\Core\Messaging\FlashMessage (
                    $GLOBALS['LANG']->sL($this->globals['locallangPath'] . 'import.errorNoFile'),
                    $GLOBALS['LANG']->sL($this->globals['locallangPath'] . 'import.errorNoFileHeader'),
                    \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR,
                    false
                )
            );
            $success = false;
        }

        if ($success) {
            // Do next operation
        } else {
            $hasError = true;
        }
        
        return $success;
    }
}