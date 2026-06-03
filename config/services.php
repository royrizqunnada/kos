<?php

return [
    'whatsapp' => [
        'driver' => env('WHATSAPP_DRIVER', 'log'),
        'token' => env('WHATSAPP_TOKEN'),
        'endpoint' => env('WHATSAPP_ENDPOINT', 'https://api.fonnte.com/send'),
    ],
];
