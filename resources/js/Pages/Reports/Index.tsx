import { Head, router, Deferred } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Card, Input, PageHeader, SecondaryButton } from '@/Components/ui';
import { rupiah, tanggal } from '@/lib/format';
import { useState } from 'react';
import { Download, Printer } from 'lucide-react';

interface Summary {
    income: number; cash: number; expense: number; profit: number; receivables: number;
    active_tenants: number;
    occupancy: { total: number; occupied: number; available: number; maintenance: number; rate: number };
}
interface Serie { month: string; income: number; expense: number; }
interface PaymentRow {
    id: number; paid_at: string | null; tenant_name: string | null; room_number: string | null;
    invoice_number: string | null; method: string; amount: number; note: string | null;
}

function Metric({ label, value, tone = 'text-slate-900' }: { label: string; value: string; tone?: string }) {
    return (
        <Card className="p-5">
            <p className="text-sm text-slate-500">{label}</p>
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
                    <span className="mb-1 block text-slate-500">Dari</span>
                    <Input type="date" value={range.from} onChange={(e) => apply({ from: e.target.value })} />
                </label>
                <label className="text-sm">
                    <span className="mb-1 block text-slate-500">Sampai</span>
                    <Input type="date" value={range.to} onChange={(e) => apply({ to: e.target.value })} />
                </label>
                <div className="ml-auto flex gap-2">
                    <a href={`/reports/export/csv?from=${range.from}&to=${range.to}`}>
                        <SecondaryButton type="button"><Download size={16} /> Excel</SecondaryButton>
                    </a>
                    <a href={`/reports/print?from=${range.from}&to=${range.to}`} target="_blank" rel="noreferrer">
                        <SecondaryButton type="button"><Printer size={16} /> PDF</SecondaryButton>
                    </a>
                </div>
            </Card>

            <div className="grid grid-cols-2 gap-4 lg:grid-cols-4">
                <Metric label="Kas Masuk" value={rupiah(summary.cash)} tone="text-emerald-600" />
                <Metric label="Pendapatan (sewa/bln)" value={rupiah(summary.income)} tone="text-emerald-600" />
                <Metric label="Pengeluaran" value={rupiah(summary.expense)} tone="text-rose-600" />
                <Metric label="Laba" value={rupiah(summary.profit)} tone={summary.profit >= 0 ? 'text-emerald-600' : 'text-rose-600'} />
                <Metric label="Piutang" value={rupiah(summary.receivables)} tone="text-amber-600" />
                <Metric label="Kamar Kosong" value={String(summary.occupancy.available)} />
                <Metric label="Kamar Terisi" value={String(summary.occupancy.occupied)} />
                <Metric label="Tingkat Hunian" value={`${summary.occupancy.rate}%`} />
                <Metric label="Penghuni Aktif" value={String(summary.active_tenants)} />
            </div>

            <p className="mt-2 px-1 text-xs text-slate-400">
                <b>Kas Masuk</b> = uang yang benar-benar diterima pada periode (sama dengan Dashboard).{' '}
                <b>Pendapatan</b> = pembayaran yang dialokasikan per bulan masa sewa.
            </p>

            <Card className="mt-6 p-0">
                <div className="flex items-center justify-between px-5 py-4">
                    <h2 className="text-sm font-semibold text-slate-700">Rincian Kas Masuk</h2>
                </div>
                <Deferred data="payments" fallback={<div className="py-10 text-center text-sm text-slate-400">Memuat…</div>}>
                    <PaymentsTable />
                </Deferred>
            </Card>

            <Card className="mt-6 p-5">
                <h2 className="mb-4 text-sm font-semibold text-slate-700">Tren Tahunan</h2>
                <Deferred data="series" fallback={<div className="py-10 text-center text-sm text-slate-400">Memuat…</div>}>
                    <Chart />
                </Deferred>
            </Card>
        </AuthenticatedLayout>
    );
}

const METODE: Record<string, string> = { cash: 'Tunai', transfer: 'Transfer Bank', ewallet: 'E-Wallet' };

function PaymentsTable({ payments }: { payments?: PaymentRow[] }) {
    const rows = payments ?? [];
    const total = rows.reduce((sum, r) => sum + r.amount, 0);

    if (rows.length === 0) {
        return <p className="px-5 pb-6 text-sm text-slate-400">Tidak ada pembayaran pada periode ini.</p>;
    }

    return (
        <div className="overflow-x-auto">
            <table className="w-full text-sm">
                <thead>
                    <tr className="border-y border-slate-100 bg-slate-50 text-left text-xs text-slate-500">
                        <th className="px-5 py-2 font-medium">Tanggal</th>
                        <th className="px-3 py-2 font-medium">Penghuni</th>
                        <th className="px-3 py-2 font-medium">Kamar</th>
                        <th className="px-3 py-2 font-medium">No. Invoice</th>
                        <th className="px-3 py-2 font-medium">Metode</th>
                        <th className="px-3 py-2 font-medium">Catatan</th>
                        <th className="px-5 py-2 text-right font-medium">Nominal</th>
                    </tr>
                </thead>
                <tbody className="divide-y divide-slate-100">
                    {rows.map((r) => (
                        <tr key={r.id} className="text-slate-700">
                            <td className="whitespace-nowrap px-5 py-2.5">{tanggal(r.paid_at)}</td>
                            <td className="px-3 py-2.5">{r.tenant_name ?? '-'}</td>
                            <td className="px-3 py-2.5">{r.room_number ?? '-'}</td>
                            <td className="px-3 py-2.5">{r.invoice_number ?? '-'}</td>
                            <td className="px-3 py-2.5">{METODE[r.method] ?? r.method}</td>
                            <td className="px-3 py-2.5 text-slate-500">{r.note || '-'}</td>
                            <td className="whitespace-nowrap px-5 py-2.5 text-right font-medium tabular-nums">{rupiah(r.amount)}</td>
                        </tr>
                    ))}
                </tbody>
                <tfoot>
                    <tr className="border-t-2 border-slate-200 font-bold text-slate-900">
                        <td className="px-5 py-3" colSpan={6}>TOTAL</td>
                        <td className="whitespace-nowrap px-5 py-3 text-right tabular-nums">{rupiah(total)}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
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
