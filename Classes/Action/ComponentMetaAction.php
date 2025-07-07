<?php

declare(strict_types=1);

namespace Andersundsehr\Storybook\Action;

use Andersundsehr\Storybook\Dto\ViewHelperName;
use Andersundsehr\Storybook\Factory\RenderJobFactory;
use Andersundsehr\Storybook\Middleware\StorybookMiddleware;
use Andersundsehr\Storybook\Service\ComponentCollectionService;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3Fluid\Fluid\Core\Component\ComponentDefinition;
use TYPO3Fluid\Fluid\Core\Component\ComponentDefinitionProviderInterface;
use TYPO3Fluid\Fluid\Core\Component\ComponentTemplateResolverInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolverDelegateInterface;

final readonly class ComponentMetaAction implements ActionInterface
{
    public function __construct(private ComponentCollectionService $componentCollectionService)
    {
    }

    public function __invoke(ServerRequestInterface $request): JsonResponse
    {
        $viewHelper = new ViewHelperName(
            ($request->getQueryParams()['viewHelper'] ?? null) ?: throw new RuntimeException('Missing `viewHelper` GET parameter', 4602881064)
        );

        return $this->componentCollectionService->tryCollection(
            $viewHelper->namespace,
            function (
                ViewHelperResolverDelegateInterface&ComponentDefinitionProviderInterface&ComponentTemplateResolverInterface $collection
            ) use ($viewHelper): JsonResponse {
                $componentDefinition = $collection->getComponentDefinition($viewHelper->name);

                $argTypes = $this->getArgTypes($componentDefinition);

                $data = [
                    'viewHelper' => $viewHelper->fullName,
                    'collectionClassName' => $collection::class,
                    'argTypes' => $argTypes,
                ];

                return new JsonResponse($data);
            }
        );
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function getArgTypes(ComponentDefinition $componentDefinition): array
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
