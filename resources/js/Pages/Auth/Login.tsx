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
                <div className="w-full max-w-md rounded-3xl border border-slate-200 bg-white p-8 shadow-xl">
                    <div className="mb-7 flex items-center gap-3">
                        <div className="grid h-12 w-12 place-items-center rounded-2xl bg-brand-600 text-lg font-bold text-white shadow-sm">C</div>
                        <div>
                            <h1 className="text-xl font-bold text-slate-900">Cozy Corner</h1>
                            <p className="text-xs text-slate-400">Modern Student Living</p>
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
                            <input type="checkbox" checked={data.remember} onChange={(e) => setData('remember', e.target.checked)} className="rounded border-slate-300 text-brand-600 focus:ring-brand-500" />
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
