<?php

declare(strict_types=1);

namespace Andersundsehr\Storybook\Action;

use Andersundsehr\Storybook\Service\GlobalTypesService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\JsonResponse;

use function array_map;
use function array_values;
use function json_decode;

final readonly class PreviewAction implements ActionInterface
{
    public function __construct(
        private GlobalTypesService $globalTypesService,
    ) {
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $body = $request->getBody()->getContents();
        $globals = json_decode($body ?: '[]', true, 512, JSON_THROW_ON_ERROR)['globals'] ?? [];

        $globalTypes = $this->globalTypesService->getGlobalTypes($globals);

        return new JsonResponse([
            'initialGlobals' => array_map(array_key_first(...), $globalTypes),
            'globalTypes' => array_map(fn(array $x): array => ([
                'toolbar' => [
                    'dynamicTitle' => true,
                    'items' => array_values($x),
                ]
            ]), $globalTypes),
        ]);
    }
}
