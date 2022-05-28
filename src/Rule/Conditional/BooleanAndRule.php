<?php

declare(strict_types=1);

namespace XUnitLint\Rule\Conditional;

use PHPStan\Node\BooleanAndNode;

class BooleanAndRule extends ConditionalTestLogicRule
{
    protected string $message = '(Boolean and/&&)';

    public function getNodeType(): string
    {
        return BooleanAndNode::class;
    }
}
