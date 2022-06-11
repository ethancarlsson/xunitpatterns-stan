<?php

declare(strict_types=1);

namespace XUnitLint\Answerer;

use PHPStan\Reflection\NamespaceAnswerer;

class NamespaceAnswererImp implements NamespaceAnswerer
{

    public function __construct(private string $namespaceName)
    {
    }

    public function getNamespace(): ?string
    {
        return $this->namespaceName;
    }
}