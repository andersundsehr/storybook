<?php

declare(strict_types=1);

namespace Andersundsehr\Storybook\Transformer;

use Andersundsehr\Storybook\Transformer\TransformerFactory;
use RuntimeException;

use function array_keys;
use function explode;
use function is_a;
use function sort;
use function str_contains;
use function trim;

use const PHP_INT_MIN;

/**
 * @phpstan-type TransformerConfigArray array{
 *      handler: class-string<object>,
 *      method: string,
 *      returnType: string,
 *      priority: int
 *  }
 */
final class TypeTransformers
{
    /**
     * @var array<string, list<TransformerConfigArray>>
     */
    private array $configuration = [];

    /** @var array<string, Transformer> */
    private array $handlers = [];

    /**
     * @var array<string, int>
     */
    private array $priorities = [];

    public function __construct(private readonly TransformerFactory $transformerFactory)
    {
    }

    /**
     * called from Symfony DI configuration
     */
    public function addTransformer(object $handler, string $method, string $returnType, int $priority): void
    {
        if (!method_exists($handler, $method)) {
            throw new RuntimeException(
                'The method "' . $method . '" does not exist on the handler class "' . $handler::class . '".',
                1988452467
            );
        }

        $returnType = $this->normalizeType($returnType);

        $this->configuration[$returnType] ??= [];
        $this->configuration[$returnType][] = [
            'handler' => $handler::class,
            'method' => $method,
            'returnType' => $returnType,
            'priority' => $priority,
        ];

        if (($this->priorities[$returnType] ?? PHP_INT_MIN) > $priority) {
            // If a transformer with a higher priority already exists, do not add this one
            return;
        }

        $this->handlers[$returnType] = $this->transformerFactory->fromCallable(
            $handler->{$method}(...),
            $handler::class . '->' . $method
        );
        $this->priorities[$returnType] = $priority;
    }

    public function has(string $returnType): bool
    {
        return (bool)$this->getInternal($returnType);
    }

    public function get(string $returnType): Transformer
    {
        return $this->getInternal($returnType) ?? throw new RuntimeException(
            'No transformer found for return type "' . $returnType . '".',
            1988452468
        );
    }

    private function getInternal(string $returnType): ?Transformer
    {
        $returnType = $this->normalizeType($returnType);

        if (isset($this->handlers[$returnType])) {
            return $this->handlers[$returnType];
        }

        $maxPriority = PHP_INT_MIN;
        $matched = null;
        foreach ($this->handlers as $type => $transformer) {
            if (!$this->matchesRequestedType($type, $returnType)) {
                continue;
            }

            $priority = $this->priorities[$type] ?? throw new RuntimeException('No priority found for transformer of type "' . $type . '".', 6799603216);
            if ($priority < $maxPriority) {
                continue;
            }

            $maxPriority = $priority;
            $matched = $transformer;
        }

        if ($matched) {
            // cache the result:
            $this->handlers[$returnType] = $matched;
            $this->priorities[$returnType] = $maxPriority;
        }

        return $matched;
    }

    private function matchesRequestedType(string $registeredType, string $requestedType): bool
    {
        foreach ($this->splitUnionType($registeredType) as $registeredTypePart) {
            foreach ($this->splitUnionType($requestedType) as $requestedTypePart) {
                if ($this->matchesSingleRequestedType($registeredTypePart, $requestedTypePart)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function matchesSingleRequestedType(string $registeredType, string $requestedType): bool
    {
        if ($this->normalizeType($registeredType) === $this->normalizeType($requestedType)) {
            return true;
        }

        if ($registeredType === $requestedType) {
            return true;
        }

        return is_a($registeredType, $requestedType, true);
    }

    /**
     * @return list<string>
     */
    private function splitUnionType(string $type): array
    {
        return explode('|', $type);
    }

    private function normalizeType(string $type): string
    {
        if (!str_contains($type, '|')) {
            return trim($type);
        }

        $parts = array_map(trim(...), $this->splitUnionType($type));
        sort($parts);

        return implode('|', $parts);
    }

    /**
     * @return array{raw: array<string, list<TransformerConfigArray>>, used: array<string, Transformer>}
     */
    public function getConfiguration(): array
    {
        return [
            'raw' => $this->configuration,
            'used' => $this->handlers,
        ];
    }
}
