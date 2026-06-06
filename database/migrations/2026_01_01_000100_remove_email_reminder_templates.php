<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Email tidak lagi digunakan — hanya WhatsApp.
        DB::table('reminder_templates')->where('channel', 'email')->delete();
    }

    public function down(): void
    {
        // Tidak dipulihkan.
    }
};
