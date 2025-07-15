<?php

declare(strict_types=1);

namespace Andersundsehr\Storybook\Service;

use Andersundsehr\Storybook\Factory\RenderJobFactory;
use Andersundsehr\Storybook\Transformer\ArgumentTransformers;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use TYPO3Fluid\Fluid\Core\Component\ComponentDefinition;
use TYPO3Fluid\Fluid\Core\ViewHelper\ArgumentDefinition;
use function array_filter;
use function implode;
use function in_array;

final readonly class ArgTypesService
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public function getArgTypes(ComponentDefinition $componentDefinition, ArgumentTransformers $argumentTransformers): array
    {
        $argTypes = [];
        foreach ($componentDefinition->getArgumentDefinitions() as $argumentDefinition) {
            $transformerDefinition = $argumentTransformers->getDefinition($argumentDefinition->getName());
            if ($transformerDefinition) {
                // If a transformer is defined, we use the transformer definition instead of the argument definition
                foreach ($transformerDefinition as $name => $definition) {
                    $argTypes[$argumentDefinition->getName() . '__' . $name] = $this->getArgTypesForArgument(
                        $definition,
                        $argumentDefinition,
                        implode(', ', array_keys($transformerDefinition))
                    );
                }
                continue;
            }
            $argTypes[$argumentDefinition->getName()] = $this->getArgTypesForArgument($argumentDefinition);
        }

        foreach ($componentDefinition->getAvailableSlots() as $availableSlot) {
            $argTypes[RenderJobFactory::SLOT_PREFIX . $availableSlot] = [
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

    private function getArgTypesForArgument(ArgumentDefinition $argumentDefinition, ?ArgumentDefinition $parent = null, ?string $args = null): array
    {
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
                $options[$label] = $case->name;
            }
        } else {
            throw new \InvalidArgumentException(
                sprintf(
                    'Unsupported argument type "%s" for argument "%s". Only basic types are supported.',
                    $argumentDefinition->getType(),
                    $argumentDefinition->getName()
                )
            );
        }

        return [
            // TODO add controls if possible (auto convert?) add <storybook:controls> ViewHelper for that? => maybe not needed as transformers can handle that
            'description' => ($parent ? ('argument has type: ' . '`' . $parent->getType(
                    ) . '`' . PHP_EOL . PHP_EOL) : '') . $argumentDefinition->getDescription(),
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
                'subcategory' => $parent ? ($parent->getName() . '(' . $args . ')') : null,
                'defaultValue' => [
                    'summary' => $argumentDefinition->getDefaultValue(),
                ],
            ],
        ];
    }
}
