import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Card, PageHeader } from '@/Components/ui';
import RoomForm from './Form';
import type { Option, Room } from '@/types';

export default function Edit({ room, statuses, types }: { room: Room; statuses: Option[]; types: Option[] }) {
    return (
        <AuthenticatedLayout>
            <Head title={`Edit Kamar ${room.room_number}`} />
            <PageHeader title={`Edit Kamar ${room.room_number}`} action={<Link href="/rooms" className="text-sm text-brand-600 hover:underline">Kembali</Link>} />
            <Card className="max-w-3xl p-6"><RoomForm room={room} statuses={statuses} types={types} /></Card>
        </AuthenticatedLayout>
    );
}
