import { Head, Link, router, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Badge, Button, Card, EmptyState, Input, PageHeader, Pagination, Select } from '@/Components/ui';
import { rupiah } from '@/lib/format';
import type { Option, Paginated, Room } from '@/types';
import { Plus } from 'lucide-react';

interface Props {
    rooms: Paginated<Room>;
    filters: { search?: string; status?: string; type?: string };
    statuses: Option[];
    types: Option[];
}

export default function Index({ rooms, filters, statuses, types }: Props) {
    const { data, setData } = useForm({ search: filters.search ?? '', status: filters.status ?? '', type: filters.type ?? '' });

    const apply = (patch: Partial<typeof data>) => {
        const next = { ...data, ...patch };
        setData(next as typeof data);
        router.get('/rooms', next, { preserveState: true, replace: true });
    };

    return (
        <AuthenticatedLayout>
            <Head title="Kamar" />
            <PageHeader
                title="Manajemen Kamar"
                subtitle="Kelola kamar, harga, status, dan fasilitas"
                action={<Link href="/rooms/create"><Button><Plus size={16} /> Tambah Kamar</Button></Link>}
            />

            <Card>
                <div className="flex flex-wrap gap-3 border-b border-slate-100 p-4">
                    <Input
                        placeholder="Cari nomor kamar…"
                        value={data.search}
                        onChange={(e) => setData('search', e.target.value)}
                        onKeyDown={(e) => e.key === 'Enter' && apply({ search: data.search })}
                        className="max-w-xs"
                    />
                    <Select value={data.status} onChange={(e) => apply({ status: e.target.value })} className="max-w-40">
                        <option value="">Semua Status</option>
                        {statuses.map((s) => <option key={s.value} value={s.value}>{s.label}</option>)}
                    </Select>
                    <Select value={data.type} onChange={(e) => apply({ type: e.target.value })} className="max-w-40">
                        <option value="">Semua Tipe</option>
                        {types.map((t) => <option key={t.value} value={t.value}>{t.label}</option>)}
                    </Select>
                </div>

                {rooms.data.length === 0 ? (
                    <EmptyState message="Belum ada kamar." />
                ) : (
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead className="bg-slate-50 text-left text-xs uppercase text-slate-500">
                                <tr>
                                    <th className="px-4 py-3">Nomor</th>
                                    <th className="px-4 py-3">Tipe</th>
                                    <th className="px-4 py-3">Harga</th>
                                    <th className="px-4 py-3">Status</th>
                                    <th className="px-4 py-3">Fasilitas</th>
                                    <th className="px-4 py-3"></th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100">
                                {rooms.data.map((room) => {
                                    const st = statuses.find((s) => s.value === room.status);
                                    return (
                                        <tr key={room.id} className="hover:bg-slate-50">
                                            <td className="px-4 py-3 font-semibold text-slate-800">{room.room_number}</td>
                                            <td className="px-4 py-3 capitalize">{types.find((t) => t.value === room.type)?.label}</td>
                                            <td className="px-4 py-3">{rupiah(room.price)}</td>
                                            <td className="px-4 py-3"><Badge status={room.status} label={st?.label ?? room.status} /></td>
                                            <td className="px-4 py-3 text-slate-500">{(room.facilities ?? []).slice(0, 3).join(', ')}</td>
                                            <td className="px-4 py-3 text-right">
                                                <Link href={`/rooms/${room.id}/edit`} className="text-brand-600 hover:underline">Edit</Link>
                                            </td>
                                        </tr>
                                    );
                                })}
                            </tbody>
                        </table>
                    </div>
                )}
                <Pagination paginator={rooms} />
            </Card>
        </AuthenticatedLayout>
    );
}
