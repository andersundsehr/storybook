<?php

declare(strict_types=1);

namespace Andersundsehr\Storybook\Service;

use TYPO3\CMS\Core\Site\SiteFinder;

use function array_key_first;

final readonly class GlobalTypesService
{
    public function __construct(private SiteFinder $siteFinder, private CountryFlagEmojiService $countryFlagEmojiService)
    {
    }

    /**
     * @param array<string, string> $currentGlobals
     * @return array<string, array<string, array{value: string, title: string}>>
     */
    public function getGlobalTypes(array $currentGlobals): array
    {
        $siteObjects = $this->siteFinder->getAllSites();

        $currentSite = $currentGlobals['site'] ?? array_key_first($siteObjects) ?? '';

        $globalTypes = [];
        foreach ($siteObjects as $site) {
            $siteIdentifier = $site->getIdentifier();
            $title = ($site->getConfiguration()['websiteTitle'] ?? '') ?: $siteIdentifier;
            $globalTypes['site'][$siteIdentifier] = [
                'value' => $siteIdentifier,
                'title' => '🌐 ' . $title,
            ];

            if ($currentSite && $currentSite !== $site->getIdentifier()) {
                // skip language of other sites
                continue;
            }

            foreach ($site->getLanguages() as $language) {
                $identifier = $language->getHreflang();
                $countryIsoAlpha2 = $language->getLocale()->getCountryCode();
                $title = $this->countryFlagEmojiService->countryFlagEmoji($countryIsoAlpha2) . ' ' . $language->getTitle();
                $globalTypes['language'][$identifier] = [
                    'value' => $identifier,
                    'title' => $title,
                ];
            }
        }

        return $globalTypes;
    }
}
