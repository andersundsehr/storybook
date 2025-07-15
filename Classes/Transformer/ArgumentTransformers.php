<?php

declare(strict_types=1);

namespace Andersundsehr\Storybook\Transformer;

use ReflectionFunction;
use TYPO3Fluid\Fluid\Core\ViewHelper\ArgumentDefinition;

final readonly class ArgumentTransformers
{
    /** @var array<string, \Closure> */
    public array $arguments;

    public function __construct(
        \Closure ...$arguments,
    ) {
        foreach ($arguments as $key => $argument) {
            if (is_int($key)) {
                throw new \InvalidArgumentException('Arguments must be named, not indexed. Use named arguments instead.');
            }
        }
        $this->arguments = $arguments;
    }

    public function execute(string $name, array $arguments = [])
    {
        if (!isset($this->arguments[$name])) {
            throw new \InvalidArgumentException(sprintf('No argument transformer found for "%s".', $name));
        }
        return $this->arguments[$name](...$arguments);
    }

    /**
     * TODO use for validation of Transformers against argument types
     */
    public function getResultType(string $name): ?string
    {
        if (!isset($this->arguments[$name])) {
            throw new \InvalidArgumentException(sprintf('No argument transformer found for "%s".', $name));
        }
        $reflection = new ReflectionFunction($this->arguments[$name]);
        return $reflection->getReturnType()?->getName();
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
            $subArgType = $parameter->getType()?->getName();
            $subArgName = $parameter->getName();
            if (!in_array($subArgType, ['string', 'int', 'float', 'bool']) && !class_exists($subArgType) && !enum_exists($subArgType)) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Invalid type "%s" for argument "%s" in function "%s". Only basic types are allowed.',
                        $subArgType,
                        $subArgName,
                        $reflection->getName()
                    )
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
}
