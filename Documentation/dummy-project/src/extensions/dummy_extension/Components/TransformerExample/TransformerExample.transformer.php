<?php

use Andersundsehr\Storybook\Transformer\ArgumentTransformers;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Resource\File;

return new ArgumentTransformers(
    // uri: uses the TypeTransformer automatically.
    websocket: fn(string $url): Uri => (new Uri($url))->withScheme('wss'),
    contextIcon: fn(ContextualFeedbackSeverity $severity): string => $severity->getIconIdentifier(),
    // TODO add more examples (eg. SysFile/SysFileReference, ContentBlockData)
    combineUri: fn(string $host, string $path, string $query = '', string $fragment = '', string $scheme = 'https'): Uri => new Uri(
        $scheme . '://' . $host . '/' . $path . '?' . $query . '#' . $fragment
    ),
    transformerWithoutArguments: fn(): Uri => new Uri('https://storybook.andersundsehr.com'),
    // file: uses the TypeTransformer automatically.
    // typolink: uses the TypeTransformer automatically.
    // Dependency injection of any public service is possible, so we can inject the ResourceFactory here:
    fileWithDefault: function (ResourceFactory $resourceFactory, string $extPath = 'EXT:storybook/Resources/Public/Icons/AusLogo.svg'): File {
        $file = $resourceFactory->retrieveFileOrFolderObject($extPath);
        assert($file instanceof File, 'The file with the path "' . $extPath . '" could not be resolved to a File object.');
        return $file;
    },
);
