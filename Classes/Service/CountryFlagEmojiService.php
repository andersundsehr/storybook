<?php

declare(strict_types=1);

namespace Andersundsehr\Storybook\Service;

use function chr;
use function ord;
use function preg_match;
use function strtoupper;

final readonly class CountryFlagEmojiService
{
    /**
     * Thanks to https://github.com/sergeyakovlev/country-flag-emoji-php
     */
    public function countryFlagEmoji(?string $countryIsoAlpha2): string
    {
        if ($countryIsoAlpha2 === null) {
            return '🏳️‍🌈';
        }

        $unicodePrefix = "\xF0\x9F\x87";
        $unicodeAdditionForUpperCase = 0x65;

        $countryIsoAlpha2 = strtoupper($countryIsoAlpha2);
        if (preg_match('/^[A-Z]{2}$/', $countryIsoAlpha2)) {
            return $unicodePrefix . chr(ord($countryIsoAlpha2[0]) + $unicodeAdditionForUpperCase)
                . $unicodePrefix . chr(ord($countryIsoAlpha2[1]) + $unicodeAdditionForUpperCase);
        }

        return '🏳️‍🌈';
    }
}
