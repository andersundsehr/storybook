<?php

declare(strict_types=1);

namespace Andersundsehr\Storybook\Factory;

use Andersundsehr\Storybook\Dto\ComponentData;
use Andersundsehr\Storybook\Dto\RenderJob;
use Andersundsehr\Storybook\Service\ArgTypesService;
use Andersundsehr\Storybook\Transformer\ArgumentTransformers;
use Closure;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use RuntimeException;
use TYPO3Fluid\Fluid\Core\Component\ComponentDefinition;

use function array_keys;
use function enum_exists;
use function get_defined_vars;
use function in_array;

final readonly class ComponentDataFactory
{
    public const string SLOT_PREFIX = 'slot____';

    public function transform(ComponentDefinition $componentDefinition, ArgumentTransformers $argumentTransformers, RenderJob $renderJob): ComponentData
    {
        $this->validateArgumentTransformers($componentDefinition, $argumentTransformers);
        $argumentDefinitions = $componentDefinition->getArgumentDefinitions();

        $args = [];
        /** @var array<string, Closure():string> $slots */
        $slots = [];
        foreach ($renderJob->rawArgs as $key => $value) {
            if (str_starts_with((string)$key, self::SLOT_PREFIX)) {
                $slots[substr((string)$key, strlen(self::SLOT_PREFIX))] = static fn(): string => (string)$value;
                continue;
            }

            $keyParts = explode('__', $key, 2);
            if (count($keyParts) === 1) {
                $targetType = $argumentDefinitions[$keyParts[0]]->getType();
                $args[$keyParts[0]] = $this->convertToTargetType($targetType, $value);
                continue;
            }

            $transformerDefinition = $argumentTransformers->getDefinition($keyParts[0]) ?? throw new RuntimeException(
                'The argument transformer for "' . $keyParts[0] . '" is not defined. Please add it to the component definition or remove it from the stories file.',
                4628123687
            );
            $transformerArgumentDefinition = $transformerDefinition[$keyParts[1]] ?? throw new RuntimeException(
                'The argument transformer for "' . $keyParts[0] . '" does not have a definition for "' . $keyParts[1] . '". Please add it to the component definition or remove it from the stories file.',
                2249880039
            );
            $targetType = $transformerArgumentDefinition->getType();
            $args[$keyParts[0]][$keyParts[1]] = $this->convertToTargetType($targetType, $value);
        }

        foreach ($args as $argumentName => $argumentValue) {
            if (!$argumentTransformers->hasTransformer($argumentName)) {
                continue;
            }

            $args[$argumentName] = $argumentTransformers->execute($argumentName, $argumentValue);
        }

        $this->validateResult($componentDefinition, $args, $slots);
        return new ComponentData($args, $slots);
    }

    private function validateArgumentTransformers(ComponentDefinition $componentDefinition, ArgumentTransformers $argumentTransformers): void
    {
        $argumentDefinitions = $componentDefinition->getArgumentDefinitions();
        foreach (array_keys($argumentTransformers->arguments) as $argumentName) {
            if (!isset($argumentDefinitions[$argumentName])) {
                throw new RuntimeException(
                    'The transformer for argument "' . $argumentName . '" is not present in the component. remove it from ' . $argumentTransformers->fromFile . ' or add it to the component.',
                    1153552856
                );
            }

            $resultType = $argumentTransformers->getResultType($argumentName);
            $targetType = $argumentDefinitions[$argumentName]->getType();
            if ($targetType === 'mixed') {
                // If the target type is mixed, we don't need to validate the result type
                continue;
            }

            if ($resultType !== $targetType) {
                // TODO use better type comparison
                // instanceof, is_a, etc.
                // make this work with Type[] arrays
                // and Union types
                throw new RuntimeException(
                    'The transformer for argument "' . $argumentName . '" returns a value of type "' . $resultType . '" but the component expects a value of type "' . $targetType . '". ' .
                    'Please adjust the transformer or the component definition.',
                    4128088840
                );
            }
        }
    }

    private function convertToTargetType(string $targetType, mixed $value): mixed
    {
        if (in_array($targetType, [DateTimeInterface::class, DateTimeImmutable::class], true)) {
            return new DateTimeImmutable($value);
        }

        if ($targetType === DateTime::class) {
            return new DateTime($value);
        }

        if (enum_exists($targetType)) {
            if (!is_string($value)) {
                throw new RuntimeException(
                    'The value for an enum must be a string. The value "' . $value . '" is not a valid enum value.',
                    5151228270
                );
            }

            foreach ($targetType::cases() as $case) {
                if (ArgTypesService::getCaseValue($case) === $value) {
                    return $case;
                }
            }

            throw new RuntimeException(
                'The value "' . $value . '" is not a valid value for the enum "' . $targetType . '".',
                4820654599
            );
        }

        // we do not convert the other types, fluid dose some and for others it throws an error
        return $value;
    }

    /**
     * @param array<string, mixed> $args
     * @param array<string, Closure():string> $slots
     */
    private function validateResult(ComponentDefinition $componentDefinition, array $args, array $slots): void
    {
        $argumentDefinitions = $componentDefinition->getArgumentDefinitions();
        foreach (array_keys($args) as $argumentName) {
            if (!isset($argumentDefinitions[$argumentName])) {
                throw new RuntimeException(
                    'The argument "' . $argumentName . '" is not present in the component. Remove it from the stories file or add it to the component.',
                    3931702316
                );
            }
        }

        $availableSlots = array_flip($componentDefinition->getAvailableSlots());
        foreach (array_keys($slots) as $slotName) {
            if (!isset($availableSlots[$slotName])) {
                throw new RuntimeException(
                    'The slot "' . $slotName . '" is not present in the component. Remove it from the stories file or add it to the component.',
                    3399576233
                );
            }
        }
    }
}
