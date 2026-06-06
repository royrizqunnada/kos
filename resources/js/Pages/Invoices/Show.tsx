import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Badge, Button, Card, PageHeader, SecondaryButton } from '@/Components/ui';
import { rupiah, tanggal } from '@/lib/format';
import type { Invoice } from '@/types';

function waLink(invoice: Invoice): string {
    const phone = invoice.lease?.tenant?.phone ?? '';
    const cleaned = phone.replace(/\D/g, '');
    const wa = cleaned.startsWith('0') ? '62' + cleaned.slice(1) : cleaned.startsWith('62') ? cleaned : '62' + cleaned;
    const tenant = invoice.lease?.tenant?.name ?? 'Kak';
    const room = invoice.lease?.room?.room_number ?? '-';
    const amount = new Intl.NumberFormat('id-ID').format(Number(invoice.amount));
    const due = new Date(invoice.due_date).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
    const msg = `Halo ${tenant}, kami ingin menginformasikan bahwa tagihan sewa kamar ${room} (${invoice.invoice_number}) sebesar Rp${amount} jatuh tempo pada ${due}. Mohon segera diselesaikan. Terima kasih 🙏`;
    return `https://wa.me/${wa}?text=${encodeURIComponent(msg)}`;
}

export default function Show({ invoice }: { invoice: Invoice }) {
    const outstanding = Number(invoice.amount) - Number(invoice.paid_amount);
    const hasPhone = !!(invoice.lease?.tenant?.phone);
    return (
        <AuthenticatedLayout>
            <Head title={invoice.invoice_number} />
            <PageHeader
                title={invoice.invoice_number}
                subtitle={`${invoice.lease?.tenant?.name} · Kamar ${invoice.lease?.room?.room_number}`}
                action={
                    <div className="flex flex-wrap gap-2">
                        <Link href="/invoices"><SecondaryButton>Kembali</SecondaryButton></Link>
                        <Link href={`/invoices/${invoice.id}/tagihan`}>
                            <SecondaryButton className="border-brand-300 text-brand-700 hover:bg-brand-50">🖼️ Tagihan (gambar)</SecondaryButton>
                        </Link>
                        {hasPhone && invoice.status !== 'paid' && (
                            <a href={waLink(invoice)} target="_blank" rel="noreferrer">
                                <SecondaryButton className="border-emerald-300 text-emerald-700 hover:bg-emerald-50">📲 Kirim WA</SecondaryButton>
                            </a>
                        )}
                        {invoice.status !== 'paid' && (
                            <Link href={`/payments/create?invoice_id=${invoice.id}`}><Button>Catat Pembayaran</Button></Link>
                        )}
                        <SecondaryButton
                            onClick={() => {
                                if (confirm('Hapus tagihan ini? Pembayaran terkait ikut terhapus permanen.')) {
                                    router.delete(`/invoices/${invoice.id}`);
                                }
                            }}
                            className="border-rose-200 text-rose-600 hover:bg-rose-50"
                        >
                            Hapus
                        </SecondaryButton>
                    </div>
                }
            />
            <div className="grid gap-6 lg:grid-cols-3">
                <Card className="p-6 lg:col-span-2">
                    <h2 className="mb-3 text-sm font-semibold text-slate-700">Rincian</h2>
                    <table className="w-full text-sm">
                        <thead className="text-left text-xs uppercase text-slate-400"><tr><th className="py-2">Deskripsi</th><th className="py-2">Qty</th><th className="py-2 text-right">Subtotal</th></tr></thead>
                        <tbody className="divide-y divide-slate-100">
                            {(invoice.items ?? []).map((it) => (
                                <tr key={it.id}><td className="py-2">{it.description}</td><td className="py-2">{it.quantity}</td><td className="py-2 text-right">{rupiah(it.amount)}</td></tr>
                            ))}
                        </tbody>
                        <tfoot>
                            <tr className="border-t border-slate-200 font-semibold"><td className="py-2" colSpan={2}>Total</td><td className="py-2 text-right">{rupiah(invoice.amount)}</td></tr>
                            <tr className="text-emerald-600"><td className="py-1" colSpan={2}>Dibayar</td><td className="py-1 text-right">{rupiah(invoice.paid_amount)}</td></tr>
                            <tr className="text-rose-600 font-semibold"><td className="py-1" colSpan={2}>Sisa</td><td className="py-1 text-right">{rupiah(outstanding)}</td></tr>
                        </tfoot>
                    </table>
                </Card>
                <Card className="p-6">
                    <dl className="space-y-2 text-sm">
                        <div className="flex justify-between"><dt className="text-slate-500">Status</dt><dd><Badge status={invoice.status} label={invoice.status} /></dd></div>
                        <div className="flex justify-between"><dt className="text-slate-500">Periode</dt><dd>{tanggal(invoice.period_start)} – {tanggal(invoice.period_end)}</dd></div>
                        <div className="flex justify-between"><dt className="text-slate-500">Jatuh Tempo</dt><dd>{tanggal(invoice.due_date)}</dd></div>
                    </dl>
                    <h3 className="mb-2 mt-5 text-sm font-semibold text-slate-700">Riwayat Pembayaran</h3>
                    <div className="space-y-2">
                        {(invoice.payments ?? []).map((p) => (
                            <div key={p.id} className="rounded-lg border border-slate-100 px-3 py-2 text-sm">
                                <div className="flex justify-between"><span>{rupiah(p.amount)}</span><span className="text-slate-400">{tanggal(p.paid_at)}</span></div>
                                {p.note && <p className="text-xs text-slate-400">{p.note}</p>}
                            </div>
                        ))}
                        {(invoice.payments ?? []).length === 0 && <p className="text-sm text-slate-400">Belum ada pembayaran.</p>}
                    </div>
                </Card>
            </div>
        </AuthenticatedLayout>
    );
}
