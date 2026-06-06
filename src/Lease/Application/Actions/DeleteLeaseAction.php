<?php

declare(strict_types=1);

namespace Src\Lease\Application\Actions;

use Illuminate\Support\Facades\DB;
use Src\Lease\Domain\Enums\LeaseStatus;
use Src\Lease\Domain\Models\Lease;
use Src\Room\Domain\Enums\RoomStatus;

final readonly class DeleteLeaseAction
{
    public function execute(Lease $lease): void
    {
        DB::transaction(function () use ($lease) {
            // Bebaskan kamar bila kontrak masih aktif.
            if ($lease->status === LeaseStatus::Active) {
                $lease->room()->update(['status' => RoomStatus::Available->value]);
            }

            // Tagihan & pembayaran ikut diarsipkan lewat event `deleting` di model.
            $lease->delete();
        });
    }
}
