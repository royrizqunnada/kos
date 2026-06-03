import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Button, Card, EmptyState, Input, PageHeader, Pagination } from '@/Components/ui';
import type { Paginated, Tenant } from '@/types';
import { Plus } from 'lucide-react';

export default function Index({ tenants, filters }: { tenants: Paginated<Tenant>; filters: { search?: string } }) {
    const [search, setSearch] = useState(filters.search ?? '');

    return (
        <AuthenticatedLayout>
            <Head title="Penghuni" />
            <PageHeader
                title="Manajemen Penghuni"
                subtitle="Data penghuni kos"
                action={<Link href="/tenants/create"><Button><Plus size={16} /> Tambah Penghuni</Button></Link>}
            />
            <Card>
                <div className="border-b border-slate-100 p-4">
                    <Input
                        placeholder="Cari nama / NIK / HP…"
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        onKeyDown={(e) => e.key === 'Enter' && router.get('/tenants', { search }, { preserveState: true, replace: true })}
                        className="max-w-xs"
                    />
                </div>
                {tenants.data.length === 0 ? <EmptyState message="Belum ada penghuni." /> : (
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead className="bg-slate-50 text-left text-xs uppercase text-slate-500">
                                <tr><th className="px-4 py-3">Nama</th><th className="px-4 py-3">NIK</th><th className="px-4 py-3">No. HP</th><th className="px-4 py-3">Kontak Darurat</th><th className="px-4 py-3"></th></tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100">
                                {tenants.data.map((t) => (
                                    <tr key={t.id} className="hover:bg-slate-50">
                                        <td className="px-4 py-3 font-semibold text-slate-800">{t.name}</td>
                                        <td className="px-4 py-3">{t.nik}</td>
                                        <td className="px-4 py-3">{t.phone}</td>
                                        <td className="px-4 py-3 text-slate-500">{t.emergency_contact_name ?? '-'}</td>
                                        <td className="px-4 py-3 text-right">
                                            <Link href={`/tenants/${t.id}/edit`} className="text-brand-600 hover:underline">Edit</Link>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}
                <Pagination paginator={tenants} />
            </Card>
        </AuthenticatedLayout>
    );
}
