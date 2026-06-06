<?php

return [
    // Identitas kos untuk kwitansi & tagihan.
    'name' => env('KOS_NAME', 'Cozy Corner Student Living In Semarang'),
    'owner' => env('KOS_OWNER', 'Febrina Nuke Kardini'),

    // Default reminder day-offsets relative to invoice due date.
    'reminder_offsets' => [-7, -3, -1, 1, 7],

    // Default billing day of month for auto-generated monthly invoices.
    'billing_day' => env('KOS_BILLING_DAY', 1),

    // How many days before the period start an invoice should be generated.
    'generate_days_ahead' => env('KOS_GENERATE_DAYS_AHEAD', 7),
];
