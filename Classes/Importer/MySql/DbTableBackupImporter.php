<?php

namespace Hwt\HwtImporthandler\Importer\MySql;

use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2017 Heiko Westermann <hwt3@gmx.de>
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
 * Table backup importer
 *
 * @package TYPO3
 * @subpackage tx_hwtimporthandler
 * @author Heiko Westermann <hwt3@gmx.de>
 */


class DbTableBackupImporter extends \Hwt\HwtImporthandler\Importer\AbstractImporter {

    /**
     * Run the whole importer
     *
     * @return bool  The success flag
     */
    public function run() {

        if ( $this->settings['tablesToBackup'] && ($this->settings['tablesToBackup']!==' ') ) {            
            /*
             * Check, if all database tables exist
             */
            if ( $this->settings['abortOnMissingTable']==='0' ) {
                    // check without stopping importer on failure
                $resultDbTablesExist = $this->checkDbTablesExist($this->settings['tablesToBackup'], false);
            } else {
                    // check and stop importer on failure
                $resultDbTablesExist = $this->checkDbTablesExist($this->settings['tablesToBackup']);
            }


            /*
             * Do dumping
             */
            if ( $resultDbTablesExist ) {
                    // if all tables exist

                $this->backupTables($this->settings['tablesToBackup']);
            }
        } else {
                // No table(s) configured to dump
            // Error?
            $this->hasError = true;
        }

        // evaluate if importer completed with success and return
        $success = false;
        if (!$this->hasError) {
            $success = true;
        }
        return $success;
    }



    /**
     * Check, if db tables exist
     *
     * @return bool  Return true/false, if all dbtables exist
     */
    protected function checkDbTablesExist($dbTables, $abortOnMissingTable=true) {
        $success = true;

        $dbTables = explode(',', $dbTables);
        foreach($dbTables as $table) {
            $schemaManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\ConnectionPool::class)
                ->getConnectionForTable($table)
                ->getSchemaManager();

            $result = $schemaManager->tablesExist(array($table));

            /*
             * Error handling
             */
            if( !$result && $abortOnMissingTable) {
                $this->hasError = true;
                $success = false;

                $this->globals['flashMessages']->enqueue(
                    new FlashMessage (
                        LocalizationUtility::translate(
                            $this->locallangPath . 'importerDbDump.errorTableNotExist',
                            $this->extKey,
                            array($table)
                        ),
                        LocalizationUtility::translate(
                            $this->locallangPath . 'importerDbDump.errorTableNotExistHeader',
                            $this->extKey
                        ),
                        FlashMessage::ERROR
                    )
                );
            } elseif ( !$result ) {
                $success = false;
                
                $this->globals['flashMessages']->enqueue(
                    new FlashMessage (
                        LocalizationUtility::translate(
                            $this->locallangPath . 'importerDbDump.warningTableNotExist',
                            $this->extKey,
                            array($table)
                        ),
                        LocalizationUtility::translate(
                            $this->locallangPath . 'importerDbDump.warningTableNotExistHeader',
                            $this->extKey
                        ),
                        FlashMessage::WARNING
                    )
                );
            }
        }

        return $success;
    }



    /**
     * Backup tables in database
     */
    protected function backupTables($dbTables) {

        $error = 0;

        $dbTables = explode(',', $dbTables);
        //$dbTables[] = 'test_to_fail';
        foreach ($dbTables as $dbTable) {
            // This didn't transfer auto_increment
            //$resultCopy = $GLOBALS['TYPO3_DB']->sql_query('CREATE TABLE ' . $this->getBackupTableName($dbTable) . ' SELECT * FROM ' . $dbTable);

            $backupTable = $this->getBackupTableName($dbTable);
            $resultCopy = $GLOBALS['TYPO3_DB']->sql_query('CREATE TABLE ' . $backupTable . ' LIKE ' . $dbTable);
            if ($resultCopy) {
                $resultCopy = $GLOBALS['TYPO3_DB']->sql_query('INSERT INTO ' . $backupTable . ' SELECT * FROM ' . $dbTable);
            }


            // if everything went right, set error to false
            if (!$resultCopy)  {
                $error = 1;
                break;
            }
        }

        if( $error===0 ) {
            $this->globals['flashMessages']->enqueue(
                new FlashMessage(
                    LocalizationUtility::translate(
                        $this->locallangPath . 'importerDbTableBackup.okCreateBackup',
                        $this->extKey
                    ),
                    LocalizationUtility::translate(
                        $this->locallangPath . 'importerDbTableBackup.okCreateBackupHeader',
                        $this->extKey
                    ),
                    FlashMessage::OK
                )
            );
        } else {
            $this->hasError = true;

            $this->globals['flashMessages']->enqueue(
                new FlashMessage(
                    LocalizationUtility::translate(
                        $this->locallangPath . 'importerDbTableBackup.errorCreateBackup',
                        $this->extKey,
                        array($dbTable)
                    ),
                    LocalizationUtility::translate(
                        $this->locallangPath . 'importerDbTableBackup.errorCreateBackupHeader',
                        $this->extKey
                    ),
                    FlashMessage::ERROR
                )
            );
        }

    }





    /**
     * @param string $dbTable  The name of the origin table
     * @return string
     */
    protected function getBackupTableName($dbTable) {
        return $dbTable . '_' . date('Y_m_d_His');
    }
}
