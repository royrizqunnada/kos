import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Card, PageHeader } from '@/Components/ui';
import TenantForm from './Form';

export default function Create() {
    return (
        <AuthenticatedLayout>
            <Head title="Tambah Penghuni" />
            <PageHeader title="Tambah Penghuni" action={<Link href="/tenants" className="text-sm text-brand-600 hover:underline">Kembali</Link>} />
            <Card className="max-w-3xl p-6"><TenantForm /></Card>
        </AuthenticatedLayout>
    );
}
