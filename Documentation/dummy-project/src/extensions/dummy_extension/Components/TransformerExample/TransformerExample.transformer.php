<?php

use Andersundsehr\Storybook\Transformer\ArgumentTransformers;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;

return new ArgumentTransformers(
    // uri: uses the TypeTransformer automatically.
    websocket: fn(string $url): Uri => (new Uri($url))->withScheme('wss'),
    contextIcon: fn(ContextualFeedbackSeverity $severity): string => $severity->getIconIdentifier(),
    // TODO add more examples (eg. SysFile/SysFileReference, ContentBlockData)
    combineUri: fn(string $host, string $path, string $query = '', string $fragment = '', string $scheme = 'https'): Uri => new Uri(
        $scheme . '://' . $host . '/' . $path . '?' . $query . '#' . $fragment
    ),
);
