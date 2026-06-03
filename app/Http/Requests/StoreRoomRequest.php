<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Src\Room\Domain\Enums\RoomStatus;
use Src\Room\Domain\Enums\RoomType;

class StoreRoomRequest extends FormRequest
{
    public function rules(): array
    {
        $roomId = $this->route('room')?->id;

        return [
            'room_number' => ['required', 'string', 'max:50', Rule::unique('rooms', 'room_number')->ignore($roomId)],
            'type' => ['required', Rule::enum(RoomType::class)],
            'price' => ['required', 'numeric', 'min:0'],
            'status' => ['required', Rule::enum(RoomStatus::class)],
            'facilities' => ['nullable', 'array'],
            'facilities.*' => ['string', 'max:50'],
            'description' => ['nullable', 'string'],
            'photos' => ['nullable', 'array'],
            'photos.*' => ['image', 'max:4096'],
        ];
    }
}
