<?php

declare(strict_types=1);

namespace XUnitLint\Rule\Conditional;

use PhpParser\Node\Stmt\Foreach_;

class ForEachRule extends ConditionalTestLogicRule
{
    protected string $message = '(for each)';

    public function getNodeType(): string
    {
        return Foreach_::class;
    }
}