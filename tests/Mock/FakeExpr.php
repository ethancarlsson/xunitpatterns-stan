<?php

declare(strict_types=1);

namespace XUnitLint\Tests\Mock;

use PhpParser\Node\Expr;

class FakeExpr extends Expr
{
    public function getType(): string
    {
        return 'fake expr';
    }

    /**
     * @return string[]
     */
    public function getSubNodeNames(): array
    {
        return [];
    }
}