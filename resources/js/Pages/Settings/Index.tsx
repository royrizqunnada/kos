import { Head, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Button, Card, PageHeader, Textarea, Input } from '@/Components/ui';
import type { ReminderTemplate } from '@/types';
import { FormEvent } from 'react';

const offsetLabel = (o: number) => (o < 0 ? `H${o}` : `H+${o}`);

export default function Index({ templates, variables }: { templates: ReminderTemplate[]; variables: string[] }) {
    const { data, setData, put, processing } = useForm({
        templates: templates.map((t) => ({ id: t.id, subject: t.subject ?? '', body: t.body, is_active: t.is_active })),
    });

    const patch = (id: number, key: 'subject' | 'body' | 'is_active', value: string | boolean) => {
        setData('templates', data.templates.map((t) => (t.id === id ? { ...t, [key]: value } : t)));
    };

    const submit = (e: FormEvent) => { e.preventDefault(); put('/settings'); };

    return (
        <AuthenticatedLayout>
            <Head title="Pengaturan" />
            <PageHeader title="Pengaturan Reminder" subtitle="Ubah template pesan WhatsApp & Email per jadwal" />

            <Card className="mb-6 p-4 text-sm text-slate-600">
                Variabel tersedia: {variables.map((v) => <code key={v} className="mx-1 rounded bg-slate-100 px-1.5 py-0.5 text-xs">{v}</code>)}
            </Card>

            <form onSubmit={submit} className="space-y-4">
                {templates.map((tpl) => {
                    const value = data.templates.find((t) => t.id === tpl.id)!;
                    return (
                        <Card key={tpl.id} className="p-5">
                            <div className="mb-3 flex items-center justify-between">
                                <h3 className="text-sm font-semibold text-slate-800">
                                    <span className="mr-2 rounded bg-brand-50 px-2 py-0.5 text-xs text-brand-700">{tpl.channel.toUpperCase()}</span>
                                    Jadwal {offsetLabel(tpl.offset_days)}
                                </h3>
                                <label className="flex items-center gap-2 text-sm text-slate-500">
                                    <input type="checkbox" checked={value.is_active} onChange={(e) => patch(tpl.id, 'is_active', e.target.checked)} /> Aktif
                                </label>
                            </div>
                            {tpl.channel === 'email' && (
                                <div className="mb-3">
                                    <span className="mb-1 block text-xs font-medium text-slate-500">Subjek</span>
                                    <Input value={value.subject} onChange={(e) => patch(tpl.id, 'subject', e.target.value)} />
                                </div>
                            )}
                            <Textarea rows={4} value={value.body} onChange={(e) => patch(tpl.id, 'body', e.target.value)} />
                        </Card>
                    );
                })}
                <Button type="submit" disabled={processing}>Simpan Semua Template</Button>
            </form>
        </AuthenticatedLayout>
    );
}
