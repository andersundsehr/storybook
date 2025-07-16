<?php

declare(strict_types=1);

namespace Andersundsehr\Storybook\Transformer;

use Closure;
use InvalidArgumentException;
use ReflectionFunction;
use TYPO3Fluid\Fluid\Core\ViewHelper\ArgumentDefinition;

final readonly class ArgumentTransformers
{
    /** @var array<string, Closure():mixed> */
    public array $arguments;

    public string $fromFile;

    public function __construct(
        /**
         * @var array<string, Closure():mixed>
         */
        Closure ...$arguments,
    ) {
        $this->validateArguments($arguments);

        $this->arguments = $arguments;
        $this->fromFile = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['file'] ?? '';
    }

    /**
     * @param array<string, mixed> $arguments
     */
    public function execute(string $name, array $arguments = []): mixed
    {
        if (!isset($this->arguments[$name])) {
            throw new InvalidArgumentException(sprintf('No argument transformer found for "%s".', $name), 1152616843);
        }

        return $this->arguments[$name](...$arguments);
    }

    public function getResultType(string $name): ?string
    {
        if (!isset($this->arguments[$name])) {
            throw new InvalidArgumentException(sprintf('No argument transformer found for "%s".', $name), 6694197001);
        }

        $reflection = new ReflectionFunction($this->arguments[$name]);
        return $reflection->getReturnType()?->__toString();
    }

    /**
     * @return array<string, ArgumentDefinition>
     */
    public function getDefinition(string $name): ?array
    {
        if (!isset($this->arguments[$name])) {
            return null;
        }

        $result = [];
        $reflection = new ReflectionFunction($this->arguments[$name]);
        foreach ($reflection->getParameters() as $parameter) {
            $subArgType = $parameter->getType()?->__toString() ?? throw new InvalidArgumentException(
                sprintf(
                    'Argument "%s" for Transformer "%s" does not have a type defined. Please define a type for the argument.',
                    $parameter->getName(),
                    $name,
                ),
                1410352323,
            );
            $subArgName = $parameter->getName();
            if (!in_array($subArgType, ['string', 'int', 'float', 'bool']) && !class_exists($subArgType) && !enum_exists($subArgType)) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Invalid type "%s" for argument "%s" in function "%s". Only basic types are allowed.',
                        $subArgType,
                        $subArgName,
                        $reflection->getName()
                    ),
                    5873867879
                );
            }

            $result[$subArgName] = new ArgumentDefinition(
                name: $subArgName,
                type: $subArgType,
                description: 'virtual argument has type:',
                required: !$parameter->isOptional(),
                defaultValue: $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null,
                escape: false,
            );
        }

        return $result;
    }

    public function hasTransformer(string $argumentName): bool
    {
        return isset($this->arguments[$argumentName]);
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
