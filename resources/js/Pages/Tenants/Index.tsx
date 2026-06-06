import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Badge, Button, Card, EmptyState, Input, PageHeader, Pagination, Segmented, Select } from '@/Components/ui';
import type { Paginated, Tenant } from '@/types';
import { Plus } from 'lucide-react';

type TenantRow = Tenant & { active_count?: number; leases_count?: number };

function StatusBadge({ t }: { t: TenantRow }) {
    if ((t.active_count ?? 0) > 0) return <Badge status="active" label="Aktif" />;
    if ((t.leases_count ?? 0) > 0) return <Badge status="ended" label="Mantan" />;
    return <Badge status="pending" label="Calon" />;
}

export default function Index({ tenants, filters }: { tenants: Paginated<TenantRow>; filters: { search?: string; status?: string } }) {
    const [search, setSearch] = useState(filters.search ?? '');
    const apply = (patch: Record<string, string>) => router.get('/tenants', { search, status: filters.status ?? '', ...patch }, { preserveState: true, replace: true });
    const initial = (name: string) => (name ?? 'P').charAt(0).toUpperCase();

    return (
        <AuthenticatedLayout>
            <Head title="Penghuni" />
            <PageHeader
                title="Kamar & Penghuni"
                subtitle="Data penghuni kos"
                action={<Link href="/tenants/create"><Button><Plus size={16} /> Tambah Penghuni</Button></Link>}
            />
            <Segmented items={[{ label: 'Kamar', href: '/rooms' }, { label: 'Penghuni', href: '/tenants' }]} />
            <Card>
                <div className="flex flex-wrap gap-3 border-b border-slate-100 p-4">
                    <Input
                        placeholder="Cari nama / NIK / HP…"
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        onKeyDown={(e) => e.key === 'Enter' && apply({ search })}
                        className="max-w-xs"
                    />
                    <Select value={filters.status ?? ''} onChange={(e) => apply({ status: e.target.value })} className="max-w-40">
                        <option value="">Semua</option>
                        <option value="aktif">Aktif</option>
                        <option value="mantan">Mantan</option>
                    </Select>
                </div>
                {tenants.data.length === 0 ? (
                    <EmptyState title="Belum ada penghuni" message="Tambahkan data penghuni untuk mulai membuat kontrak & tagihan." action={<Link href="/tenants/create"><Button><Plus size={16} /> Tambah Penghuni</Button></Link>} />
                ) : (
                    <>
                        {/* Mobile: kartu */}
                        <ul className="divide-y divide-slate-100 lg:hidden">
                            {tenants.data.map((t) => (
                                <li key={t.id}>
                                    <Link href={`/tenants/${t.id}`} className="flex items-center gap-3 px-4 py-3 active:bg-slate-50">
                                        <div className="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-brand-50 text-sm font-bold text-brand-700">{initial(t.name)}</div>
                                        <div className="min-w-0 flex-1">
                                            <p className="truncate font-semibold text-slate-800">{t.name}</p>
                                            <p className="truncate text-sm text-slate-500">{t.phone} · NIK {t.nik}</p>
                                        </div>
                                        <StatusBadge t={t} />
                                    </Link>
                                </li>
                            ))}
                        </ul>

                        {/* Desktop: tabel */}
                        <div className="hidden overflow-x-auto lg:block">
                            <table className="w-full text-sm">
                                <thead className="bg-slate-50 text-left text-xs uppercase text-slate-500">
                                    <tr><th className="px-4 py-3">Nama</th><th className="px-4 py-3">NIK</th><th className="px-4 py-3">No. HP</th><th className="px-4 py-3">Status</th><th className="px-4 py-3"></th></tr>
                                </thead>
                                <tbody className="divide-y divide-slate-100">
                                    {tenants.data.map((t) => (
                                        <tr key={t.id} className="hover:bg-slate-50">
                                            <td className="px-4 py-3 font-semibold text-slate-800">{t.name}</td>
                                            <td className="px-4 py-3">{t.nik}</td>
                                            <td className="px-4 py-3">{t.phone}</td>
                                            <td className="px-4 py-3"><StatusBadge t={t} /></td>
                                            <td className="px-4 py-3 text-right">
                                                <Link href={`/tenants/${t.id}`} className="text-brand-600 hover:underline">Detail</Link>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </>
                )}
                <Pagination paginator={tenants} />
            </Card>
        </AuthenticatedLayout>
    );
}
