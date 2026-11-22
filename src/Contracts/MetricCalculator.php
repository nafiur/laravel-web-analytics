<?php

namespace Nafiur\WebAnalytics\Contracts;

interface MetricCalculator
{
    public function name(): string;

    /** @param array<string, mixed> $context */
    public function calculate(array $context): int|float;
}
