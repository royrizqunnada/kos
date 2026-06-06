<?php

declare(strict_types=1);

namespace Src\Lease\Application\Actions;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Src\Lease\Domain\Enums\LeaseStatus;
use Src\Lease\Domain\Models\Lease;
use Src\Room\Domain\Enums\RoomStatus;

final readonly class EndExpiredLeasesAction
{
    /**
     * Akhiri otomatis kontrak aktif yang masa berlakunya sudah lewat:
     * status -> Berakhir, kamar dibebaskan. Data kontrak tetap tersimpan sebagai history.
     * Mengembalikan jumlah kontrak yang diakhiri.
     */
    public function execute(?CarbonImmutable $asOf = null): int
    {
        $asOf = ($asOf ?? CarbonImmutable::now())->startOfDay();
        $count = 0;

        Lease::query()
            ->where('status', LeaseStatus::Active->value)
            ->whereDate('end_date', '<', $asOf->toDateString())
            ->chunkById(100, function ($leases) use (&$count) {
                foreach ($leases as $lease) {
                    DB::transaction(function () use ($lease) {
                        $lease->update([
                            'status' => LeaseStatus::Ended->value,
                            'ended_at' => $lease->end_date->toDateString(),
                            'checkout_notes' => $lease->checkout_notes ?: 'Berakhir otomatis (masa kontrak habis).',
                        ]);
                        $lease->room()->update(['status' => RoomStatus::Available->value]);
                    });
                    $count++;
                }
            });

        return $count;
    }
}
