<?php

declare(strict_types=1);

namespace Src\Invoice\Domain\Models;

use Database\Factories\InvoiceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Src\Invoice\Domain\Enums\InvoiceStatus;
use Src\Lease\Domain\Models\Lease;
use Src\Payment\Domain\Models\Payment;

class Invoice extends Model
{
    /** @use HasFactory<InvoiceFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'lease_id', 'invoice_number', 'period_start', 'period_end',
        'due_date', 'amount', 'discount', 'paid_amount', 'status', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'due_date' => 'date',
            'amount' => 'decimal:2',
            'discount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'status' => InvoiceStatus::class,
        ];
    }

    protected static function booted(): void
    {
        // Soft-delete bertingkat: arsipkan pembayaran saat tagihan diarsipkan.
        // (Cascade FK database hanya berlaku untuk hard-delete / forceDelete.)
        static::deleting(function (Invoice $invoice): void {
            if (! $invoice->isForceDeleting()) {
                $invoice->payments()->delete();
            }
        });
    }

    /** @return BelongsTo<Lease, $this> */
    public function lease(): BelongsTo
    {
        return $this->belongsTo(Lease::class);
    }

    /** @return HasMany<InvoiceItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /** @return HasMany<Payment, $this> */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function outstanding(): string
    {
        return bcsub((string) $this->amount, (string) $this->paid_amount, 2);
    }

    protected static function newFactory(): InvoiceFactory
    {
        return InvoiceFactory::new();
    }
}
