import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Card, EmptyState } from '@/Components/ui';
import { rupiah, tanggal } from '@/lib/format';
import { DoorClosed, DoorOpen, Users, AlarmClock, Wallet, FileSignature, Lightbulb, AlertTriangle, Clock, CalendarX } from 'lucide-react';

interface Activity { type: string; title: string; subtitle: string; amount: number | null; date: string }
interface AlertItem { id: number; tenant_name: string | null; room_number: string | null; tenant_phone?: string | null; due_date?: string; end_date?: string; outstanding?: number; invoice_number?: string }
interface MonthSeries { month: string; income: number; expense: number }
interface Stats {
    rooms_total: number; rooms_occupied: number; rooms_available: number; rooms_maintenance: number;
    occupancy_rate: number; active_tenants: number; unpaid_invoices: number; overdue_invoices: number;
    receivables: number; income_this_month: number; income_last_3_months: number; income_this_year: number;
    cash_this_month: number; cash_this_year: number;
    current_year: number; expense_this_month: number; profit_this_month: number;
    recent_activity: Activity[];
    alerts: { overdue: AlertItem[]; due_soon: AlertItem[]; lease_ending: AlertItem[] };
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

function RevenueChart({ series }: { series: MonthSeries[] }) {
    const max = Math.max(...series.map((s) => Math.max(s.income, s.expense)), 1);
    return (
        <div className="flex h-32 items-end gap-1">
            {series.map((s) => (
                <div key={s.month} className="flex flex-1 flex-col items-center gap-0.5">
                    <div className="flex w-full items-end justify-center gap-0.5" style={{ height: '100px' }}>
                        <div
                            className="w-2 rounded-t bg-brand-500"
                            style={{ height: `${Math.round((s.income / max) * 100)}px` }}
                            title={`Pendapatan: ${rupiah(s.income)}`}
                        />
                        <div
                            className="w-2 rounded-t bg-rose-300"
                            style={{ height: `${Math.round((s.expense / max) * 100)}px` }}
                            title={`Pengeluaran: ${rupiah(s.expense)}`}
                        />
                    </div>
                    <span className="text-[9px] text-slate-400">{s.month}</span>
                </div>
            ))}
        </div>
    );
}

export default function Dashboard({ stats, revenueSeries }: { stats: Stats; revenueSeries?: MonthSeries[] }) {
    const [ignored, setIgnored] = useState<number[]>([]);
    const endingLeases = stats.alerts.lease_ending.filter((a) => !ignored.includes(a.id));
    const totalAlerts = stats.alerts.overdue.length + stats.alerts.due_soon.length + endingLeases.length;

    const perpanjang = (a: AlertItem) => {
        if (confirm(`Perpanjang kontrak ${a.tenant_name} (Kamar ${a.room_number})? Kontrak & tagihan periode baru otomatis dibuat.`)) {
            router.post(`/leases/${a.id}/quick-renew`);
        }
    };
    const waLanjut = (a: AlertItem) => {
        const wa = (a.tenant_phone ?? '').replace(/\D/g, '').replace(/^0/, '62');
        const msg = `Halo ${a.tenant_name}, kontrak kamar ${a.room_number} akan habis pada ${tanggal(a.end_date!)}. Apakah ingin dilanjutkan (perpanjang)? Mohon konfirmasinya ya. Terima kasih 🙏`;
        return `https://wa.me/${wa}?text=${encodeURIComponent(msg)}`;
    };

    return (
        <AuthenticatedLayout>
            <Head title="Dashboard" />

            {/* Hero pendapatan */}
            <div className="relative overflow-hidden rounded-3xl bg-gradient-to-br from-brand-600 to-brand-700 p-6 text-white shadow-lg">
                <div className="absolute -right-8 -top-10 h-40 w-40 rounded-full bg-white/10" />
                <div className="relative">
                    <p className="text-sm text-white/80">Pendapatan bulan ini <span className="text-white/50">(sewa per bulan)</span></p>
                    <p className="mt-1 text-3xl font-extrabold tracking-tight sm:text-4xl">{rupiah(stats.income_this_month)}</p>
                    <div className="mt-5 grid grid-cols-2 gap-x-8 gap-y-3 sm:grid-cols-4">
                        <div>
                            <p className="text-lg font-bold">{rupiah(stats.cash_this_month)}</p>
                            <p className="text-xs text-white/70">Kas masuk bln ini</p>
                        </div>
                        <div>
                            <p className="text-lg font-bold">{rupiah(stats.expense_this_month)}</p>
                            <p className="text-xs text-white/70">Pengeluaran bln ini</p>
                        </div>
                        <div>
                            <p className={`text-lg font-bold ${stats.profit_this_month >= 0 ? 'text-white' : 'text-rose-200'}`}>{rupiah(stats.profit_this_month)}</p>
                            <p className="text-xs text-white/70">Laba operasional bln ini</p>
                        </div>
                        <div>
                            <p className="text-lg font-bold">{rupiah(stats.receivables)}</p>
                            <p className="text-xs text-white/70">Belum dibayar</p>
                        </div>
                    </div>
                    <div className="mt-4 flex flex-wrap gap-x-6 gap-y-1 border-t border-white/20 pt-3 text-xs text-white/80">
                        <span>Pendapatan 3 bulan: <b className="text-white">{rupiah(stats.income_last_3_months)}</b></span>
                        <span>Kas masuk {stats.current_year}: <b className="text-white">{rupiah(stats.cash_this_year)}</b></span>
                    </div>
                </div>
            </div>

            {/* Perlu Perhatian */}
            {totalAlerts > 0 && (
                <Card className="mt-5 overflow-hidden">
                    <div className="flex items-center gap-2 border-b border-amber-100 bg-amber-50 px-5 py-3">
                        <AlertTriangle size={16} className="text-amber-500" />
                        <span className="text-sm font-semibold text-amber-700">Perlu Perhatian</span>
                        <span className="ml-auto rounded-full bg-amber-200 px-2 py-0.5 text-xs font-bold text-amber-700">{totalAlerts}</span>
                    </div>
                    <div className="divide-y divide-slate-100">
                        {stats.alerts.overdue.map((a) => (
                            <Link key={`ov-${a.id}`} href={`/invoices/${a.id}`} className="flex items-center gap-3 px-5 py-3 hover:bg-rose-50">
                                <div className="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-rose-100"><Clock size={14} className="text-rose-600" /></div>
                                <div className="min-w-0 flex-1">
                                    <p className="truncate text-sm font-semibold text-slate-800">{a.tenant_name} · Kamar {a.room_number}</p>
                                    <p className="text-xs text-slate-500">Tagihan telat · jatuh tempo {tanggal(a.due_date!)}</p>
                                </div>
                                <span className="shrink-0 text-sm font-bold text-rose-600">{rupiah(a.outstanding!)}</span>
                            </Link>
                        ))}
                        {stats.alerts.due_soon.map((a) => (
                            <Link key={`ds-${a.id}`} href={`/invoices/${a.id}`} className="flex items-center gap-3 px-5 py-3 hover:bg-amber-50">
                                <div className="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-amber-100"><Clock size={14} className="text-amber-600" /></div>
                                <div className="min-w-0 flex-1">
                                    <p className="truncate text-sm font-semibold text-slate-800">{a.tenant_name} · Kamar {a.room_number}</p>
                                    <p className="text-xs text-slate-500">Jatuh tempo {tanggal(a.due_date!)}</p>
                                </div>
                                <span className="shrink-0 text-sm font-bold text-amber-600">{rupiah(a.outstanding!)}</span>
                            </Link>
                        ))}
                        {endingLeases.map((a) => (
                            <div key={`le-${a.id}`} className="px-5 py-3">
                                <div className="flex items-center gap-3">
                                    <div className="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-indigo-100"><CalendarX size={14} className="text-indigo-600" /></div>
                                    <Link href={`/leases/${a.id}`} className="min-w-0 flex-1">
                                        <p className="truncate text-sm font-semibold text-slate-800">{a.tenant_name} · Kamar {a.room_number}</p>
                                        <p className="text-xs text-slate-500">Kontrak habis {tanggal(a.end_date!)} — lanjut atau tidak?</p>
                                    </Link>
                                </div>
                                <div className="mt-2 flex flex-wrap items-center gap-2 pl-11">
                                    <button onClick={() => perpanjang(a)} className="inline-flex items-center gap-1 rounded-lg bg-brand-600 px-3 py-1.5 text-xs font-semibold text-white active:bg-brand-700">✅ Lanjut (Perpanjang)</button>
                                    {a.tenant_phone && (
                                        <a href={waLanjut(a)} target="_blank" rel="noreferrer" className="inline-flex items-center gap-1 rounded-lg border border-emerald-300 px-3 py-1.5 text-xs font-medium text-emerald-700 active:bg-emerald-50">📲 Tanya WA</a>
                                    )}
                                    <button onClick={() => setIgnored((prev) => [...prev, a.id])} className="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-500 active:bg-slate-100">❌ Tidak</button>
                                </div>
                            </div>
                        ))}
                    </div>
                </Card>
            )}

            {/* Statistik */}
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

            {/* Grafik tren pendapatan */}
            {revenueSeries && revenueSeries.some((s) => s.income > 0 || s.expense > 0) && (
                <Card className="mt-5 p-5">
                    <div className="mb-4 flex items-center justify-between">
                        <span className="text-sm font-semibold text-slate-700">Tren Pendapatan & Pengeluaran</span>
                        <div className="flex items-center gap-3 text-xs text-slate-500">
                            <span className="flex items-center gap-1"><span className="inline-block h-2.5 w-2.5 rounded bg-brand-500" /> Pendapatan</span>
                            <span className="flex items-center gap-1"><span className="inline-block h-2.5 w-2.5 rounded bg-rose-300" /> Pengeluaran</span>
                        </div>
                    </div>
                    <RevenueChart series={revenueSeries} />
                </Card>
            )}

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
