import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Badge, Button, Card, EmptyState, PageHeader, Pagination, Select } from '@/Components/ui';
import { rupiah, tanggal } from '@/lib/format';
import type { Invoice, Option, Paginated } from '@/types';
import { RefreshCw } from 'lucide-react';

interface Props { invoices: Paginated<Invoice>; filters: { status?: string }; statuses: Option[]; outstandingTotal: number; }

export default function Index({ invoices, filters, statuses, outstandingTotal }: Props) {
    return (
        <AuthenticatedLayout>
            <Head title="Tagihan" />
            <PageHeader
                title="Tagihan"
                subtitle={`Total piutang berjalan: ${rupiah(outstandingTotal)}`}
                action={<Button onClick={() => router.post('/invoices/generate')}><RefreshCw size={16} /> Generate Bulan Ini</Button>}
            />
            <Card>
                <div className="border-b border-slate-100 p-4">
                    <Select value={filters.status ?? ''} onChange={(e) => router.get('/invoices', { status: e.target.value }, { preserveState: true, replace: true })} className="max-w-44">
                        <option value="">Semua Status</option>
                        {statuses.map((s) => <option key={s.value} value={s.value}>{s.label}</option>)}
                    </Select>
                </div>
                {invoices.data.length === 0 ? <EmptyState message="Belum ada tagihan." /> : (
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead className="bg-slate-50 text-left text-xs uppercase text-slate-500">
                                <tr><th className="px-4 py-3">No. Invoice</th><th className="px-4 py-3">Penghuni</th><th className="px-4 py-3">Jatuh Tempo</th><th className="px-4 py-3">Nominal</th><th className="px-4 py-3">Dibayar</th><th className="px-4 py-3">Status</th><th className="px-4 py-3"></th></tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100">
                                {invoices.data.map((inv) => (
                                    <tr key={inv.id} className="hover:bg-slate-50">
                                        <td className="px-4 py-3 font-mono text-xs font-semibold text-slate-800">{inv.invoice_number}</td>
                                        <td className="px-4 py-3">{inv.lease?.tenant?.name}</td>
                                        <td className="px-4 py-3 text-slate-500">{tanggal(inv.due_date)}</td>
                                        <td className="px-4 py-3">{rupiah(inv.amount)}</td>
                                        <td className="px-4 py-3 text-slate-500">{rupiah(inv.paid_amount)}</td>
                                        <td className="px-4 py-3"><Badge status={inv.status} label={statuses.find((s) => s.value === inv.status)?.label ?? inv.status} /></td>
                                        <td className="px-4 py-3 text-right"><Link href={`/invoices/${inv.id}`} className="text-brand-600 hover:underline">Detail</Link></td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}
                <Pagination paginator={invoices} />
            </Card>
        </AuthenticatedLayout>
    );
}
