<?php

declare(strict_types=1);

namespace Andersundsehr\Storybook\Middleware;

use Andersundsehr\Storybook\Action\ComponentMetaAction;
use Andersundsehr\Storybook\Action\PreviewAction;
use Andersundsehr\Storybook\Action\RenderAction;
use Andersundsehr\Storybook\Service\KeyService;
use Exception;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Http\JsonResponse;

use function str_replace;
use function str_starts_with;

readonly class StorybookMiddleware implements MiddlewareInterface
{
    public function __construct(
        private ContainerInterface $container,
        private KeyService $keyService,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!str_starts_with($request->getUri()->getPath(), '/_storybook/')) {
            return $handler->handle($request);
        }

        $this->keyService->validateKey($request);

        try {
            $response = $this->handle($request);
        } catch (Throwable $throwable) {
            $trace = str_replace(Environment::getProjectPath() . '/', '', $throwable->getTraceAsString());
            $trace = str_replace('): ', "):\n   ", $trace);

            $html = '<div class="storybook-error"><h1>💥 Backend ERROR</h1><p>💬 ' . htmlspecialchars($throwable->getMessage()) . '</p>' . PHP_EOL;
            $html .= '🕵🏻‍♂️ Stack Trace:<br><pre>' . htmlspecialchars($trace) . '</pre>';
            $html .= <<<'EOF'
                <style>
                .storybook-error {
                  font: 16px/1.5 "Courier New", Courier, monospace;
                }
                .storybook-error pre {
                  background: #292929;
                  color: #e2e2e2;
                  padding: 10px;
                  overflow: auto;
                  font: monospace;
                  max-height: 250px;
                }
                </style></div>
                EOF;
            $html = str_replace(Environment::getProjectPath() . '/', '', $html);
            $message = $throwable->getMessage();
            $message = str_replace(Environment::getProjectPath() . '/', '', $message);

            $data = [
                'errorType' => 'extension',
                'reason' => $message,
                'stackTrace' => $trace,
                'errorHtml' => $html,
            ];
            $response = new JsonResponse($data, 500);
        }

        // Add CORS headers to the response, not a security risk here as this is only used if the user has the correct API key
        return self::cors($response);
    }

    private function handle(ServerRequestInterface $request): ResponseInterface
    {
        if ($request->getUri()->getPath() === '/_storybook/componentMeta') {
            $componentMetaAction = $this->container->get(ComponentMetaAction::class);
            return $componentMetaAction->__invoke($request);
        }

        if ($request->getUri()->getPath() === '/_storybook/preview') {
            $previewAction = $this->container->get(PreviewAction::class);
            return $previewAction->__invoke($request);
        }

        if ($request->getUri()->getPath() === '/_storybook/render') {
            $renderAction = $this->container->get(RenderAction::class);
            return $renderAction->__invoke($request);
        }

        return new HtmlResponse('<h1>ERROR</h1><p>Invalid route to Storybook middleware</p>', 400);
    }

    /**
     * @template T of MessageInterface
     * @param T $response
     * @return T
     */
    public static function cors(MessageInterface $response): MessageInterface
    {
        return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Methods', '*')
            ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, X-Storybook-TYPO3-Key')
            ->withHeader('X-Content-Type-Options', '')
            ->withHeader('Access-Control-Max-Age', '86400')
            ;
    }
}
