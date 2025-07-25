<?php

declare(strict_types=1);

namespace Andersundsehr\Storybook\Factory;

use Andersundsehr\Storybook\Dto\RenderJob;
use Andersundsehr\Storybook\Dto\ViewHelperName;
use Exception;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use TYPO3\CMS\Core\Exception\Page\PageNotFoundException;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\TypoScript\FrontendTypoScriptFactory;
use TYPO3\CMS\Core\TypoScript\IncludeTree\SysTemplateRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\RootlineUtility;

use function json_decode;

final readonly class RenderJobFactory
{
    public function __construct(
        private SiteFinder $siteFinder,
        private FrontendTypoScriptFactory $frontendTypoScriptFactory,
        private SysTemplateRepository $sysTemplateRepository,
    ) {
    }

    /**
     * TODO refactor or at least reformat this method.
     */
    public function createFromRequest(ServerRequestInterface $request): RenderJob
    {
        $renderRequest = $request;
        $body = $request->getBody()->getContents() ?: throw new RuntimeException('Missing request body for render', 1532676721);

        $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);

        $storybookArguments = $data['arguments'] ?? throw new RuntimeException('Missing `arguments` parameter in request body', 8927775902);

        $viewHelper = ($data['viewHelper'] ?? null) ?? throw new RuntimeException('Missing `viewHelper` parameter in request body', 9632741250);
        $site = $this->siteFinder->getSiteByIdentifier($data['site'] ?? throw new RuntimeException('Missing `site` parameter in request body', 2443948237));

        $renderRequest = $renderRequest->withAttribute('site', $site);

        $siteLanguage = null;
        foreach ($site->getLanguages() as $language) {
            if ($language->getHreflang() === ($data['siteLanguage'] ?? throw new RuntimeException('Missing `siteLanguage` parameter in request body', 7817723176))) {
                $siteLanguage = $language;
                break;
            }
        }

        if (!$siteLanguage) {
            $validLanguages = implode(', ', array_map(static fn($lang): string => $lang->getHreflang(), $site->getLanguages()));
            throw new RuntimeException(
                'Invalid `siteLanguage` parameter in request body. for site: ' . $site->getIdentifier() . PHP_EOL
                . ' got: ' . ($data['siteLanguage'] ?? 'null') . ' valid values are: ' . $validLanguages,
                3709594580
            );
        }

        $renderRequest = $renderRequest->withAttribute('language', $siteLanguage);


//        $GLOBALS['TSFE'] = GeneralUtility::makeInstance(TypoScriptFrontendController::class);
//        $GLOBALS['TSFE']->initializePageRenderer($renderRequest);
//        $renderRequest = $renderRequest->withAttribute('frontend.controller', $GLOBALS['TSFE']);

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

        return new RenderJob(
            new ViewHelperName($viewHelper),
            $site,
            $siteLanguage,
            $renderRequest,
            $storybookArguments,
        );
    }
}
