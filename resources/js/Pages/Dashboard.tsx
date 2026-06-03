import { Head, Deferred } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Card, PageHeader } from '@/Components/ui';
import { rupiah } from '@/lib/format';
import {
    DoorOpen, DoorClosed, Wrench, Users, ReceiptText, Wallet, CalendarRange,
} from 'lucide-react';

interface Stats {
    rooms_total: number; rooms_occupied: number; rooms_available: number; rooms_maintenance: number;
    occupancy_rate: number; active_tenants: number; unpaid_invoices: number; receivables: number;
    income_this_month: number; income_this_year: number;
}
interface Serie { month: string; income: number; expense: number; }

function Stat({ icon: Icon, label, value, tone }: { icon: typeof DoorOpen; label: string; value: string; tone: string }) {
    return (
        <Card className="p-5">
            <div className="flex items-center justify-between">
                <div>
                    <p className="text-sm text-slate-500">{label}</p>
                    <p className="mt-1 text-2xl font-bold text-slate-900">{value}</p>
                </div>
                <div className={`grid h-11 w-11 place-items-center rounded-lg ${tone}`}><Icon size={20} /></div>
            </div>
        </Card>
    );
}

export default function Dashboard({ stats }: { stats: Stats }) {
    return (
        <AuthenticatedLayout>
            <Head title="Dashboard" />
            <PageHeader title="Dashboard" subtitle="Ringkasan operasional kos Anda" />

            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <Stat icon={DoorOpen} label="Total Kamar" value={String(stats.rooms_total)} tone="bg-slate-100 text-slate-600" />
                <Stat icon={DoorClosed} label="Kamar Terisi" value={String(stats.rooms_occupied)} tone="bg-blue-100 text-blue-600" />
                <Stat icon={DoorOpen} label="Kamar Kosong" value={String(stats.rooms_available)} tone="bg-emerald-100 text-emerald-600" />
                <Stat icon={Wrench} label="Maintenance" value={String(stats.rooms_maintenance)} tone="bg-amber-100 text-amber-600" />
                <Stat icon={Users} label="Penghuni Aktif" value={String(stats.active_tenants)} tone="bg-indigo-100 text-indigo-600" />
                <Stat icon={ReceiptText} label="Tagihan Belum Dibayar" value={String(stats.unpaid_invoices)} tone="bg-rose-100 text-rose-600" />
                <Stat icon={Wallet} label="Pendapatan Bulan Ini" value={rupiah(stats.income_this_month)} tone="bg-emerald-100 text-emerald-600" />
                <Stat icon={CalendarRange} label="Pendapatan Tahun Ini" value={rupiah(stats.income_this_year)} tone="bg-brand-50 text-brand-600" />
            </div>

            <Card className="mt-6 p-5">
                <h2 className="mb-4 text-sm font-semibold text-slate-700">Pendapatan vs Pengeluaran (Tahun Berjalan)</h2>
                <Deferred data="revenueSeries" fallback={<div className="py-12 text-center text-sm text-slate-400">Memuat grafik…</div>}>
                    <RevenueChart />
                </Deferred>
            </Card>
        </AuthenticatedLayout>
    );
}

function RevenueChart({ revenueSeries }: { revenueSeries?: Serie[] }) {
    const series = revenueSeries ?? [];
    const max = Math.max(1, ...series.map((s) => Math.max(s.income, s.expense)));
    return (
        <div className="flex h-56 items-end gap-2">
            {series.map((s) => (
                <div key={s.month} className="flex flex-1 flex-col items-center gap-1">
                    <div className="flex h-44 w-full items-end justify-center gap-1">
                        <div className="w-1/2 rounded-t bg-brand-500" style={{ height: `${(s.income / max) * 100}%` }} title={`Pendapatan: ${rupiah(s.income)}`} />
                        <div className="w-1/2 rounded-t bg-rose-300" style={{ height: `${(s.expense / max) * 100}%` }} title={`Pengeluaran: ${rupiah(s.expense)}`} />
                    </div>
                    <span className="text-[10px] text-slate-400">{s.month}</span>
                </div>
            ))}
        </div>
    );
}
