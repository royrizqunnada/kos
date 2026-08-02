import { Head, Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Button, Card, Field, Input, PageHeader } from '@/Components/ui';
import UserForm from './Form';
import type { ManagedUser, Option } from '@/types';
import { KeyRound } from 'lucide-react';

function PasswordForm({ user }: { user: ManagedUser }) {
    const { data, setData, put, processing, errors, reset } = useForm({
        password: '',
        password_confirmation: '',
    });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        put(`/users/${user.id}/password`, {
            preserveScroll: true,
            onSuccess: () => reset(),
        });
    };

    return (
        <form onSubmit={submit} className="space-y-5">
            <div className="flex items-center gap-3">
                <div className="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-amber-50 text-amber-600"><KeyRound size={18} /></div>
                <div>
                    <p className="font-semibold text-slate-800">Ubah Password</p>
                    <p className="text-xs text-slate-400">Password lama tidak diperlukan. Beritahukan password baru ke pemilik akun.</p>
                </div>
            </div>
            <div className="grid gap-5 sm:grid-cols-2">
                <Field label="Password Baru" error={errors.password}>
                    <Input type="password" value={data.password} onChange={(e) => setData('password', e.target.value)} autoComplete="new-password" />
                </Field>
                <Field label="Ulangi Password Baru" error={errors.password_confirmation}>
                    <Input type="password" value={data.password_confirmation} onChange={(e) => setData('password_confirmation', e.target.value)} autoComplete="new-password" />
                </Field>
            </div>
            <Button type="submit" disabled={processing}>Simpan Password</Button>
        </form>
    );
}

export default function Edit({ user, roles, is_self }: { user: ManagedUser; roles: Option[]; is_self: boolean }) {
    return (
        <AuthenticatedLayout>
            <Head title={`Edit ${user.name}`} />
            <PageHeader title={`Edit ${user.name}`} subtitle={user.email} action={<Link href="/users" className="text-sm text-brand-600 hover:underline">Kembali</Link>} />
            <div className="max-w-3xl space-y-5">
                <Card className="p-6"><UserForm user={user} roles={roles} lockRole={is_self} /></Card>
                <Card className="p-6"><PasswordForm user={user} /></Card>
            </div>
        </AuthenticatedLayout>
    );
}
