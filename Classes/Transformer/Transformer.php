<?php

declare(strict_types=1);

namespace Andersundsehr\Storybook\Transformer;

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
    ) {
    }

    public static function fromCallable(callable $transformer, string $from): self
    {
        $closure = Closure::fromCallable($transformer);
        $arguments = [];
        $reflection = new ReflectionFunction($closure);
        $returnType = $reflection->getReturnType()?->__toString() ?? throw new InvalidArgumentException(
            sprintf(
                'Transformer "%s" does not have a return type defined. Please define a return type for the transformer.',
                $reflection->getName(),
            ),
            5914437566,
        );
        foreach ($reflection->getParameters() as $parameter) {
            $subArgType = $parameter->getType()?->__toString() ?? throw new InvalidArgumentException(
                sprintf(
                    'Argument "%s" for Transformer "%s" does not have a type defined. Please define a type for the argument.',
                    $parameter->getName(),
                    $from,
                ),
                9068759075,
            );
            $argumentName = $parameter->getName();
            if (!in_array($subArgType, ['string', 'int', 'float', 'bool']) && !class_exists($subArgType) && !enum_exists($subArgType)) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Invalid type "%s" for argument "%s" in function "%s". Only basic types are allowed.',
                        $subArgType,
                        $argumentName,
                        $reflection->getName()
                    ),
                    9211667105,
                );
            }

            $descriptionPrefix = 'transformed via: ' . (str_contains($from, '::') ? '`' . $from . '`' : '`*.transformer.php`') . ' - ';
            $arguments[$argumentName] = new ArgumentDefinition(
                name: $argumentName,
                type: $subArgType,
                description: $descriptionPrefix . ' virtual argument has type:',
                required: !$parameter->isOptional(),
                defaultValue: $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null,
                escape: false,
            );
        }

        return new self(
            transformer: $closure,
            from: $from,
            returnType: $returnType,
            arguments: $arguments
        );
    }

    /**
     * @param array<string, mixed> $arguments
     */
    public function execute(array $arguments = []): mixed
    {
        return ($this->transformer)(...$arguments);
    }
}
