<?php

namespace Nafiur\WebAnalytics\Contracts;

use Nafiur\WebAnalytics\Data\EventData;
use Nafiur\WebAnalytics\Data\VisitContext;

interface Collector
{
    public function pageview(VisitContext $context): void;

    public function event(EventData $event): void;
}
