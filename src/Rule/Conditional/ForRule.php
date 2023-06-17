<?php

declare(strict_types=1);

namespace XUnitLint\Rule\Conditional;

use PhpParser\Node\Stmt\For_;
use PHPStan\Rules\Rule;

class ForRule extends ConditionalTestLogicRule
{

    protected string $message = '(For loops)';

    public function getNodeType(): string
    {
        return For_::class;
    }
}
