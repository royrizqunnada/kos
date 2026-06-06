import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Badge, Button, Card, PageHeader, SecondaryButton } from '@/Components/ui';
import { tanggal } from '@/lib/format';
import type { Tenant } from '@/types';

export default function Show({ tenant }: { tenant: Tenant }) {
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
