<?php

declare(strict_types=1);

namespace Andersundsehr\Storybook\Transformer\Defaults;

use Andersundsehr\Storybook\Transformer\Attribute\TypeTransformer;
use TYPO3\CMS\Core\Http\Uri;

final readonly class DefaultTransformer
{
    #[TypeTransformer(priority: 100)]
    public function uri(string $url): Uri
    {
        return new Uri($url);
    }
}
