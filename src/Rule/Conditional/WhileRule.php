<?php

declare(strict_types=1);

namespace XUnitLint\Rule\Conditional;

use PhpParser\Node\Stmt\While_;

class WhileRule extends ConditionalTestLogicRule
{
    protected string $message = '(while loop)';

    public function getNodeType(): string
    {
        return While_::class;
    }
}
