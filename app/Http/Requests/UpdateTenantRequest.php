<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTenantRequest extends FormRequest
{
    public function rules(): array
    {
        $tenantId = $this->route('tenant')?->id;

        return [
            'name' => ['required', 'string', 'max:120'],
            'nik' => ['required', 'string', 'max:32', Rule::unique('tenants', 'nik')->ignore($tenantId)],
            'phone' => ['required', 'string', 'max:32'],
            'email' => ['nullable', 'email', 'max:120'],
            'address' => ['nullable', 'string'],
            'ktp_photo' => ['nullable', 'image', 'max:4096'],
            'emergency_contact_name' => ['nullable', 'string', 'max:120'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:32'],
        ];
    }
}
