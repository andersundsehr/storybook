<?php

declare(strict_types=1);

namespace Andersundsehr\Storybook\Service;

use Exception;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use TYPO3Fluid\Fluid\Core\Component\ComponentDefinitionProviderInterface;
use TYPO3Fluid\Fluid\Core\Component\ComponentTemplateResolverInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolverDelegateInterface;

use function array_map;
use function assert;
use function class_exists;
use function implode;
use function is_array;

/**
 * @phpstan-type CollectionClass ViewHelperResolverDelegateInterface&ComponentDefinitionProviderInterface&ComponentTemplateResolverInterface
 */
final readonly class ComponentCollectionService
{
    public function __construct(private ContainerInterface $container)
    {
    }

    /**
     * @template T of ResponseInterface
     * @param callable(CollectionClass):T $callback
     * @return T
     * @throws Exception
     */
    public function tryCollection(string $namespace, callable $callback): ResponseInterface
    {
        $error = [];
        foreach ($this->getCollections($namespace) as $collection) {
            try {
                return $callback($collection);
            } catch (Exception $e) {
                $error[] = $e;
            }
        }

        if (count($error) === 1) {
            throw $error[0];
        }

        throw new RuntimeException(
            'Multiple errors occurred while processing the request: ' . implode(', ', array_map(fn($e): string => $e->getMessage(), $error)),
            previous: $error[0] ?? new RuntimeException('Unknown error occurred'),
        );
    }

    /**
     * @return list<CollectionClass>
     */
    private function getCollections(string $namespace): array
    {
        $classes = $GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces'][$namespace] ?? throw new RuntimeException('No classes found for namespace ' . $namespace, 3009437593);

        if (!is_array($classes)) {
            $classes = [$classes];
        }

        $result = [];
        foreach ($classes as $class) {
            if (!class_exists($class)) {
                continue;
            }

            $collection = $this->container->get($class);

            if (!$collection instanceof ViewHelperResolverDelegateInterface) {
                throw new RuntimeException('Collection class ' . $class . ' does not implement ' . ViewHelperResolverDelegateInterface::class, 5554833801);
            }

            if (!$collection instanceof ComponentTemplateResolverInterface) {
                throw new RuntimeException('Collection class ' . $class . ' does not implement ' . ComponentTemplateResolverInterface::class, 5437990910);
            }

            if (!$collection instanceof ComponentDefinitionProviderInterface) {
                throw new RuntimeException('Collection class ' . $class . ' does not implement ' . ComponentDefinitionProviderInterface::class, 3748876747);
            }

            $result[] = $collection;
        }

        return $result ?: throw new RuntimeException('No collections found for namespace ' . $namespace, 6927882978);
    }
}
