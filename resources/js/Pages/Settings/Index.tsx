import { Head, Link, useForm, usePage, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Button, Card, Field, Input, Textarea } from '@/Components/ui';
import { can } from '@/lib/can';
import type { PageProps, ReminderTemplate } from '@/types';
import { FormEvent, useState } from 'react';
import { MessageSquareText, LogOut, ChevronRight, ChevronDown, UserCog, KeyRound, UserRound } from 'lucide-react';

const offsetLabel = (o: number) => (o < 0 ? `H${o}` : `H+${o}`);

type Profile = { name: string; email: string; phone: string | null };

function AkunForm({ profile }: { profile: Profile }) {
    const { data, setData, put, processing, errors } = useForm({
        name: profile.name,
        email: profile.email,
        phone: profile.phone ?? '',
    });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        put('/profile', { preserveScroll: true });
    };

    return (
        <form onSubmit={submit} className="space-y-5 p-5">
            <div className="grid gap-5 sm:grid-cols-2">
                <Field label="Nama Lengkap" error={errors.name}>
                    <Input value={data.name} onChange={(e) => setData('name', e.target.value)} autoComplete="name" />
                </Field>
                <Field label="Email (dipakai untuk login)" error={errors.email}>
                    <Input type="email" value={data.email} onChange={(e) => setData('email', e.target.value)} autoComplete="email" />
                </Field>
                <Field label="Nomor HP" error={errors.phone}>
                    <Input value={data.phone} onChange={(e) => setData('phone', e.target.value)} placeholder="08xxxxxxxxxx" />
                </Field>
            </div>
            <Button type="submit" disabled={processing}>Simpan Data Akun</Button>
        </form>
    );
}

function PasswordForm() {
    const { data, setData, put, processing, errors, reset } = useForm({
        current_password: '',
        password: '',
        password_confirmation: '',
    });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        put('/profile/password', { preserveScroll: true, onSuccess: () => reset() });
    };

    return (
        <form onSubmit={submit} className="space-y-5 p-5">
            <Field label="Password Saat Ini" error={errors.current_password}>
                <Input type="password" value={data.current_password} onChange={(e) => setData('current_password', e.target.value)} autoComplete="current-password" className="sm:max-w-sm" />
            </Field>
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

function Row({ icon: Icon, tone = 'brand', title, subtitle, open, onClick }: {
    icon: typeof UserRound;
    tone?: 'brand' | 'amber';
    title: string;
    subtitle: string;
    open: boolean;
    onClick: () => void;
}) {
    const tones = { brand: 'bg-brand-50 text-brand-600', amber: 'bg-amber-50 text-amber-600' };
    return (
        <button onClick={onClick} className="flex w-full items-center gap-3 px-4 py-4 text-left transition hover:bg-slate-50">
            <div className={`grid h-10 w-10 shrink-0 place-items-center rounded-xl ${tones[tone]}`}><Icon size={18} /></div>
            <div className="min-w-0 flex-1">
                <p className="font-semibold text-slate-800">{title}</p>
                <p className="text-xs text-slate-400">{subtitle}</p>
            </div>
            {open ? <ChevronDown size={18} className="text-slate-400" /> : <ChevronRight size={18} className="text-slate-400" />}
        </button>
    );
}

export default function Index({ templates, variables, profile, canManageSettings }: {
    templates: ReminderTemplate[];
    variables: string[];
    profile: Profile;
    canManageSettings: boolean;
}) {
    const { auth } = usePage<PageProps>().props;
    const [section, setSection] = useState<'akun' | 'password' | 'template' | null>(null);
    const toggle = (s: 'akun' | 'password' | 'template') => setSection((v) => (v === s ? null : s));

    const { data, setData, put, processing } = useForm({
        templates: templates.map((t) => ({ id: t.id, subject: t.subject ?? '', body: t.body, is_active: t.is_active })),
    });

    const patch = (id: number, key: 'body' | 'is_active', value: string | boolean) => {
        setData('templates', data.templates.map((t) => (t.id === id ? { ...t, [key]: value } : t)));
    };
    const submit = (e: FormEvent) => { e.preventDefault(); put('/settings'); };
    const initial = (auth.user?.name ?? 'U').charAt(0).toUpperCase();

    return (
        <AuthenticatedLayout>
            <Head title="Profil & Pengaturan" />

            {/* Header profil (sesuai PDF) */}
            <div className="flex flex-col items-center py-5 text-center">
                <div className="grid h-24 w-24 place-items-center rounded-[28px] bg-brand-600 text-3xl font-bold text-white shadow-lg">{initial}</div>
                <p className="mt-4 text-xl font-bold text-slate-900">{auth.user?.name}</p>
                <p className="mt-0.5 text-sm text-slate-500 capitalize">{auth.user?.roles?.join(', ') || 'Pengguna'} · Cozy Corner</p>
                <p className="mt-0.5 text-sm text-slate-400">{profile.email}</p>
            </div>

            <h2 className="mb-2 mt-3 px-1 text-xs font-semibold uppercase tracking-wide text-slate-400">Akun Saya</h2>
            <Card className="divide-y divide-slate-100 overflow-hidden">
                <Row icon={UserRound} title="Data Akun" subtitle="Ubah nama, email login, dan nomor HP" open={section === 'akun'} onClick={() => toggle('akun')} />
                {section === 'akun' && <AkunForm profile={profile} />}

                <Row icon={KeyRound} tone="amber" title="Ubah Password" subtitle="Ganti password login akun ini" open={section === 'password'} onClick={() => toggle('password')} />
                {section === 'password' && <PasswordForm />}
            </Card>

            <h2 className="mb-2 mt-6 px-1 text-xs font-semibold uppercase tracking-wide text-slate-400">Pengaturan</h2>
            <Card className="divide-y divide-slate-100 overflow-hidden">
                {can(auth.user, 'user.viewAny') && (
                    <Link href="/users" className="flex w-full items-center gap-3 px-4 py-4 text-left transition hover:bg-slate-50">
                        <div className="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-violet-50 text-violet-600"><UserCog size={18} /></div>
                        <div className="min-w-0 flex-1">
                            <p className="font-semibold text-slate-800">Manajemen User</p>
                            <p className="text-xs text-slate-400">Tambah user, atur peran, dan reset password</p>
                        </div>
                        <ChevronRight size={18} className="text-slate-400" />
                    </Link>
                )}

                {canManageSettings && (
                    <Row icon={MessageSquareText} title="Template Pengingat" subtitle="Pesan WhatsApp jatuh tempo (H-7 … H+7)" open={section === 'template'} onClick={() => toggle('template')} />
                )}

                <button onClick={() => router.post('/logout')} className="flex w-full items-center gap-3 px-4 py-4 text-left transition hover:bg-rose-50">
                    <div className="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-rose-50 text-rose-600"><LogOut size={18} /></div>
                    <div className="min-w-0 flex-1"><p className="font-semibold text-rose-600">Keluar</p></div>
                    <ChevronRight size={18} className="text-rose-300" />
                </button>
            </Card>

            {canManageSettings && section === 'template' && (
                <div className="mt-5">
                    <Card className="mb-4 p-4 text-sm text-slate-600">
                        <span className="font-medium text-slate-700">Variabel tersedia</span>
                        <div className="mt-2">
                            {variables.map((v) => <code key={v} className="mr-1.5 inline-block rounded bg-slate-100 px-1.5 py-0.5 text-xs">{v}</code>)}
                        </div>
                    </Card>

                    <form onSubmit={submit} className="space-y-4">
                        {templates.map((tpl) => {
                            const value = data.templates.find((t) => t.id === tpl.id)!;
                            return (
                                <Card key={tpl.id} className="p-5">
                                    <div className="mb-3 flex items-center justify-between">
                                        <h3 className="text-sm font-semibold text-slate-800">
                                            <span className="mr-2 rounded bg-brand-50 px-2 py-0.5 text-xs text-brand-700">WHATSAPP</span>
                                            Jadwal {offsetLabel(tpl.offset_days)}
                                        </h3>
                                        <label className="flex items-center gap-2 text-sm text-slate-500">
                                            <input type="checkbox" checked={value.is_active} onChange={(e) => patch(tpl.id, 'is_active', e.target.checked)} className="rounded border-slate-300 text-brand-600 focus:ring-brand-500" /> Aktif
                                        </label>
                                    </div>
                                    <Textarea rows={4} value={value.body} onChange={(e) => patch(tpl.id, 'body', e.target.value)} />
                                </Card>
                            );
                        })}
                        <Button type="submit" disabled={processing}>Simpan Template</Button>
                    </form>
                </div>
            )}
        </AuthenticatedLayout>
    );
}
