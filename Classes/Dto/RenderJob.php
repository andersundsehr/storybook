<?php

declare(strict_types=1);

namespace Andersundsehr\Storybook\Dto;

use Closure;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;

final readonly class RenderJob
{
    public function __construct(
        public ViewHelperName $viewHelper,
        public Site $site,
        public SiteLanguage $siteLanguage,
        public ServerRequestInterface $renderRequest,
        /** @var array<string, null|bool|int|float|string> */
        public array $rawArgs,
        public string $baseHref,
        public string $iframeContextId,
    ) {
    }
}
