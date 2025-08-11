<?php

declare(strict_types=1);

namespace Andersundsehr\Storybook\Service;

use Andersundsehr\Storybook\Dto\RenderJob;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Page\AssetCollector;
use TYPO3\CMS\Core\Page\AssetRenderer;

use function array_filter;
use function implode;
use function str_contains;
use function trim;

use const PHP_EOL;

final readonly class PreviewAssetRenderer
{
    public function __construct(
        private AssetCollector $assetCollector,
        private AssetRenderer $assetRenderer,
    ) {
    }

    public function renderAssets(RenderJob $renderJob): string
    {
        foreach ($this->assetCollector->getJavaScripts() as $identifier => $asset) {
            $asset['options']['external'] = true;
            $this->assetCollector->addJavaScript(
                $identifier,
                $this->changeUrl($asset['source'], $renderJob->iframeContextId),
                $asset['attributes'],
                $asset['options']
            );
        }

        foreach ($this->assetCollector->getStyleSheets() as $identifier => $asset) {
            $asset['options']['external'] = true;
            $this->assetCollector->addStyleSheet(
                $identifier,
                $this->changeUrl($asset['source'], $renderJob->iframeContextId),
                $asset['attributes'],
                $asset['options']
            );
        }

        return trim(implode(PHP_EOL, array_filter([
            $this->assetRenderer->renderInlineJavaScript(true),
            $this->assetRenderer->renderInlineJavaScript(false),
            $this->assetRenderer->renderJavaScript(true),
            $this->assetRenderer->renderJavaScript(false),
            $this->assetRenderer->renderInlineStyleSheets(true),
            $this->assetRenderer->renderInlineStyleSheets(false),
            $this->assetRenderer->renderStyleSheets(true),
            $this->assetRenderer->renderStyleSheets(false),
        ])));
    }

    private function changeUrl(string $source, string $iframeContextId): string
    {
        if (str_contains($source, '/@vite')) {
            // if you include /@vite/client or /@vite-plugin-checker-runtime-entry
            // we do not want to reload it every time as that is not necessary
            return $source;
        }

        // add cache bust so the module is reevaluated on each render
        // this is necessary because the iframe is not reloaded and the module not re-evaluated automatically
        if (str_contains($source, '#')) {
            return $source . '&id=' . $iframeContextId;
        }

        return $source . '#id=' . $iframeContextId;
    }
}
