<?php

declare(strict_types=1);

namespace Src\Lease\Application\Actions;

use Illuminate\Support\Facades\DB;
use RuntimeException;
use Src\Lease\Domain\Data\LeaseData;
use Src\Lease\Domain\Enums\LeaseStatus;
use Src\Lease\Domain\Models\Lease;
use Src\Lease\Domain\Repositories\LeaseRepositoryInterface;
use Src\Room\Domain\Enums\RoomStatus;
use Src\Room\Domain\Models\Room;

final readonly class UpdateLeaseAction
{
    public function __construct(private LeaseRepositoryInterface $leases) {}

    public function execute(Lease $lease, LeaseData $data): Lease
    {
        return DB::transaction(function () use ($lease, $data) {
            $oldRoomId = $lease->room_id;
            $isActive = $lease->status === LeaseStatus::Active;

            // Pindah kamar: bebaskan kamar lama, isi kamar baru (hanya bila kontrak aktif).
            if ($data->roomId !== $oldRoomId) {
                /** @var Room $newRoom */
                $newRoom = Room::query()->lockForUpdate()->findOrFail($data->roomId);

                if ($isActive && $newRoom->status === RoomStatus::Occupied) {
                    throw new RuntimeException('Kamar tujuan sudah terisi.');
                }

                if ($isActive) {
                    Room::query()->whereKey($oldRoomId)->update(['status' => RoomStatus::Available->value]);
                    $newRoom->update(['status' => RoomStatus::Occupied->value]);
                }
            }

            $this->leases->update($lease, [
                'room_id' => $data->roomId,
                'tenant_id' => $data->tenantId,
                'start_date' => $data->startDate->toDateString(),
                'end_date' => $data->endDate()->toDateString(),
                'duration' => $data->duration->value,
                'monthly_price' => $data->monthlyPrice,
                'deposit' => $data->deposit,
                'notes' => $data->notes,
            ]);

            return $lease->refresh()->load(['room', 'tenant']);
        });
    }
}
