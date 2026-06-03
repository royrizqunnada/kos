<?php

declare(strict_types=1);

namespace Src\Payment\Domain\Data;

use Carbon\CarbonImmutable;
use Src\Payment\Domain\Enums\PaymentMethod;

final readonly class PaymentData
{
    public function __construct(
        public int $invoiceId,
        public float $amount,
        public PaymentMethod $method,
        public CarbonImmutable $paidAt,
        public ?int $recordedBy = null,
        public ?string $proofPath = null,
        public ?string $note = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            invoiceId: (int) $data['invoice_id'],
            amount: (float) $data['amount'],
            method: PaymentMethod::from($data['method']),
            paidAt: CarbonImmutable::parse($data['paid_at'] ?? now()),
            recordedBy: isset($data['recorded_by']) ? (int) $data['recorded_by'] : null,
            proofPath: $data['proof_path'] ?? null,
            note: $data['note'] ?? null,
        );
    }
}
