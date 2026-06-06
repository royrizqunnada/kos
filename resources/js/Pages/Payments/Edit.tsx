import { Head, Link, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Button, Card, Field, Input, PageHeader, SecondaryButton, Select, Textarea } from '@/Components/ui';
import { FormEvent } from 'react';

interface PaymentEdit {
    id: number; invoice_number: string; tenant: string;
    amount: number; method: string; paid_at: string; note: string | null; proof_path: string | null;
}

export default function Edit({ payment }: { payment: PaymentEdit }) {
    const { data, setData, post, processing, errors } = useForm({
        _method: 'put',
        amount: String(payment.amount),
        method: payment.method,
        paid_at: payment.paid_at,
        note: payment.note ?? '',
        proof: null as File | null,
    });

    const submit = (e: FormEvent) => { e.preventDefault(); post(`/payments/${payment.id}`, { forceFormData: true }); };

    return (
        <AuthenticatedLayout>
            <Head title="Edit Pembayaran" />
            <PageHeader title="Edit Pembayaran" action={<Link href="/payments"><SecondaryButton>Kembali</SecondaryButton></Link>} />
            <Card className="max-w-2xl p-6">
                <div className="mb-5 rounded-lg bg-slate-50 px-4 py-3 text-sm text-slate-600">
                    Tagihan: <span className="font-semibold text-slate-800">{payment.invoice_number}</span> · {payment.tenant}
                </div>
                <form onSubmit={submit} className="space-y-5">
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
                        <Field label="Ganti Bukti (opsional)" error={errors.proof}><Input type="file" accept="image/*" onChange={(e) => setData('proof', e.target.files?.[0] ?? null)} /></Field>
                    </div>
                    {payment.proof_path && !data.proof && (
                        <a href={`/storage/${payment.proof_path}`} target="_blank" rel="noreferrer" className="block w-fit">
                            <img src={`/storage/${payment.proof_path}`} alt="Bukti" className="h-32 w-auto rounded-lg border border-slate-200 object-contain" />
                        </a>
                    )}
                    <Field label="Keterangan" error={errors.note}><Textarea rows={2} value={data.note} onChange={(e) => setData('note', e.target.value)} /></Field>
                    <Button type="submit" disabled={processing}>Simpan Perubahan</Button>
                </form>
            </Card>
        </AuthenticatedLayout>
    );
}
