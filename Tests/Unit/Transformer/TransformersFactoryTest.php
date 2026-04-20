<?php

declare(strict_types=1);

namespace Andersundsehr\Storybook\Tests\Unit\Transformer;

use Throwable;
use Stringable;
use Andersundsehr\Storybook\Service\ConfigService;
use Andersundsehr\Storybook\Transformer\Transformer;
use Andersundsehr\Storybook\Transformer\TransformerFactory;
use Andersundsehr\Storybook\Transformer\Transformers;
use Andersundsehr\Storybook\Transformer\TransformersFactory;
use Andersundsehr\Storybook\Transformer\TypeTransformers;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Container\ContainerInterface;
use RuntimeException;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;
use TYPO3Fluid\Fluid\Core\Component\ComponentDefinition;
use TYPO3Fluid\Fluid\Core\ViewHelper\ArgumentDefinition;

final class TransformersFactoryTest extends UnitTestCase
{
    #[DataProvider('validReturnTypesDataProvider')]
    public function testValidateReturnTypeAcceptsCompatibleTypes(string $targetType, string $returnType): void
    {
        $this->expectNotToPerformAssertions();

        $this->createSubject()->validateReturnType(
            $this->createComponentDefinition($targetType),
            $this->createTransformers($returnType),
        );
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function validReturnTypesDataProvider(): array
    {
        return [
            'exact scalar match' => ['string', 'string'],
            'mixed accepts any return type' => ['mixed', 'string'],
            'array satisfies array-shape-like target' => ['string[]', 'array'],
            'subclass satisfies parent/interface target' => [Throwable::class, RuntimeException::class],
            'exact union type match' => ['string|int', 'string|int'],
            'order different union type match' => ['string|int', 'int|string'],
            'narrower scalar satisfies union target' => ['string|int', 'string'],
            'class satisfies union target' => [Throwable::class . '|' . Stringable::class, RuntimeException::class],
        ];
    }

    #[DataProvider('invalidReturnTypesDataProvider')]
    public function testValidateReturnTypeThrowsForIncompatibleTypes(string $targetType, string $returnType): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(4128088840);

        $this->createSubject()->validateReturnType(
            $this->createComponentDefinition($targetType),
            $this->createTransformers($returnType),
        );
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function invalidReturnTypesDataProvider(): array
    {
        return [
            'different scalar type' => ['string', 'int'],
            'broader union does not satisfy narrower target' => ['string', 'string|int'],
        ];
    }

    private function createSubject(): TransformersFactory
    {
        $transformerFactory = new TransformerFactory(new class implements ContainerInterface {
            public function get(string $id): mixed
            {
                throw new RuntimeException('No services available in unit test container.', 1589909511);
            }

            public function has(string $id): bool
            {
                return false;
            }
        });

        return new TransformersFactory(
            new TypeTransformers($transformerFactory),
            $transformerFactory,
            $this->createStub(ConfigService::class),
        );
    }

    private function createComponentDefinition(string $targetType): ComponentDefinition
    {
        return new ComponentDefinition(
            'storybook:test',
            [
                'title' => new ArgumentDefinition(
                    'title',
                    $targetType,
                    'Test argument',
                    false,
                    null,
                    false,
                ),
            ],
            false,
            [],
        );
    }

    private function createTransformers(string $returnType): Transformers
    {
        return new Transformers(
            [
                'title' => new Transformer(
                    static fn(): mixed => null,
                    'test',
                    $returnType,
                    [],
                    [],
                ),
            ],
            'test.transformer.php',
        );
    }
}
