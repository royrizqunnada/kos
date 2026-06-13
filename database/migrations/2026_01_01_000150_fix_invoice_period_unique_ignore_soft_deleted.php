<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Unique tagihan per periode harus MENGABAIKAN tagihan yang sudah di-soft-delete.
 *
 * Sebelumnya unique (lease_id, period_start) berlaku ke semua baris termasuk yang
 * deleted_at terisi. Akibatnya bila sebuah tagihan dihapus (soft-delete), saat
 * "Generate" mencoba membuat ulang periode yang sama -> duplicate key -> error 500,
 * padahal secara aplikasi (Eloquent menyaring soft-delete) slotnya dianggap kosong.
 *
 * Solusi: partial unique index, hanya untuk tagihan aktif (deleted_at IS NULL).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropUnique('invoices_lease_id_period_start_unique');
        });

        DB::statement(
            'CREATE UNIQUE INDEX invoices_lease_period_active_unique ON invoices (lease_id, period_start) WHERE deleted_at IS NULL'
        );
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS invoices_lease_period_active_unique');

        Schema::table('invoices', function (Blueprint $table) {
            $table->unique(['lease_id', 'period_start']);
        });
    }
};
