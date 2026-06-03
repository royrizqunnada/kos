<?php

declare(strict_types=1);

namespace Src\Reminder\Application\Services\WhatsApp;

interface WhatsAppDriver
{
    /** @throws \RuntimeException when the gateway rejects the message */
    public function send(string $phone, string $message): void;
}
