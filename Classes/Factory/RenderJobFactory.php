<?php

declare(strict_types=1);

namespace Andersundsehr\Storybook\Factory;

use Closure;
use Andersundsehr\Storybook\Dto\RenderJob;
use Andersundsehr\Storybook\Dto\ViewHelperName;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use TYPO3\CMS\Core\Site\SiteFinder;

use function json_decode;
use function strlen;
use function substr;

final readonly class RenderJobFactory
{
    public const string SLOT_PREFIX = 'slot__';

    public function __construct(private SiteFinder $siteFinder)
    {
    }

    /**
     * TODO refector or at least reformat this method.
     */
    public function createFromRequest(ServerRequestInterface $request): RenderJob
    {
        $body = $request->getBody()->getContents() ?: throw new RuntimeException('Missing request body for render', 1532676721);
        $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);

        $arguments = $data['arguments'] ?? throw new RuntimeException('Missing `arguments` parameter in request body', 8927775902);

        $args = [];
        /** @var array<string, Closure():string> $slots */
        $slots = [];
        foreach ($arguments as $key => $value) {
            if (str_starts_with((string) $key, self::SLOT_PREFIX)) {
                $slots[substr((string) $key, strlen(self::SLOT_PREFIX))] = static fn(): mixed => $value;
            } else {
                $args[$key] = $value;
            }
        }

        $viewHelper = ($data['viewHelper'] ?? null) ?? throw new RuntimeException('Missing `viewHelper` parameter in request body', 9632741250);
        $site = $this->siteFinder->getSiteByIdentifier($data['site'] ?? throw new RuntimeException('Missing `site` parameter in request body', 2443948237));
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

        $renderRequest = $request->withAttribute('site', $site)->withAttribute('language', $siteLanguage);
        return new RenderJob(
            new ViewHelperName($viewHelper),
            $site,
            $siteLanguage,
            $args,
            $slots,
            $renderRequest,
        );
    }
}
