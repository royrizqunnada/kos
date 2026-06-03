<?php

declare(strict_types=1);

namespace Src\Lease\Application\Actions;

use Illuminate\Support\Facades\DB;
use Src\Lease\Domain\Enums\LeaseStatus;
use Src\Lease\Domain\Models\Lease;
use Src\Room\Domain\Enums\RoomStatus;

final readonly class EndLeaseAction
{
    public function execute(Lease $lease): Lease
    {
        return DB::transaction(function () use ($lease) {
            $lease->update(['status' => LeaseStatus::Ended->value]);
            $lease->room()->update(['status' => RoomStatus::Available->value]);

            return $lease->refresh();
        });
    }
}
