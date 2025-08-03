<?php

declare(strict_types=1);

namespace Andersundsehr\Storybook\Transformer\Defaults;

use RuntimeException;
use Andersundsehr\Storybook\Transformer\Attribute\TypeTransformer;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\LinkHandling\TypolinkParameter;
use TYPO3\CMS\Core\Resource\File;
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
    public function file(string $extPath = 'EXT:storybook/Resources/Public/Icons/Extension.svg'): File
    {
        $file = $this->resourceFactory->retrieveFileOrFolderObject($extPath);
        if (!$file instanceof File) {
            throw new RuntimeException('The file with the path "' . $extPath . '" could not be resolved to a File object.', 7836790419);
        }

        return $file;
    }

    #[TypeTransformer(priority: 100)]
    public function typolinkParameter(
        string $url = '',
        TypolinkTargetEnum $target = TypolinkTargetEnum::none,
        string $class = '',
        string $title = '',
        string $additionalParams = '',
    ): TypolinkParameter {
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
