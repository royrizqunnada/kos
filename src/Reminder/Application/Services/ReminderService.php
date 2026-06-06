<?php

declare(strict_types=1);

namespace Src\Reminder\Application\Services;

use Src\Invoice\Domain\Models\Invoice;
use Src\Reminder\Application\Services\WhatsApp\WhatsAppDriver;
use Src\Reminder\Domain\Enums\ReminderChannel;
use Src\Reminder\Domain\Enums\ReminderStatus;
use Src\Reminder\Domain\Models\ReminderLog;
use Src\Reminder\Domain\Models\ReminderTemplate;
use Throwable;

final readonly class ReminderService
{
    public function __construct(private WhatsAppDriver $whatsapp) {}

    /** Send reminders for one invoice on a given offset, idempotently. */
    public function sendForInvoice(Invoice $invoice, int $offsetDays): void
    {
        $invoice->loadMissing(['lease.room', 'lease.tenant']);
        $tenant = $invoice->lease->tenant;
        $vars = $this->variables($invoice);

        // Hanya kanal WhatsApp (email tidak digunakan).
        foreach ([ReminderChannel::WhatsApp] as $channel) {
            if ($this->alreadySent($invoice->id, $channel, $offsetDays)) {
                continue;
            }

            $template = ReminderTemplate::query()
                ->where('channel', $channel->value)
                ->where('offset_days', $offsetDays)
                ->where('is_active', true)
                ->first();

            if (! $template) {
                continue;
            }

            $log = ReminderLog::query()->create([
                'invoice_id' => $invoice->id,
                'channel' => $channel->value,
                'offset_days' => $offsetDays,
                'status' => ReminderStatus::Pending->value,
            ]);

            try {
                $body = $template->render($vars);

                if ($tenant->phone) {
                    $this->whatsapp->send($tenant->phone, $body);
                } else {
                    $log->update(['status' => ReminderStatus::Failed->value, 'error' => 'Nomor WhatsApp tidak tersedia']);

                    continue;
                }

                $log->update(['status' => ReminderStatus::Sent->value, 'sent_at' => now()]);
            } catch (Throwable $e) {
                $log->update(['status' => ReminderStatus::Failed->value, 'error' => $e->getMessage()]);
            }
        }
    }

    private function alreadySent(int $invoiceId, ReminderChannel $channel, int $offset): bool
    {
        return ReminderLog::query()
            ->where('invoice_id', $invoiceId)
            ->where('channel', $channel->value)
            ->where('offset_days', $offset)
            ->where('status', ReminderStatus::Sent->value)
            ->exists();
    }

    /** @return array<string, string> */
    private function variables(Invoice $invoice): array
    {
        return [
            'tenant_name' => $invoice->lease->tenant->name,
            'room_number' => $invoice->lease->room->room_number,
            'invoice_number' => $invoice->invoice_number,
            'due_date' => $invoice->due_date->translatedFormat('d F Y'),
            'amount' => number_format((float) $invoice->amount, 0, ',', '.'),
            'outstanding' => number_format((float) $invoice->outstanding(), 0, ',', '.'),
        ];
    }

}
