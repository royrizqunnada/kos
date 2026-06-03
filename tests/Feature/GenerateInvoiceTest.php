<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Src\Invoice\Application\Actions\GenerateInvoiceAction;
use Src\Lease\Domain\Models\Lease;

it('generates one invoice per lease period and is idempotent', function () {
    $lease = Lease::factory()->create(['monthly_price' => 1_500_000]);
    $action = app(GenerateInvoiceAction::class);
    $period = CarbonImmutable::now()->startOfMonth();

    $first = $action->execute($lease->load('room'), $period);
    $second = $action->execute($lease->load('room'), $period);

    expect($first)->not->toBeNull();
    expect($second)->toBeNull();
    expect($lease->invoices()->count())->toBe(1);
    expect((float) $first->amount)->toBe(1_500_000.0);
});
