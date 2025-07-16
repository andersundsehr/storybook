<?php

declare(strict_types=1);

namespace Andersundsehr\Storybook\Dto;

use Closure;

final readonly class ComponentData
{
    public function __construct(
        /** @var array<string, mixed> */
        public array $arguments = [],
        /** @var array<string, Closure():string> */
        public array $slots = [],
    ) {
    }
}
