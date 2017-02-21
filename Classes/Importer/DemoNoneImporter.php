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
 * Demo none importer
 *
 * @package TYPO3
 * @subpackage tx_hwtimporthandler
 * @author Heiko Westermann <hwt3@gmx.de>
 */

class DemoNoneImporter extends AbstractImporter {
    public function run() {
        $hasError = false;

        $this->globals['flashMessages']->enqueue(
            new \TYPO3\CMS\Core\Messaging\FlashMessage (
                $GLOBALS['LANG']->sL($this->globals['locallangPath'] . 'import.noticeEmptyImport'),
                $GLOBALS['LANG']->sL($this->globals['locallangPath'] . 'import.noticeEmptyImportHeader'),
                \TYPO3\CMS\Core\Messaging\FlashMessage::NOTICE,
                false
            )
        );
        
        $success = true;
        if ( $this->settings['error'] ) {
            $success = false;
        } else {
            $this->globals['flashMessages']->enqueue(
                new \TYPO3\CMS\Core\Messaging\FlashMessage (
                    $GLOBALS['LANG']->sL($this->globals['locallangPath'] . 'import.infoExecutedDemoNone'),
                    $GLOBALS['LANG']->sL($this->globals['locallangPath'] . 'import.infoExecutedDemoNone'),
                    \TYPO3\CMS\Core\Messaging\FlashMessage::INFO,
                    false
                )
            );
        }

        return $success;
    }
}