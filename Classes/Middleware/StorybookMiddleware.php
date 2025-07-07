<?php

declare(strict_types=1);

namespace Andersundsehr\Storybook\Middleware;

use Andersundsehr\Storybook\Action\ComponentMetaAction;
use Andersundsehr\Storybook\Action\PreviewAction;
use Andersundsehr\Storybook\Action\RenderAction;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Http\HtmlResponse;

use function str_starts_with;

readonly class StorybookMiddleware implements MiddlewareInterface
{
    public function __construct(
        private PreviewAction $previewAction,
        private ComponentMetaAction $componentMetaAction,
        private RenderAction $renderAction,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!str_starts_with($request->getUri()->getPath(), '/_storybook/')) {
            return $handler->handle($request);
        }

        try {
            $response = $this->handle($request);
        } catch (Exception $exception) {
            $response = new HtmlResponse(
                '<h1>ERROR</h1><p>' . htmlspecialchars($exception->getMessage()) . '</p>',
                500,
                ['Content-Type' => 'text/html']
            );
        }

        return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Methods', '*')
            ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With')
            ->withHeader('X-Content-Type-Options', '')
            ->withHeader('Access-Control-Max-Age', '86400');
    }

    private function handle(ServerRequestInterface $request): ResponseInterface
    {
        if ($request->getUri()->getPath() === '/_storybook/componentMeta') {
            return $this->componentMetaAction->__invoke($request);
        }

        if ($request->getUri()->getPath() === '/_storybook/preview') {
            return $this->previewAction->__invoke($request);
        }

        if ($request->getUri()->getPath() === '/_storybook/render') {
            return $this->renderAction->__invoke($request);
        }

        return new HtmlResponse('<h1>ERROR</h1><p>Invalid request to Storybook middleware</p>', 400);
    }
}
