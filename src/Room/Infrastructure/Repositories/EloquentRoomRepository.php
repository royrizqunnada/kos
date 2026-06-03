<?php

declare(strict_types=1);

namespace Src\Room\Infrastructure\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Src\Room\Domain\Data\RoomData;
use Src\Room\Domain\Models\Room;
use Src\Room\Domain\Repositories\RoomRepositoryInterface;

final class EloquentRoomRepository implements RoomRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        return Room::query()
            ->with('photos')
            ->when($filters['search'] ?? null, fn ($q, $s) => $q->where('room_number', 'ilike', "%$s%"))
            ->when($filters['status'] ?? null, fn ($q, $st) => $q->where('status', $st))
            ->when($filters['type'] ?? null, fn ($q, $t) => $q->where('type', $t))
            ->latest('room_number')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function find(int $id): ?Room
    {
        return Room::query()->with(['photos', 'leases.tenant'])->find($id);
    }

    public function create(RoomData $data): Room
    {
        return Room::query()->create($data->toArray());
    }

    public function update(Room $room, RoomData $data): Room
    {
        $room->update($data->toArray());

        return $room->refresh();
    }

    public function delete(Room $room): void
    {
        $room->delete();
    }

    public function countByStatus(): array
    {
        return Room::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();
    }
}
