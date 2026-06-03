import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Badge, Button, Card, EmptyState, PageHeader, Pagination } from '@/Components/ui';
import { rupiah, tanggal } from '@/lib/format';
import type { Paginated, Payment } from '@/types';
import { Plus } from 'lucide-react';

export default function Index({ payments }: { payments: Paginated<Payment> }) {
    return (
        <AuthenticatedLayout>
            <Head title="Pembayaran" />
            <PageHeader
                title="Pembayaran"
                subtitle="Riwayat pembayaran tagihan"
                action={<Link href="/payments/create"><Button><Plus size={16} /> Catat Pembayaran</Button></Link>}
            />
            <Card>
                {payments.data.length === 0 ? <EmptyState message="Belum ada pembayaran." /> : (
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead className="bg-slate-50 text-left text-xs uppercase text-slate-500">
                                <tr><th className="px-4 py-3">Tanggal</th><th className="px-4 py-3">Invoice</th><th className="px-4 py-3">Penghuni</th><th className="px-4 py-3">Metode</th><th className="px-4 py-3">Nominal</th></tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100">
                                {payments.data.map((p) => (
                                    <tr key={p.id} className="hover:bg-slate-50">
                                        <td className="px-4 py-3 text-slate-500">{tanggal(p.paid_at)}</td>
                                        <td className="px-4 py-3 font-mono text-xs">
                                            {p.invoice ? <Link href={`/invoices/${p.invoice.id}`} className="text-brand-600 hover:underline">{p.invoice.invoice_number}</Link> : '-'}
                                        </td>
                                        <td className="px-4 py-3">{p.invoice?.lease?.tenant?.name ?? '-'}</td>
                                        <td className="px-4 py-3"><Badge status="paid" label={p.method} /></td>
                                        <td className="px-4 py-3 font-semibold text-emerald-600">{rupiah(p.amount)}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}
                <Pagination paginator={payments} />
            </Card>
        </AuthenticatedLayout>
    );
}
