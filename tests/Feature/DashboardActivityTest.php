<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use Src\Invoice\Domain\Models\Invoice;
use Src\Lease\Domain\Models\Lease;
use Src\Payment\Application\Actions\RecordPaymentAction;
use Src\Payment\Domain\Data\PaymentData;
use Src\Payment\Domain\Enums\PaymentMethod;
use Src\Reporting\Application\Services\DashboardService;

it('builds dashboard recent activity without lazy-loading violations', function () {
    $lease = Lease::factory()->create();
    $invoice = Invoice::factory()->create(['lease_id' => $lease->id, 'amount' => 500_000]);
    app(RecordPaymentAction::class)->execute(new PaymentData(
        invoiceId: $invoice->id,
        amount: 200_000,
        method: PaymentMethod::Cash,
        paidAt: now()->toImmutable(),
    ));

    Model::preventLazyLoading(true);
    try {
        $stats = app(DashboardService::class)->stats();
    } finally {
        Model::preventLazyLoading(false);
    }

    expect($stats['recent_activity'])->not->toBeEmpty();
});
