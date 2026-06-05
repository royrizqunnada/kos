import { Head, Deferred } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Card } from '@/Components/ui';
import { rupiah } from '@/lib/format';
import { DoorClosed, DoorOpen, Users, ReceiptText } from 'lucide-react';

interface Stats {
    rooms_total: number; rooms_occupied: number; rooms_available: number; rooms_maintenance: number;
    occupancy_rate: number; active_tenants: number; unpaid_invoices: number; receivables: number;
    income_this_month: number; income_this_year: number; expense_this_month: number;
}
interface Serie { month: string; income: number; expense: number; }

function MiniStat({ icon: Icon, label, value, tone }: { icon: typeof DoorOpen; label: string; value: string; tone: string }) {
    return (
        <Card className="p-4">
            <div className={`mb-3 grid h-10 w-10 place-items-center rounded-xl ${tone}`}><Icon size={18} /></div>
            <p className="text-2xl font-bold text-slate-900">{value}</p>
            <p className="mt-0.5 text-xs text-slate-500">{label}</p>
        </Card>
    );
}

export default function Dashboard({ stats }: { stats: Stats }) {
    return (
        <AuthenticatedLayout>
            <Head title="Dashboard" />

            {/* Hero pendapatan */}
            <div className="overflow-hidden rounded-2xl bg-gradient-to-br from-brand-600 to-brand-700 p-6 text-white shadow-lg">
                <p className="text-sm text-white/80">Pendapatan bulan ini</p>
                <p className="mt-1 text-3xl font-extrabold tracking-tight sm:text-4xl">{rupiah(stats.income_this_month)}</p>
                <div className="mt-5 flex gap-8">
                    <div>
                        <p className="text-lg font-bold">{rupiah(stats.receivables)}</p>
                        <p className="text-xs text-white/70">Belum dibayar</p>
                    </div>
                    <div>
                        <p className="text-lg font-bold">{rupiah(stats.expense_this_month)}</p>
                        <p className="text-xs text-white/70">Pengeluaran</p>
                    </div>
                </div>
            </div>

            {/* Statistik ringkas */}
            <div className="mt-5 grid grid-cols-2 gap-4 lg:grid-cols-4">
                <MiniStat icon={DoorClosed} label="Kamar terisi" value={String(stats.rooms_occupied)} tone="bg-blue-100 text-blue-600" />
                <MiniStat icon={DoorOpen} label="Kamar kosong" value={String(stats.rooms_available)} tone="bg-emerald-100 text-emerald-600" />
                <MiniStat icon={Users} label="Penghuni aktif" value={String(stats.active_tenants)} tone="bg-indigo-100 text-indigo-600" />
                <MiniStat icon={ReceiptText} label="Tagihan belum dibayar" value={String(stats.unpaid_invoices)} tone="bg-rose-100 text-rose-600" />
            </div>

            {/* Tingkat hunian */}
            <Card className="mt-5 p-5">
                <div className="flex items-center justify-between">
                    <span className="text-sm font-semibold text-slate-700">Tingkat hunian</span>
                    <span className="text-sm font-bold text-brand-600">{stats.occupancy_rate}%</span>
                </div>
                <div className="mt-3 h-2.5 overflow-hidden rounded-full bg-slate-100">
                    <div className="h-full rounded-full bg-brand-500 transition-all" style={{ width: `${stats.occupancy_rate}%` }} />
                </div>
                <p className="mt-2 text-xs text-slate-400">{stats.rooms_occupied} dari {stats.rooms_total} kamar terisi</p>
            </Card>

            {/* Grafik */}
            <Card className="mt-5 p-5">
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
