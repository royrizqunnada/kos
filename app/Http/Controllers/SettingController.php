<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\UpdateReminderTemplateRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Src\Reminder\Domain\Models\ReminderTemplate;

class SettingController extends Controller
{
    public function edit(): Response
    {
        // Halaman profil: terbuka untuk semua user (ubah nama/email/password sendiri).
        // Bagian template pengingat hanya tampil bagi yang berhak mengelola pengaturan.
        $user = request()->user();
        $canManageSettings = $user->can('setting.update') || $user->hasRole('super_admin');

        return Inertia::render('Settings/Index', [
            'profile' => [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
            ],
            'canManageSettings' => $canManageSettings,
            'templates' => $canManageSettings
                ? ReminderTemplate::query()->where('channel', 'whatsapp')->orderBy('offset_days')->get()
                : [],
            'variables' => ['{tenant_name}', '{room_number}', '{invoice_number}', '{due_date}', '{amount}', '{outstanding}'],
        ]);
    }

    public function update(UpdateReminderTemplateRequest $request): RedirectResponse
    {
        abort_unless($request->user()->can('setting.update') || $request->user()->hasRole('super_admin'), 403);

        foreach ($request->validated('templates') as $row) {
            ReminderTemplate::query()->whereKey($row['id'])->update([
                'subject' => $row['subject'] ?? null,
                'body' => $row['body'],
                'is_active' => $row['is_active'],
            ]);
        }

        return back()->with('success', 'Template reminder berhasil disimpan.');
    }
}
