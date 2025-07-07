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
        /** @var array<string, string> */
        public array $arguments,
        /** @var array<string, Closure():string> */
        public array $slots,
        public ServerRequestInterface $renderRequest,
    ) {
    }
}
