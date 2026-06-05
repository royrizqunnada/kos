import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Badge, Button, Card, EmptyState, PageHeader, Pagination, Select } from '@/Components/ui';
import { rupiah, tanggal } from '@/lib/format';
import type { Lease, Option, Paginated } from '@/types';
import { Plus } from 'lucide-react';

export default function Index({ leases, filters, statuses }: { leases: Paginated<Lease>; filters: { status?: string }; statuses: Option[] }) {
    const statusLabel = (l: Lease) => statuses.find((s) => s.value === l.status)?.label ?? l.status;

    return (
        <AuthenticatedLayout>
            <Head title="Kontrak" />
            <PageHeader
                title="Kontrak Sewa"
                subtitle="Daftar kontrak penghuni & kamar"
                action={<Link href="/leases/create"><Button><Plus size={16} /> Buat Kontrak</Button></Link>}
            />
            <Card>
                <div className="border-b border-slate-100 p-4">
                    <Select
                        value={filters.status ?? ''}
                        onChange={(e) => router.get('/leases', { status: e.target.value }, { preserveState: true, replace: true })}
                        className="max-w-40"
                    >
                        <option value="">Semua Status</option>
                        {statuses.map((s) => <option key={s.value} value={s.value}>{s.label}</option>)}
                    </Select>
                </div>
                {leases.data.length === 0 ? <EmptyState message="Belum ada kontrak." /> : (
                    <>
                        {/* Mobile: kartu */}
                        <ul className="divide-y divide-slate-100 lg:hidden">
                            {leases.data.map((l) => (
                                <li key={l.id}>
                                    <Link href={`/leases/${l.id}`} className="flex items-center gap-3 px-4 py-3 active:bg-slate-50">
                                        <div className="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-brand-50 text-sm font-bold text-brand-700">{l.room?.room_number}</div>
                                        <div className="min-w-0 flex-1">
                                            <p className="truncate font-semibold text-slate-800">{l.tenant?.name}</p>
                                            <p className="truncate text-sm text-slate-500">Kamar {l.room?.room_number} · {rupiah(l.monthly_price)}/bln</p>
                                        </div>
                                        <Badge status={l.status} label={statusLabel(l)} />
                                    </Link>
                                </li>
                            ))}
                        </ul>

                        {/* Desktop: tabel */}
                        <div className="hidden overflow-x-auto lg:block">
                            <table className="w-full text-sm">
                                <thead className="bg-slate-50 text-left text-xs uppercase text-slate-500">
                                    <tr><th className="px-4 py-3">Penghuni</th><th className="px-4 py-3">Kamar</th><th className="px-4 py-3">Periode</th><th className="px-4 py-3">Harga/bln</th><th className="px-4 py-3">Status</th><th className="px-4 py-3"></th></tr>
                                </thead>
                                <tbody className="divide-y divide-slate-100">
                                    {leases.data.map((l) => (
                                        <tr key={l.id} className="hover:bg-slate-50">
                                            <td className="px-4 py-3 font-semibold text-slate-800">{l.tenant?.name}</td>
                                            <td className="px-4 py-3">{l.room?.room_number}</td>
                                            <td className="px-4 py-3 text-slate-500">{tanggal(l.start_date)} – {tanggal(l.end_date)}</td>
                                            <td className="px-4 py-3">{rupiah(l.monthly_price)}</td>
                                            <td className="px-4 py-3"><Badge status={l.status} label={statusLabel(l)} /></td>
                                            <td className="px-4 py-3 text-right"><Link href={`/leases/${l.id}`} className="text-brand-600 hover:underline">Detail</Link></td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </>
                )}
                <Pagination paginator={leases} />
            </Card>
        </AuthenticatedLayout>
    );
}
