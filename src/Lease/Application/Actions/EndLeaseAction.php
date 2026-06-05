<?php

declare(strict_types=1);

namespace Src\Lease\Application\Actions;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Src\Lease\Domain\Data\LeaseCheckoutData;
use Src\Lease\Domain\Enums\LeaseStatus;
use Src\Lease\Domain\Models\Lease;
use Src\Room\Domain\Enums\RoomStatus;

final readonly class EndLeaseAction
{
    public function execute(Lease $lease, ?LeaseCheckoutData $data = null): Lease
    {
        $data ??= new LeaseCheckoutData(CarbonImmutable::now());

        return DB::transaction(function () use ($lease, $data) {
            if ($lease->status !== LeaseStatus::Active) {
                throw new RuntimeException('Hanya kontrak aktif yang bisa diakhiri.');
            }

            if ($data->depositRefunded < 0 || $data->depositDeduction < 0) {
                throw new RuntimeException('Nilai deposit tidak boleh negatif.');
            }

            // Total pengembalian + potongan tidak boleh melebihi deposit yang diterima.
            // Dihitung dalam satuan sen (integer) agar bebas galat pembulatan float.
            $toCents = static fn (float|string $value): int => (int) round((float) $value * 100);
            $settled = $toCents($data->depositRefunded) + $toCents($data->depositDeduction);
            if ($settled > $toCents($lease->deposit)) {
                throw new RuntimeException('Total pengembalian + potongan melebihi deposit yang diterima.');
            }

            $lease->update([
                'status' => LeaseStatus::Ended->value,
                'ended_at' => $data->endedAt->toDateString(),
                'deposit_refunded' => $data->depositRefunded,
                'deposit_deduction' => $data->depositDeduction,
                'checkout_notes' => $data->notes,
            ]);

            $lease->room()->update(['status' => RoomStatus::Available->value]);

            return $lease->refresh();
        });
    }
}
