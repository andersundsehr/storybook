<?php

declare(strict_types=1);

namespace Andersundsehr\Storybook\Service;

use Andersundsehr\Storybook\Middleware\StorybookMiddleware;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Http\ImmediateResponseException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

use function bin2hex;
use function file_exists;
use function getenv;
use function hash_equals;
use function preg_match;
use function putenv;
use function random_bytes;
use function str_contains;
use function strtoupper;

final readonly class KeyService
{
    /**
     * @throws ImmediateResponseException
     */
    public function validateKey(ServerRequestInterface $request): void
    {
        $apiKey = $this->getCurrentKey();
        if ($request->getMethod() === 'OPTIONS') {
            // OPTIONS request, we just allow CORS and return a 200 OK response
            // This is needed for the Storybook UI to work properly with CORS
            $response = new HtmlResponse('', 200);
            $response = StorybookMiddleware::cors($response);
            throw new ImmediateResponseException($response, 4080773490);
        }

        if (getenv('STORYBOOK_TYPO3_DISABLE_KEY_VALIDATION') === '1') {
            return;
        }

        $givenKey = $request->getHeaderLine('X-Storybook-TYPO3-Key');
        if (!$givenKey) {
            $this->throw('Header X-Storybook-TYPO3-Key is missing');
        }

        if (!$this->keyIsValid($givenKey)) {
            $this->throw('Header X-Storybook-TYPO3-Key key is invalid, must start with _ext_storybook_ and be followed by 32 hexadecimal characters');
        }

        if (!hash_equals($apiKey, $givenKey)) {
            $this->throw('Header X-Storybook-TYPO3-Key key is invalid');
        }
    }

    private function getCurrentKey(): string
    {
        $key = getenv('STORYBOOK_TYPO3_KEY') ?: $_ENV['STORYBOOK_TYPO3_KEY'] ?? false;
        if ($key && $this->keyIsValid($key)) {
            return $key;
        }

        if (!Environment::getContext()->isDevelopment()) {
            $this->throw('No valid Storybook key found in environment variables. Please set the STORYBOOK_TYPO3_KEY environment variable or create a .env file with the key.');
        }

        $envFilename = Environment::getProjectPath() . '/.env';
        if (!file_exists($envFilename)) {
            GeneralUtility::writeFile($envFilename, '');
        }

        $content = file_get_contents($envFilename);
        if ($content === false) {
            $this->throw('Could not read .env file, please check permissions.');
        }

        if (str_contains($content, "STORYBOOK_TYPO3_KEY=")) {
            // no automatic .env file loading was done, so we need to load it manually
            $lines = explode("\n", $content);
            foreach ($lines as $line) {
                $line = trim($line);
                if (str_starts_with($line, 'STORYBOOK_TYPO3_KEY=')) {
                    $key = str_replace('STORYBOOK_TYPO3_KEY=', '', $line);
                    if (!$this->keyIsValid($key)) {
                        $this->throw('Key in .env file is invalid, must start with _ext_storybook_ and be followed by 32 hexadecimal characters');
                    }

                    putenv('STORYBOOK_TYPO3_KEY=' . $key);
                    $_ENV['STORYBOOK_TYPO3_KEY'] = $key;
                    return $key;
                }
            }
        }

        // we create a new key
        $key = '_ext_storybook_' . strtoupper(bin2hex(random_bytes(16)));
        putenv('STORYBOOK_TYPO3_KEY=' . $key);
        $_ENV['STORYBOOK_TYPO3_KEY'] = $key;
        file_put_contents($envFilename, "\nSTORYBOOK_TYPO3_KEY={$key}\n", FILE_APPEND | LOCK_EX);

        return $key;
    }

    /**
     * @throws ImmediateResponseException
     */
    private function throw(string $message): never
    {
        $response = new HtmlResponse($message, 401);
        $response = StorybookMiddleware::cors($response);
        throw new ImmediateResponseException($response, 4323399613);
    }

    private function keyIsValid(string $key): bool
    {
        return (bool)preg_match('/^_ext_storybook_[A-F0-9]{32}$/', $key);
    }
}
