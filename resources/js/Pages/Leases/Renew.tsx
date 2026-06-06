import { Head, Link, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Button, Card, Field, Input, PageHeader, SecondaryButton, Select, Textarea } from '@/Components/ui';
import { rupiah } from '@/lib/format';
import type { Option } from '@/types';
import { FormEvent } from 'react';

interface RoomOpt { id: number; room_number: string; price: string; }
interface TenantOpt { id: number; name: string; nik: string; }
interface OldLease { id: number; tenant_id: number; tenant_name: string; room_id: number; duration: string; monthly_price: number; deposit: number; }

export default function Renew({ old, default_start, rooms, tenants, durations }: { old: OldLease; default_start: string; rooms: RoomOpt[]; tenants: TenantOpt[]; durations: Option[] }) {
    const { data, setData, post, processing, errors } = useForm({
        room_id: String(old.room_id),
        tenant_id: String(old.tenant_id),
        start_date: default_start,
        duration: old.duration,
        monthly_price: String(old.monthly_price),
        deposit: '0',
        notes: '',
        generate_invoice: true,
    });

    const submit = (e: FormEvent) => { e.preventDefault(); post(`/leases/${old.id}/renew`); };

    return (
        <AuthenticatedLayout>
            <Head title="Perpanjang Kontrak" />
            <PageHeader title="Perpanjang Kontrak" subtitle={`Lanjutkan ${old.tenant_name}`} action={<Link href={`/leases/${old.id}`}><SecondaryButton>Kembali</SecondaryButton></Link>} />
            <Card className="max-w-3xl p-6">
                <div className="mb-5 rounded-lg bg-brand-50 px-4 py-3 text-sm text-brand-700">
                    Kontrak lama akan ditandai <b>Berakhir</b> (tersimpan sebagai history), lalu dibuat kontrak baru + tagihan pertamanya.
                </div>
                <form onSubmit={submit} className="space-y-5">
                    <div className="grid gap-5 sm:grid-cols-2">
                        <Field label="Penghuni" error={errors.tenant_id}>
                            <Select value={data.tenant_id} onChange={(e) => setData('tenant_id', e.target.value)}>
                                {tenants.map((t) => <option key={t.id} value={t.id}>{t.name} ({t.nik})</option>)}
                            </Select>
                        </Field>
                        <Field label="Kamar" error={errors.room_id}>
                            <Select value={data.room_id} onChange={(e) => setData('room_id', e.target.value)}>
                                {rooms.map((r) => <option key={r.id} value={r.id}>{r.room_number} — {rupiah(r.price)}</option>)}
                            </Select>
                        </Field>
                        <Field label="Mulai Kontrak Baru" error={errors.start_date}>
                            <Input type="date" value={data.start_date} onChange={(e) => setData('start_date', e.target.value)} />
                        </Field>
                        <Field label="Durasi" error={errors.duration}>
                            <Select value={data.duration} onChange={(e) => setData('duration', e.target.value)}>
                                {durations.map((d) => <option key={d.value} value={d.value}>{d.label}</option>)}
                            </Select>
                        </Field>
                        <Field label="Harga / bulan" error={errors.monthly_price}>
                            <Input type="number" value={data.monthly_price} onChange={(e) => setData('monthly_price', e.target.value)} />
                        </Field>
                        <Field label="Deposit" error={errors.deposit}>
                            <Input type="number" value={data.deposit} onChange={(e) => setData('deposit', e.target.value)} />
                        </Field>
                    </div>
                    <Field label="Catatan" error={errors.notes}><Textarea rows={2} value={data.notes} onChange={(e) => setData('notes', e.target.value)} /></Field>
                    <label className="flex items-center gap-2 text-sm text-slate-600">
                        <input type="checkbox" checked={data.generate_invoice} onChange={(e) => setData('generate_invoice', e.target.checked)} className="rounded border-slate-300 text-brand-600 focus:ring-brand-500" />
                        Buat tagihan pertama otomatis
                    </label>
                    <Button type="submit" disabled={processing}>Perpanjang Kontrak</Button>
                </form>
            </Card>
        </AuthenticatedLayout>
    );
}
