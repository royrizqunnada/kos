<?php

declare(strict_types=1);

namespace Src\Lease\Domain\Data;

use Carbon\CarbonImmutable;

final readonly class LeaseCheckoutData
{
    public function __construct(
        public CarbonImmutable $endedAt,
        public float $depositRefunded = 0,
        public float $depositDeduction = 0,
        public ?string $notes = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            endedAt: isset($data['ended_at'])
                ? CarbonImmutable::parse($data['ended_at'])
                : CarbonImmutable::now(),
            depositRefunded: (float) ($data['deposit_refunded'] ?? 0),
            depositDeduction: (float) ($data['deposit_deduction'] ?? 0),
            notes: $data['checkout_notes'] ?? null,
        );
    }
}
