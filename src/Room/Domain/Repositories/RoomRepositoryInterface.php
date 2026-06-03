<?php

declare(strict_types=1);

namespace Src\Room\Domain\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Src\Room\Domain\Data\RoomData;
use Src\Room\Domain\Models\Room;

interface RoomRepositoryInterface
{
    /** @param array<string, mixed> $filters */
    public function paginate(array $filters = [], int $perPage = 12): LengthAwarePaginator;

    public function find(int $id): ?Room;

    public function create(RoomData $data): Room;

    public function update(Room $room, RoomData $data): Room;

    public function delete(Room $room): void;

    /** @return array<string, int> */
    public function countByStatus(): array;
}
