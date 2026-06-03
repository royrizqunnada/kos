<?php

declare(strict_types=1);

namespace Src\Payment\Application\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Src\Payment\Domain\Repositories\PaymentRepositoryInterface;

final readonly class PaymentService
{
    public function __construct(private PaymentRepositoryInterface $payments) {}

    public function list(array $filters = []): LengthAwarePaginator
    {
        return $this->payments->paginate($filters);
    }
}
