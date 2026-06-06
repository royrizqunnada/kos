import { Head, useForm, usePage, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Button, Card, Textarea } from '@/Components/ui';
import type { PageProps, ReminderTemplate } from '@/types';
import { FormEvent, useState } from 'react';
import { MessageSquareText, LogOut, ChevronRight, ChevronDown } from 'lucide-react';

const offsetLabel = (o: number) => (o < 0 ? `H${o}` : `H+${o}`);

export default function Index({ templates, variables }: { templates: ReminderTemplate[]; variables: string[] }) {
    const { auth } = usePage<PageProps>().props;
    const [showTpl, setShowTpl] = useState(false);
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
            </div>

            <h2 className="mb-2 mt-3 px-1 text-xs font-semibold uppercase tracking-wide text-slate-400">Pengaturan</h2>
            <Card className="divide-y divide-slate-100 overflow-hidden">
                <button onClick={() => setShowTpl((v) => !v)} className="flex w-full items-center gap-3 px-4 py-4 text-left transition hover:bg-slate-50">
                    <div className="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-brand-50 text-brand-600"><MessageSquareText size={18} /></div>
                    <div className="min-w-0 flex-1">
                        <p className="font-semibold text-slate-800">Template Pengingat</p>
                        <p className="text-xs text-slate-400">Pesan WhatsApp jatuh tempo (H-7 … H+7)</p>
                    </div>
                    {showTpl ? <ChevronDown size={18} className="text-slate-400" /> : <ChevronRight size={18} className="text-slate-400" />}
                </button>
                <button onClick={() => router.post('/logout')} className="flex w-full items-center gap-3 px-4 py-4 text-left transition hover:bg-rose-50">
                    <div className="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-rose-50 text-rose-600"><LogOut size={18} /></div>
                    <div className="min-w-0 flex-1"><p className="font-semibold text-rose-600">Keluar</p></div>
                    <ChevronRight size={18} className="text-rose-300" />
                </button>
            </Card>

            {showTpl && (
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
