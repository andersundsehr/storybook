<?php

declare(strict_types=1);

namespace Andersundsehr\Storybook\ViewHelpers;

use Override;
use RuntimeException;
use Andersundsehr\Storybook\Dto\ViewHelperName;
use Andersundsehr\Storybook\Service\ComponentCollectionService;
use Andersundsehr\Storybook\Transformer\TransformersFactory;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * <storybook:transform component="component:button" name="uri" arguments="{url: 'https://example.com'}" />
 */
final class TransformViewHelper extends AbstractViewHelper
{
    public function __construct(
        private readonly ComponentCollectionService $componentCollectionService,
        private readonly TransformersFactory $argumentTransformerFactory,
    ) {
    }

    public function initializeArguments(): void
    {
        $this->registerArgument('component', 'string', 'the name of the component (eg: component:button)', true);
        $this->registerArgument('name', 'string', 'the name of the argument that will be transformed', true);
        $this->registerArgument('arguments', 'array', 'The arguments to pass to the transformer', true);
    }

    #[Override]
    public function render(): mixed
    {
        $component = $this->arguments['component'];
        $argumentToTransform = $this->arguments['name'];
        $arguments = $this->arguments['arguments'];

        $viewHelperName = new ViewHelperName($component);

        $collection = $this->componentCollectionService->getCollection($viewHelperName);

        $transformers = $this->argumentTransformerFactory->get(
            collection: $collection,
            viewHelperName: $viewHelperName,
        );

        if (!$transformers->has($argumentToTransform)) {
            throw new RuntimeException(
                'The argument transformer for "' . $argumentToTransform . '" is not defined. Please add it to the component definition or remove it from the story file.',
                1988452467
            );
        }

        return $transformers->execute($argumentToTransform, $arguments);
    }
}
