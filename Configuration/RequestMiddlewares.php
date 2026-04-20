<?php

declare(strict_types=1);

use Andersundsehr\Storybook\Middleware\StorybookMiddleware;

return [
    'frontend' => [
        'andersundsehr/storybook/storybook-middleware' => [
            'target' => StorybookMiddleware::class,
            'before' => [
                'typo3/cms-frontend/authentication',
            ],
        ],
    ],
];
