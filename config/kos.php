<?php

return [
    // Identitas kos untuk kwitansi, tagihan & halaman profil publik.
    'name' => env('KOS_NAME', 'Cozy Corner'),
    'tagline' => env('KOS_TAGLINE', 'Student Living In Semarang'),
    'owner' => env('KOS_OWNER', 'Febrina Nuke Kardini'),

    // Halaman profil publik (cozycornerliving.id).
    'profile_domain' => env('PROFILE_DOMAIN', 'cozycornerliving.id'),
    'apps_url' => env('APPS_URL', 'https://apps.cozycornerliving.id'),
    'phone' => env('KOS_PHONE', '6282323671558'),            // untuk wa.me (tanpa +)
    'phone_display' => env('KOS_PHONE_DISPLAY', '0823-2367-1558'),
    'address_line1' => env('KOS_ADDRESS_1', 'Jl. Klentengsari I No. 27A, RT 06 RW 02'),
    'address_line2' => env('KOS_ADDRESS_2', 'Kel. Pedalangan, Kec. Banyumanik'),
    'address_line3' => env('KOS_ADDRESS_3', 'Kota Semarang, Jawa Tengah'),
    'near' => env('KOS_NEAR', '± 2 KM dari UNDIP Tembalang'),
    'maps_url' => env('KOS_MAPS_URL', 'https://maps.app.goo.gl/4kC8S41LbBdUsA6HA'),
    'instagram' => env('KOS_INSTAGRAM', 'https://instagram.com/cozycorner.semarang'),
    'instagram_handle' => env('KOS_INSTAGRAM_HANDLE', '@cozycorner.semarang'),
    'tiktok' => env('KOS_TIKTOK', 'https://tiktok.com/@cozycorner.semarang'),
    'tagline_long' => env('KOS_TAGLINE_LONG', 'Hunian nyaman dan aman untuk mahasiswi, hanya ± 2 KM dari UNDIP Tembalang. Kamar mandi dalam, AC, WiFi, dan CCTV 24 jam.'),

    // Default reminder day-offsets relative to invoice due date.
    'reminder_offsets' => [-7, -3, -1, 1, 7],

    // Tenggang jatuh tempo tagihan (hari setelah tanggal mulai). 0 = bayar di muka (jatuh tempo = tanggal mulai).
    'due_grace_days' => env('KOS_DUE_GRACE_DAYS', 0),

    // Default billing day of month for auto-generated monthly invoices.
    'billing_day' => env('KOS_BILLING_DAY', 1),

    // How many days before the period start an invoice should be generated.
    'generate_days_ahead' => env('KOS_GENERATE_DAYS_AHEAD', 7),
];
