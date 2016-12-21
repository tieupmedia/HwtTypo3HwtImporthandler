<?php

namespace Hwt\HwtImporthandler\Configurator;

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
 * Local file configurator
 *
 * @package TYPO3
 * @subpackage tx_hwtimporthandler
 * @author Heiko Westermann <hwt3@gmx.de>
 */

class LocalFileConfigurator extends AbstractConfigurator {
    protected $partialName = 'LocalFile';

    public function run() {
        $configuration = parent::run();
        $importFilesPath = PATH_site . $this->settings['importPath'];
        $configuration['localfile']['options'] = $this->getImportFiles($importFilesPath);
        return $configuration;
    }



    protected function getImportFiles($path) {
        $return = array();
        if ( $handle = opendir($path) ) {
            while ( ($file=readdir($handle)) !== false) {
                if ( is_file($path . $file) ) {
                    $return[$file] = $file;
                } 
            }
            closedir($handle);
        }
        return $return;
    }
}