import { Head, router, Deferred } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Card, Input, PageHeader } from '@/Components/ui';
import { rupiah } from '@/lib/format';
import { useState } from 'react';

interface Summary {
    income: number; expense: number; profit: number; receivables: number;
    active_tenants: number;
    occupancy: { total: number; occupied: number; available: number; maintenance: number; rate: number };
}
interface Serie { month: string; income: number; expense: number; }

function Metric({ label, value, tone = 'text-slate-900 dark:text-white' }: { label: string; value: string; tone?: string }) {
    return (
        <Card className="p-5">
            <p className="text-sm text-slate-500 dark:text-slate-400">{label}</p>
            <p className={`mt-1 text-xl font-bold ${tone}`}>{value}</p>
        </Card>
    );
}

export default function Index({ summary, filters }: { summary: Summary; filters: { from: string; to: string } }) {
    const [range, setRange] = useState(filters);
    const apply = (patch: Partial<typeof range>) => {
        const next = { ...range, ...patch };
        setRange(next);
        router.get('/reports', next, { preserveState: true, replace: true });
    };

    return (
        <AuthenticatedLayout>
            <Head title="Laporan" />
            <PageHeader title="Laporan" subtitle="Pendapatan, pengeluaran, laba, piutang & hunian" />

            <Card className="mb-6 flex flex-wrap items-end gap-4 p-4">
                <label className="text-sm">
                    <span className="mb-1 block text-slate-500 dark:text-slate-400">Dari</span>
                    <Input type="date" value={range.from} onChange={(e) => apply({ from: e.target.value })} />
                </label>
                <label className="text-sm">
                    <span className="mb-1 block text-slate-500 dark:text-slate-400">Sampai</span>
                    <Input type="date" value={range.to} onChange={(e) => apply({ to: e.target.value })} />
                </label>
            </Card>

            <div className="grid grid-cols-2 gap-4 lg:grid-cols-4">
                <Metric label="Pendapatan" value={rupiah(summary.income)} tone="text-emerald-600" />
                <Metric label="Pengeluaran" value={rupiah(summary.expense)} tone="text-rose-600" />
                <Metric label="Laba" value={rupiah(summary.profit)} tone={summary.profit >= 0 ? 'text-emerald-600' : 'text-rose-600'} />
                <Metric label="Piutang" value={rupiah(summary.receivables)} tone="text-amber-600" />
                <Metric label="Kamar Kosong" value={String(summary.occupancy.available)} />
                <Metric label="Kamar Terisi" value={String(summary.occupancy.occupied)} />
                <Metric label="Tingkat Hunian" value={`${summary.occupancy.rate}%`} />
                <Metric label="Penghuni Aktif" value={String(summary.active_tenants)} />
            </div>

            <Card className="mt-6 p-5">
                <h2 className="mb-4 text-sm font-semibold text-slate-700 dark:text-slate-200">Tren Tahunan</h2>
                <Deferred data="series" fallback={<div className="py-10 text-center text-sm text-slate-400">Memuat…</div>}>
                    <Chart />
                </Deferred>
            </Card>
        </AuthenticatedLayout>
    );
}

function Chart({ series }: { series?: Serie[] }) {
    const rows = series ?? [];
    const max = Math.max(1, ...rows.map((r) => Math.max(r.income, r.expense)));
    return (
        <div className="flex h-56 items-end gap-2">
            {rows.map((r) => (
                <div key={r.month} className="flex flex-1 flex-col items-center gap-1">
                    <div className="flex h-44 w-full items-end justify-center gap-1">
                        <div className="w-1/2 rounded-t bg-brand-500" style={{ height: `${(r.income / max) * 100}%` }} />
                        <div className="w-1/2 rounded-t bg-rose-300" style={{ height: `${(r.expense / max) * 100}%` }} />
                    </div>
                    <span className="text-[10px] text-slate-400">{r.month}</span>
                </div>
            ))}
        </div>
    );
}
