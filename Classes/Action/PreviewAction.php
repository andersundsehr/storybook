<?php

declare(strict_types=1);

namespace Andersundsehr\Storybook\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

use function array_key_first;
use function array_values;
use function chr;
use function json_decode;
use function ord;
use function preg_match;
use function strlen;

final readonly class PreviewAction implements ActionInterface
{
    public function __construct(private SiteFinder $siteFinder)
    {
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $body = $request->getBody()->getContents();
        $globals = json_decode($body ?: '[]', true, 512, JSON_THROW_ON_ERROR)['globals'] ?? [];

        $siteObjects = $this->siteFinder->getAllSites();

        $currentSite = $globals['site'] ?? array_key_first($siteObjects) ?? '';

        $sites = [];
        $langauges = [];
        foreach ($siteObjects as $site) {
            $siteIdentifier = $site->getIdentifier();
            $sites[$siteIdentifier] = [
                'value' => $siteIdentifier,
                'title' => '🌐 ' . $siteIdentifier . ' ' . ($site->getRawConfiguration()['base'] ?? $site->getBase()),
            ];

            if ($currentSite && $currentSite !== $site->getIdentifier()) {
                // skip langauge of other sites
                continue;
            }

            foreach ($site->getLanguages() as $language) {
                $identifier = $language->getHreflang();
                $langauges[$identifier] = [
                    'value' => $identifier,
                    'title' => $this->countryFlagEmoji($language->getLocale()->getCountryCode()) . ' ' . $language->getTitle(),
                ];
            }
        }

        $initalGlobals = [
            'site' => $sites ? array_key_first($sites) : null,
            'language' => $langauges ? array_key_first($langauges) : null,
        ];

        $globalTypes = [];

        if (count($sites) > 1) {
            $globalTypes['site'] = [
                'description' => 'Site selection',
                'toolbar' => [
                    'title' => '🌐 Site',
                    'items' => array_values($sites),
                    'dynamicTitle' => true,
                ],
            ];
        }

        if (count($langauges) > 1) {
            $globalTypes['language'] = [
                'description' => 'Site language selection',
                'toolbar' => [
                    'title' => '🏳️‍🌈️ Site Language',
                    'items' => array_values($langauges),
                    'dynamicTitle' => true,
                ],
            ];
        }

        return new JsonResponse([
            'globalTypes' => $globalTypes,
            'initialGlobals' => $initalGlobals,
            'globals' => [
                ...$globals,
                'site' => $currentSite,
                'language' => isset($langauges[$globals['language'] ?? '']) ? $globals['language'] : $initalGlobals['language'],
            ],
        ]);
    }

    /**
     * Thanks to https://github.com/sergeyakovlev/country-flag-emoji-php
     */
    private function countryFlagEmoji(?string $countryIsoAlpha2, ?string $extLeft = null, ?string $extRight = null): string
    {
        if ($countryIsoAlpha2 === null) {
            return '🏳️‍🌈';
        }

        $unicodePrefix = "\xF0\x9F\x87";
        $unicodeAdditionForLowerCase = 0x45;
        $unicodeAdditionForUpperCase = 0x65;

        if (preg_match('/^[A-Z]{2}$/', $countryIsoAlpha2)) {
            $emoji = $unicodePrefix . chr(ord($countryIsoAlpha2[0]) + $unicodeAdditionForUpperCase)
                . $unicodePrefix . chr(ord($countryIsoAlpha2[1]) + $unicodeAdditionForUpperCase);
        } elseif (preg_match('/^[a-z]{2}$/', $countryIsoAlpha2)) {
            $emoji = $unicodePrefix . chr(ord($countryIsoAlpha2[0]) + $unicodeAdditionForLowerCase)
                . $unicodePrefix . chr(ord($countryIsoAlpha2[1]) + $unicodeAdditionForLowerCase);
        } else {
            $emoji = '';
        }

        return strlen($emoji) ? ($extLeft ?? '') . $emoji . ($extRight ?? '') : '';
    }
}
