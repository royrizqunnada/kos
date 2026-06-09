<?php

declare(strict_types=1);

namespace Src\Lease\Application\Actions;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Src\Invoice\Application\Actions\GenerateInvoiceAction;
use Src\Lease\Domain\Data\LeaseData;
use Src\Lease\Domain\Enums\LeaseStatus;
use Src\Lease\Domain\Models\Lease;
use Src\Lease\Domain\Repositories\LeaseRepositoryInterface;
use Src\Room\Domain\Enums\RoomStatus;
use Src\Room\Domain\Models\Room;

final readonly class CreateLeaseAction
{
    public function __construct(
        private LeaseRepositoryInterface $leases,
        private GenerateInvoiceAction $generateInvoice,
    ) {}

    public function execute(LeaseData $data, bool $generateFirstInvoice = true): Lease
    {
        return DB::transaction(function () use ($data, $generateFirstInvoice) {
            /** @var Room $room */
            $room = Room::query()->lockForUpdate()->findOrFail($data->roomId);

            if ($room->status === RoomStatus::Occupied) {
                throw new RuntimeException('Kamar sudah terisi.');
            }

            $lease = $this->leases->create([
                'room_id' => $data->roomId,
                'tenant_id' => $data->tenantId,
                'start_date' => $data->startDate->toDateString(),
                'end_date' => $data->endDate()->toDateString(),
                'duration' => $data->duration->value,
                'monthly_price' => $data->monthlyPrice,
                'discount_type' => $data->discountType,
                'discount_value' => $data->discountValue,
                'deposit' => $data->deposit,
                'status' => LeaseStatus::Active->value,
                'notes' => $data->notes,
            ]);

            $room->update(['status' => RoomStatus::Occupied]);

            if ($generateFirstInvoice) {
                $this->generateInvoice->execute(
                    $lease->load('room'),
                    CarbonImmutable::parse($data->startDate),
                    $data->duration->months()
                );
            }

            return $lease->load(['room', 'tenant']);
        });
    }
}
