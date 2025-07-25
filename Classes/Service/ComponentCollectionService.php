<?php

declare(strict_types=1);

namespace Andersundsehr\Storybook\Service;

use Andersundsehr\Storybook\Dto\ViewHelperName;
use RuntimeException;
use TYPO3\CMS\Fluid\Core\ViewHelper\ViewHelperResolverFactoryInterface;
use TYPO3Fluid\Fluid\Core\Component\ComponentDefinitionProviderInterface;
use TYPO3Fluid\Fluid\Core\Component\ComponentTemplateResolverInterface;

final readonly class ComponentCollectionService
{
    public function __construct(
        private ViewHelperResolverFactoryInterface $viewHelperResolverFactory,
    ) {
    }

    public function getCollection(ViewHelperName $viewHelperName): ComponentDefinitionProviderInterface&ComponentTemplateResolverInterface
    {
        $viewHelperResolver = $this->viewHelperResolverFactory->create();
        $viewHelperResolverDelegate = $viewHelperResolver->getResponsibleDelegate(
            $viewHelperName->namespace,
            $viewHelperName->name
        );
        if (!$viewHelperResolverDelegate) {
            throw new RuntimeException(
                'Could not resolve component collection for ' . $viewHelperName->namespace . ':' . $viewHelperName->name . ', no ViewHelperResolverDelegate found',
                3009437593
            );
        }

        if (!$viewHelperResolverDelegate instanceof ComponentDefinitionProviderInterface) {
            throw new RuntimeException(
                'Could not resolve component collection for ' . $viewHelperName->namespace . ':' . $viewHelperName->name . ', ViewHelperResolverDelegate does not implement ' . ComponentDefinitionProviderInterface::class,
                3009437594
            );
        }

        if (!$viewHelperResolverDelegate instanceof ComponentTemplateResolverInterface) {
            throw new RuntimeException(
                'Could not resolve component collection for ' . $viewHelperName->namespace . ':' . $viewHelperName->name . ', ViewHelperResolverDelegate does not implement ' . ComponentTemplateResolverInterface::class,
                3009437595
            );
        }

        return $viewHelperResolverDelegate;
    }
}
