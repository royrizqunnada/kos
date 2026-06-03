<?php

declare(strict_types=1);

namespace Src\Invoice\Application\Actions;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Src\Invoice\Application\Services\InvoiceNumberGenerator;
use Src\Invoice\Domain\Enums\InvoiceStatus;
use Src\Invoice\Domain\Models\Invoice;
use Src\Invoice\Domain\Repositories\InvoiceRepositoryInterface;
use Src\Lease\Domain\Models\Lease;

final readonly class GenerateInvoiceAction
{
    public function __construct(
        private InvoiceRepositoryInterface $invoices,
        private InvoiceNumberGenerator $numberGenerator,
    ) {}

    public function execute(Lease $lease, CarbonImmutable $periodStart): ?Invoice
    {
        if ($this->invoices->existsForPeriod($lease->id, $periodStart->toDateString())) {
            return null;
        }

        return DB::transaction(function () use ($lease, $periodStart) {
            $periodEnd = $periodStart->addMonth()->subDay();
            $dueDate = $periodStart->addDays(5);
            $amount = (float) $lease->monthly_price;

            $invoice = $this->invoices->create([
                'lease_id' => $lease->id,
                'invoice_number' => $this->numberGenerator->generate($periodStart),
                'period_start' => $periodStart->toDateString(),
                'period_end' => $periodEnd->toDateString(),
                'due_date' => $dueDate->toDateString(),
                'amount' => $amount,
                'paid_amount' => 0,
                'status' => InvoiceStatus::Unpaid->value,
            ]);

            $invoice->items()->create([
                'description' => "Sewa kamar {$lease->room->room_number} ({$periodStart->translatedFormat('F Y')})",
                'quantity' => 1,
                'unit_price' => $amount,
                'amount' => $amount,
            ]);

            return $invoice;
        });
    }
}
