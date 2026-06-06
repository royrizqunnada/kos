<?php

declare(strict_types=1);

use Src\Room\Domain\Enums\RoomStatus;
use Src\Room\Domain\Enums\RoomType;
use Src\Room\Domain\Models\Room;

function profileUrl(string $path = '/'): string
{
    return 'http://'.config('kos.profile_domain').$path;
}

it('shows the public profile with live room availability', function () {
    Room::factory()->create(['type' => RoomType::A->value, 'price' => 1_700_000, 'status' => RoomStatus::Available->value]);
    Room::factory()->create(['type' => RoomType::D->value, 'price' => 1_400_000, 'status' => RoomStatus::Available->value]);
    Room::factory()->create(['type' => RoomType::D->value, 'price' => 1_400_000, 'status' => RoomStatus::Occupied->value]);

    $this->get(profileUrl())
        ->assertOk()
        ->assertSee('Cozy Corner')
        ->assertSee('TIPE A')
        ->assertSee('TIPE D')
        ->assertSee('kamar tersedia')        // ada kamar kosong
        ->assertSee('Termurah');             // tipe termurah (D) ditandai
});

it('marks a fully occupied type as penuh', function () {
    Room::factory()->create(['type' => RoomType::A->value, 'price' => 1_700_000, 'status' => RoomStatus::Occupied->value]);

    $this->get(profileUrl())
        ->assertOk()
        ->assertSee('Penuh');
});

it('is publicly accessible without login', function () {
    $this->get(profileUrl())->assertOk();
});

it('includes share meta, structured data, floating WhatsApp & admin login', function () {
    Room::factory()->create(['type' => RoomType::A->value, 'price' => 1_700_000, 'status' => RoomStatus::Available->value]);

    $this->get(profileUrl())
        ->assertOk()
        ->assertSee('og:title', false)              // preview share WhatsApp/sosmed
        ->assertSee('LodgingBusiness', false)       // structured data buat Google
        ->assertSee('wa-float', false)              // tombol WA melayang
        ->assertSee('Login Admin');                 // link ke apps
});
