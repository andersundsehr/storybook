<?php

declare(strict_types=1);

namespace Andersundsehr\Storybook\Service;

use Andersundsehr\Storybook\Factory\RenderJobFactory;
use TYPO3Fluid\Fluid\Core\Component\ComponentDefinition;

use function array_filter;
use function in_array;

final readonly class ArgTypesService
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public function getArgTypes(ComponentDefinition $componentDefinition): array
    {
        $argTypes = [];
        foreach ($componentDefinition->getArgumentDefinitions() as $argumentDefinition) {
            $controlType = 'text';
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
            } elseif (in_array($argumentDefinition->getType(), ['Datetime', 'DateTimeImmutable', 'DateTimeInterface'], true)) {
                $controlType = 'date';
            }

            $argTypes[$argumentDefinition->getName()] = [
                // TODO add controls if possible (auto convert?) add <storybook:controls> ViewHelper for that?
                // TODO auto convert enum to select (and back in rendering)
                'description' => $argumentDefinition->getDescription(),
                'control' => [
                    'type' => $controlType, // only boolean and string for now
                    // TODO add select for enums
                    ...array_filter([
                        'step' => $numberStep, // only for number
                    ]),
                ],
                'type' => [
                    'name' => $argumentDefinition->getType(), // only boolean and string for now
                    'required' => $argumentDefinition->isRequired(),
                ],
                'table' => [
                    'category' => 'argument',
                    'defaultValue' => [
                        'summary' => $argumentDefinition->getDefaultValue(),
                    ],
                ],
            ];
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
}
