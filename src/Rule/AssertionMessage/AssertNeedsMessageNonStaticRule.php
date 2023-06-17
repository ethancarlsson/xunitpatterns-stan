<?php

declare(strict_types=1);

namespace XUnitLint\Rule\AssertionMessage;

use PhpParser\Node\Expr\MethodCall;

class AssertNeedsMessageNonStaticRule extends AssertNeedsMessageRule
{
    public function getNodeType(): string
    {
        return MethodCall::class;
    }
}
