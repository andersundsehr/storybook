<?php

declare(strict_types=1);

namespace Andersundsehr\Storybook\Action;

use Andersundsehr\Storybook\Factory\RenderJobFactory;
use Andersundsehr\Storybook\Service\ComponentCollectionService;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextFactory;
use TYPO3Fluid\Fluid\Core\Component\ComponentDefinitionProviderInterface;
use TYPO3Fluid\Fluid\Core\Component\ComponentTemplateResolverInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolverDelegateInterface;

final readonly class RenderAction implements ActionInterface
{
    public function __construct(
        private ComponentCollectionService $componentCollectionService,
        private RenderingContextFactory $renderingContextFactory,
        private RenderJobFactory $renderJobFactory,
    ) {
    }

    public function __invoke(ServerRequestInterface $request): HtmlResponse
    {
        $renderJob = $this->renderJobFactory->createfromRequest($request);

        // do we want the site context? => would be nice
        // do we want the langauge? => would be nice
        // do we want auth for this route? => maybe not necessary

        return $this->componentCollectionService->tryCollection(
            $renderJob->viewHelper->namespace,
            function (
                ViewHelperResolverDelegateInterface&ComponentDefinitionProviderInterface&ComponentTemplateResolverInterface $collection
            ) use ($renderJob): HtmlResponse {
                $renderingContext = $this->renderingContextFactory->create(
                    request: $renderJob->renderRequest,
                );

                $componentRenderer = $collection->getComponentRenderer();
                $html = $componentRenderer->renderComponent($renderJob->viewHelper->name, $renderJob->arguments, $renderJob->slots, $renderingContext);
                return new HtmlResponse(trim($html));
            }
        );
    }
}
