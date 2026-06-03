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
        'duration', 'monthly_price', 'deposit', 'status', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'duration' => LeaseDuration::class,
            'status' => LeaseStatus::class,
            'monthly_price' => 'decimal:2',
            'deposit' => 'decimal:2',
        ];
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
