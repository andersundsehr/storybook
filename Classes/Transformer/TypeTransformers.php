<?php

declare(strict_types=1);

namespace Andersundsehr\Storybook\Transformer;

use RuntimeException;

use function array_column;
use function array_keys;
use function is_a;
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

        $this->handlers[$returnType] = Transformer::fromCallable(
            $handler->{
            $method
            }(...),
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
        if(isset($this->handlers[$returnType])){
            return $this->handlers[$returnType];
        }
        $maxPriority = PHP_INT_MIN;
        $matched = null;
        foreach ($this->handlers as $type => $transformer) {
            // if the requested type is contained in the transformers type, we can use it to transform
            if (!is_a($type, $returnType, true)) {
                continue;
            }

            $priority = $this->priorities[$type] ?? throw new RuntimeException('No priority found for transformer of type "' . $type . '".');
            if ($priority < $maxPriority) {
                continue;
            }
            $maxPriority = $priority;
            $matched = $transformer;
        }

        // cache the result:
        $this->handlers[$returnType] = $matched;
        $this->priorities[$returnType] = $maxPriority;
        return $matched;
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
