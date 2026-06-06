<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\View\View;
use Src\Room\Domain\Enums\RoomStatus;
use Src\Room\Domain\Enums\RoomType;
use Src\Room\Domain\Models\Room;

class PublicProfileController extends Controller
{
    /** Halaman profil publik — harga & ketersediaan kamar live dari data sistem. */
    public function index(): View
    {
        $rooms = Room::query()->get(['type', 'price', 'status']);

        $types = collect(RoomType::cases())
            ->map(function (RoomType $type) use ($rooms) {
                $ofType = $rooms->filter(fn (Room $r) => $r->type === $type);

                return [
                    'label' => $type->label(),
                    'price' => $ofType->min(fn (Room $r) => (float) $r->price),
                    'total' => $ofType->count(),
                    'available' => $ofType->filter(fn (Room $r) => $r->status === RoomStatus::Available)->count(),
                ];
            })
            ->filter(fn (array $t) => $t['total'] > 0)
            ->sortByDesc('price')
            ->values();

        // Tandai tipe termurah sebagai "Termurah".
        $cheapest = $types->min('price');

        return view('profile', [
            'kos' => config('kos'),
            'types' => $types,
            'cheapest' => $cheapest,
            'totalAvailable' => $rooms->filter(fn (Room $r) => $r->status === RoomStatus::Available)->count(),
        ]);
    }
}
