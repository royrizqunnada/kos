<?php

declare(strict_types=1);

namespace Src\Reminder\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Src\Invoice\Domain\Models\Invoice;
use Src\Reminder\Domain\Enums\ReminderChannel;
use Src\Reminder\Domain\Enums\ReminderStatus;

class ReminderLog extends Model
{
    protected $fillable = [
        'invoice_id', 'channel', 'offset_days', 'status', 'sent_at', 'error',
    ];

    protected function casts(): array
    {
        return [
            'channel' => ReminderChannel::class,
            'status' => ReminderStatus::class,
            'offset_days' => 'integer',
            'sent_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Invoice, $this> */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
