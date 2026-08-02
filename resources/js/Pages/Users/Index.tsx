import { Head, Link, router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Button, Card, EmptyState, Input, PageHeader, Pagination, Select } from '@/Components/ui';
import type { ManagedUser, Option, Paginated, PageProps } from '@/types';
import { Plus, Pencil, Trash2, UserCog } from 'lucide-react';

const ROLE_COLORS: Record<string, string> = {
    super_admin: 'bg-violet-100 text-violet-700',
    owner: 'bg-blue-100 text-blue-700',
    admin: 'bg-emerald-100 text-emerald-700',
};

function RoleBadge({ role, roles }: { role: string | null; roles: Option[] }) {
    if (!role) return <span className="text-xs text-slate-400">Tanpa peran</span>;
    const label = roles.find((r) => r.value === role)?.label ?? role;
    return <span className={`inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold ${ROLE_COLORS[role] ?? 'bg-slate-100 text-slate-600'}`}>{label}</span>;
}

export default function Index({ users, filters, roles }: { users: Paginated<ManagedUser>; filters: { search?: string; role?: string }; roles: Option[] }) {
    const { auth } = usePage<PageProps>().props;
    const [search, setSearch] = useState(filters.search ?? '');

    const apply = (patch: Record<string, string>) =>
        router.get('/users', { search, role: filters.role ?? '', ...patch }, { preserveState: true, replace: true });

    const hapus = (user: ManagedUser) => {
        if (confirm(`Hapus user ${user.name}? Tindakan ini tidak bisa dibatalkan.`)) {
            router.delete(`/users/${user.id}`, { preserveScroll: true });
        }
    };

    const initial = (name: string) => (name ?? 'U').charAt(0).toUpperCase();

    return (
        <AuthenticatedLayout>
            <Head title="Manajemen User" />
            <PageHeader
                title="Manajemen User"
                subtitle="Akun yang bisa masuk ke aplikasi"
                action={<Link href="/users/create"><Button><Plus size={16} /> Tambah User</Button></Link>}
            />

            <Card>
                <div className="flex flex-wrap gap-3 border-b border-slate-100 p-4">
                    <Input
                        placeholder="Cari nama / email / HP…"
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        onKeyDown={(e) => e.key === 'Enter' && apply({ search })}
                        className="max-w-xs"
                    />
                    <Select value={filters.role ?? ''} onChange={(e) => apply({ role: e.target.value })} className="max-w-44">
                        <option value="">Semua Peran</option>
                        {roles.map((r) => <option key={r.value} value={r.value}>{r.label}</option>)}
                    </Select>
                </div>

                {users.data.length === 0 ? (
                    <EmptyState
                        icon={UserCog}
                        title="Belum ada user"
                        message="Tambahkan akun untuk pemilik atau admin operasional."
                        action={<Link href="/users/create"><Button><Plus size={16} /> Tambah User</Button></Link>}
                    />
                ) : (
                    <>
                        {/* Mobile: kartu */}
                        <ul className="divide-y divide-slate-100 lg:hidden">
                            {users.data.map((u) => (
                                <li key={u.id} className="flex items-center gap-3 px-4 py-3">
                                    <div className="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-brand-50 text-sm font-bold text-brand-700">{initial(u.name)}</div>
                                    <div className="min-w-0 flex-1">
                                        <p className="truncate font-semibold text-slate-800">
                                            {u.name}
                                            {u.id === auth.user?.id && <span className="ml-1.5 text-xs font-normal text-slate-400">(Anda)</span>}
                                        </p>
                                        <p className="truncate text-sm text-slate-500">{u.email}</p>
                                        <div className="mt-1"><RoleBadge role={u.role} roles={roles} /></div>
                                    </div>
                                    <Link href={`/users/${u.id}/edit`} className="grid h-9 w-9 place-items-center rounded-xl text-brand-600 hover:bg-brand-50" title="Edit">
                                        <Pencil size={16} />
                                    </Link>
                                </li>
                            ))}
                        </ul>

                        {/* Desktop: tabel */}
                        <div className="hidden overflow-x-auto lg:block">
                            <table className="w-full text-sm">
                                <thead className="bg-slate-50 text-left text-xs uppercase text-slate-500">
                                    <tr>
                                        <th className="px-4 py-3">Nama</th>
                                        <th className="px-4 py-3">Email</th>
                                        <th className="px-4 py-3">No. HP</th>
                                        <th className="px-4 py-3">Peran</th>
                                        <th className="px-4 py-3"></th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-slate-100">
                                    {users.data.map((u) => (
                                        <tr key={u.id} className="hover:bg-slate-50">
                                            <td className="px-4 py-3 font-semibold text-slate-800">
                                                {u.name}
                                                {u.id === auth.user?.id && <span className="ml-1.5 text-xs font-normal text-slate-400">(Anda)</span>}
                                            </td>
                                            <td className="px-4 py-3">{u.email}</td>
                                            <td className="px-4 py-3">{u.phone ?? '-'}</td>
                                            <td className="px-4 py-3"><RoleBadge role={u.role} roles={roles} /></td>
                                            <td className="px-4 py-3">
                                                <div className="flex items-center justify-end gap-1">
                                                    <Link href={`/users/${u.id}/edit`} className="rounded-lg px-2.5 py-1.5 text-brand-600 hover:bg-brand-50" title="Edit user">
                                                        <Pencil size={16} />
                                                    </Link>
                                                    {u.id !== auth.user?.id && (
                                                        <button onClick={() => hapus(u)} className="rounded-lg px-2.5 py-1.5 text-rose-600 hover:bg-rose-50" title="Hapus user">
                                                            <Trash2 size={16} />
                                                        </button>
                                                    )}
                                                </div>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </>
                )}
                <Pagination paginator={users} />
            </Card>
        </AuthenticatedLayout>
    );
}
