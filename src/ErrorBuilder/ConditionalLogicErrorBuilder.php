<?php

declare(strict_types=1);

namespace XUnitLint\ErrorBuilder;

use PHPStan\Rules\RuleErrorBuilder;

class ConditionalLogicErrorBuilder extends RuleErrorBuilder
{
    public static function message(string $message): RuleErrorBuilder
    {
        return parent::message(
            "Conditional Test Logic $message: Avoid any control structures inside test methods, "
            . 'it should be clear what code the test is running | '
            . 'http://xunitpatterns.com/Conditional%20Test%20Logic.html'
        );
    }
}