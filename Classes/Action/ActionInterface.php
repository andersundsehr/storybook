<?php

declare(strict_types=1);

namespace Andersundsehr\Storybook\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure(public: true)]
interface ActionInterface
{
    public function __invoke(ServerRequestInterface $request): ResponseInterface;
}
