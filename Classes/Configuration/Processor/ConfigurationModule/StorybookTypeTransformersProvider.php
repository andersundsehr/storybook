<?php

declare(strict_types=1);

namespace Andersundsehr\Storybook\Configuration\Processor\ConfigurationModule;

use Andersundsehr\Storybook\Transformer\Transformer;
use Andersundsehr\Storybook\Transformer\TypeTransformers;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use TYPO3\CMS\Lowlevel\ConfigurationModuleProvider\AbstractProvider;

/**
 * @phpstan-import-type TransformerConfigArray from TypeTransformers
 */
#[AutoconfigureTag(
    'lowlevel.configuration.module.provider',
    attributes: [
        'label' => 'EXT:storybook TypeTransformers',
        'identifier' => 'storybook-type-transformers',
    ]
)]
final class StorybookTypeTransformersProvider extends AbstractProvider
{
    public function __construct(private readonly TypeTransformers $typeTransformers)
    {
    }

    /**
     * @return array{raw: array<string, list<TransformerConfigArray>>, used: array<string, Transformer>}
     */
    public function getConfiguration(): array
    {
        return $this->typeTransformers->getConfiguration();
    }
}
