<?php

declare(strict_types=1);

namespace XUnitLint\Rule\Conditional;

use PHPStan\Node\BooleanOrNode;

class BooleanOrRule extends ConditionalTestLogicRule
{
    protected string $message = '(Boolean or/||)';

    public function getNodeType(): string
    {
        return BooleanOrNode::class;
    }
}
