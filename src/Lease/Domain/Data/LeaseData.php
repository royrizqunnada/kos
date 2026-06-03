<?php

declare(strict_types=1);

namespace Src\Lease\Domain\Data;

use Carbon\CarbonImmutable;
use Src\Lease\Domain\Enums\LeaseDuration;

final readonly class LeaseData
{
    public function __construct(
        public int $roomId,
        public int $tenantId,
        public CarbonImmutable $startDate,
        public LeaseDuration $duration,
        public float $monthlyPrice,
        public float $deposit = 0,
        public ?string $notes = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            roomId: (int) $data['room_id'],
            tenantId: (int) $data['tenant_id'],
            startDate: CarbonImmutable::parse($data['start_date']),
            duration: LeaseDuration::from($data['duration']),
            monthlyPrice: (float) $data['monthly_price'],
            deposit: (float) ($data['deposit'] ?? 0),
            notes: $data['notes'] ?? null,
        );
    }

    public function endDate(): CarbonImmutable
    {
        return $this->startDate->addMonths($this->duration->months())->subDay();
    }
}
