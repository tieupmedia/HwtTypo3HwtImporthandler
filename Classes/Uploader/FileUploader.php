<?php

namespace Hwt\HwtImporthandler\Uploader;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2015 Heiko Westermann <hwt3@gmx.de>
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
 * File Uploader
 *
 * @package TYPO3
 * @subpackage tx_importhandler
 * @author Heiko Westermann <hwt3@gmx.de>
 */
class FileUploader extends \Hwt\HwtImporthandler\Component\AbstractComponent {
    protected $uploadDir = 'uploads/tx_hwtimporthandler';

	/**
	 * Store file
	 */
	public function run() {
        //\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($_FILES);

        $fileName = $_FILES['tx_hwtimporthandler_web_hwtimporthandlertxhwtimporthandlerm1']['name']['file'];
        $fileTmpName = $_FILES['tx_hwtimporthandler_web_hwtimporthandlertxhwtimporthandlerm1']['tmp_name']['file'];
        
        if ($fileName && $fileTmpName) {
        
            $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');

            $basicFileUtility = $objectManager->get('TYPO3\CMS\Core\Utility\File\BasicFileUtility');
		
            $absFileName = $basicFileUtility->getUniqueName(
                $fileName, 
                \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName($this->uploadDir)
            );
            //\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($absFileName);
		
            $realFileName = str_replace(\TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName($this->uploadDir), '', $absFileName);
            $copySucces = \TYPO3\CMS\Core\Utility\GeneralUtility::upload_copy_move($fileTmpName, $absFileName);
            //\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($copySucces);
            if ($copySucces) {
                return $realFileName;
            }
        }
	}

    public function setUploadDir($dir) {
        $this->uploadDir = $dir;
    }
}