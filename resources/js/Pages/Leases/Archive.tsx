import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Button, Card, EmptyState, PageHeader, SecondaryButton } from '@/Components/ui';
import { tanggal } from '@/lib/format';
import { ArrowLeft } from 'lucide-react';

type ArchivedLease = {
    id: number;
    tenant_name: string | null;
    room_number: string | null;
    start_date: string;
    end_date: string;
    deleted_at: string | null;
};

export default function Archive({ leases }: { leases: ArchivedLease[] }) {
    const pulihkan = (l: ArchivedLease) => {
        if (confirm(`Pulihkan kontrak ${l.tenant_name} (Kamar ${l.room_number})? Tagihan & pembayarannya ikut dipulihkan.`)) {
            router.post(`/leases/${l.id}/restore`);
        }
    };
    const hapusPermanen = (l: ArchivedLease) => {
        if (confirm(`Hapus PERMANEN kontrak ${l.tenant_name} (Kamar ${l.room_number})? Tindakan ini tidak bisa dibatalkan.`)) {
            router.delete(`/leases/${l.id}/force`);
        }
    };

    return (
        <AuthenticatedLayout>
            <Head title="Arsip Kontrak" />
            <PageHeader
                title="Arsip Kontrak"
                subtitle="Kontrak yang dihapus — bisa dipulihkan atau dihapus permanen"
                action={<Link href="/leases"><SecondaryButton><ArrowLeft size={16} /> Kembali</SecondaryButton></Link>}
            />
            <Card>
                {leases.length === 0 ? (
                    <EmptyState title="Arsip kosong" message="Belum ada kontrak yang diarsipkan." />
                ) : (
                    <>
                        {/* Mobile: kartu */}
                        <ul className="divide-y divide-slate-100 lg:hidden">
                            {leases.map((l) => (
                                <li key={l.id} className="px-4 py-3">
                                    <div className="flex items-center gap-3">
                                        <div className="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-slate-100 text-sm font-bold text-slate-500">{l.room_number}</div>
                                        <div className="min-w-0 flex-1">
                                            <p className="truncate font-semibold text-slate-800">{l.tenant_name}</p>
                                            <p className="truncate text-sm text-slate-500">Kamar {l.room_number} · diarsipkan {l.deleted_at ? tanggal(l.deleted_at) : '-'}</p>
                                        </div>
                                    </div>
                                    <div className="mt-3 flex items-center gap-2">
                                        <Button onClick={() => pulihkan(l)} className="flex-1">Pulihkan</Button>
                                        <SecondaryButton onClick={() => hapusPermanen(l)} className="flex-1 !text-rose-600">Hapus Permanen</SecondaryButton>
                                    </div>
                                </li>
                            ))}
                        </ul>

                        {/* Desktop: tabel */}
                        <div className="hidden overflow-x-auto lg:block">
                            <table className="w-full text-sm">
                                <thead className="bg-slate-50 text-left text-xs uppercase text-slate-500">
                                    <tr><th className="px-4 py-3">Penghuni</th><th className="px-4 py-3">Kamar</th><th className="px-4 py-3">Periode</th><th className="px-4 py-3">Diarsipkan</th><th className="px-4 py-3"></th></tr>
                                </thead>
                                <tbody className="divide-y divide-slate-100">
                                    {leases.map((l) => (
                                        <tr key={l.id} className="hover:bg-slate-50">
                                            <td className="px-4 py-3 font-semibold text-slate-800">{l.tenant_name}</td>
                                            <td className="px-4 py-3">{l.room_number}</td>
                                            <td className="px-4 py-3 text-slate-500">{tanggal(l.start_date)} – {tanggal(l.end_date)}</td>
                                            <td className="px-4 py-3 text-slate-500">{l.deleted_at ? tanggal(l.deleted_at) : '-'}</td>
                                            <td className="px-4 py-3 text-right">
                                                <div className="flex items-center justify-end gap-3">
                                                    <button onClick={() => pulihkan(l)} className="font-semibold text-brand-600 hover:underline">Pulihkan</button>
                                                    <button onClick={() => hapusPermanen(l)} className="font-semibold text-rose-600 hover:underline">Hapus Permanen</button>
                                                </div>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </>
                )}
            </Card>
        </AuthenticatedLayout>
    );
}
