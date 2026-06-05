import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Badge, Button, Card, EmptyState, PageHeader, Pagination, Segmented } from '@/Components/ui';
import { rupiah, tanggal } from '@/lib/format';
import type { Paginated, Payment } from '@/types';
import { Plus } from 'lucide-react';

export default function Index({ payments }: { payments: Paginated<Payment> }) {
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
                        <ul className="divide-y divide-slate-100 lg:hidden dark:divide-slate-800">
                            {payments.data.map((p) => (
                                <li key={p.id} className="flex items-center gap-3 px-4 py-3">
                                    <div className="min-w-0 flex-1">
                                        <p className="truncate font-semibold text-slate-800 dark:text-slate-100">{p.invoice?.lease?.tenant?.name ?? '-'} · {p.method}</p>
                                        <p className="truncate text-sm text-slate-500 dark:text-slate-400">{p.invoice?.invoice_number ?? '-'} · {tanggal(p.paid_at)}</p>
                                    </div>
                                    <span className="font-bold text-emerald-600 dark:text-emerald-400">{rupiah(p.amount)}</span>
                                </li>
                            ))}
                        </ul>

                        {/* Desktop: tabel */}
                        <div className="hidden overflow-x-auto lg:block">
                            <table className="w-full text-sm">
                                <thead className="bg-slate-50 text-left text-xs uppercase text-slate-500 dark:bg-slate-800/50 dark:text-slate-400">
                                    <tr><th className="px-4 py-3">Tanggal</th><th className="px-4 py-3">Invoice</th><th className="px-4 py-3">Penghuni</th><th className="px-4 py-3">Metode</th><th className="px-4 py-3">Nominal</th></tr>
                                </thead>
                                <tbody className="divide-y divide-slate-100 dark:divide-slate-800">
                                    {payments.data.map((p) => (
                                        <tr key={p.id} className="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                            <td className="px-4 py-3 text-slate-500 dark:text-slate-400">{tanggal(p.paid_at)}</td>
                                            <td className="px-4 py-3 font-mono text-xs">
                                                {p.invoice ? <Link href={`/invoices/${p.invoice.id}`} className="text-brand-600 hover:underline dark:text-brand-400">{p.invoice.invoice_number}</Link> : '-'}
                                            </td>
                                            <td className="px-4 py-3 dark:text-slate-300">{p.invoice?.lease?.tenant?.name ?? '-'}</td>
                                            <td className="px-4 py-3"><Badge status="paid" label={p.method} /></td>
                                            <td className="px-4 py-3 font-semibold text-emerald-600 dark:text-emerald-400">{rupiah(p.amount)}</td>
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
