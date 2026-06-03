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

        $emailBody = "Yth. {tenant_name},\n\nTagihan sewa kamar {room_number} (Invoice {invoice_number}) ".
            "jatuh tempo pada {due_date}.\nNominal: Rp {amount}\nSisa: Rp {outstanding}\n\nHormat kami,\nPengelola Kos";

        foreach (config('kos.reminder_offsets') as $offset) {
            ReminderTemplate::query()->updateOrCreate(
                ['channel' => ReminderChannel::WhatsApp->value, 'offset_days' => $offset],
                ['subject' => null, 'body' => $waBody, 'is_active' => true]
            );

            ReminderTemplate::query()->updateOrCreate(
                ['channel' => ReminderChannel::Email->value, 'offset_days' => $offset],
                ['subject' => 'Pengingat Tagihan Kos - {invoice_number}', 'body' => $emailBody, 'is_active' => true]
            );
        }
    }
}
