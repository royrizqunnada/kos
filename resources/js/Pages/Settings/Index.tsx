import { Head, useForm, usePage, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Button, Card, Textarea, Input } from '@/Components/ui';
import type { PageProps, ReminderTemplate } from '@/types';
import { FormEvent } from 'react';
import { MessageSquareText, LogOut } from 'lucide-react';

const offsetLabel = (o: number) => (o < 0 ? `H${o}` : `H+${o}`);

export default function Index({ templates, variables }: { templates: ReminderTemplate[]; variables: string[] }) {
    const { auth } = usePage<PageProps>().props;
    const { data, setData, put, processing } = useForm({
        templates: templates.map((t) => ({ id: t.id, subject: t.subject ?? '', body: t.body, is_active: t.is_active })),
    });

    const patch = (id: number, key: 'subject' | 'body' | 'is_active', value: string | boolean) => {
        setData('templates', data.templates.map((t) => (t.id === id ? { ...t, [key]: value } : t)));
    };
    const submit = (e: FormEvent) => { e.preventDefault(); put('/settings'); };
    const initial = (auth.user?.name ?? 'U').charAt(0).toUpperCase();

    return (
        <AuthenticatedLayout>
            <Head title="Profil & Pengaturan" />

            {/* Header profil (sesuai PDF) */}
            <Card className="mb-6 flex flex-col items-center p-6 text-center">
                <div className="grid h-20 w-20 place-items-center rounded-3xl bg-brand-600 text-2xl font-bold text-white shadow-sm">{initial}</div>
                <p className="mt-3 text-lg font-bold text-slate-900 dark:text-white">{auth.user?.name}</p>
                <p className="text-sm text-slate-500 dark:text-slate-400 capitalize">{auth.user?.roles?.join(', ') || 'Pengguna'}</p>
            </Card>

            <h2 className="mb-3 px-1 text-xs font-semibold uppercase tracking-wide text-slate-400">Template Pengingat</h2>

            <Card className="mb-5 p-4 text-sm text-slate-600 dark:text-slate-300">
                <span className="flex items-center gap-2 font-medium text-slate-700 dark:text-slate-200"><MessageSquareText size={16} /> Variabel tersedia</span>
                <div className="mt-2">
                    {variables.map((v) => <code key={v} className="mr-1.5 inline-block rounded bg-slate-100 px-1.5 py-0.5 text-xs dark:bg-slate-800 dark:text-slate-300">{v}</code>)}
                </div>
            </Card>

            <form onSubmit={submit} className="space-y-4">
                {templates.map((tpl) => {
                    const value = data.templates.find((t) => t.id === tpl.id)!;
                    return (
                        <Card key={tpl.id} className="p-5">
                            <div className="mb-3 flex items-center justify-between">
                                <h3 className="text-sm font-semibold text-slate-800 dark:text-slate-100">
                                    <span className="mr-2 rounded bg-brand-50 px-2 py-0.5 text-xs text-brand-700 dark:bg-brand-600/15 dark:text-brand-300">{tpl.channel.toUpperCase()}</span>
                                    Jadwal {offsetLabel(tpl.offset_days)}
                                </h3>
                                <label className="flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400">
                                    <input type="checkbox" checked={value.is_active} onChange={(e) => patch(tpl.id, 'is_active', e.target.checked)} className="rounded border-slate-300 text-brand-600 focus:ring-brand-500" /> Aktif
                                </label>
                            </div>
                            {tpl.channel === 'email' && (
                                <div className="mb-3">
                                    <span className="mb-1 block text-xs font-medium text-slate-500 dark:text-slate-400">Subjek</span>
                                    <Input value={value.subject} onChange={(e) => patch(tpl.id, 'subject', e.target.value)} />
                                </div>
                            )}
                            <Textarea rows={4} value={value.body} onChange={(e) => patch(tpl.id, 'body', e.target.value)} />
                        </Card>
                    );
                })}
                <Button type="submit" disabled={processing}>Simpan Semua Template</Button>
            </form>

            <button
                onClick={() => router.post('/logout')}
                className="mt-6 flex w-full items-center justify-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-600 transition hover:bg-rose-100 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-400"
            >
                <LogOut size={16} /> Keluar
            </button>
        </AuthenticatedLayout>
    );
}
