<?php

declare(strict_types=1);

namespace Src\Payment\Domain\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Src\Payment\Domain\Data\PaymentData;
use Src\Payment\Domain\Models\Payment;

interface PaymentRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function create(PaymentData $data): Payment;

    public function update(Payment $payment, array $data): Payment;

    public function delete(Payment $payment): void;

    public function sumForInvoice(int $invoiceId): string;
}
