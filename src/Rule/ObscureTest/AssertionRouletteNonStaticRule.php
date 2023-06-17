<?php

declare(strict_types=1);

namespace XUnitLint\Rule\ObscureTest;

use PhpParser\Node\Expr\MethodCall;

class AssertionRouletteNonStaticRule extends AssertionRouletteRule
{
    public function getNodeType(): string
    {
        return MethodCall::class;
    }
}
