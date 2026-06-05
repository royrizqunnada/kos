<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leases', function (Blueprint $table) {
            $table->date('ended_at')->nullable()->after('status');
            $table->decimal('deposit_refunded', 12, 2)->default(0)->after('deposit');
            $table->decimal('deposit_deduction', 12, 2)->default(0)->after('deposit_refunded');
            $table->text('checkout_notes')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('leases', function (Blueprint $table) {
            $table->dropColumn(['ended_at', 'deposit_refunded', 'deposit_deduction', 'checkout_notes']);
        });
    }
};
