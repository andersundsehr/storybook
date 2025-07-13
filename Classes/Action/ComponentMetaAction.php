<?php

declare(strict_types=1);

namespace Andersundsehr\Storybook\Action;

use Andersundsehr\Storybook\Dto\ViewHelperName;
use Andersundsehr\Storybook\Service\ArgTypesService;
use Andersundsehr\Storybook\Service\ComponentCollectionService;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use TYPO3\CMS\Core\Http\JsonResponse;

final readonly class ComponentMetaAction implements ActionInterface
{
    public function __construct(
        private ComponentCollectionService $componentCollectionService,
        private ArgTypesService $argTypesService,
    ) {
    }

    public function __invoke(ServerRequestInterface $request): JsonResponse
    {
        $viewHelper = new ViewHelperName(
            ($request->getQueryParams()['viewHelper'] ?? null) ?: throw new RuntimeException('Missing `viewHelper` GET parameter', 4602881064)
        );

        $collection = $this->componentCollectionService->getCollection($viewHelper);
        $componentDefinition = $collection->getComponentDefinition($viewHelper->name);

        $argTypes = $this->argTypesService->getArgTypes($componentDefinition);

        return new JsonResponse([
            'viewHelper' => $viewHelper->fullName,
            'collectionClassName' => $collection::class,
            'argTypes' => $argTypes,
        ]);
    }
}
