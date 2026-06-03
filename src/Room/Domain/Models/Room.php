<?php

declare(strict_types=1);

namespace Src\Room\Domain\Models;

use Database\Factories\RoomFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Src\Lease\Domain\Models\Lease;
use Src\Room\Domain\Enums\RoomStatus;
use Src\Room\Domain\Enums\RoomType;

class Room extends Model
{
    /** @use HasFactory<RoomFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'room_number', 'type', 'price', 'status', 'facilities', 'description',
    ];

    protected function casts(): array
    {
        return [
            'type' => RoomType::class,
            'status' => RoomStatus::class,
            'price' => 'decimal:2',
            'facilities' => 'array',
        ];
    }

    /** @return HasMany<RoomPhoto, $this> */
    public function photos(): HasMany
    {
        return $this->hasMany(RoomPhoto::class);
    }

    /** @return HasMany<Lease, $this> */
    public function leases(): HasMany
    {
        return $this->hasMany(Lease::class);
    }

    public function activeLease(): ?Lease
    {
        return $this->leases()->where('status', 'active')->latest('start_date')->first();
    }

    protected static function newFactory(): RoomFactory
    {
        return RoomFactory::new();
    }
}
