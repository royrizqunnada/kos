<?php

declare(strict_types=1);

namespace Src\Lease\Domain\Models;

use Database\Factories\LeaseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Src\Invoice\Domain\Models\Invoice;
use Src\Lease\Domain\Enums\LeaseDuration;
use Src\Lease\Domain\Enums\LeaseStatus;
use Src\Room\Domain\Models\Room;
use Src\Tenant\Domain\Models\Tenant;

class Lease extends Model
{
    /** @use HasFactory<LeaseFactory> */
    use HasFactory;

    protected $fillable = [
        'room_id', 'tenant_id', 'start_date', 'end_date',
        'duration', 'monthly_price', 'deposit', 'deposit_refunded',
        'deposit_deduction', 'status', 'ended_at', 'notes', 'checkout_notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'ended_at' => 'date',
            'duration' => LeaseDuration::class,
            'status' => LeaseStatus::class,
            'monthly_price' => 'decimal:2',
            'deposit' => 'decimal:2',
            'deposit_refunded' => 'decimal:2',
            'deposit_deduction' => 'decimal:2',
        ];
    }

    /**
     * Sisa total seluruh tagihan yang belum lunas pada kontrak ini.
     * Membutuhkan relasi `invoices` ter-load.
     */
    public function outstandingTotal(): string
    {
        $cents = $this->invoices
            ->reject(fn (Invoice $invoice) => $invoice->status->isSettled())
            ->reduce(
                fn (int $carry, Invoice $invoice): int => $carry
                    + (int) round(((float) $invoice->amount - (float) $invoice->paid_amount) * 100),
                0,
            );

        return number_format(max(0, $cents) / 100, 2, '.', '');
    }

    /** @return BelongsTo<Room, $this> */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /** @return BelongsTo<Tenant, $this> */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /** @return HasMany<Invoice, $this> */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    protected static function newFactory(): LeaseFactory
    {
        return LeaseFactory::new();
    }
}
