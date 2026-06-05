export function rupiah(value: number | string | null | undefined): string {
    const n = Number(value ?? 0);
    return 'Rp ' + n.toLocaleString('id-ID', { maximumFractionDigits: 0 });
}

export function tanggal(value: string | null | undefined): string {
    if (!value) return '-';
    return new Date(value).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
}

const FALLBACK = 'bg-slate-100 text-slate-600 dark:bg-slate-700/40 dark:text-slate-300';

const STATUS_COLORS: Record<string, string> = {
    available: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300',
    occupied: 'bg-blue-100 text-blue-700 dark:bg-blue-500/15 dark:text-blue-300',
    booking: 'bg-violet-100 text-violet-700 dark:bg-violet-500/15 dark:text-violet-300',
    maintenance: 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300',
    draft: FALLBACK,
    unpaid: 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300',
    partial: 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300',
    paid: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300',
    overdue: 'bg-rose-100 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300',
    active: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300',
    ended: FALLBACK,
    cancelled: 'bg-rose-100 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300',
    pending: 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300',
};

export function statusColor(status: string): string {
    return STATUS_COLORS[status] ?? FALLBACK;
}
