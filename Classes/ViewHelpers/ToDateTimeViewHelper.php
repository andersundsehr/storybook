<?php

declare(strict_types=1);

namespace Andersundsehr\Storybook\ViewHelpers;

use Override;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use RuntimeException;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

final class ToDateTimeViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('date', 'string|int', 'the date as string', true);
        $this->registerArgument('type', 'string', DateTime::class . ' or ' . DateTimeImmutable::class, false, DateTimeImmutable::class);
    }

    #[Override]
    public function render(): DateTimeInterface
    {
        $date = $this->arguments['date'];
        $type = $this->arguments['type'];

        if (!in_array($type, [DateTime::class, DateTimeImmutable::class], true)) {
            throw new RuntimeException('Invalid type argument. Expected ' . DateTime::class . ' or ' . DateTimeImmutable::class, 3935868812);
        }

        if (is_int($date)) {
            if ($date > 1000000000000) {
                // this is a JS timestamp with milliseconds
                $date /= 1000; // convert to seconds
            }

            $date = '@' . $date;
        }

        return new $type($date);
    }
}
