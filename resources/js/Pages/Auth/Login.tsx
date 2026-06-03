import { Head, useForm } from '@inertiajs/react';
import { Button, Field, Input } from '@/Components/ui';
import { FormEvent } from 'react';

export default function Login() {
    const { data, setData, post, processing, errors } = useForm({ email: '', password: '', remember: false });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        post('/login');
    };

    return (
        <>
            <Head title="Masuk" />
            <div className="flex min-h-screen items-center justify-center bg-slate-100 px-4">
                <div className="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
                    <div className="mb-6 flex items-center gap-2">
                        <div className="grid h-10 w-10 place-items-center rounded-lg bg-brand-600 font-bold text-white">K</div>
                        <div>
                            <h1 className="text-xl font-bold text-slate-900">Kos Manager</h1>
                            <p className="text-xs text-slate-400">Sistem Management Kos</p>
                        </div>
                    </div>
                    <form onSubmit={submit} className="space-y-4">
                        <Field label="Email" error={errors.email}>
                            <Input type="email" value={data.email} onChange={(e) => setData('email', e.target.value)} autoFocus />
                        </Field>
                        <Field label="Kata Sandi" error={errors.password}>
                            <Input type="password" value={data.password} onChange={(e) => setData('password', e.target.value)} />
                        </Field>
                        <label className="flex items-center gap-2 text-sm text-slate-600">
                            <input type="checkbox" checked={data.remember} onChange={(e) => setData('remember', e.target.checked)} />
                            Ingat saya
                        </label>
                        <Button type="submit" disabled={processing} className="w-full">Masuk</Button>
                    </form>
                    <p className="mt-6 text-center text-xs text-slate-400">
                        Demo: superadmin@kos.test / password
                    </p>
                </div>
            </div>
        </>
    );
}
