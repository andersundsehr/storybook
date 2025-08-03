<?php

declare(strict_types=1);

namespace Andersundsehr\Storybook\Transformer\Defaults;

enum TypolinkTargetEnum: string
{
    case none = '';
    case _blank = '_blank';
    case _self = '_self';
    case _top = '_top';
    case _parent = '_parent';
}
