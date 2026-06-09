<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Src\Gallery\Domain\Models\Gallery;

class GalleryController extends Controller
{
    private function authorizeManage(Request $request): void
    {
        abort_unless(
            $request->user()->can('setting.update') || $request->user()->hasRole('super_admin'),
            403,
        );
    }

    public function index(Request $request): Response
    {
        $this->authorizeManage($request);

        return Inertia::render('Galleries/Index', [
            'photos' => Gallery::query()->orderBy('category')->orderBy('sort_order')->orderByDesc('id')->get()
                ->map(fn (Gallery $g) => [
                    'id' => $g->id,
                    'category' => $g->category,
                    'caption' => $g->caption,
                    'path' => $g->path,
                ]),
            'categories' => [
                ['value' => 'kos', 'label' => 'Foto Kos'],
                ['value' => 'fasilitas', 'label' => 'Fasilitas'],
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeManage($request);

        $request->validate([
            'category' => ['required', 'in:kos,fasilitas'],
            'caption' => ['nullable', 'string', 'max:120'],
            'photos' => ['required', 'array', 'min:1'],
            'photos.*' => ['image', 'max:5120'], // maks 5 MB / foto
        ]);

        foreach ((array) $request->file('photos', []) as $photo) {
            Gallery::query()->create([
                'category' => $request->input('category'),
                'caption' => $request->input('caption'),
                'path' => $photo->store('galeri', 'public'),
            ]);
        }

        return back()->with('success', 'Foto berhasil diupload.');
    }

    public function destroy(Request $request, Gallery $gallery): RedirectResponse
    {
        $this->authorizeManage($request);

        Storage::disk('public')->delete($gallery->path);
        $gallery->delete();

        return back()->with('success', 'Foto dihapus.');
    }
}
