import { Head, Link, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Button, Card, Field, Input, PageHeader, Select, Textarea } from '@/Components/ui';
import { rupiah } from '@/lib/format';
import { FormEvent } from 'react';

interface InvoiceOpt { id: number; invoice_number: string; tenant: string; outstanding: number; }

export default function Create({ invoices, preselect }: { invoices: InvoiceOpt[]; preselect: number | null }) {
    const { data, setData, post, processing, errors } = useForm({
        invoice_id: preselect ? String(preselect) : '',
        amount: '', method: 'transfer', paid_at: new Date().toISOString().slice(0, 10), note: '', proof: null as File | null,
    });

    const selected = invoices.find((i) => String(i.id) === data.invoice_id);

    const submit = (e: FormEvent) => { e.preventDefault(); post('/payments', { forceFormData: true }); };

    return (
        <AuthenticatedLayout>
            <Head title="Catat Pembayaran" />
            <PageHeader title="Catat Pembayaran" action={<Link href="/payments" className="text-sm text-brand-600 hover:underline">Kembali</Link>} />
            <Card className="max-w-2xl p-6">
                <form onSubmit={submit} className="space-y-5">
                    <Field label="Tagihan" error={errors.invoice_id}>
                        <Select value={data.invoice_id} onChange={(e) => setData('invoice_id', e.target.value)}>
                            <option value="">Pilih tagihan…</option>
                            {invoices.map((i) => <option key={i.id} value={i.id}>{i.invoice_number} — {i.tenant} (sisa {rupiah(i.outstanding)})</option>)}
                        </Select>
                    </Field>
                    {selected && (
                        <div className="rounded-lg bg-slate-50 px-4 py-3 text-sm text-slate-600 dark:bg-slate-800/60 dark:text-slate-300">
                            Sisa tagihan: <span className="font-semibold text-slate-800 dark:text-slate-100">{rupiah(selected.outstanding)}</span>
                            <button type="button" onClick={() => setData('amount', String(selected.outstanding))} className="ml-3 text-brand-600 hover:underline">Bayar penuh</button>
                        </div>
                    )}
                    <div className="grid gap-5 sm:grid-cols-2">
                        <Field label="Nominal" error={errors.amount}><Input type="number" value={data.amount} onChange={(e) => setData('amount', e.target.value)} /></Field>
                        <Field label="Metode" error={errors.method}>
                            <Select value={data.method} onChange={(e) => setData('method', e.target.value)}>
                                <option value="cash">Tunai</option>
                                <option value="transfer">Transfer Bank</option>
                                <option value="ewallet">E-Wallet</option>
                            </Select>
                        </Field>
                        <Field label="Tanggal Bayar" error={errors.paid_at}><Input type="date" value={data.paid_at} onChange={(e) => setData('paid_at', e.target.value)} /></Field>
                        <Field label="Bukti Transfer" error={errors.proof}><Input type="file" accept="image/*" onChange={(e) => setData('proof', e.target.files?.[0] ?? null)} /></Field>
                    </div>
                    <Field label="Keterangan" error={errors.note}><Textarea rows={2} value={data.note} onChange={(e) => setData('note', e.target.value)} /></Field>
                    <Button type="submit" disabled={processing}>Simpan Pembayaran</Button>
                </form>
            </Card>
        </AuthenticatedLayout>
    );
}
