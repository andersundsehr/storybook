<?php

declare(strict_types=1);

namespace Andersundsehr\Storybook\Transformer;

use Closure;
use InvalidArgumentException;

use function is_int;

final readonly class ArgumentTransformers
{
    /** @var array<string, Closure():mixed> */
    public array $arguments;

    public function __construct(
        /**
         * @var array<string, Closure():mixed>
         */
        Closure ...$arguments,
    ) {
        $this->validateArguments($arguments);
        $this->arguments = $arguments;
    }

    /**
     * @param array<Closure():mixed> $arguments
     * @phpstan-assert array<string, Closure():mixed> $arguments
     */
    private function validateArguments(array $arguments): void
    {
        foreach (array_keys($arguments) as $key) {
            if (is_int($key)) {
                throw new InvalidArgumentException('Arguments must be named, not indexed. Use named arguments instead.', 5356189982);
            }
        }
    }
}
