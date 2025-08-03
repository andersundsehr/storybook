<?php

declare(strict_types=1);

namespace Andersundsehr\Storybook\Transformer\Defaults;

use Andersundsehr\Storybook\Transformer\Attribute\TypeTransformer;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\LinkHandling\TypolinkParameter;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\ResourceFactory;

final readonly class DefaultTransformer
{
    public function __construct(private ResourceFactory $resourceFactory)
    {
    }

    #[TypeTransformer(priority: 100)]
    public function uri(string $url): Uri
    {
        return new Uri($url);
    }

    // Transformer also matches to FileInterface or AbstractFile
    #[TypeTransformer(priority: 100)]
    public function file(string $extPath): File
    {
        return $this->resourceFactory->retrieveFileOrFolderObject($extPath);
    }

    #[TypeTransformer(priority: 100)]
    public function typolinkParameter(
        string $url = '',
        TypolinkTargetEnum $target = TypolinkTargetEnum::none,
        string $class = '',
        string $title = '',
        string $additionalParams = '',
    ): TypolinkParameter
    {
        return TypolinkParameter::createFromTypolinkParts(
            [
                'url' => $url,
                'target' => $target->value,
                'class' => $class,
                'title' => $title,
                'additionalParams' => $additionalParams,
            ]
        );
    }
}
