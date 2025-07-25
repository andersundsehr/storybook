<?php

use Andersundsehr\DummyExtension\ComponentCollection;

# This is required so EXT:storybook can find the component collection
$GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['de'][] = ComponentCollection::class;
