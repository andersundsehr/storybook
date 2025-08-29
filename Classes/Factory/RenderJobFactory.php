<?php

declare(strict_types=1);

namespace Andersundsehr\Storybook\Factory;

use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use Andersundsehr\Storybook\Dto\RenderJob;
use Andersundsehr\Storybook\Dto\ViewHelperName;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Exception\Page\PageNotFoundException;
use TYPO3\CMS\Core\Html\SanitizerBuilderFactory;
use TYPO3\CMS\Core\Http\NormalizedParams;
use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\TypoScript\FrontendTypoScript;
use TYPO3\CMS\Core\TypoScript\FrontendTypoScriptFactory;
use TYPO3\CMS\Core\TypoScript\IncludeTree\SysTemplateRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\RootlineUtility;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

use function json_decode;
use function trim;

final readonly class RenderJobFactory
{
    public function __construct(
        private SiteFinder $siteFinder,
        private FrontendTypoScriptFactory $frontendTypoScriptFactory,
        private SysTemplateRepository $sysTemplateRepository,
        private Context $context,
        private FrontendUserAuthentication $frontendUserAuthentication,
        private SanitizerBuilderFactory $sanitizerBuilderFactory,
    ) {
    }

    /**
     * TODO refactor or at least reformat this method.
     */
    public function createFromRequest(ServerRequestInterface $request): RenderJob
    {
        $renderRequest = $request;
        $renderRequest = $renderRequest->withUri($renderRequest->getUri()->withPath('/')->withQuery('')->withFragment(''));

        $normalizedParams = NormalizedParams::createFromRequest($renderRequest);
        $renderRequest = $renderRequest->withAttribute('normalizedParams', $normalizedParams);

        $body = $request->getBody()->getContents() ?: throw new RuntimeException('Missing request body for render', 1532676721);

        $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);

        $storybookArguments = $data['arguments'] ?? throw new RuntimeException('Missing `arguments` parameter in request body', 8927775902);
        $storybookArguments = $this->sanitizeArguments($storybookArguments);

        $iframeContextId = $data['iframeContextId'] ?? throw new RuntimeException('Missing `iframeContextId` parameter in request body', 3505032869);

        $viewHelper = ($data['viewHelper'] ?? null) ?? throw new RuntimeException('Missing `viewHelper` parameter in request body', 9632741250);

        $site = $this->siteFinder->getSiteByIdentifier($data['site'] ?? throw new RuntimeException('Missing `site` parameter in request body', 2443948237));

        $renderRequest = $renderRequest->withAttribute('site', $site);

        $siteLanguage = null;
        foreach ($site->getLanguages() as $language) {
            $siteLangaugeHref = $data['siteLanguage']
                ?? throw new RuntimeException(
                    'Missing `siteLanguage` parameter in request body',
                    7817723176
                );
            if ($language->getHreflang() === $siteLangaugeHref) {
                $siteLanguage = $language;
                break;
            }
        }

        if (!$siteLanguage) {
            $validLanguages = implode(', ', array_map(static fn(SiteLanguage $lang): string => $lang->getHreflang(), $site->getLanguages()));
            throw new RuntimeException(
                'Invalid `siteLanguage` parameter in request body. for site: ' . $site->getIdentifier() . PHP_EOL
                . ' got: ' . ($data['siteLanguage'] ?? 'null') . ' valid values are: ' . $validLanguages,
                3709594580
            );
        }

        $renderRequest = $renderRequest->withAttribute('language', $siteLanguage);


        try {
            $rootLine = GeneralUtility::makeInstance(RootlineUtility::class, $site->getRootPageId())->get();
            $sysTemplateRows = $this->sysTemplateRepository->getSysTemplateRowsByRootline($rootLine, $request);
        } catch (PageNotFoundException) {
            // if the root page is not found, we cannot assume that the database is completely empty. We can ignore the sys_template rows for now. (USE site sets they are better for that!!!)
            $sysTemplateRows = [];
        }

        $plainFrontendTypoScript = $this->frontendTypoScriptFactory->createSettingsAndSetupConditions($site, $sysTemplateRows, [], null);
        $plainFrontendTypoScript = $this->frontendTypoScriptFactory->createSetupConfigOrFullSetup(
            true,
            $plainFrontendTypoScript,
            $site,
            $sysTemplateRows,
            [],
            '',
            null,
            $renderRequest
        );


        $renderRequest = $renderRequest->withAttribute('frontend.typoscript', $plainFrontendTypoScript);

        // set the global, since some ViewHelper still fallback to $GLOBALS['TYPO3_REQUEST']
        $GLOBALS['TYPO3_REQUEST'] = $renderRequest;

        $tsfe = GeneralUtility::makeInstance(
            TypoScriptFrontendController::class,
            $this->context,
            $site,
            $siteLanguage,
            new PageArguments(0, '0', []),
            $this->frontendUserAuthentication
        );
        $GLOBALS['TSFE'] = $tsfe;
        $tsfe->initializePageRenderer($renderRequest);
        $this->preparePageContentGeneration($plainFrontendTypoScript, $tsfe, $normalizedParams);

        $renderRequest = $renderRequest->withAttribute('frontend.controller', $tsfe);

        // set the global, since some ViewHelper still fallback to $GLOBALS['TYPO3_REQUEST']
        $GLOBALS['TYPO3_REQUEST'] = $renderRequest;

        return new RenderJob(
            new ViewHelperName($viewHelper),
            $site,
            $siteLanguage,
            $renderRequest,
            $storybookArguments,
            $renderRequest->getUri()->__toString(),
            $iframeContextId
        );
    }

    private function preparePageContentGeneration(
        FrontendTypoScript $plainFrontendTypoScript,
        TypoScriptFrontendController $tsfe,
        NormalizedParams $normalizedParams,
    ): void {
        // instead of calling $GLOBALS['TSFE']->preparePageContentGeneration($request);
        // we call some the method directly, since we can't call all at this point in the request lifecycle.

        $typoScriptConfigArray = $plainFrontendTypoScript->getConfigArray();
        // calculate the absolute path prefix
        $tsfe->absRefPrefix = trim((string)$typoScriptConfigArray['absRefPrefix']);
        if ($tsfe->absRefPrefix === 'auto') {
            $tsfe->absRefPrefix = $normalizedParams->getSitePath();
        }

        // config.forceAbsoluteUrls will override absRefPrefix
        if ($typoScriptConfigArray['forceAbsoluteUrls'] ?? false) {
            $tsfe->absRefPrefix = $normalizedParams->getSiteUrl();
        }
    }

    /**
     * @param array<string, mixed> $storybookArguments
     * @return array<string, string|int|float|bool|null>
     */
    private function sanitizeArguments(array $storybookArguments): array
    {
        $result = [];
        foreach ($storybookArguments as $key => $value) {
            $result[$key] = match (gettype($value)) {
                'integer', 'double', 'boolean', 'NULL' => $value,
                'string' => $this->sanitizerBuilderFactory->build('default')->build()->sanitize($value),
                default => throw new RuntimeException(
                    'Invalid argument type for key "' . $key . '". Expected string, integer, double, boolean or NULL, got: ' . gettype($value),
                    8927775903
                ),
            };
        }

        return $result;
    }
}
