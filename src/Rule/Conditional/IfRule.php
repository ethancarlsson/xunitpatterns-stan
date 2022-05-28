<?php

declare(strict_types=1);

namespace XUnitLint\Rule\Conditional;

use PhpParser\Node\Stmt\If_;

class IfRule extends ConditionalTestLogicRule
{
    protected string $message = '(If statement)';

    public function getNodeType(): string
    {
        return If_::class;
    }
}
