import { useForm } from '@inertiajs/react';
import { Button, Field, Input, Select } from '@/Components/ui';
import type { ManagedUser, Option } from '@/types';
import { FormEvent } from 'react';

export default function UserForm({ user, roles, lockRole = false }: { user?: ManagedUser; roles: Option[]; lockRole?: boolean }) {
    const { data, setData, post, put, processing, errors } = useForm({
        name: user?.name ?? '',
        email: user?.email ?? '',
        phone: user?.phone ?? '',
        role: user?.role ?? roles[roles.length - 1]?.value ?? '',
        password: '',
        password_confirmation: '',
    });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        if (user) put(`/users/${user.id}`);
        else post('/users');
    };

    return (
        <form onSubmit={submit} className="space-y-5">
            <div className="grid gap-5 sm:grid-cols-2">
                <Field label="Nama Lengkap" error={errors.name}>
                    <Input value={data.name} onChange={(e) => setData('name', e.target.value)} autoComplete="name" />
                </Field>
                <Field label="Email (dipakai untuk login)" error={errors.email}>
                    <Input type="email" value={data.email} onChange={(e) => setData('email', e.target.value)} autoComplete="email" />
                </Field>
                <Field label="Nomor HP" error={errors.phone}>
                    <Input value={data.phone ?? ''} onChange={(e) => setData('phone', e.target.value)} placeholder="08xxxxxxxxxx" />
                </Field>
                <Field label="Peran" error={errors.role}>
                    <Select value={data.role} onChange={(e) => setData('role', e.target.value)} disabled={lockRole}>
                        {roles.map((r) => <option key={r.value} value={r.value}>{r.label}</option>)}
                    </Select>
                    {lockRole && <span className="mt-1 block text-xs text-slate-400">Peran akun sendiri tidak dapat diubah.</span>}
                </Field>
            </div>

            {!user && (
                <div className="grid gap-5 sm:grid-cols-2">
                    <Field label="Password" error={errors.password}>
                        <Input type="password" value={data.password} onChange={(e) => setData('password', e.target.value)} autoComplete="new-password" />
                    </Field>
                    <Field label="Ulangi Password" error={errors.password_confirmation}>
                        <Input type="password" value={data.password_confirmation} onChange={(e) => setData('password_confirmation', e.target.value)} autoComplete="new-password" />
                    </Field>
                </div>
            )}

            <Button type="submit" disabled={processing}>{user ? 'Simpan Perubahan' : 'Simpan User'}</Button>
        </form>
    );
}
