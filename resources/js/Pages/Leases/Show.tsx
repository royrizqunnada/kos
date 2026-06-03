import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Badge, Button, Card, PageHeader, SecondaryButton } from '@/Components/ui';
import { rupiah, tanggal } from '@/lib/format';
import type { Lease } from '@/types';

export default function Show({ lease }: { lease: Lease }) {
    const end = () => {
        if (confirm('Akhiri kontrak ini dan kosongkan kamar?')) router.post(`/leases/${lease.id}/end`);
    };

    return (
        <AuthenticatedLayout>
            <Head title="Detail Kontrak" />
            <PageHeader
                title={`Kontrak ${lease.tenant?.name}`}
                subtitle={`Kamar ${lease.room?.room_number}`}
                action={
                    <div className="flex gap-2">
                        <Link href="/leases"><SecondaryButton>Kembali</SecondaryButton></Link>
                        {lease.status === 'active' && <Button onClick={end} className="bg-rose-600 hover:bg-rose-700">Akhiri Kontrak</Button>}
                    </div>
                }
            />
            <div className="grid gap-6 lg:grid-cols-3">
                <Card className="p-6 lg:col-span-1">
                    <dl className="space-y-2 text-sm">
                        <div className="flex justify-between"><dt className="text-slate-500">Status</dt><dd><Badge status={lease.status} label={lease.status} /></dd></div>
                        <div className="flex justify-between"><dt className="text-slate-500">Mulai</dt><dd>{tanggal(lease.start_date)}</dd></div>
                        <div className="flex justify-between"><dt className="text-slate-500">Berakhir</dt><dd>{tanggal(lease.end_date)}</dd></div>
                        <div className="flex justify-between"><dt className="text-slate-500">Harga/bln</dt><dd>{rupiah(lease.monthly_price)}</dd></div>
                        <div className="flex justify-between"><dt className="text-slate-500">Deposit</dt><dd>{rupiah(lease.deposit)}</dd></div>
                    </dl>
                </Card>
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
        </AuthenticatedLayout>
    );
}
