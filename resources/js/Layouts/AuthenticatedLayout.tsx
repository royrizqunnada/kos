import { Link, usePage, router } from '@inertiajs/react';
import { ReactNode, useState } from 'react';
import {
    LayoutDashboard, DoorOpen, Users, FileSignature, ReceiptText,
    Wallet, TrendingDown, BarChart3, Settings, LogOut, Menu, User,
} from 'lucide-react';
import { Logo } from '@/Components/ui';
import type { PageProps } from '@/types';

const NAV = [
    { name: 'Dashboard', href: '/', icon: LayoutDashboard },
    { name: 'Kamar', href: '/rooms', icon: DoorOpen },
    { name: 'Penghuni', href: '/tenants', icon: Users },
    { name: 'Kontrak', href: '/leases', icon: FileSignature },
    { name: 'Tagihan', href: '/invoices', icon: ReceiptText },
    { name: 'Pembayaran', href: '/payments', icon: Wallet },
    { name: 'Pengeluaran', href: '/expenses', icon: TrendingDown },
    { name: 'Laporan', href: '/reports', icon: BarChart3 },
    { name: 'Pengaturan', href: '/settings', icon: Settings },
];

// 5 tab utama untuk bottom-navigation (sesuai PDF: Beranda·Kamar·Tagihan·Laporan·Profil).
const BOTTOM = [
    { name: 'Beranda', href: '/', icon: LayoutDashboard, match: '/' },
    { name: 'Kamar', href: '/rooms', icon: DoorOpen, match: '/rooms' },
    { name: 'Tagihan', href: '/invoices', icon: ReceiptText, match: '/invoices' },
    { name: 'Laporan', href: '/reports', icon: BarChart3, match: '/reports' },
    { name: 'Profil', href: '/settings', icon: User, match: '/settings' },
];

export default function AuthenticatedLayout({ children }: { children: ReactNode }) {
    const { auth, flash } = usePage<PageProps>().props;
    const currentUrl = usePage().url;
    const [open, setOpen] = useState(false);
    const [menu, setMenu] = useState(false);

    const isActive = (href: string) => (href === '/' ? currentUrl === '/' : currentUrl.startsWith(href));
    const initial = (auth.user?.name ?? 'U').charAt(0).toUpperCase();

    return (
        <div className="min-h-screen bg-slate-50">
            {open && <div className="fixed inset-0 z-30 bg-slate-900/50 lg:hidden" onClick={() => setOpen(false)} />}

            {/* Sidebar (desktop) + drawer (mobile) */}
            <aside
                className={`fixed inset-y-0 left-0 z-40 w-64 transform border-r border-slate-200 bg-white transition-transform lg:translate-x-0 ${
                    open ? 'translate-x-0' : '-translate-x-full'
                }`}
            >
                <div className="flex h-16 items-center gap-2.5 border-b border-slate-200 px-6">
                    <Logo className="h-9 w-9" />
                    <div className="leading-tight">
                        <span className="block text-base font-bold text-slate-900">Cozy Corner</span>
                        <span className="block text-[10px] uppercase tracking-wide text-slate-400">Student Living</span>
                    </div>
                </div>
                <nav className="space-y-1 p-3">
                    {NAV.map((item) => {
                        const Icon = item.icon;
                        return (
                            <Link
                                key={item.name}
                                href={item.href}
                                onClick={() => setOpen(false)}
                                className={`flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition ${
                                    isActive(item.href)
                                        ? 'bg-brand-50 text-brand-700'
                                        : 'text-slate-600 hover:bg-slate-100'
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
                        <button className="grid h-9 w-9 place-items-center rounded-xl text-slate-500 hover:bg-slate-100 lg:hidden" onClick={() => setOpen(true)} title="Menu">
                            <Menu size={20} />
                        </button>
                        <div className="lg:hidden">
                            <p className="text-xs text-slate-400">Selamat datang,</p>
                            <p className="-mt-0.5 text-sm font-bold text-slate-800">{auth.user?.name}</p>
                        </div>
                    </div>

                    <div className="ml-auto flex items-center gap-1.5">
                        <div className="relative">
                            <button
                                onClick={() => setMenu((v) => !v)}
                                className="grid h-9 w-9 place-items-center rounded-full bg-brand-600 text-sm font-bold text-white shadow-sm"
                                title="Akun"
                            >
                                {initial}
                            </button>
                            {menu && (
                                <>
                                    <div className="fixed inset-0 z-30" onClick={() => setMenu(false)} />
                                    <div className="absolute right-0 z-40 mt-2 w-56 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-lg">
                                        <div className="border-b border-slate-100 px-4 py-3">
                                            <p className="truncate text-sm font-semibold text-slate-800">{auth.user?.name}</p>
                                            <p className="truncate text-xs text-slate-400">{auth.user?.roles?.join(', ')}</p>
                                        </div>
                                        <Link href="/settings" onClick={() => setMenu(false)} className="flex items-center gap-2.5 px-4 py-2.5 text-sm text-slate-600 hover:bg-slate-50">
                                            <Settings size={16} /> Pengaturan
                                        </Link>
                                        <button
                                            onClick={() => router.post('/logout')}
                                            className="flex w-full items-center gap-2.5 px-4 py-2.5 text-sm text-rose-600 hover:bg-rose-50"
                                        >
                                            <LogOut size={16} /> Keluar
                                        </button>
                                    </div>
                                </>
                            )}
                        </div>
                    </div>
                </header>

                <main className="mx-auto max-w-6xl p-4 pb-24 lg:p-8 lg:pb-8">
                    {flash?.success && (
                        <div className="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                            {flash.success}
                        </div>
                    )}
                    {flash?.error && (
                        <div className="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                            {flash.error}
                        </div>
                    )}
                    {children}
                </main>
            </div>

            {/* Bottom navigation (mobile) */}
            <nav className="fixed inset-x-0 bottom-0 z-40 grid grid-cols-5 border-t border-slate-200 bg-white/95 backdrop-blur shadow-[0_-1px_10px_rgba(0,0,0,0.05)] lg:hidden">
                {BOTTOM.map((item) => {
                    const Icon = item.icon;
                    // Kamar mencakup Penghuni & Kontrak; Tagihan mencakup Pembayaran.
                    const group: Record<string, string[]> = {
                        '/rooms': ['/rooms', '/tenants', '/leases'],
                        '/invoices': ['/invoices', '/payments'],
                    };
                    const paths = group[item.href] ?? [item.href];
                    const active = item.href === '/' ? currentUrl === '/' : paths.some((p) => currentUrl.startsWith(p));
                    return (
                        <Link
                            key={item.name}
                            href={item.href}
                            className={`flex flex-col items-center gap-1 py-2.5 text-[11px] font-semibold transition ${
                                active ? 'text-brand-600' : 'text-slate-400'
                            }`}
                        >
                            <Icon size={21} strokeWidth={active ? 2.4 : 2} />
                            {item.name}
                        </Link>
                    );
                })}
            </nav>
        </div>
    );
}
