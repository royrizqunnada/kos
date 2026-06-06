import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Badge, Button, Card, EmptyState, PageHeader, Pagination, Segmented } from '@/Components/ui';
import { rupiah, tanggal } from '@/lib/format';
import type { Paginated, Payment } from '@/types';
import { Plus, Pencil, Trash2, Receipt } from 'lucide-react';

export default function Index({ payments }: { payments: Paginated<Payment> }) {
    const hapus = (p: Payment) => {
        if (confirm(`Hapus pembayaran ${rupiah(p.amount)} (${p.invoice?.invoice_number ?? '-'})? Tagihan akan dihitung ulang.`)) {
            router.delete(`/payments/${p.id}`);
        }
    };

    return (
        <AuthenticatedLayout>
            <Head title="Pembayaran" />
            <PageHeader
                title="Tagihan & Pembayaran"
                subtitle="Riwayat pembayaran tagihan"
                action={<Link href="/payments/create"><Button><Plus size={16} /> Catat Pembayaran</Button></Link>}
            />
            <Segmented items={[{ label: 'Tagihan', href: '/invoices' }, { label: 'Pembayaran', href: '/payments' }]} />
            <Card>
                {payments.data.length === 0 ? (
                    <EmptyState title="Belum ada pembayaran" message="Catat pembayaran dari sebuah tagihan untuk melihat riwayatnya di sini." action={<Link href="/payments/create"><Button><Plus size={16} /> Catat Pembayaran</Button></Link>} />
                ) : (
                    <>
                        {/* Mobile: kartu */}
                        <ul className="divide-y divide-slate-100 lg:hidden">
                            {payments.data.map((p) => (
                                <li key={p.id} className="px-4 py-3">
                                    <div className="flex items-start justify-between gap-3">
                                        <div className="min-w-0 flex-1">
                                            <p className="font-semibold text-slate-800">{p.invoice?.lease?.tenant?.name ?? '-'}</p>
                                            <p className="mt-0.5 text-xs text-slate-500">{p.invoice?.invoice_number ?? '-'} · {tanggal(p.paid_at)} · {p.method}</p>
                                        </div>
                                        <span className="shrink-0 font-bold text-emerald-600">{rupiah(p.amount)}</span>
                                    </div>
                                    <div className="mt-2.5 flex items-center gap-2">
                                        <Link href={`/payments/${p.id}/kwitansi`} className="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600 active:bg-slate-100"><Receipt size={14} /> Kwitansi</Link>
                                        <Link href={`/payments/${p.id}/edit`} className="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600 active:bg-slate-100"><Pencil size={14} /> Edit</Link>
                                        <button onClick={() => hapus(p)} className="inline-flex items-center gap-1 rounded-lg border border-rose-200 px-3 py-1.5 text-xs font-medium text-rose-600 active:bg-rose-50"><Trash2 size={14} /> Hapus</button>
                                    </div>
                                </li>
                            ))}
                        </ul>

                        {/* Desktop: tabel */}
                        <div className="hidden overflow-x-auto lg:block">
                            <table className="w-full text-sm">
                                <thead className="bg-slate-50 text-left text-xs uppercase text-slate-500">
                                    <tr><th className="px-4 py-3">Tanggal</th><th className="px-4 py-3">Invoice</th><th className="px-4 py-3">Penghuni</th><th className="px-4 py-3">Metode</th><th className="px-4 py-3">Nominal</th><th className="px-4 py-3 text-right">Aksi</th></tr>
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
                                            <td className="px-4 py-3">
                                                <div className="flex items-center justify-end gap-3">
                                                    <Link href={`/payments/${p.id}/kwitansi`} className="text-slate-500 hover:underline">Kwitansi</Link>
                                                    <Link href={`/payments/${p.id}/edit`} className="text-brand-600 hover:underline">Edit</Link>
                                                    <button onClick={() => hapus(p)} className="text-rose-600 hover:underline">Hapus</button>
                                                </div>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </>
                )}
                <Pagination paginator={payments} />
            </Card>
        </AuthenticatedLayout>
    );
}
