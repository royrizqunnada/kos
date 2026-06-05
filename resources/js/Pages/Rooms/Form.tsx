import { useForm } from '@inertiajs/react';
import { Button, Field, Input, Select, Textarea } from '@/Components/ui';
import type { Option, Room } from '@/types';
import { FormEvent, useState } from 'react';

const FACILITY_OPTIONS = ['AC', 'Wi-Fi', 'Kamar Mandi Dalam', 'Lemari', 'Kasur', 'Meja', 'Air Panas', 'TV'];

export default function RoomForm({ room, statuses, types }: { room?: Room; statuses: Option[]; types: Option[] }) {
    const { data, setData, post, processing, errors } = useForm({
        _method: room ? 'put' : 'post',
        room_number: room?.room_number ?? '',
        type: room?.type ?? types[0]?.value ?? '',
        price: room?.price ?? '',
        status: room?.status ?? statuses[0]?.value ?? '',
        facilities: room?.facilities ?? [],
        description: room?.description ?? '',
        photos: [] as File[],
    });
    const [selected, setSelected] = useState<string[]>(room?.facilities ?? []);

    const toggle = (f: string) => {
        const next = selected.includes(f) ? selected.filter((x) => x !== f) : [...selected, f];
        setSelected(next);
        setData('facilities', next);
    };

    const submit = (e: FormEvent) => {
        e.preventDefault();
        post(room ? `/rooms/${room.id}` : '/rooms', { forceFormData: true });
    };

    return (
        <form onSubmit={submit} className="space-y-5">
            <div className="grid gap-5 sm:grid-cols-2">
                <Field label="Nomor Kamar" error={errors.room_number}>
                    <Input value={data.room_number} onChange={(e) => setData('room_number', e.target.value)} />
                </Field>
                <Field label="Tipe Kamar" error={errors.type}>
                    <Select value={data.type} onChange={(e) => setData('type', e.target.value)}>
                        {types.map((t) => <option key={t.value} value={t.value}>{t.label}</option>)}
                    </Select>
                </Field>
                <Field label="Harga Sewa (per bulan)" error={errors.price}>
                    <Input type="number" value={data.price} onChange={(e) => setData('price', e.target.value)} />
                </Field>
                <Field label="Status" error={errors.status}>
                    <Select value={data.status} onChange={(e) => setData('status', e.target.value)}>
                        {statuses.map((s) => <option key={s.value} value={s.value}>{s.label}</option>)}
                    </Select>
                </Field>
            </div>

            <Field label="Fasilitas">
                <div className="flex flex-wrap gap-2">
                    {FACILITY_OPTIONS.map((f) => (
                        <button
                            type="button"
                            key={f}
                            onClick={() => toggle(f)}
                            className={`rounded-full border px-3 py-1 text-sm transition ${selected.includes(f) ? 'border-brand-500 bg-brand-50 text-brand-700 dark:bg-brand-600/15 dark:text-brand-300' : 'border-slate-300 text-slate-600 dark:border-slate-700 dark:text-slate-300'}`}
                        >
                            {f}
                        </button>
                    ))}
                </div>
            </Field>

            <Field label="Deskripsi" error={errors.description}>
                <Textarea rows={3} value={data.description} onChange={(e) => setData('description', e.target.value)} />
            </Field>

            <Field label="Foto Kamar" error={errors['photos.0' as keyof typeof errors]}>
                <Input type="file" multiple accept="image/*" onChange={(e) => setData('photos', Array.from(e.target.files ?? []))} />
            </Field>

            <Button type="submit" disabled={processing}>{room ? 'Simpan Perubahan' : 'Simpan Kamar'}</Button>
        </form>
    );
}
