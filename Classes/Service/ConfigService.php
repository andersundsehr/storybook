<?php

declare(strict_types=1);

namespace Andersundsehr\Storybook\Service;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ConfigService
{
    public function __construct(
        private readonly ExtensionConfiguration $extensionConfiguration
    ) {
    }

    /**
     * @return string[]
     */
    public function getExcludedArguments(): array
    {
        $excludeString = $this->extensionConfiguration->get('storybook', 'excludeArguments');
        if ($excludeString === '') {
            return [];
        }

        return GeneralUtility::trimExplode(',', $excludeString);
    }
}
