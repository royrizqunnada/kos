<?php

declare(strict_types=1);

namespace Src\Reminder\Application\Services\WhatsApp;

use Illuminate\Support\Facades\Log;

final class LogWhatsAppDriver implements WhatsAppDriver
{
    public function send(string $phone, string $message): void
    {
        Log::channel('stack')->info('[WhatsApp] simulated send', [
            'phone' => $phone,
            'message' => $message,
        ]);
    }
}
