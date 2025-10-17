<?php

declare(strict_types=1);

namespace Andersundsehr\Storybook\Service;

use Andersundsehr\Storybook\Factory\ComponentDataFactory;
use Andersundsehr\Storybook\Transformer\Transformers;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use InvalidArgumentException;
use TYPO3Fluid\Fluid\Core\Component\ComponentDefinition;
use TYPO3Fluid\Fluid\Core\ViewHelper\ArgumentDefinition;
use UnitEnum;

use function array_filter;
use function array_keys;
use function implode;
use function in_array;
use function strlen;

final readonly class ArgTypesService
{
    public function __construct(
        private ConfigService $configService
    ) {
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function getArgTypes(ComponentDefinition $componentDefinition, Transformers $transformers): array
    {
        $excludedArguments = $this->configService->getExcludedArguments();

        $argTypes = [];
        foreach ($componentDefinition->getArgumentDefinitions() as $argumentDefinition) {
            if (in_array($argumentDefinition->getName(), $excludedArguments, true)) {
                continue;
            }

            $transformerDefinition = $transformers->arguments[$argumentDefinition->getName()] ?? null;
            if ($transformerDefinition) {
                // If a transformer is defined, we use the transformer definition instead of the argument definition
                foreach ($transformerDefinition->arguments as $name => $definition) {
                    $argTypes[$argumentDefinition->getName() . '__' . $name] = $this->getArgTypesForArgument(
                        $definition,
                        $argumentDefinition,
                        implode(', ', array_keys($transformerDefinition->arguments))
                    );
                }

                continue;
            }

            $argTypes[$argumentDefinition->getName()] = $this->getArgTypesForArgument($argumentDefinition);
        }

        foreach ($componentDefinition->getAvailableSlots() as $availableSlot) {
            $argTypes[ComponentDataFactory::SLOT_PREFIX . $availableSlot] = [
                'description' => 'Slot content for ' . $availableSlot,
                'name' => $availableSlot,
                'control' => [
                    'type' => 'text',
                ],
                'type' => [
                    'name' => 'string',
                    'required' => false,
                ],
                'table' => [
                    'category' => 'slot',
                    'defaultValue' => [
                        'summary' => "''", // TODO get default value if that is implemented in fluid
                        'detail' => 'slots are not required by default',
                    ],
                ],
            ];
        }

        return $argTypes;
    }

    /**
     * @return array<string, mixed>
     */
    private function getArgTypesForArgument(
        ArgumentDefinition $argumentDefinition,
        ?ArgumentDefinition $parent = null,
        ?string $transformerArgumentString = null,
    ): array {
        $options = [];
        $numberStep = null;
        if ($argumentDefinition->isBooleanType()) {
            $controlType = 'boolean';
        } elseif (in_array($argumentDefinition->getType(), ['int', 'integer'], true)) {
            $controlType = 'number';
            $numberStep = 1;
        } elseif (in_array($argumentDefinition->getType(), ['float', 'double'], true)) {
            $controlType = 'number';
        } elseif (in_array($argumentDefinition->getType(), ['string', 'text'], true)) {
            $controlType = 'text';
        } elseif (in_array($argumentDefinition->getType(), [DateTime::class, DateTimeImmutable::class, DateTimeInterface::class], true)) {
            $controlType = 'date';
        } elseif (enum_exists($argumentDefinition->getType())) {
            $controlType = 'select';
            foreach ($argumentDefinition->getType()::cases() as $case) {
                $label = '::' . $case->name;
                if (isset($case->value) && $case->value !== $case->name) {
                    $label .= '=' . $case->value;
                }

                $options[$label] = ArgTypesService::getCaseValue($case);
            }

            if (count($options) <= 4) {
                $controlType = 'radio';
            }

            $strlen = strlen(implode('', array_keys($options)));
            if ($strlen < 50) {
                $controlType = 'inline-radio';
            }
        } else {
            throw new InvalidArgumentException(
                sprintf(
                    'Unsupported argument type "%s" for argument "%s". Only basic types are supported.',
                    $argumentDefinition->getType(),
                    $argumentDefinition->getName()
                ),
                2432569368
            );
        }

        $descriptionPrefix = '';
        if ($parent) {
            $descriptionPrefix = ('argument has type: `' . $parent->getType() . '`' . PHP_EOL . PHP_EOL);
        }

        $subCategory = null;
        if ($parent) {
            $subCategory = $parent->getName() . '(' . $transformerArgumentString . ')';
            if ($parent->isRequired()) {
                $subCategory .= '*';
            }
        }

        return [
            'description' => $descriptionPrefix . $argumentDefinition->getDescription(),
            'name' => $argumentDefinition->getName(),
            'control' => [
                'type' => $controlType, // only boolean and string for now
                ...array_filter([
                    'step' => $numberStep, // only for number
                ]),
            ],
            'options' => $options, // only for select
            'type' => [
                'name' => $argumentDefinition->getType(), // only boolean and string for now
                'required' => $argumentDefinition->isRequired(),
            ],
            'table' => [
                'category' => 'argument',
                'subcategory' => $subCategory,
                'defaultValue' => [
                    'summary' => $argumentDefinition->getDefaultValue(),
                ],
            ],
        ];
    }

    public static function getCaseValue(UnitEnum $case): string
    {
        return "{f:constant(name: '" . $case::class . "::" . $case->name . "')}";
    }
}
