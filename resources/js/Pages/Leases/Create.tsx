import { Head, Link, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Button, Card, Field, Input, PageHeader, Select, Textarea } from '@/Components/ui';
import { rupiah } from '@/lib/format';
import type { Option } from '@/types';
import { FormEvent } from 'react';

interface RoomOpt { id: number; room_number: string; price: string; }
interface TenantOpt { id: number; name: string; nik: string; }
type DurationOpt = Option & { months?: number };

export default function Create({ rooms, tenants, durations }: { rooms: RoomOpt[]; tenants: TenantOpt[]; durations: DurationOpt[] }) {
    const { data, setData, post, processing, errors } = useForm({
        room_id: '', tenant_id: '', start_date: new Date().toISOString().slice(0, 10),
        duration: durations[0]?.value ?? 'monthly', monthly_price: '', discount_type: 'none', discount_value: '', deposit: '', notes: '', generate_invoice: true,
    });

    const months = Number(durations.find((d) => d.value === data.duration)?.months ?? 1);
    const gross = (Number(data.monthly_price) || 0) * months;
    const potongan = data.discount_type === 'nominal' ? Math.min(Number(data.discount_value) || 0, gross)
        : data.discount_type === 'percent' ? Math.round(gross * (Number(data.discount_value) || 0) / 100)
        : 0;

    const onRoom = (id: string) => {
        const room = rooms.find((r) => String(r.id) === id);
        setData((d) => ({ ...d, room_id: id, monthly_price: room?.price ?? d.monthly_price }));
    };

    const submit = (e: FormEvent) => { e.preventDefault(); post('/leases'); };

    return (
        <AuthenticatedLayout>
            <Head title="Buat Kontrak" />
            <PageHeader title="Buat Kontrak" action={<Link href="/leases" className="text-sm text-brand-600 hover:underline">Kembali</Link>} />
            <Card className="max-w-3xl p-6">
                <form onSubmit={submit} className="space-y-5">
                    <div className="grid gap-5 sm:grid-cols-2">
                        <Field label="Kamar (kosong)" error={errors.room_id}>
                            <Select value={data.room_id} onChange={(e) => onRoom(e.target.value)}>
                                <option value="">Pilih kamar…</option>
                                {rooms.map((r) => <option key={r.id} value={r.id}>{r.room_number} — {rupiah(r.price)}</option>)}
                            </Select>
                        </Field>
                        <Field label="Penghuni" error={errors.tenant_id}>
                            <Select value={data.tenant_id} onChange={(e) => setData('tenant_id', e.target.value)}>
                                <option value="">Pilih penghuni…</option>
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
                    {/* Diskon */}
                    <div className="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <p className="mb-3 text-sm font-semibold text-slate-700">Diskon (opsional)</p>
                        <div className="grid gap-4 sm:grid-cols-2">
                            <Field label="Jenis diskon" error={errors.discount_type}>
                                <Select value={data.discount_type} onChange={(e) => setData('discount_type', e.target.value)}>
                                    <option value="none">Tanpa diskon</option>
                                    <option value="nominal">Nominal (Rp)</option>
                                    <option value="percent">Persen (%)</option>
                                </Select>
                            </Field>
                            {data.discount_type !== 'none' && (
                                <Field label={data.discount_type === 'percent' ? 'Besar diskon (%)' : 'Besar diskon (Rp)'} error={errors.discount_value}>
                                    <Input type="number" value={data.discount_value} onChange={(e) => setData('discount_value', e.target.value)} placeholder={data.discount_type === 'percent' ? '10' : '100000'} />
                                </Field>
                            )}
                        </div>
                        {data.discount_type !== 'none' && potongan > 0 && (
                            <div className="mt-3 space-y-0.5 border-t border-slate-200 pt-3 text-sm">
                                <div className="flex justify-between text-slate-500"><span>Total sewa ({months} bln)</span><span>{rupiah(gross)}</span></div>
                                <div className="flex justify-between text-rose-600"><span>Diskon</span><span>− {rupiah(potongan)}</span></div>
                                <div className="flex justify-between font-bold text-slate-800"><span>Total bayar / tagihan</span><span>{rupiah(gross - potongan)}</span></div>
                            </div>
                        )}
                    </div>

                    <Field label="Catatan" error={errors.notes}><Textarea rows={2} value={data.notes} onChange={(e) => setData('notes', e.target.value)} /></Field>
                    <label className="flex items-center gap-2 text-sm text-slate-600">
                        <input type="checkbox" checked={data.generate_invoice} onChange={(e) => setData('generate_invoice', e.target.checked)} />
                        Buat tagihan pertama otomatis
                    </label>
                    <Button type="submit" disabled={processing}>Simpan Kontrak</Button>
                </form>
            </Card>
        </AuthenticatedLayout>
    );
}
