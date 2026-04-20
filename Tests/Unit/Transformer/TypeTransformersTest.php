<?php

declare(strict_types=1);

namespace Andersundsehr\Storybook\Tests\Unit\Transformer;

use Generator;
use InvalidArgumentException;
use Throwable;
use Andersundsehr\Storybook\Transformer\TransformerFactory;
use Andersundsehr\Storybook\Transformer\TypeTransformers;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Psr\Container\ContainerInterface;
use RuntimeException;
use Stringable;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class TypeTransformersTest extends UnitTestCase
{
    /**
     * @param list<array{type:string, priority:int}> $transformers
     */
    #[Test]
    #[DataProvider('getDataProvider')]
    public function get(array $transformers, string $type, int $expectedIndex): void
    {
        $subject = $this->createSubject();
        foreach ($transformers as $index => $transformer) {
            $returnType = $transformer['type'];
            $priority = $transformer['priority'];
            $classInstance = eval(sprintf("return new class() { public function __invoke(): %s {}};", $returnType));
            $transformers[$index]['class'] = $classInstance::class;
            $subject->addTransformer(
                handler: $classInstance,
                method: '__invoke',
                returnType: $returnType,
                priority: $priority,
            );
        }

        self::assertEquals($transformers[$expectedIndex]['class'] . '->__invoke', $subject->get($type)->from);
    }

    public static function getDataProvider(): Generator
    {
        yield 'exact scalar lookup' => [
            'transformers' => [
                ['type' => 'string', 'priority' => 10],
            ],
            'type' => 'string',
            'expectedIndex' => 0,
        ];
        yield 'exact union lookup prefers exact registration' => [
            'transformers' => [
                ['type' => 'string|int', 'priority' => 10],
                ['type' => 'string', 'priority' => 20],
            ],
            'type' => 'string|int',
            'expectedIndex' => 0,
        ];
        yield 'exact union lookup beats lower priority scalar' => [
            'transformers' => [
                ['type' => 'string|int', 'priority' => 20],
                ['type' => 'string', 'priority' => 10],
            ],
            'type' => 'string|int',
            'expectedIndex' => 0,
        ];
        yield 'higher priority exact union wins' => [
            'transformers' => [
                ['type' => 'string', 'priority' => 10],
                ['type' => 'string|int', 'priority' => 20],
            ],
            'type' => 'string|int',
            'expectedIndex' => 1,
        ];
        yield 'normalized union request resolves exact union registration' => [
            'transformers' => [
                ['type' => 'string', 'priority' => 10],
                ['type' => 'string|int', 'priority' => 20],
            ],
            'type' => 'int|string',
            'expectedIndex' => 1,
        ];
        yield 'narrower scalar resolves requested union' => [
            'transformers' => [
                ['type' => 'string', 'priority' => 10],
            ],
            'type' => 'string|int',
            'expectedIndex' => 0,
        ];
        yield 'registered subtype resolves interface request' => [
            'transformers' => [
                ['type' => RuntimeException::class, 'priority' => 10],
            ],
            'type' => Stringable::class,
            'expectedIndex' => 0,
        ];
        yield 'higher priority inherited subtype wins for parent request' => [
            'transformers' => [
                ['type' => RuntimeException::class, 'priority' => 10],
                ['type' => InvalidArgumentException::class, 'priority' => 20],
            ],
            'type' => Throwable::class,
            'expectedIndex' => 1,
        ];
        yield 'equal priority inherited subtype uses later registration' => [
            'transformers' => [
                ['type' => RuntimeException::class, 'priority' => 10],
                ['type' => InvalidArgumentException::class, 'priority' => 10],
            ],
            'type' => Throwable::class,
            'expectedIndex' => 1,
        ];
        yield 'union request resolves inherited subtype through compatible member' => [
            'transformers' => [
                ['type' => RuntimeException::class, 'priority' => 10],
            ],
            'type' => Throwable::class . '|' . Stringable::class,
            'expectedIndex' => 0,
        ];
        yield 'registered union transformer resolves inherited union request' => [
            'transformers' => [
                ['type' => RuntimeException::class . '|' . Stringable::class, 'priority' => 10],
            ],
            'type' => Throwable::class . '|' . Stringable::class,
            'expectedIndex' => 0,
        ];
        yield 'lower priority exact type keeps first registration' => [
            'transformers' => [
                ['type' => 'string', 'priority' => 20],
                ['type' => 'string', 'priority' => 10],
            ],
            'type' => 'string',
            'expectedIndex' => 0,
        ];
        yield 'equal priority exact type uses later registration' => [
            'transformers' => [
                ['type' => 'string', 'priority' => 10],
                ['type' => 'string', 'priority' => 10],
            ],
            'type' => 'string',
            'expectedIndex' => 1,
        ];
        yield 'higher priority exact type overrides earlier registration' => [
            'transformers' => [
                ['type' => 'string', 'priority' => 10],
                ['type' => 'string', 'priority' => 20],
            ],
            'type' => 'string',
            'expectedIndex' => 1,
        ];
    }

    public function testAddTransformerThrowsIfMethodDoesNotExist(): void
    {
        $subject = $this->createSubject();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(1988452467);

        $subject->addTransformer(new class {
        }, 'doesNotExist', 'string', 10);
    }


    public function testGetThrowsForUnknownOrUnmatchedType(): void
    {
        $subject = $this->createSubject();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(1988452468);

        $subject->get('string');
    }

    private function createSubject(): TypeTransformers
    {
        $transformerFactory = new TransformerFactory(
            new class implements ContainerInterface {
                public function get(string $id): mixed
                {
                    throw new RuntimeException('No services available in unit test container.', 1589909511);
                }

                public function has(string $id): bool
                {
                    return false;
                }
            },
        );

        return new TypeTransformers($transformerFactory);
    }
}
