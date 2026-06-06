<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Src\Reminder\Domain\Enums\ReminderChannel;
use Src\Reminder\Domain\Models\ReminderTemplate;

class ReminderTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $waBody = "Halo {tenant_name}, ini pengingat tagihan kamar {room_number}.\n".
            "No. Invoice: {invoice_number}\nJatuh tempo: {due_date}\n".
            "Nominal: Rp {amount}\nSisa tagihan: Rp {outstanding}\nTerima kasih.";

        // Hanya kanal WhatsApp; bersihkan sisa template email lama bila ada.
        ReminderTemplate::query()->where('channel', ReminderChannel::Email->value)->delete();

        foreach (config('kos.reminder_offsets') as $offset) {
            ReminderTemplate::query()->updateOrCreate(
                ['channel' => ReminderChannel::WhatsApp->value, 'offset_days' => $offset],
                ['subject' => null, 'body' => $waBody, 'is_active' => true]
            );
        }
    }
}
