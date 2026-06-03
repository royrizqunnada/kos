<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoomRequest;
use App\Http\Requests\UpdateRoomRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Src\Room\Domain\Data\RoomData;
use Src\Room\Domain\Enums\RoomStatus;
use Src\Room\Domain\Enums\RoomType;
use Src\Room\Domain\Models\Room;
use Src\Room\Domain\Repositories\RoomRepositoryInterface;

class RoomController extends Controller
{
    public function __construct(private readonly RoomRepositoryInterface $rooms) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Room::class);

        return Inertia::render('Rooms/Index', [
            'rooms' => $this->rooms->paginate($request->only('search', 'status', 'type')),
            'filters' => $request->only('search', 'status', 'type'),
            'statuses' => $this->options(RoomStatus::cases()),
            'types' => $this->options(RoomType::cases()),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Room::class);

        return Inertia::render('Rooms/Create', [
            'statuses' => $this->options(RoomStatus::cases()),
            'types' => $this->options(RoomType::cases()),
        ]);
    }

    public function store(StoreRoomRequest $request): RedirectResponse
    {
        $this->authorize('create', Room::class);
        $room = $this->rooms->create(RoomData::fromArray($request->validated()));
        $this->syncPhotos($request, $room);

        return redirect()->route('rooms.index')->with('success', 'Kamar berhasil ditambahkan.');
    }

    public function show(Room $room): Response
    {
        $this->authorize('view', $room);

        return Inertia::render('Rooms/Show', [
            'room' => $this->rooms->find($room->id),
        ]);
    }

    public function edit(Room $room): Response
    {
        $this->authorize('update', $room);

        return Inertia::render('Rooms/Edit', [
            'room' => $room->load('photos'),
            'statuses' => $this->options(RoomStatus::cases()),
            'types' => $this->options(RoomType::cases()),
        ]);
    }

    public function update(UpdateRoomRequest $request, Room $room): RedirectResponse
    {
        $this->authorize('update', $room);
        $this->rooms->update($room, RoomData::fromArray($request->validated()));
        $this->syncPhotos($request, $room);

        return redirect()->route('rooms.index')->with('success', 'Kamar berhasil diperbarui.');
    }

    public function destroy(Room $room): RedirectResponse
    {
        $this->authorize('delete', $room);
        $this->rooms->delete($room);

        return redirect()->route('rooms.index')->with('success', 'Kamar berhasil dihapus.');
    }

    private function syncPhotos(Request $request, Room $room): void
    {
        foreach ((array) $request->file('photos', []) as $i => $photo) {
            $path = $photo->store('rooms', 'public');
            $room->photos()->create(['path' => $path, 'is_primary' => $i === 0 && $room->photos()->count() === 0]);
        }
    }

    private function options(array $cases): array
    {
        return array_map(fn ($c) => ['value' => $c->value, 'label' => $c->label()], $cases);
    }
}
