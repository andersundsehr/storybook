<?php

declare(strict_types=1);

namespace Andersundsehr\Storybook\Dto;

use InvalidArgumentException;

final readonly class ViewHelperName
{
    public string $namespace;

    public string $name;

    public function __construct(
        public string $fullName,
    ) {
        $parts = explode(':', $this->fullName, 2);
        if (count($parts) !== 2) {
            throw new InvalidArgumentException('Invalid view helper name: ' . $fullName, 1948933220);
        }

        $this->namespace = $parts[0];
        $this->name = $parts[1];
    }
}
