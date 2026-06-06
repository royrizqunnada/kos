<?php

declare(strict_types=1);

namespace Src\Invoice\Application\Actions;

use Src\Invoice\Application\Services\InvoiceStatusResolver;
use Src\Invoice\Domain\Enums\InvoiceStatus;
use Src\Invoice\Domain\Models\Invoice;

final readonly class RefreshInvoiceStatusesAction
{
    public function __construct(private InvoiceStatusResolver $resolver) {}

    /**
     * Hitung ulang status tagihan yang belum lunas — yang sudah lewat jatuh tempo
     * otomatis jadi "Telat" tanpa perlu disentuh manual. Mengembalikan jumlah yang berubah.
     */
    public function execute(): int
    {
        $changed = 0;

        Invoice::query()
            ->whereIn('status', [
                InvoiceStatus::Unpaid->value,
                InvoiceStatus::Partial->value,
                InvoiceStatus::Overdue->value,
            ])
            ->chunkById(200, function ($invoices) use (&$changed) {
                foreach ($invoices as $invoice) {
                    $new = $this->resolver->resolve($invoice);
                    if ($new !== $invoice->status) {
                        $invoice->status = $new;
                        $invoice->save();
                        $changed++;
                    }
                }
            });

        return $changed;
    }
}
