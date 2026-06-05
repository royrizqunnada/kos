import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Button, Card, EmptyState, Input, PageHeader, Pagination } from '@/Components/ui';
import type { Paginated, Tenant } from '@/types';
import { Plus } from 'lucide-react';

export default function Index({ tenants, filters }: { tenants: Paginated<Tenant>; filters: { search?: string } }) {
    const [search, setSearch] = useState(filters.search ?? '');
    const go = () => router.get('/tenants', { search }, { preserveState: true, replace: true });
    const initial = (name: string) => (name ?? 'P').charAt(0).toUpperCase();

    return (
        <AuthenticatedLayout>
            <Head title="Penghuni" />
            <PageHeader
                title="Manajemen Penghuni"
                subtitle="Data penghuni kos"
                action={<Link href="/tenants/create"><Button><Plus size={16} /> Tambah Penghuni</Button></Link>}
            />
            <Card>
                <div className="border-b border-slate-100 p-4 dark:border-slate-800">
                    <Input
                        placeholder="Cari nama / NIK / HP…"
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        onKeyDown={(e) => e.key === 'Enter' && go()}
                        className="max-w-xs"
                    />
                </div>
                {tenants.data.length === 0 ? (
                    <EmptyState title="Belum ada penghuni" message="Tambahkan data penghuni untuk mulai membuat kontrak & tagihan." action={<Link href="/tenants/create"><Button><Plus size={16} /> Tambah Penghuni</Button></Link>} />
                ) : (
                    <>
                        {/* Mobile: kartu */}
                        <ul className="divide-y divide-slate-100 lg:hidden dark:divide-slate-800">
                            {tenants.data.map((t) => (
                                <li key={t.id}>
                                    <Link href={`/tenants/${t.id}/edit`} className="flex items-center gap-3 px-4 py-3 active:bg-slate-50 dark:active:bg-slate-800/50">
                                        <div className="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-brand-50 text-sm font-bold text-brand-700 dark:bg-brand-600/15 dark:text-brand-300">{initial(t.name)}</div>
                                        <div className="min-w-0 flex-1">
                                            <p className="truncate font-semibold text-slate-800 dark:text-slate-100">{t.name}</p>
                                            <p className="truncate text-sm text-slate-500 dark:text-slate-400">{t.phone} · NIK {t.nik}</p>
                                        </div>
                                    </Link>
                                </li>
                            ))}
                        </ul>

                        {/* Desktop: tabel */}
                        <div className="hidden overflow-x-auto lg:block">
                            <table className="w-full text-sm">
                                <thead className="bg-slate-50 text-left text-xs uppercase text-slate-500 dark:bg-slate-800/50 dark:text-slate-400">
                                    <tr><th className="px-4 py-3">Nama</th><th className="px-4 py-3">NIK</th><th className="px-4 py-3">No. HP</th><th className="px-4 py-3">Kontak Darurat</th><th className="px-4 py-3"></th></tr>
                                </thead>
                                <tbody className="divide-y divide-slate-100 dark:divide-slate-800">
                                    {tenants.data.map((t) => (
                                        <tr key={t.id} className="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                            <td className="px-4 py-3 font-semibold text-slate-800 dark:text-slate-100">{t.name}</td>
                                            <td className="px-4 py-3 dark:text-slate-300">{t.nik}</td>
                                            <td className="px-4 py-3 dark:text-slate-300">{t.phone}</td>
                                            <td className="px-4 py-3 text-slate-500 dark:text-slate-400">{t.emergency_contact_name ?? '-'}</td>
                                            <td className="px-4 py-3 text-right">
                                                <Link href={`/tenants/${t.id}/edit`} className="text-brand-600 hover:underline dark:text-brand-400">Edit</Link>
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
