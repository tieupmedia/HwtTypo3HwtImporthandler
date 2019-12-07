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
 * Database dump importer
 *
 * @package TYPO3
 * @subpackage tx_hwtimporthandler
 * @author Heiko Westermann <hwt3@gmx.de>
 */


class DbDumpImporter extends \Hwt\HwtImporthandler\Importer\AbstractImporter {

    //protected $backupFileName;
    //protected $backupDirectory;

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

                /*if ( $this->settings['pathToMysqlDump'] ) {
                        // if no valid path to mysqldump is set
                    $this->settings['pathToMysqlDump'] = false;
                }*/
                $this->createDbDumpFile($this->settings['tablesToBackup'], $this->settings['pathToMysqlDump']);
            }
        } else {
                // No table(s) configured to dump
            // Error?
            $this->hasError = true;
        }

        /*$this->tablesToBackup = $this->settings['tablesToBackup'];
        if( $this->checkDBTablesExist($this->tablesToBackup) ) {
            $this->setBackupFileName($this->settings['backupFileNamePrefix']);
            $this->setBackupDirectory($this->settings['backupDirectory']);

            $this->createDbBackupFile($this->settings['pathMysqlDump']);
        }*/

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
            $queryBuilder = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\ConnectionPool::class)
                ->getConnectionForTable($table)
                ->createQueryBuilder();
            
            $statement = $queryBuilder->select('uid')->from($table);
            $result = $statement->fetchAll();

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
     * Create a backup file with mysqldump
     */
    protected function createDbDumpFile($dbTables, $pathToMysqlDump=false) {

        /*
         * Try to determine path to mysqldump
         */
        //$pathToMysqlDump = '/usr/bins/mysqldump';
        if ( $pathToMysqlDump ) {
                // if path to mysqldump was configured, test it

            exec($pathToMysqlDump.' --help', $output, $error);
            if ( $error!==0 ) {
                    // reset on error
                $pathToMysqlDump = false;
            }
        } elseif ( is_executable(exec('which mysqldump', $output, $error)) ) {
                // elseif path to mysqldump could be determined

            $pathToMysqlDump = $output[0];
        }


        /*
         * Try to execute backup
         */
        if ( $pathToMysqlDump ) {
                // if mysqldump command is available

            $dbTables = str_replace(',', ' ', $dbTables);
            $backupPath = $this->getBackupDirectory() . $this->getBackupFileName();
            $command = $pathToMysqlDump . 
                ' --add-drop-table ' . 
                ' --opt -h ' . $GLOBALS['TYPO3_CONF_VARS']['DB']['host'] . 
                ' --user=' . $GLOBALS['TYPO3_CONF_VARS']['DB']['username'] . 
                ' --password=' . $GLOBALS['TYPO3_CONF_VARS']['DB']['password'] . 
                ' --databases ' . $GLOBALS['TYPO3_CONF_VARS']['DB']['database'] . 
                ' --tables ' . $dbTables . 
                ' > ' . $backupPath;

            escapeshellcmd(exec($command, $output, $error));

            if( $error===0 ) {
                $this->globals['flashMessages']->enqueue(
                    new FlashMessage(
                        LocalizationUtility::translate(
                            $this->locallangPath . 'importerDbDump.okCreateMysqldump',
                            $this->extKey,
                            array($backupPath)
                        ),
                        LocalizationUtility::translate(
                            $this->locallangPath . 'importerDbDump.okCreateMysqldumpHeader',
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
                            $this->locallangPath . 'importerDbDump.errorCreateMysqldump',
                            $this->extKey,
                            array($backupPath)
                        ),
                        LocalizationUtility::translate(
                            $this->locallangPath . 'importerDbDump.errorCreateMysqldumpHeader',
                            $this->extKey
                        ),
                        FlashMessage::ERROR
                    )
                );
            }
        } else {
                // if mysqldump command is missing
            $this->hasError = true;

            $this->globals['flashMessages']->enqueue(
                new FlashMessage(
                    LocalizationUtility::translate(
                        $this->locallangPath . 'importerDbDump.errorNoMysqldump',
                        $this->extKey
                    ),
                    LocalizationUtility::translate(
                        $this->locallangPath . 'importerDbDump.errorNoMysqldumpHeader',
                        $this->extKey
                    ),
                    FlashMessage::ERROR
                )
            );
        }
    }



    


    /**
     * @return string $backupFilename  The name for the sql backup file
     */
    protected function getBackupFileName() {
        return 'dbdumpimporter' . '_' .
            $GLOBALS['TYPO3_CONF_VARS']['DB']['database'] . "_" .
            date('Y_m_d_His') . '.dump.sql';
    }



    /**
     * Return the directory to store the dump file in
     */
    protected function getBackupDirectory() {
        return \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName('uploads/tx_hwtimporthandler/backups/');
    }
}
