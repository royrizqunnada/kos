import { Head, router, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Button, Card, EmptyState, Field, Input, PageHeader, Pagination, Select } from '@/Components/ui';
import { rupiah, tanggal } from '@/lib/format';
import type { Expense, Option, Paginated } from '@/types';
import { FormEvent } from 'react';
import { Trash2 } from 'lucide-react';

interface Props { expenses: Paginated<Expense>; filters: { category?: string }; categories: Option[]; }

export default function Index({ expenses, filters, categories }: Props) {
    const { data, setData, post, processing, errors, reset } = useForm({
        category: categories[0]?.value ?? '', amount: '', description: '', spent_at: new Date().toISOString().slice(0, 10),
    });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        post('/expenses', { onSuccess: () => reset('amount', 'description') });
    };

    return (
        <AuthenticatedLayout>
            <Head title="Pengeluaran" />
            <PageHeader title="Pengeluaran Kos" subtitle="Catat biaya listrik, air, internet, perbaikan, dll." />
            <div className="grid gap-6 lg:grid-cols-3">
                <Card className="p-6 lg:col-span-1">
                    <h2 className="mb-4 text-sm font-semibold text-slate-700">Tambah Pengeluaran</h2>
                    <form onSubmit={submit} className="space-y-4">
                        <Field label="Kategori" error={errors.category}>
                            <Select value={data.category} onChange={(e) => setData('category', e.target.value)}>
                                {categories.map((c) => <option key={c.value} value={c.value}>{c.label}</option>)}
                            </Select>
                        </Field>
                        <Field label="Nominal" error={errors.amount}><Input type="number" value={data.amount} onChange={(e) => setData('amount', e.target.value)} /></Field>
                        <Field label="Deskripsi" error={errors.description}><Input value={data.description} onChange={(e) => setData('description', e.target.value)} /></Field>
                        <Field label="Tanggal" error={errors.spent_at}><Input type="date" value={data.spent_at} onChange={(e) => setData('spent_at', e.target.value)} /></Field>
                        <Button type="submit" disabled={processing} className="w-full">Simpan</Button>
                    </form>
                </Card>

                <Card className="lg:col-span-2">
                    <div className="border-b border-slate-100 p-4">
                        <Select value={filters.category ?? ''} onChange={(e) => router.get('/expenses', { category: e.target.value }, { preserveState: true, replace: true })} className="max-w-44">
                            <option value="">Semua Kategori</option>
                            {categories.map((c) => <option key={c.value} value={c.value}>{c.label}</option>)}
                        </Select>
                    </div>
                    {expenses.data.length === 0 ? <EmptyState message="Belum ada pengeluaran." /> : (
                        <div className="overflow-x-auto">
                            <table className="w-full text-sm">
                                <thead className="bg-slate-50 text-left text-xs uppercase text-slate-500">
                                    <tr><th className="px-4 py-3">Tanggal</th><th className="px-4 py-3">Kategori</th><th className="px-4 py-3">Deskripsi</th><th className="px-4 py-3">Nominal</th><th className="px-4 py-3"></th></tr>
                                </thead>
                                <tbody className="divide-y divide-slate-100">
                                    {expenses.data.map((ex) => (
                                        <tr key={ex.id} className="hover:bg-slate-50">
                                            <td className="px-4 py-3 text-slate-500">{tanggal(ex.spent_at)}</td>
                                            <td className="px-4 py-3 capitalize">{categories.find((c) => c.value === ex.category)?.label}</td>
                                            <td className="px-4 py-3">{ex.description}</td>
                                            <td className="px-4 py-3 font-semibold text-rose-600">{rupiah(ex.amount)}</td>
                                            <td className="px-4 py-3 text-right">
                                                <button onClick={() => confirm('Hapus pengeluaran ini?') && router.delete(`/expenses/${ex.id}`)} className="text-slate-400 hover:text-rose-600"><Trash2 size={16} /></button>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    )}
                    <Pagination paginator={expenses} />
                </Card>
            </div>
        </AuthenticatedLayout>
    );
}
