import { useForm } from '@inertiajs/react';
import { Button, Field, Input, Textarea } from '@/Components/ui';
import type { Tenant } from '@/types';
import { FormEvent } from 'react';

export default function TenantForm({ tenant }: { tenant?: Tenant }) {
    const { data, setData, post, processing, errors } = useForm({
        _method: tenant ? 'put' : 'post',
        name: tenant?.name ?? '',
        nik: tenant?.nik ?? '',
        phone: tenant?.phone ?? '',
        address: tenant?.address ?? '',
        emergency_contact_name: tenant?.emergency_contact_name ?? '',
        emergency_contact_phone: tenant?.emergency_contact_phone ?? '',
        ktp_photo: null as File | null,
    });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        post(tenant ? `/tenants/${tenant.id}` : '/tenants', { forceFormData: true });
    };

    return (
        <form onSubmit={submit} className="space-y-5">
            <div className="grid gap-5 sm:grid-cols-2">
                <Field label="Nama Lengkap" error={errors.name}><Input value={data.name} onChange={(e) => setData('name', e.target.value)} /></Field>
                <Field label="NIK" error={errors.nik}><Input value={data.nik} onChange={(e) => setData('nik', e.target.value)} /></Field>
                <Field label="Nomor HP" error={errors.phone}><Input value={data.phone} onChange={(e) => setData('phone', e.target.value)} /></Field>
            </div>
            <Field label="Alamat" error={errors.address}><Textarea rows={2} value={data.address} onChange={(e) => setData('address', e.target.value)} /></Field>
            <div className="grid gap-5 sm:grid-cols-2">
                <Field label="Nama Kontak Darurat" error={errors.emergency_contact_name}><Input value={data.emergency_contact_name} onChange={(e) => setData('emergency_contact_name', e.target.value)} /></Field>
                <Field label="HP Kontak Darurat" error={errors.emergency_contact_phone}><Input value={data.emergency_contact_phone} onChange={(e) => setData('emergency_contact_phone', e.target.value)} /></Field>
            </div>
            <Field label="Foto KTP" error={errors.ktp_photo}>
                {tenant?.ktp_photo && !data.ktp_photo && (
                    <a href={`/storage/${tenant.ktp_photo}`} target="_blank" rel="noreferrer" className="mb-2 block w-fit">
                        <img src={`/storage/${tenant.ktp_photo}`} alt="Foto KTP" className="h-40 w-auto rounded-lg border border-slate-200 object-contain" />
                    </a>
                )}
                {data.ktp_photo && (
                    <img src={URL.createObjectURL(data.ktp_photo)} alt="Pratinjau KTP" className="mb-2 h-40 w-auto rounded-lg border border-slate-200 object-contain" />
                )}
                <Input type="file" accept="image/*" onChange={(e) => setData('ktp_photo', e.target.files?.[0] ?? null)} />
                <p className="mt-1 text-xs text-slate-400">{tenant?.ktp_photo ? 'Pilih file untuk mengganti foto.' : 'JPG/PNG, maksimal 4 MB.'}</p>
            </Field>
            <Button type="submit" disabled={processing}>{tenant ? 'Simpan Perubahan' : 'Simpan Penghuni'}</Button>
        </form>
    );
}
