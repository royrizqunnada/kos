import { Head } from '@inertiajs/react';
import { useRef } from 'react';
import { rupiah, tanggal } from '@/lib/format';
import ReceiptActions from '@/Components/ReceiptActions';
import ReceiptHeader from '@/Components/ReceiptHeader';

interface TagihanInvoice {
    id: number;
    invoice_number: string;
    period_start: string;
    period_end: string;
    due_date: string;
    amount: number;
    discount: number;
    paid_amount: number;
    status: string;
    status_label: string;
    tenant_name: string | null;
    tenant_phone: string | null;
    room_number: string | null;
}

export default function Tagihan({ invoice, kos_name, kos_tagline, kos_owner }: { invoice: TagihanInvoice; kos_name: string; kos_tagline: string; kos_owner: string }) {
    const cardRef = useRef<HTMLDivElement>(null);
    const sisa = invoice.amount - invoice.paid_amount;
    const lunas = invoice.status === 'paid' || sisa <= 0;
    const filename = `tagihan-${invoice.invoice_number}.png`.replace(/[/\s]+/g, '-');

    const waText = lunas
        ? `Tagihan ${invoice.invoice_number} (${kos_name} ${kos_tagline}) untuk ${invoice.tenant_name ?? '-'} Kamar ${invoice.room_number ?? '-'} sudah LUNAS. Terima kasih 🙏`
        : `Halo ${invoice.tenant_name ?? ''} 🙏\nBerikut tagihan sewa kamar ${invoice.room_number ?? '-'} (${kos_name} ${kos_tagline}):\nNo: ${invoice.invoice_number}\nPeriode: ${tanggal(invoice.period_start)} – ${tanggal(invoice.period_end)}\nTotal: ${rupiah(invoice.amount)}\nSisa: ${rupiah(sisa)}\nJatuh tempo: ${tanggal(invoice.due_date)}\nMohon segera diselesaikan ya. Terima kasih.`;

    return (
        <>
            <Head title={`Tagihan ${invoice.invoice_number}`} />

            <ReceiptActions targetRef={cardRef} filename={filename} waText={waText} />

            <div className="mx-auto max-w-md p-4">
                <div ref={cardRef} className="bg-white p-8 shadow-md print:shadow-none">
                    <ReceiptHeader name={kos_name} tagline={kos_tagline} subtitle="Tagihan Sewa" />

                    {/* No + status */}
                    <div className="mt-4 flex items-center justify-between text-xs">
                        <span className="font-mono text-slate-500">{invoice.invoice_number}</span>
                        <span className={`rounded-full px-2.5 py-0.5 font-semibold ${lunas ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'}`}>
                            {lunas ? 'LUNAS' : invoice.status_label}
                        </span>
                    </div>

                    {/* Detail */}
                    <table className="mt-4 w-full text-sm">
                        <tbody className="divide-y divide-slate-100">
                            <tr>
                                <td className="w-36 py-2 text-slate-500">Penghuni</td>
                                <td className="py-2 font-semibold">{invoice.tenant_name ?? '-'}</td>
                            </tr>
                            <tr>
                                <td className="py-2 text-slate-500">Kamar</td>
                                <td className="py-2 font-semibold">{invoice.room_number ?? '-'}</td>
                            </tr>
                            <tr>
                                <td className="py-2 text-slate-500">Periode</td>
                                <td className="py-2">{tanggal(invoice.period_start)} – {tanggal(invoice.period_end)}</td>
                            </tr>
                            <tr>
                                <td className="py-2 text-slate-500">Jatuh Tempo</td>
                                <td className="py-2 font-semibold">{tanggal(invoice.due_date)}</td>
                            </tr>
                            {invoice.discount > 0 && (
                                <>
                                    <tr>
                                        <td className="py-2 text-slate-500">Harga Sewa</td>
                                        <td className="py-2">{rupiah(invoice.amount + invoice.discount)}</td>
                                    </tr>
                                    <tr>
                                        <td className="py-2 text-slate-500">Diskon</td>
                                        <td className="py-2 text-rose-600">− {rupiah(invoice.discount)}</td>
                                    </tr>
                                </>
                            )}
                            {invoice.paid_amount > 0 && (
                                <tr>
                                    <td className="py-2 text-slate-500">Sudah Dibayar</td>
                                    <td className="py-2 text-emerald-600">{rupiah(invoice.paid_amount)}</td>
                                </tr>
                            )}
                        </tbody>
                    </table>

                    {/* Nominal */}
                    <div className={`mt-5 rounded-xl px-5 py-4 text-center ${lunas ? 'bg-emerald-50' : 'bg-amber-50'}`}>
                        <p className={`text-xs font-medium uppercase tracking-wide ${lunas ? 'text-emerald-600' : 'text-amber-600'}`}>
                            {lunas ? 'Total Tagihan (Lunas)' : 'Total Harus Dibayar'}
                        </p>
                        <p className={`mt-1 text-3xl font-extrabold ${lunas ? 'text-emerald-700' : 'text-amber-700'}`}>
                            {rupiah(lunas ? invoice.amount : sisa)}
                        </p>
                    </div>

                    {/* Tanda tangan */}
                    <div className="mt-8 flex justify-end">
                        <div className="text-center text-xs text-slate-500">
                            <p>Pemilik Kos</p>
                            <div className="my-10" />
                            <p className="border-t border-slate-300 pt-1 font-semibold text-slate-700">{kos_owner}</p>
                        </div>
                    </div>

                    <p className="mt-6 border-t border-dashed border-slate-200 pt-4 text-center text-xs text-slate-400">
                        Mohon selesaikan pembayaran sebelum jatuh tempo. Terima kasih.
                    </p>
                </div>
            </div>

            <style>{`
                @media print {
                    .no-print { display: none !important; }
                    body { background: white; }
                }
            `}</style>
        </>
    );
}
