export function rupiah(value: number | string | null | undefined): string {
    const n = Number(value ?? 0);
    return 'Rp ' + n.toLocaleString('id-ID', { maximumFractionDigits: 0 });
}

export function tanggal(value: string | null | undefined): string {
    if (!value) return '-';
    return new Date(value).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
}

const STATUS_COLORS: Record<string, string> = {
    available: 'bg-emerald-100 text-emerald-700',
    occupied: 'bg-blue-100 text-blue-700',
    maintenance: 'bg-amber-100 text-amber-700',
    draft: 'bg-slate-100 text-slate-600',
    unpaid: 'bg-amber-100 text-amber-700',
    partial: 'bg-indigo-100 text-indigo-700',
    paid: 'bg-emerald-100 text-emerald-700',
    overdue: 'bg-rose-100 text-rose-700',
    active: 'bg-emerald-100 text-emerald-700',
    ended: 'bg-slate-100 text-slate-600',
    cancelled: 'bg-rose-100 text-rose-700',
    pending: 'bg-amber-100 text-amber-700',
};

export function statusColor(status: string): string {
    return STATUS_COLORS[status] ?? 'bg-slate-100 text-slate-600';
}
