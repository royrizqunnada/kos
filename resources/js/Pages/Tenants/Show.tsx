import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Badge, Button, Card, PageHeader, SecondaryButton } from '@/Components/ui';
import { rupiah, tanggal } from '@/lib/format';
import type { Tenant } from '@/types';

interface TenantStats { total_leases: number; is_active: boolean; total_paid: number }

export default function Show({ tenant, stats }: { tenant: Tenant; stats: TenantStats }) {
    return (
        <AuthenticatedLayout>
            <Head title={tenant.name} />
            <PageHeader
                title={tenant.name}
                subtitle={`NIK: ${tenant.nik}`}
                action={
                    <div className="flex gap-2">
                        <Link href="/tenants"><SecondaryButton>Kembali</SecondaryButton></Link>
                        <Link href={`/tenants/${tenant.id}/edit`}><Button>Edit</Button></Link>
                    </div>
                }
            />
            <div className="mb-6 grid grid-cols-3 gap-4">
                <Card className="p-4 text-center">
                    <div className="flex justify-center">{stats.is_active ? <Badge status="active" label="Aktif" /> : <Badge status="ended" label="Mantan" />}</div>
                    <p className="mt-1 text-xs text-slate-500">Status</p>
                </Card>
                <Card className="p-4 text-center">
                    <p className="text-xl font-bold text-slate-900">{stats.total_leases}</p>
                    <p className="text-xs text-slate-500">Kontrak</p>
                </Card>
                <Card className="p-4 text-center">
                    <p className="truncate text-base font-bold text-emerald-600">{rupiah(stats.total_paid)}</p>
                    <p className="text-xs text-slate-500">Total dibayar</p>
                </Card>
            </div>
            <div className="grid gap-6 lg:grid-cols-2">
                <Card className="p-6">
                    <h2 className="mb-3 text-sm font-semibold text-slate-700">Data Diri</h2>
                    <dl className="space-y-2 text-sm">
                        <div className="flex justify-between"><dt className="text-slate-500">No. HP</dt><dd>{tenant.phone}</dd></div>
                        <div className="flex justify-between"><dt className="text-slate-500">Alamat</dt><dd className="text-right">{tenant.address ?? '-'}</dd></div>
                        <div className="flex justify-between"><dt className="text-slate-500">Kontak Darurat</dt><dd>{tenant.emergency_contact_name ?? '-'} ({tenant.emergency_contact_phone ?? '-'})</dd></div>
                    </dl>

                    <h2 className="mb-3 mt-6 text-sm font-semibold text-slate-700">Foto KTP</h2>
                    {tenant.ktp_photo ? (
                        <a href={`/storage/${tenant.ktp_photo}`} target="_blank" rel="noreferrer" className="block">
                            <img src={`/storage/${tenant.ktp_photo}`} alt="Foto KTP" className="w-full rounded-xl border border-slate-200 object-contain" />
                        </a>
                    ) : (
                        <p className="text-sm text-slate-400">Belum ada foto KTP.</p>
                    )}
                </Card>

                <Card className="p-6">
                    <h2 className="mb-3 text-sm font-semibold text-slate-700">Riwayat Kontrak</h2>
                    <div className="space-y-2">
                        {(tenant.leases ?? []).map((l) => (
                            <Link key={l.id} href={`/leases/${l.id}`} className="flex items-center justify-between rounded-lg border border-slate-100 px-3 py-2 text-sm hover:bg-slate-50">
                                <span>Kamar {l.room?.room_number} · {tanggal(l.start_date)} – {tanggal(l.end_date)}</span>
                                <Badge status={l.status} label={l.status} />
                            </Link>
                        ))}
                        {(tenant.leases ?? []).length === 0 && <p className="text-sm text-slate-400">Belum ada kontrak.</p>}
                    </div>
                </Card>
            </div>
        </AuthenticatedLayout>
    );
}
