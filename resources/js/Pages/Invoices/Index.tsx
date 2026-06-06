import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Badge, Button, Card, EmptyState, PageHeader, Pagination, Segmented, Select } from '@/Components/ui';
import { rupiah, tanggal } from '@/lib/format';
import type { Invoice, Option, Paginated } from '@/types';
import { RefreshCw, BellRing } from 'lucide-react';

interface Props { invoices: Paginated<Invoice>; filters: { status?: string }; statuses: Option[]; outstandingTotal: number; }

export default function Index({ invoices, filters, statuses, outstandingTotal }: Props) {
    const statusLabel = (inv: Invoice) => statuses.find((s) => s.value === inv.status)?.label ?? inv.status;
    const hapus = (inv: Invoice) => {
        if (confirm(`Hapus tagihan ${inv.invoice_number}? Pembayaran terkait ikut terhapus.`)) {
            router.delete(`/invoices/${inv.id}`);
        }
    };

    return (
        <AuthenticatedLayout>
            <Head title="Tagihan" />
            <PageHeader
                title="Tagihan & Pembayaran"
                subtitle={`Total piutang berjalan: ${rupiah(outstandingTotal)}`}
                action={<Button onClick={() => router.post('/invoices/generate')}><RefreshCw size={16} /> Generate</Button>}
            />
            <Segmented items={[{ label: 'Tagihan', href: '/invoices' }, { label: 'Pembayaran', href: '/payments' }]} />
            <Card>
                <div className="border-b border-slate-100 p-4">
                    <Select value={filters.status ?? ''} onChange={(e) => router.get('/invoices', { status: e.target.value }, { preserveState: true, replace: true })} className="max-w-44">
                        <option value="">Semua Status</option>
                        {statuses.map((s) => <option key={s.value} value={s.value}>{s.label}</option>)}
                    </Select>
                </div>
                {invoices.data.length === 0 ? (
                    <EmptyState title="Belum ada tagihan" message="Tagihan akan muncul di sini. Buat kontrak atau klik Generate Bulan Ini." />
                ) : (
                    <>
                        {/* Mobile: kartu */}
                        <ul className="divide-y divide-slate-100 lg:hidden">
                            {invoices.data.map((inv) => (
                                <li key={inv.id}>
                                    <Link href={`/invoices/${inv.id}`} className="flex items-start justify-between gap-3 px-4 py-3 active:bg-slate-50">
                                        <div className="min-w-0 flex-1">
                                            <p className="font-semibold text-slate-800">{inv.lease?.tenant?.name ?? '-'}</p>
                                            <p className="mt-0.5 text-xs text-slate-500">{inv.invoice_number} · jatuh tempo {tanggal(inv.due_date)}</p>
                                        </div>
                                        <div className="shrink-0 text-right">
                                            <p className="font-bold text-slate-800">{rupiah(inv.amount)}</p>
                                            <span className="mt-1 inline-block"><Badge status={inv.status} label={statusLabel(inv)} /></span>
                                        </div>
                                    </Link>
                                </li>
                            ))}
                        </ul>

                        {/* Desktop: tabel */}
                        <div className="hidden overflow-x-auto lg:block">
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
                                            <td className="px-4 py-3"><Badge status={inv.status} label={statusLabel(inv)} /></td>
                                            <td className="px-4 py-3 text-right">
                                                <div className="flex items-center justify-end gap-3">
                                                    <Link href={`/invoices/${inv.id}`} className="text-brand-600 hover:underline">Detail</Link>
                                                    <button onClick={() => hapus(inv)} className="text-rose-600 hover:underline">Hapus</button>
                                                </div>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </>
                )}
                <Pagination paginator={invoices} />
            </Card>

            {/* Pengingat Otomatis (sesuai PDF) */}
            <Card className="mt-5 p-5">
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-3">
                        <div className="grid h-10 w-10 place-items-center rounded-xl bg-brand-50 text-brand-600"><BellRing size={18} /></div>
                        <div>
                            <p className="text-sm font-semibold text-slate-800">Pengingat Otomatis</p>
                            <p className="text-xs text-slate-500">WhatsApp · terkirim H-7, H-3, H-1, H+1, H+7</p>
                        </div>
                    </div>
                    <Badge status="active" label="Aktif" />
                </div>
            </Card>
        </AuthenticatedLayout>
    );
}
