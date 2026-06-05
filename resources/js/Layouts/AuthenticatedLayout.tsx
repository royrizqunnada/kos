import { Link, usePage, router } from '@inertiajs/react';
import { ReactNode, useState } from 'react';
import {
    LayoutDashboard, DoorOpen, Users, FileSignature, ReceiptText,
    Wallet, TrendingDown, BarChart3, Settings, LogOut, Menu, Home, User,
} from 'lucide-react';
import type { PageProps } from '@/types';

const NAV = [
    { name: 'Dashboard', href: '/', icon: LayoutDashboard, route: 'dashboard' },
    { name: 'Kamar', href: '/rooms', icon: DoorOpen, route: 'rooms.index' },
    { name: 'Penghuni', href: '/tenants', icon: Users, route: 'tenants.index' },
    { name: 'Kontrak', href: '/leases', icon: FileSignature, route: 'leases.index' },
    { name: 'Tagihan', href: '/invoices', icon: ReceiptText, route: 'invoices.index' },
    { name: 'Pembayaran', href: '/payments', icon: Wallet, route: 'payments.index' },
    { name: 'Pengeluaran', href: '/expenses', icon: TrendingDown, route: 'expenses.index' },
    { name: 'Laporan', href: '/reports', icon: BarChart3, route: 'reports.index' },
    { name: 'Pengaturan', href: '/settings', icon: Settings, route: 'settings.edit' },
];

// Tab utama untuk bottom-navigation di mobile.
const BOTTOM = [
    { name: 'Beranda', href: '/', icon: Home },
    { name: 'Kamar', href: '/rooms', icon: DoorOpen },
    { name: 'Tagihan', href: '/invoices', icon: ReceiptText },
    { name: 'Laporan', href: '/reports', icon: BarChart3 },
    { name: 'Profil', href: '/settings', icon: User },
];

export default function AuthenticatedLayout({ children }: { children: ReactNode }) {
    const { auth, flash } = usePage<PageProps>().props;
    const currentUrl = usePage().url;
    const [open, setOpen] = useState(false);

    const isActive = (href: string) => (href === '/' ? currentUrl === '/' : currentUrl.startsWith(href));

    return (
        <div className="min-h-screen bg-slate-50">
            {/* Overlay drawer (mobile) */}
            {open && <div className="fixed inset-0 z-30 bg-slate-900/40 lg:hidden" onClick={() => setOpen(false)} />}

            <aside
                className={`fixed inset-y-0 left-0 z-40 w-64 transform border-r border-slate-200 bg-white transition-transform lg:translate-x-0 ${
                    open ? 'translate-x-0' : '-translate-x-full'
                }`}
            >
                <div className="flex h-16 items-center gap-2 border-b border-slate-200 px-6">
                    <div className="grid h-9 w-9 place-items-center rounded-lg bg-brand-600 font-bold text-white">K</div>
                    <span className="text-lg font-bold text-slate-900">Kos Manager</span>
                </div>
                <nav className="space-y-1 p-3">
                    {NAV.map((item) => {
                        const Icon = item.icon;
                        return (
                            <Link
                                key={item.name}
                                href={item.href}
                                onClick={() => setOpen(false)}
                                className={`flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition ${
                                    isActive(item.href) ? 'bg-brand-50 text-brand-700' : 'text-slate-600 hover:bg-slate-100'
                                }`}
                            >
                                <Icon size={18} />
                                {item.name}
                            </Link>
                        );
                    })}
                </nav>
            </aside>

            <div className="lg:pl-64">
                <header className="sticky top-0 z-20 flex h-16 items-center justify-between border-b border-slate-200 bg-white/80 px-4 backdrop-blur lg:px-8">
                    <div className="flex items-center gap-3">
                        <button className="grid h-9 w-9 place-items-center rounded-lg text-slate-500 hover:bg-slate-100 lg:hidden" onClick={() => setOpen(true)} title="Menu">
                            <Menu size={20} />
                        </button>
                        <div className="lg:hidden">
                            <p className="text-xs text-slate-400">Selamat datang,</p>
                            <p className="-mt-0.5 text-sm font-bold text-slate-800">{auth.user?.name}</p>
                        </div>
                    </div>
                    <div className="ml-auto flex items-center gap-4">
                        <div className="hidden text-right lg:block">
                            <p className="text-sm font-semibold text-slate-800">{auth.user?.name}</p>
                            <p className="text-xs text-slate-400">{auth.user?.roles?.join(', ')}</p>
                        </div>
                        <button
                            onClick={() => router.post('/logout')}
                            className="grid h-9 w-9 place-items-center rounded-lg text-slate-500 hover:bg-slate-100"
                            title="Keluar"
                        >
                            <LogOut size={18} />
                        </button>
                    </div>
                </header>

                <main className="p-4 pb-24 lg:p-8 lg:pb-8">
                    {flash?.success && (
                        <div className="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                            {flash.success}
                        </div>
                    )}
                    {flash?.error && (
                        <div className="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                            {flash.error}
                        </div>
                    )}
                    {children}
                </main>
            </div>

            {/* Bottom navigation (mobile) */}
            <nav className="fixed inset-x-0 bottom-0 z-40 grid grid-cols-5 border-t border-slate-200 bg-white shadow-[0_-1px_8px_rgba(0,0,0,0.04)] lg:hidden">
                {BOTTOM.map((item) => {
                    const Icon = item.icon;
                    const active = isActive(item.href);
                    return (
                        <Link
                            key={item.name}
                            href={item.href}
                            className={`flex flex-col items-center gap-1 py-2.5 text-[11px] font-semibold transition ${
                                active ? 'text-brand-600' : 'text-slate-400'
                            }`}
                        >
                            <Icon size={20} strokeWidth={active ? 2.4 : 2} />
                            {item.name}
                        </Link>
                    );
                })}
            </nav>
        </div>
    );
}
