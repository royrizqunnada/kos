import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Card, PageHeader } from '@/Components/ui';
import TenantForm from './Form';
import type { Tenant } from '@/types';

export default function Edit({ tenant }: { tenant: Tenant }) {
    return (
        <AuthenticatedLayout>
            <Head title={`Edit ${tenant.name}`} />
            <PageHeader title={`Edit ${tenant.name}`} action={<Link href="/tenants" className="text-sm text-brand-600 hover:underline">Kembali</Link>} />
            <Card className="max-w-3xl p-6"><TenantForm tenant={tenant} /></Card>
        </AuthenticatedLayout>
    );
}
