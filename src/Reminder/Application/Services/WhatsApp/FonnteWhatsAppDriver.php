<?php

declare(strict_types=1);

namespace Src\Reminder\Application\Services\WhatsApp;

use Illuminate\Support\Facades\Http;
use RuntimeException;

final class FonnteWhatsAppDriver implements WhatsAppDriver
{
    public function __construct(
        private readonly string $token,
        private readonly string $endpoint,
    ) {}

    public function send(string $phone, string $message): void
    {
        $response = Http::withHeaders(['Authorization' => $this->token])
            ->asForm()
            ->post($this->endpoint, [
                'target' => $this->normalize($phone),
                'message' => $message,
            ]);

        if ($response->failed()) {
            throw new RuntimeException('WhatsApp gateway error: '.$response->body());
        }
    }

    private function normalize(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        return str_starts_with($digits, '0') ? '62'.substr($digits, 1) : $digits;
    }
}
