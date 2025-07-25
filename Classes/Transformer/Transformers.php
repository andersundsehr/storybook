<?php

declare(strict_types=1);

namespace Andersundsehr\Storybook\Transformer;

use RuntimeException;

final readonly class Transformers
{
    public function __construct(
        /** @var array<string, Transformer> */
        public array $arguments,
        public string $fromFile,
    ) {
    }

    public function get(string $name): Transformer
    {
        if (!isset($this->arguments[$name])) {
            throw new RuntimeException(
                'No transformer found for argument "' . $name . '", please add it to transformers file "' . $this->fromFile . '" or add a corrospoding TypeTransformer.',
                1152616843
            );
        }

        return $this->arguments[$name];
    }

    public function has(string $name): bool
    {
        return isset($this->arguments[$name]);
    }

    /**
     * @param array<string, mixed> $argumentValues
     */
    public function execute(string $name, array $argumentValues): mixed
    {
        return $this->get($name)->execute($argumentValues);
    }
}
