<?php

declare(strict_types=1);

namespace Src\Reminder\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Src\Reminder\Domain\Enums\ReminderChannel;

class ReminderTemplate extends Model
{
    protected $fillable = ['channel', 'offset_days', 'subject', 'body', 'is_active'];

    protected function casts(): array
    {
        return [
            'channel' => ReminderChannel::class,
            'offset_days' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function render(array $vars): string
    {
        $body = $this->body;
        foreach ($vars as $key => $value) {
            $body = str_replace('{'.$key.'}', (string) $value, $body);
        }

        return $body;
    }
}
