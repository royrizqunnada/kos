import { Head, Link, useForm } from '@inertiajs/react';
import { FormEvent, useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Badge, Button, Card, Field, Input, PageHeader, SecondaryButton, Textarea } from '@/Components/ui';
import { rupiah, tanggal } from '@/lib/format';
import type { Lease } from '@/types';

const today = () => new Date().toISOString().slice(0, 10);

export default function Show({ lease, outstanding }: { lease: Lease; outstanding: string }) {
    const [showCheckout, setShowCheckout] = useState(false);
    const deposit = Number(lease.deposit ?? 0);
    const tunggakan = Number(outstanding ?? 0);

    const form = useForm({
        ended_at: today(),
        deposit_deduction: tunggakan > 0 ? Math.min(tunggakan, deposit) : 0,
        deposit_refunded: tunggakan > 0 ? Math.max(0, deposit - Math.min(tunggakan, deposit)) : deposit,
        checkout_notes: '',
    });

    // Saat potongan diubah, sisa deposit otomatis jadi nilai pengembalian.
    const setDeduction = (value: number) => {
        const deduction = Number.isFinite(value) ? value : 0;
        form.setData((data) => ({
            ...data,
            deposit_deduction: deduction,
            deposit_refunded: Math.max(0, deposit - deduction),
        }));
    };

    const submit = (e: FormEvent) => {
        e.preventDefault();
        form.post(`/leases/${lease.id}/end`, { onSuccess: () => setShowCheckout(false) });
    };

    const sisaSettlement = deposit - Number(form.data.deposit_deduction) - Number(form.data.deposit_refunded);

    return (
        <AuthenticatedLayout>
            <Head title="Detail Kontrak" />
            <PageHeader
                title={`Kontrak ${lease.tenant?.name}`}
                subtitle={`Kamar ${lease.room?.room_number}`}
                action={
                    <div className="flex gap-2">
                        <Link href="/leases"><SecondaryButton>Kembali</SecondaryButton></Link>
                        {lease.status === 'active' && (
                            <Button onClick={() => setShowCheckout(true)} className="bg-rose-600 hover:bg-rose-700">Akhiri & Check-out</Button>
                        )}
                    </div>
                }
            />
            <div className="grid gap-6 lg:grid-cols-3">
                <div className="space-y-6 lg:col-span-1">
                    <Card className="p-6">
                        <dl className="space-y-2 text-sm">
                            <div className="flex justify-between"><dt className="text-slate-500">Status</dt><dd><Badge status={lease.status} label={lease.status} /></dd></div>
                            <div className="flex justify-between"><dt className="text-slate-500">Mulai</dt><dd>{tanggal(lease.start_date)}</dd></div>
                            <div className="flex justify-between"><dt className="text-slate-500">Berakhir</dt><dd>{tanggal(lease.end_date)}</dd></div>
                            <div className="flex justify-between"><dt className="text-slate-500">Harga/bln</dt><dd>{rupiah(lease.monthly_price)}</dd></div>
                            <div className="flex justify-between"><dt className="text-slate-500">Deposit</dt><dd>{rupiah(lease.deposit)}</dd></div>
                            {tunggakan > 0 && lease.status === 'active' && (
                                <div className="flex justify-between"><dt className="text-slate-500">Tunggakan</dt><dd className="font-semibold text-rose-600">{rupiah(outstanding)}</dd></div>
                            )}
                        </dl>
                    </Card>

                    {lease.status === 'ended' && (
                        <Card className="p-6">
                            <h2 className="mb-3 text-sm font-semibold text-slate-700">Penyelesaian Check-out</h2>
                            <dl className="space-y-2 text-sm">
                                <div className="flex justify-between"><dt className="text-slate-500">Tanggal keluar</dt><dd>{tanggal(lease.ended_at)}</dd></div>
                                <div className="flex justify-between"><dt className="text-slate-500">Deposit dikembalikan</dt><dd className="font-semibold text-emerald-700">{rupiah(lease.deposit_refunded)}</dd></div>
                                <div className="flex justify-between"><dt className="text-slate-500">Potongan</dt><dd>{rupiah(lease.deposit_deduction)}</dd></div>
                                {lease.checkout_notes && (
                                    <div className="pt-2"><dt className="text-slate-500">Catatan</dt><dd className="mt-1 text-slate-700">{lease.checkout_notes}</dd></div>
                                )}
                            </dl>
                        </Card>
                    )}
                </div>

                <Card className="p-6 lg:col-span-2">
                    <h2 className="mb-3 text-sm font-semibold text-slate-700">Tagihan</h2>
                    <div className="space-y-2">
                        {(lease.invoices ?? []).map((inv) => (
                            <Link key={inv.id} href={`/invoices/${inv.id}`} className="flex items-center justify-between rounded-lg border border-slate-100 px-3 py-2 text-sm hover:bg-slate-50">
                                <span>{inv.invoice_number} · jatuh tempo {tanggal(inv.due_date)}</span>
                                <span className="flex items-center gap-3"><span>{rupiah(inv.amount)}</span><Badge status={inv.status} label={inv.status} /></span>
                            </Link>
                        ))}
                        {(lease.invoices ?? []).length === 0 && <p className="text-sm text-slate-400">Belum ada tagihan.</p>}
                    </div>
                </Card>
            </div>

            {showCheckout && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 p-4" onClick={() => setShowCheckout(false)}>
                    <Card className="w-full max-w-md p-6" >
                        <div onClick={(e) => e.stopPropagation()}>
                            <h2 className="text-lg font-bold text-slate-900">Akhiri Kontrak & Check-out</h2>
                            <p className="mt-1 text-sm text-slate-500">{lease.tenant?.name} · Kamar {lease.room?.room_number}</p>

                            {tunggakan > 0 && (
                                <div className="mt-4 rounded-lg bg-amber-50 px-3 py-2 text-sm text-amber-800">
                                    Masih ada tunggakan <strong>{rupiah(outstanding)}</strong>. Pertimbangkan memotongnya dari deposit.
                                </div>
                            )}

                            <form onSubmit={submit} className="mt-4 space-y-4">
                                <Field label="Tanggal keluar" error={form.errors.ended_at}>
                                    <Input type="date" value={form.data.ended_at} onChange={(e) => form.setData('ended_at', e.target.value)} />
                                </Field>
                                <div className="rounded-lg bg-slate-50 px-3 py-2 text-sm">
                                    <div className="flex justify-between"><span className="text-slate-500">Deposit diterima</span><span className="font-semibold">{rupiah(lease.deposit)}</span></div>
                                </div>
                                <Field label="Potongan (kerusakan / tunggakan)" error={form.errors.deposit_deduction}>
                                    <Input type="number" min={0} step="1000" value={form.data.deposit_deduction} onChange={(e) => setDeduction(Number(e.target.value))} />
                                </Field>
                                <Field label="Deposit dikembalikan" error={form.errors.deposit_refunded}>
                                    <Input type="number" min={0} step="1000" value={form.data.deposit_refunded} onChange={(e) => form.setData('deposit_refunded', Number(e.target.value))} />
                                </Field>
                                {sisaSettlement < 0 && (
                                    <p className="text-xs text-rose-600">Potongan + pengembalian melebihi deposit ({rupiah(lease.deposit)}).</p>
                                )}
                                {sisaSettlement > 0 && (
                                    <p className="text-xs text-slate-500">Sisa belum dialokasikan: {rupiah(sisaSettlement)}.</p>
                                )}
                                <Field label="Catatan check-out" error={form.errors.checkout_notes}>
                                    <Textarea rows={2} value={form.data.checkout_notes} onChange={(e) => form.setData('checkout_notes', e.target.value)} placeholder="Kondisi kamar, kunci dikembalikan, dll." />
                                </Field>
                                <div className="flex justify-end gap-2 pt-2">
                                    <SecondaryButton type="button" onClick={() => setShowCheckout(false)}>Batal</SecondaryButton>
                                    <Button type="submit" disabled={form.processing || sisaSettlement < 0} className="bg-rose-600 hover:bg-rose-700">
                                        Akhiri Kontrak
                                    </Button>
                                </div>
                            </form>
                        </div>
                    </Card>
                </div>
            )}
        </AuthenticatedLayout>
    );
}
