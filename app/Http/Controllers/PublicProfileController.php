<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\View\View;
use Src\Gallery\Domain\Models\Gallery;
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
                    'price' => $this->representativePrice($ofType),
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
            'priceMin' => $types->min('price'),
            'priceMax' => $types->max('price'),
            'totalAvailable' => $rooms->filter(fn (Room $r) => $r->status === RoomStatus::Available)->count(),
            'galleries' => Gallery::query()->orderBy('sort_order')->orderByDesc('id')->get(['category', 'caption', 'path'])
                ->map(fn (Gallery $g) => ['category' => $g->category, 'caption' => $g->caption, 'path' => $g->path])->all(),
        ]);
    }

    /**
     * Harga representatif sebuah tipe = harga yang paling sering dipakai (modus),
     * bukan yang termurah. Tujuannya agar diskon khusus pada 1-2 kamar tidak
     * menggeser harga yang tampil di profil. Jika jumlahnya seri, ambil harga
     * tertinggi (harga normal sebelum diskon).
     */
    private function representativePrice(\Illuminate\Support\Collection $rooms): ?float
    {
        if ($rooms->isEmpty()) {
            return null;
        }

        return $rooms
            ->groupBy(fn (Room $r) => (string) $r->price)   // kelompokkan per harga
            ->map(fn (\Illuminate\Support\Collection $group) => [
                'price' => (float) $group->first()->price,
                'count' => $group->count(),
            ])
            ->sortBy([
                ['count', 'desc'],   // harga yang paling sering dipakai
                ['price', 'desc'],   // seri → pilih harga tertinggi (harga normal)
            ])
            ->first()['price'];
    }
}
