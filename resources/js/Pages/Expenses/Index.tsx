import { Head, router, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Button, Card, EmptyState, Field, Input, PageHeader, Pagination, Select } from '@/Components/ui';
import { rupiah, tanggal } from '@/lib/format';
import type { Expense, Option, Paginated } from '@/types';
import { FormEvent } from 'react';
import { Trash2, Repeat } from 'lucide-react';

interface Recurring { id: number; category: string; category_label: string; description: string; amount: number; day_of_month: number; is_active: boolean }
interface Props { expenses: Paginated<Expense>; filters: { category?: string }; categories: Option[]; recurring: Recurring[]; }

export default function Index({ expenses, filters, categories, recurring }: Props) {
    const { data, setData, post, processing, errors, reset } = useForm({
        category: categories[0]?.value ?? '', amount: '', description: '', spent_at: new Date().toISOString().slice(0, 10),
    });
    const r = useForm({ category: categories.find((c) => c.value === 'internet')?.value ?? categories[0]?.value ?? '', description: '', amount: '', day_of_month: '1' });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        post('/expenses', { onSuccess: () => reset('amount', 'description') });
    };
    const submitRecurring = (e: FormEvent) => {
        e.preventDefault();
        r.post('/expenses/recurring', { onSuccess: () => r.reset('description', 'amount') });
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
                        <>
                        {/* Mobile: kartu */}
                        <ul className="divide-y divide-slate-100 lg:hidden">
                            {expenses.data.map((ex) => (
                                <li key={ex.id} className="flex items-start justify-between gap-3 px-4 py-3">
                                    <div className="min-w-0 flex-1">
                                        <p className="font-semibold text-slate-800">{ex.description || categories.find((c) => c.value === ex.category)?.label}</p>
                                        <p className="mt-0.5 text-xs text-slate-500 capitalize">{categories.find((c) => c.value === ex.category)?.label} · {tanggal(ex.spent_at)}</p>
                                    </div>
                                    <span className="shrink-0 font-bold text-rose-600">{rupiah(ex.amount)}</span>
                                    <button onClick={() => confirm('Hapus pengeluaran ini?') && router.delete(`/expenses/${ex.id}`)} className="shrink-0 text-slate-400 active:text-rose-600"><Trash2 size={16} /></button>
                                </li>
                            ))}
                        </ul>

                        {/* Desktop: tabel */}
                        <div className="hidden overflow-x-auto lg:block">
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
                        </>
                    )}
                    <Pagination paginator={expenses} />
                </Card>
            </div>

            {/* Pengeluaran Berulang */}
            <Card className="mt-6 p-6">
                <div className="mb-1 flex items-center gap-2">
                    <Repeat size={16} className="text-brand-600" />
                    <h2 className="text-sm font-semibold text-slate-700">Pengeluaran Berulang (otomatis tiap bulan)</h2>
                </div>
                <p className="mb-4 text-xs text-slate-500">Biaya tetap seperti gaji penjaga kos & wifi otomatis tercatat tiap bulan. Listrik/air tetap dicatat manual karena nominalnya berubah.</p>

                <form onSubmit={submitRecurring} className="grid gap-3 sm:grid-cols-2 lg:grid-cols-5 lg:items-end">
                    <Field label="Deskripsi" error={r.errors.description}><Input value={r.data.description} onChange={(e) => r.setData('description', e.target.value)} placeholder="Gaji penjaga kos" /></Field>
                    <Field label="Kategori" error={r.errors.category}>
                        <Select value={r.data.category} onChange={(e) => r.setData('category', e.target.value)}>
                            {categories.map((c) => <option key={c.value} value={c.value}>{c.label}</option>)}
                        </Select>
                    </Field>
                    <Field label="Nominal" error={r.errors.amount}><Input type="number" value={r.data.amount} onChange={(e) => r.setData('amount', e.target.value)} placeholder="1500000" /></Field>
                    <Field label="Tiap tanggal" error={r.errors.day_of_month}><Input type="number" min={1} max={28} value={r.data.day_of_month} onChange={(e) => r.setData('day_of_month', e.target.value)} /></Field>
                    <Button type="submit" disabled={r.processing} className="w-full">Tambah</Button>
                </form>

                {recurring.length > 0 && (
                    <ul className="mt-5 divide-y divide-slate-100 border-t border-slate-100">
                        {recurring.map((item) => (
                            <li key={item.id} className="flex items-center gap-3 py-3">
                                <div className="min-w-0 flex-1">
                                    <p className={`font-semibold ${item.is_active ? 'text-slate-800' : 'text-slate-400 line-through'}`}>{item.description}</p>
                                    <p className="text-xs text-slate-500">{item.category_label} · {rupiah(item.amount)} · tiap tgl {item.day_of_month}</p>
                                </div>
                                <button
                                    onClick={() => router.post(`/expenses/recurring/${item.id}/toggle`)}
                                    className={`rounded-lg border px-3 py-1.5 text-xs font-medium ${item.is_active ? 'border-emerald-200 text-emerald-700' : 'border-slate-200 text-slate-500'}`}
                                >
                                    {item.is_active ? 'Aktif' : 'Nonaktif'}
                                </button>
                                <button onClick={() => confirm(`Hapus pengeluaran berulang "${item.description}"?`) && router.delete(`/expenses/recurring/${item.id}`)} className="text-slate-400 hover:text-rose-600"><Trash2 size={16} /></button>
                            </li>
                        ))}
                    </ul>
                )}
            </Card>
        </AuthenticatedLayout>
    );
}
