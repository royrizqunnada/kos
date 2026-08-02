<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Src\Expense\Domain\Repositories\ExpenseRepositoryInterface;
use Src\Expense\Infrastructure\Repositories\EloquentExpenseRepository;
use Src\Identity\Domain\Repositories\UserRepositoryInterface;
use Src\Identity\Infrastructure\Repositories\EloquentUserRepository;
use Src\Invoice\Domain\Repositories\InvoiceRepositoryInterface;
use Src\Invoice\Infrastructure\Repositories\EloquentInvoiceRepository;
use Src\Lease\Domain\Repositories\LeaseRepositoryInterface;
use Src\Lease\Infrastructure\Repositories\EloquentLeaseRepository;
use Src\Payment\Domain\Repositories\PaymentRepositoryInterface;
use Src\Payment\Infrastructure\Repositories\EloquentPaymentRepository;
use Src\Reminder\Application\Services\WhatsApp\FonnteWhatsAppDriver;
use Src\Reminder\Application\Services\WhatsApp\LogWhatsAppDriver;
use Src\Reminder\Application\Services\WhatsApp\WhatsAppDriver;
use Src\Room\Domain\Repositories\RoomRepositoryInterface;
use Src\Room\Infrastructure\Repositories\EloquentRoomRepository;
use Src\Tenant\Domain\Repositories\TenantRepositoryInterface;
use Src\Tenant\Infrastructure\Repositories\EloquentTenantRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    /** @var array<class-string, class-string> */
    public array $bindings = [
        RoomRepositoryInterface::class => EloquentRoomRepository::class,
        TenantRepositoryInterface::class => EloquentTenantRepository::class,
        LeaseRepositoryInterface::class => EloquentLeaseRepository::class,
        InvoiceRepositoryInterface::class => EloquentInvoiceRepository::class,
        PaymentRepositoryInterface::class => EloquentPaymentRepository::class,
        ExpenseRepositoryInterface::class => EloquentExpenseRepository::class,
        UserRepositoryInterface::class => EloquentUserRepository::class,
    ];

    public function register(): void
    {
        $this->app->bind(WhatsAppDriver::class, function ($app) {
            return match (config('services.whatsapp.driver')) {
                'fonnte' => new FonnteWhatsAppDriver(
                    config('services.whatsapp.token') ?? '',
                    config('services.whatsapp.endpoint'),
                ),
                default => new LogWhatsAppDriver(),
            };
        });
    }
}
