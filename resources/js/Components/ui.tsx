import { Link } from '@inertiajs/react';
import { ButtonHTMLAttributes, InputHTMLAttributes, SelectHTMLAttributes, ReactNode, TextareaHTMLAttributes, useState } from 'react';
import { Moon, Sun, Inbox } from 'lucide-react';
import { statusColor } from '@/lib/format';
import type { Paginated } from '@/types';

const inputBase =
    'w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-800 shadow-sm transition placeholder:text-slate-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/30 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:placeholder:text-slate-500';

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
            className={`inline-flex items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 active:scale-[.98] dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700 ${className}`}
        />
    );
}

export function Field({ label, error, children }: { label: string; error?: string; children: ReactNode }) {
    return (
        <label className="block">
            <span className="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">{label}</span>
            {children}
            {error && <span className="mt-1 block text-xs text-rose-600 dark:text-rose-400">{error}</span>}
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
        <div className={`rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900 ${className}`}>
            {children}
        </div>
    );
}

export function PageHeader({ title, subtitle, action }: { title: string; subtitle?: string; action?: ReactNode }) {
    return (
        <div className="mb-6 flex items-end justify-between gap-4">
            <div>
                <h1 className="text-xl font-bold text-slate-900 sm:text-2xl dark:text-white">{title}</h1>
                {subtitle && <p className="mt-1 text-sm text-slate-500 dark:text-slate-400">{subtitle}</p>}
            </div>
            {action}
        </div>
    );
}

export function EmptyState({ message, title, icon: Icon = Inbox, action }: { message: string; title?: string; icon?: typeof Inbox; action?: ReactNode }) {
    return (
        <div className="flex flex-col items-center justify-center px-6 py-16 text-center">
            <div className="grid h-14 w-14 place-items-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-500">
                <Icon size={26} />
            </div>
            {title && <p className="mt-4 font-semibold text-slate-700 dark:text-slate-200">{title}</p>}
            <p className="mt-1 max-w-xs text-sm text-slate-400 dark:text-slate-500">{message}</p>
            {action && <div className="mt-5">{action}</div>}
        </div>
    );
}

export function Skeleton({ className = '' }: { className?: string }) {
    return <div className={`animate-pulse rounded-xl bg-slate-200/80 dark:bg-slate-800 ${className}`} />;
}

export function ThemeToggle({ className = '' }: { className?: string }) {
    const [dark, setDark] = useState(() => typeof document !== 'undefined' && document.documentElement.classList.contains('dark'));
    const toggle = () => {
        const next = !dark;
        setDark(next);
        document.documentElement.classList.toggle('dark', next);
        localStorage.setItem('theme', next ? 'dark' : 'light');
    };
    return (
        <button
            onClick={toggle}
            title={dark ? 'Mode terang' : 'Mode gelap'}
            className={`grid h-9 w-9 place-items-center rounded-xl text-slate-500 transition hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800 ${className}`}
        >
            {dark ? <Sun size={18} /> : <Moon size={18} />}
        </button>
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
                                : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800'
                        }`}
                        dangerouslySetInnerHTML={{ __html: link.label }}
                    />
                ) : (
                    <span key={i} className="px-3 py-1.5 text-sm text-slate-300 dark:text-slate-600" dangerouslySetInnerHTML={{ __html: link.label }} />
                )
            )}
        </nav>
    );
}
