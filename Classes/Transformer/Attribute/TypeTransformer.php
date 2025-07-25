<?php

declare(strict_types=1);

namespace Andersundsehr\Storybook\Transformer\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final readonly class TypeTransformer
{
    public const string TAG_NAME = 'storybook.transformer.type';

    public function __construct(
        public int $priority = 0,
    ) {
    }
}
