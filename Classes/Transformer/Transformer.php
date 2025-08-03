<?php

declare(strict_types=1);

namespace Andersundsehr\Storybook\Transformer;

use ArgumentCountError;
use Closure;
use InvalidArgumentException;
use ReflectionFunction;
use TYPO3Fluid\Fluid\Core\ViewHelper\ArgumentDefinition;

use function class_exists;
use function in_array;
use function sprintf;
use function str_contains;

final readonly class Transformer
{
    public function __construct(
        private Closure $transformer,
        /** @var string defines the source of the transformer, e.g. '<filename>:<argument>' or 'TypeTransformer:<class>::<method>' */
        public string $from,
        public string $returnType,
        /**
         * @var array<string, ArgumentDefinition>
         */
        public array $arguments,
        /**
         * @var array<string, object>
         */
        public array $services,
    ) {
    }

    /**
     * @param array<string, mixed> $arguments
     */
    public function execute(array $arguments = []): mixed
    {
        try {
            return ($this->transformer)(...$this->services, ...$arguments);
        } catch (ArgumentCountError) {
            throw new  ArgumentCountError(
                sprintf(
                    'Transformer "%s" expects %d arguments, but %d were given. Please check the arguments and ensure they match the transformer definition.',
                    $this->from,
                    count($this->arguments),
                    count($arguments)
                ),
                8312565203,
            );
        }
    }
}
