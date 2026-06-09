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

    /**
     * @param int $months Jumlah bulan yang ditagih (mengikuti durasi kontrak: 1/3/6/12).
     */
    public function execute(Lease $lease, CarbonImmutable $periodStart, int $months = 1): ?Invoice
    {
        if ($this->invoices->existsForPeriod($lease->id, $periodStart->toDateString())) {
            return null;
        }

        $months = max(1, $months);

        return DB::transaction(function () use ($lease, $periodStart, $months) {
            $periodEnd = $periodStart->addMonths($months)->subDay();
            // Bayar di muka: jatuh tempo = tanggal mulai. Bisa diberi tenggang lewat config.
            $dueDate = $periodStart->addDays((int) config('kos.due_grace_days', 0));
            $unitPrice = (float) $lease->monthly_price;
            $gross = $unitPrice * $months;

            // Diskon mengikuti kontrak (nominal Rp atau persen), dipotong tiap tagihan.
            $discount = match ($lease->discount_type) {
                'nominal' => min((float) $lease->discount_value, $gross),
                'percent' => round($gross * (float) $lease->discount_value / 100, 2),
                default => 0.0,
            };
            $amount = $gross - $discount;

            $periodLabel = $months > 1
                ? "{$periodStart->translatedFormat('M Y')} – {$periodEnd->translatedFormat('M Y')}"
                : $periodStart->translatedFormat('F Y');

            $invoice = $this->invoices->create([
                'lease_id' => $lease->id,
                'invoice_number' => $this->numberGenerator->generate($periodStart),
                'period_start' => $periodStart->toDateString(),
                'period_end' => $periodEnd->toDateString(),
                'due_date' => $dueDate->toDateString(),
                'amount' => $amount,
                'discount' => $discount,
                'paid_amount' => 0,
                'status' => InvoiceStatus::Unpaid->value,
            ]);

            $invoice->items()->create([
                'description' => "Sewa kamar {$lease->room->room_number} ({$periodLabel}) · {$months} bulan",
                'quantity' => $months,
                'unit_price' => $unitPrice,
                'amount' => $gross,
            ]);

            if ($discount > 0) {
                $label = $lease->discount_type === 'percent'
                    ? "Diskon {$lease->discount_value}%"
                    : 'Diskon';
                $invoice->items()->create([
                    'description' => $label,
                    'quantity' => 1,
                    'unit_price' => -$discount,
                    'amount' => -$discount,
                ]);
            }

            return $invoice;
        });
    }
}
