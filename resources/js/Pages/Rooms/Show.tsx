import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Badge, Card, PageHeader } from '@/Components/ui';
import { rupiah } from '@/lib/format';
import type { Room } from '@/types';

export default function Show({ room }: { room: Room }) {
    return (
        <AuthenticatedLayout>
            <Head title={`Kamar ${room.room_number}`} />
            <PageHeader title={`Kamar ${room.room_number}`} action={<Link href="/rooms" className="text-sm text-brand-600 hover:underline">Kembali</Link>} />
            <Card className="max-w-2xl p-6">
                <dl className="grid grid-cols-2 gap-4 text-sm">
                    <div><dt className="text-slate-500">Tipe</dt><dd className="font-medium capitalize">{room.type}</dd></div>
                    <div><dt className="text-slate-500">Harga</dt><dd className="font-medium">{rupiah(room.price)}</dd></div>
                    <div><dt className="text-slate-500">Status</dt><dd><Badge status={room.status} label={room.status} /></dd></div>
                    <div className="col-span-2"><dt className="text-slate-500">Fasilitas</dt><dd className="font-medium">{(room.facilities ?? []).join(', ') || '-'}</dd></div>
                    <div className="col-span-2"><dt className="text-slate-500">Deskripsi</dt><dd>{room.description ?? '-'}</dd></div>
                </dl>
            </Card>
        </AuthenticatedLayout>
    );
}
