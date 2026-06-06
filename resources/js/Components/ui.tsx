import { Link, usePage } from '@inertiajs/react';
import { ButtonHTMLAttributes, InputHTMLAttributes, SelectHTMLAttributes, ReactNode, TextareaHTMLAttributes } from 'react';
import { Inbox } from 'lucide-react';
import { statusColor } from '@/lib/format';
import type { Paginated } from '@/types';

const inputBase =
    'w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-800 shadow-sm transition placeholder:text-slate-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/30';

export function Logo({ className = 'h-9 w-9' }: { className?: string }) {
    // Memuat file logo asli dari public/logo.png (ikon, tanpa tulisan).
    return <img src="/logo.png" alt="Cozy Corner" className={`${className} object-contain`} />;
}

export function Button({ className = '', ...props }: ButtonHTMLAttributes<HTMLButtonElement>) {
    return (
        <button
            {...props}
            className={`inline-flex items-center justify-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 active:scale-[.98] disabled:opacity-50 ${className}`}
        />
    );
}

export function SecondaryButton({ className = '', ...props }: ButtonHTMLAttributes<HTMLButtonElement>) {
    return (
        <button
            {...props}
            className={`inline-flex items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 active:scale-[.98] ${className}`}
        />
    );
}

export function Field({ label, error, children }: { label: string; error?: string; children: ReactNode }) {
    return (
        <label className="block">
            <span className="mb-1.5 block text-sm font-medium text-slate-700">{label}</span>
            {children}
            {error && <span className="mt-1 block text-xs text-rose-600">{error}</span>}
        </label>
    );
}

export function Input({ className = '', ...props }: InputHTMLAttributes<HTMLInputElement>) {
    return <input {...props} className={`${inputBase} ${className}`} />;
}

export function Textarea({ className = '', ...props }: TextareaHTMLAttributes<HTMLTextAreaElement>) {
    return <textarea {...props} className={`${inputBase} ${className}`} />;
}

export function Select({ className = '', children, ...props }: SelectHTMLAttributes<HTMLSelectElement>) {
    return (
        <select {...props} className={`${inputBase} ${className}`}>
            {children}
        </select>
    );
}

export function Badge({ status, label }: { status: string; label: string }) {
    return <span className={`inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold ${statusColor(status)}`}>{label}</span>;
}

export function Card({ children, className = '' }: { children: ReactNode; className?: string }) {
    return (
        <div className={`rounded-2xl border border-slate-200 bg-white shadow-sm ${className}`}>
            {children}
        </div>
    );
}

export function PageHeader({ title, subtitle, action }: { title: string; subtitle?: string; action?: ReactNode }) {
    return (
        <div className="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between sm:gap-4">
            <div className="min-w-0">
                <h1 className="text-xl font-bold text-slate-900 sm:text-2xl">{title}</h1>
                {subtitle && <p className="mt-1 text-sm text-slate-500">{subtitle}</p>}
            </div>
            {action && <div className="shrink-0">{action}</div>}
        </div>
    );
}

export function EmptyState({ message, title, icon: Icon = Inbox, action }: { message: string; title?: string; icon?: typeof Inbox; action?: ReactNode }) {
    return (
        <div className="flex flex-col items-center justify-center px-6 py-16 text-center">
            <div className="grid h-14 w-14 place-items-center rounded-2xl bg-slate-100 text-slate-400">
                <Icon size={26} />
            </div>
            {title && <p className="mt-4 font-semibold text-slate-700">{title}</p>}
            <p className="mt-1 max-w-xs text-sm text-slate-400">{message}</p>
            {action && <div className="mt-5">{action}</div>}
        </div>
    );
}

export function Skeleton({ className = '' }: { className?: string }) {
    return <div className={`animate-pulse rounded-xl bg-slate-200/80 ${className}`} />;
}

/** Sub-navigasi pill antar-halaman terkait (mis. Kamar | Penghuni). */
export function Segmented({ items }: { items: { label: string; href: string }[] }) {
    const url = usePage().url;
    return (
        <div className="mb-5 inline-flex rounded-xl bg-slate-100 p-1">
            {items.map((it) => {
                const active = url === it.href || url.startsWith(it.href + '?') || (it.href !== '/' && url.startsWith(it.href + '/'));
                return (
                    <Link
                        key={it.href}
                        href={it.href}
                        className={`rounded-lg px-4 py-1.5 text-sm font-semibold transition ${
                            active ? 'bg-white text-brand-700 shadow-sm' : 'text-slate-500 hover:text-slate-700'
                        }`}
                    >
                        {it.label}
                    </Link>
                );
            })}
        </div>
    );
}

export function Pagination<T>({ paginator }: { paginator: Paginated<T> }) {
    if (!paginator.links || paginator.links.length <= 3) return null;
    return (
        <nav className="flex flex-wrap items-center gap-1 p-4">
            {paginator.links.map((link, i) =>
                link.url ? (
                    <Link
                        key={i}
                        href={link.url}
                        preserveScroll
                        className={`rounded-lg px-3 py-1.5 text-sm transition ${
                            link.active
                                ? 'bg-brand-600 text-white'
                                : 'text-slate-600 hover:bg-slate-100'
                        }`}
                        dangerouslySetInnerHTML={{ __html: link.label }}
                    />
                ) : (
                    <span key={i} className="px-3 py-1.5 text-sm text-slate-300" dangerouslySetInnerHTML={{ __html: link.label }} />
                )
            )}
        </nav>
    );
}
