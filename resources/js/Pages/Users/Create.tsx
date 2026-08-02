import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Card, PageHeader } from '@/Components/ui';
import UserForm from './Form';
import type { Option } from '@/types';

export default function Create({ roles }: { roles: Option[] }) {
    return (
        <AuthenticatedLayout>
            <Head title="Tambah User" />
            <PageHeader title="Tambah User" subtitle="Buat akun baru untuk masuk ke aplikasi" action={<Link href="/users" className="text-sm text-brand-600 hover:underline">Kembali</Link>} />
            <Card className="max-w-3xl p-6"><UserForm roles={roles} /></Card>
        </AuthenticatedLayout>
    );
}
