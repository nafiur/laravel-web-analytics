<?php

namespace Nafiur\WebAnalytics\Data;

/**
 * Minimal, privacy-filtered request context produced by the tracking
 * middleware. No raw IP is stored by v3 collectors unless explicitly enabled.
 */
final class VisitContext
{
    public function __construct(
        public readonly int $websiteId,
        public readonly string $visitorHash,
        public readonly string $path,
        public readonly string $method = 'GET',
        public readonly int $statusCode = 200,
        public readonly float $responseTimeMs = 0,
        public readonly int $bytesSent = 0,
        public readonly ?string $referrer = null,
        public readonly ?string $utmSource = null,
        public readonly ?string $utmMedium = null,
        public readonly ?string $utmCampaign = null,
        public readonly ?string $title = null,
        public readonly ?string $occurredAt = null,
    ) {
    }
}
