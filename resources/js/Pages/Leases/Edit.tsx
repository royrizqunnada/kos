import { Head, Link, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Button, Card, Field, Input, PageHeader, SecondaryButton, Select, Textarea } from '@/Components/ui';
import { rupiah } from '@/lib/format';
import type { Option } from '@/types';
import { FormEvent } from 'react';

interface RoomOpt { id: number; room_number: string; price: string; }
interface TenantOpt { id: number; name: string; nik: string; }
interface LeaseEdit {
    id: number; room_id: number; tenant_id: number; start_date: string;
    duration: string; monthly_price: number; deposit: number; notes: string | null;
}

export default function Edit({ lease, rooms, tenants, durations }: { lease: LeaseEdit; rooms: RoomOpt[]; tenants: TenantOpt[]; durations: Option[] }) {
    const { data, setData, put, processing, errors } = useForm({
        room_id: String(lease.room_id),
        tenant_id: String(lease.tenant_id),
        start_date: lease.start_date,
        duration: lease.duration,
        monthly_price: String(lease.monthly_price),
        deposit: String(lease.deposit),
        notes: lease.notes ?? '',
    });

    const submit = (e: FormEvent) => { e.preventDefault(); put(`/leases/${lease.id}`); };

    return (
        <AuthenticatedLayout>
            <Head title="Edit Kontrak" />
            <PageHeader title="Edit Kontrak" action={<Link href={`/leases/${lease.id}`}><SecondaryButton>Kembali</SecondaryButton></Link>} />
            <Card className="max-w-3xl p-6">
                <form onSubmit={submit} className="space-y-5">
                    <div className="grid gap-5 sm:grid-cols-2">
                        <Field label="Kamar" error={errors.room_id}>
                            <Select value={data.room_id} onChange={(e) => setData('room_id', e.target.value)}>
                                {rooms.map((r) => <option key={r.id} value={r.id}>{r.room_number} — {rupiah(r.price)}</option>)}
                            </Select>
                        </Field>
                        <Field label="Penghuni" error={errors.tenant_id}>
                            <Select value={data.tenant_id} onChange={(e) => setData('tenant_id', e.target.value)}>
                                {tenants.map((t) => <option key={t.id} value={t.id}>{t.name} ({t.nik})</option>)}
                            </Select>
                        </Field>
                        <Field label="Tanggal Mulai" error={errors.start_date}>
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
                    <p className="text-xs text-slate-400">Catatan: mengubah kontrak tidak mengubah tagihan yang sudah dibuat. Perbaiki tagihan/pembayaran terkait secara terpisah bila perlu.</p>
                    <Button type="submit" disabled={processing}>Simpan Perubahan</Button>
                </form>
            </Card>
        </AuthenticatedLayout>
    );
}
