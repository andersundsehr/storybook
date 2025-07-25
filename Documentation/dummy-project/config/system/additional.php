<?php

$GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['path'] = dirname(__DIR__, 2) . '/var/sqlite/cms.sqlite';

if (!is_dir(dirname($GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['path']))) {
    // Create the directory if it does not exist
    mkdir(dirname($GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['path']), 0777, true);
}

touch($GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['path']);
