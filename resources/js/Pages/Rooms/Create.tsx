import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Card, PageHeader } from '@/Components/ui';
import RoomForm from './Form';
import type { Option } from '@/types';

export default function Create({ statuses, types }: { statuses: Option[]; types: Option[] }) {
    return (
        <AuthenticatedLayout>
            <Head title="Tambah Kamar" />
            <PageHeader title="Tambah Kamar" action={<Link href="/rooms" className="text-sm text-brand-600 hover:underline">Kembali</Link>} />
            <Card className="max-w-3xl p-6"><RoomForm statuses={statuses} types={types} /></Card>
        </AuthenticatedLayout>
    );
}
