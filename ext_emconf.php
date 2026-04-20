<?php

declare(strict_types=1);

use Composer\InstalledVersions;

/** @var string $_EXTKEY */
$EM_CONF[$_EXTKEY] = [
    'title' => 'EXT:storybook',
    'description' => 'The one and only Storybook Renderer for TYPO3 Fluid Components',
    'category' => 'module',
    'author' => 'Matthias Vogel',
    'author_email' => 'm.vogel@andersundsehr.com',
    'state' => 'stable',
    'version' => InstalledVersions::getPrettyVersion('andersundsehr/storybook'),
    'constraints' => [
        'depends' => [
            'typo3' => '13.4.15-14.0.999',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
