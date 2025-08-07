<?php

declare(strict_types=1);

namespace Andersundsehr\Storybook\Transformer;

use ArgumentCountError;
use Closure;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use ReflectionFunction;
use TYPO3Fluid\Fluid\Core\ViewHelper\ArgumentDefinition;

use function class_exists;
use function in_array;
use function sprintf;
use function str_contains;

final readonly class TransformerFactory
{
    public function __construct(private ContainerInterface $container)
    {
    }

    public function fromCallable(callable $transformer, string $from): Transformer
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
        $services = [];
        foreach ($reflection->getParameters() as $parameter) {
            $type = $parameter->getType()?->__toString() ?? throw new InvalidArgumentException(
                sprintf(
                    'Argument "%s" for Transformer "%s" does not have a type defined. Please define a type for the argument.',
                    $parameter->getName(),
                    $from,
                ),
                9068759075,
            );
            if ($this->container->has($type)) {
                $services[$parameter->getName()] = $this->container->get($type);
                continue;
            }

            $argumentName = $parameter->getName();
            if (!in_array($type, TransformersFactory::DEFAULT_SUPPORTED_TYPES) && !class_exists($type) && !enum_exists($type)) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Invalid type "%s" for argument "%s" in function "%s". Only basic types are allowed.',
                        $type,
                        $argumentName,
                        $reflection->getName()
                    ),
                    9211667105,
                );
            }

            $descriptionPrefix = 'transformed via: ' . (str_contains($from, '::') ? '`' . $from . '`' : '`*.transformer.php`') . ' - ';
            $arguments[$argumentName] = new ArgumentDefinition(
                name: $argumentName,
                type: $type,
                description: $descriptionPrefix . ' virtual argument has type:',
                required: !$parameter->isOptional(),
                defaultValue: $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null,
                escape: false,
            );
        }

        return new Transformer(
            transformer: $closure,
            from: $from,
            returnType: $returnType,
            arguments: $arguments,
            services: $services
        );
    }
}
