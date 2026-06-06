import { Head } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Card, EmptyState } from '@/Components/ui';
import { rupiah, tanggal } from '@/lib/format';
import { DoorClosed, DoorOpen, Users, AlarmClock, Wallet, FileSignature, Lightbulb } from 'lucide-react';

interface Activity { type: string; title: string; subtitle: string; amount: number | null; date: string }
interface Stats {
    rooms_total: number; rooms_occupied: number; rooms_available: number; rooms_maintenance: number;
    occupancy_rate: number; active_tenants: number; unpaid_invoices: number; overdue_invoices: number;
    receivables: number; income_this_month: number; income_last_3_months: number; income_this_year: number;
    current_year: number; expense_this_month: number;
    recent_activity: Activity[];
}

function waktuLalu(iso: string): string {
    const a = new Date(iso); a.setHours(0, 0, 0, 0);
    const b = new Date(); b.setHours(0, 0, 0, 0);
    const days = Math.round((b.getTime() - a.getTime()) / 86400000);
    if (days <= 0) return 'Hari ini';
    if (days === 1) return 'Kemarin';
    if (days < 7) return `${days} hari lalu`;
    return tanggal(iso);
}

function MiniStat({ icon: Icon, label, value, tone }: { icon: typeof DoorOpen; label: string; value: string; tone: string }) {
    return (
        <Card className="p-4">
            <div className={`mb-3 grid h-10 w-10 place-items-center rounded-xl ${tone}`}><Icon size={18} /></div>
            <p className="text-2xl font-bold text-slate-900">{value}</p>
            <p className="mt-0.5 text-xs text-slate-500">{label}</p>
        </Card>
    );
}

function ActivityRow({ item }: { item: Activity }) {
    const initials = item.title.split(' ').slice(0, 2).map((w) => w.charAt(0)).join('').toUpperCase();
    const icon = item.type === 'expense' ? <Lightbulb size={16} /> : item.type === 'lease' ? <FileSignature size={16} /> : <Wallet size={16} />;
    const tone =
        item.type === 'expense' ? 'bg-amber-100 text-amber-600' :
        item.type === 'lease' ? 'bg-indigo-100 text-indigo-600' : 'bg-emerald-100 text-emerald-600';

    return (
        <div className="flex items-center gap-3 py-3">
            <div className={`grid h-10 w-10 shrink-0 place-items-center rounded-full text-xs font-bold ${tone}`}>
                {item.type === 'payment' || item.type === 'lease' ? initials : icon}
            </div>
            <div className="min-w-0 flex-1">
                <p className="truncate font-semibold text-slate-800">{item.title}</p>
                <p className="truncate text-sm text-slate-500">{item.subtitle}</p>
            </div>
            <div className="text-right">
                {item.amount !== null && (
                    <p className={`text-sm font-bold ${item.amount < 0 ? 'text-rose-600' : 'text-emerald-600'}`}>
                        {item.amount < 0 ? '-' : ''}{rupiah(Math.abs(item.amount))}
                    </p>
                )}
                <p className="text-xs text-slate-400">{waktuLalu(item.date)}</p>
            </div>
        </div>
    );
}

export default function Dashboard({ stats }: { stats: Stats }) {
    return (
        <AuthenticatedLayout>
            <Head title="Dashboard" />

            {/* Hero pendapatan */}
            <div className="relative overflow-hidden rounded-3xl bg-gradient-to-br from-brand-600 to-brand-700 p-6 text-white shadow-lg">
                <div className="absolute -right-8 -top-10 h-40 w-40 rounded-full bg-white/10" />
                <div className="relative">
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
                    <div className="mt-4 flex flex-wrap gap-x-6 gap-y-1 border-t border-white/20 pt-3 text-xs text-white/80">
                        <span>Pendapatan 3 bulan: <b className="text-white">{rupiah(stats.income_last_3_months)}</b></span>
                        <span>Tahun {stats.current_year}: <b className="text-white">{rupiah(stats.income_this_year)}</b></span>
                    </div>
                </div>
            </div>

            {/* Statistik (sesuai PDF) */}
            <div className="mt-5 grid grid-cols-2 gap-4 lg:grid-cols-4">
                <MiniStat icon={DoorClosed} label="Kamar terisi" value={String(stats.rooms_occupied)} tone="bg-blue-100 text-blue-600" />
                <MiniStat icon={DoorOpen} label="Kamar kosong" value={String(stats.rooms_available)} tone="bg-emerald-100 text-emerald-600" />
                <MiniStat icon={Users} label="Penghuni aktif" value={String(stats.active_tenants)} tone="bg-indigo-100 text-indigo-600" />
                <MiniStat icon={AlarmClock} label="Tagihan telat" value={String(stats.overdue_invoices)} tone="bg-rose-100 text-rose-600" />
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

            {/* Aktivitas terbaru */}
            <h2 className="mb-2 mt-6 px-1 text-xs font-semibold uppercase tracking-wide text-slate-400">Aktivitas Terbaru</h2>
            <Card className="px-5 py-1">
                {stats.recent_activity.length === 0 ? (
                    <EmptyState title="Belum ada aktivitas" message="Pembayaran, kontrak baru, dan pengeluaran akan muncul di sini." />
                ) : (
                    <div className="divide-y divide-slate-100">
                        {stats.recent_activity.map((a, i) => <ActivityRow key={i} item={a} />)}
                    </div>
                )}
            </Card>
        </AuthenticatedLayout>
    );
}
