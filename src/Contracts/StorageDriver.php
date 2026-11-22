<?php

namespace Nafiur\WebAnalytics\Contracts;

interface StorageDriver
{
    /** @param array<string, mixed> $payload */
    public function storePageview(array $payload): void;

    /** @param array<string, mixed> $payload */
    public function storeEvent(array $payload): void;
}
