<?php

declare(strict_types=1);

namespace Src\Lease\Application\Actions;

use Illuminate\Support\Facades\DB;
use Src\Lease\Domain\Data\LeaseData;
use Src\Lease\Domain\Enums\LeaseStatus;
use Src\Lease\Domain\Models\Lease;
use Src\Room\Domain\Enums\RoomStatus;

final readonly class RenewLeaseAction
{
    public function __construct(private CreateLeaseAction $create) {}

    /**
     * Perpanjang/lanjutkan penghuni: kontrak lama diakhiri (tersimpan sebagai history),
     * lalu dibuat kontrak baru beserta tagihan pertamanya.
     */
    public function execute(Lease $old, LeaseData $data, bool $generateInvoice = true): Lease
    {
        return DB::transaction(function () use ($old, $data, $generateInvoice) {
            if ($old->status === LeaseStatus::Active) {
                $old->update([
                    'status' => LeaseStatus::Ended->value,
                    'ended_at' => now()->toDateString(),
                    'checkout_notes' => $old->checkout_notes ?: 'Diperpanjang ke kontrak baru.',
                ]);
                $old->room()->update(['status' => RoomStatus::Available->value]);
            }

            return $this->create->execute($data, $generateInvoice);
        });
    }
}
