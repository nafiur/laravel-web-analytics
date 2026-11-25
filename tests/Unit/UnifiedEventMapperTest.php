<?php

namespace Nafiur\WebAnalytics\Tests\Unit;

use Carbon\CarbonImmutable;
use Nafiur\WebAnalytics\Data\EventData;
use Nafiur\WebAnalytics\Data\UnifiedEvent;
use Nafiur\WebAnalytics\Services\UnifiedEventMapper;
use Nafiur\WebAnalytics\Tests\TestCase;

class UnifiedEventMapperTest extends TestCase
{
    private UnifiedEventMapper $mapper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mapper = new UnifiedEventMapper();
    }

    public function test_event_is_mapped_onto_the_unified_envelope(): void
    {
        $event = $this->mapper->map(new EventData(
            websiteId:    7,
            name:        'purchase:completed',
            visitorHash: 'f_abc123',
            properties: ['plan' => 'pro', 'value' => 1500],
            value:       1500,
            occurredAt: '2026-11-25T08:15:22Z',
            requestUuid: 'req-1',
            context:     ['path' => '/checkout', 'referrer' => 'https://google.com/'],
        ));

        $this->assertInstanceOf(UnifiedEvent::class, $event);
        $this->assertSame('req-1', $event->uuid);
        $this->assertSame('purchase:completed', $event->name);
        $this->assertSame(7, $event->websiteId);
        $this->assertSame(1500.0, $event->value);
        $this->assertSame('f_abc123', $event->anonymousId);
        $this->assertNull($event->distinctId);
        $this->assertNull($event->sessionId);
        $this->assertSame(['plan' => 'pro', 'value' => 1500], $event->properties);
        $this->assertSame('/checkout', $event->context['path']);
        $this->assertSame('https://google.com/', $event->context['referrer']);
    }

    public function test_known_user_identity_is_exposed_as_distinct_id(): void
    {
        $event = $this->mapper->map(new EventData(
            websiteId:    7,
            name:        'pageview',
            visitorHash: 'u_42',
        ));

        $this->assertSame('u_42', $event->distinctId);
        $this->assertNull($event->anonymousId);
    }

    public function test_event_name_is_truncated_to_the_unified_limit(): void
    {
        $event = $this->mapper->map(new EventData(
            websiteId:    7,
            name:        str_repeat('a', 180),
        ));

        $this->assertSame(100, mb_strlen($event->name));
    }

    public function test_properties_are_sanitized(): void
    {
        $event = $this->mapper->map(new EventData(
            websiteId:    7,
            name:        'purchase',
            properties: [
                'plan'     => 'pro',
                'password' => 'secret',
                'email'    => 'a@b.test',
                'nested'   => ['deep' => true],
            ],
        ));

        $this->assertSame(['plan' => 'pro'], $event->properties);
    }

    public function test_context_is_merged_with_defaults(): void
    {
        $event = $this->mapper->map(new EventData(
            websiteId:    7,
            name:        'js_event',
            context: ['path' => '/app', 'utm_source' => 'newsletter'],
        ));

        $this->assertSame('/app', $event->context['path']);
        $this->assertSame('newsletter', $event->context['utm_source']);
        $this->assertSame('GET', $event->context['method']);
        $this->assertNull($event->context['referrer']);
    }

    public function test_storage_payload_keeps_legacy_keys_and_uuid(): void
    {
        $event = $this->mapper->map(new EventData(
            websiteId:    7,
            name:        'purchase',
            visitorHash: 'f_abc',
            value:       99,
            requestUuid: 'uniq-99',
        ));

        $payload = $event->toStoragePayload();

        $this->assertSame('uniq-99', $payload['request_uuid']);
        $this->assertSame(7, $payload['website_id']);
        $this->assertSame('purchase', $payload['name']);
        $this->assertSame('f_abc', $payload['visitor_hash']);
        $this->assertSame(99.0, $payload['value']);
        $this->assertSame($event->createdAt->toIso8601String(), $payload['created_at']);
    }

    public function test_timestamp_defaults_to_now_when_absent(): void
    {
        $before = CarbonImmutable::now();
        $event = $this->mapper->map(new EventData(
            websiteId:    7,
            name:        'pageview',
        ));
        $after = CarbonImmutable::now();

        $this->assertGreaterThanOrEqual($before->getTimestamp(), $event->timestamp->getTimestamp());
        $this->assertLessThanOrEqual($after->getTimestamp(), $event->timestamp->getTimestamp());
        $this->assertGreaterThanOrEqual($before->getTimestamp(), $event->createdAt->getTimestamp());
        $this->assertLessThanOrEqual($after->getTimestamp(), $event->createdAt->getTimestamp());
    }
}
