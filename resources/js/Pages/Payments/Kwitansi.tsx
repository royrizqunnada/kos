import { Head } from '@inertiajs/react';
import { useRef } from 'react';
import { rupiah, tanggal } from '@/lib/format';
import ReceiptActions from '@/Components/ReceiptActions';

interface KwitansiPayment {
    id: number;
    amount: number;
    method: string;
    paid_at: string;
    note: string | null;
    recorded_by: string | null;
    invoice: { invoice_number: string; period_start: string; period_end: string; amount: number } | null;
    tenant_name: string | null;
    tenant_phone: string | null;
    room_number: string | null;
}

const METHOD_LABEL: Record<string, string> = {
    cash: 'Tunai',
    transfer: 'Transfer Bank',
    qris: 'QRIS',
};

export default function Kwitansi({ payment, kos_name, kos_owner }: { payment: KwitansiPayment; kos_name: string; kos_owner: string }) {
    const cardRef = useRef<HTMLDivElement>(null);
    const noKwitansi = String(payment.id).padStart(6, '0');
    const filename = `kwitansi-${payment.tenant_name ?? 'penghuni'}-${noKwitansi}.png`.replace(/\s+/g, '-');
    const waText = `Bukti pembayaran ${kos_name}\nPenghuni: ${payment.tenant_name ?? '-'} (Kamar ${payment.room_number ?? '-'})\nJumlah dibayar: ${rupiah(payment.amount)}\nTerima kasih 🙏`;

    return (
        <>
            <Head title={`Kwitansi #${noKwitansi}`} />

            <ReceiptActions targetRef={cardRef} filename={filename} waText={waText} />

            {/* Kwitansi (yang dijadikan gambar) */}
            <div className="mx-auto max-w-md p-4">
                <div ref={cardRef} className="bg-white p-8 shadow-md print:shadow-none">
                    {/* Header */}
                    <div className="border-b-2 border-slate-800 pb-4 text-center">
                        <h1 className="text-lg font-extrabold uppercase tracking-wide text-slate-800">{kos_name}</h1>
                        <p className="mt-1 text-xs font-semibold uppercase tracking-widest text-brand-600">Bukti Pembayaran</p>
                    </div>

                    {/* No kwitansi + tanggal */}
                    <div className="mt-4 flex justify-between text-xs text-slate-500">
                        <span>No. #{noKwitansi}</span>
                        <span>{tanggal(payment.paid_at)}</span>
                    </div>

                    {/* Detail */}
                    <table className="mt-4 w-full text-sm">
                        <tbody className="divide-y divide-slate-100">
                            <tr>
                                <td className="w-36 py-2 text-slate-500">Penghuni</td>
                                <td className="py-2 font-semibold">{payment.tenant_name ?? '-'}</td>
                            </tr>
                            <tr>
                                <td className="py-2 text-slate-500">Kamar</td>
                                <td className="py-2 font-semibold">{payment.room_number ?? '-'}</td>
                            </tr>
                            {payment.invoice && (
                                <>
                                    <tr>
                                        <td className="py-2 text-slate-500">No. Invoice</td>
                                        <td className="py-2 font-mono text-xs">{payment.invoice.invoice_number}</td>
                                    </tr>
                                    <tr>
                                        <td className="py-2 text-slate-500">Periode</td>
                                        <td className="py-2">{tanggal(payment.invoice.period_start)} – {tanggal(payment.invoice.period_end)}</td>
                                    </tr>
                                    <tr>
                                        <td className="py-2 text-slate-500">Total Tagihan</td>
                                        <td className="py-2">{rupiah(payment.invoice.amount)}</td>
                                    </tr>
                                </>
                            )}
                            <tr>
                                <td className="py-2 text-slate-500">Metode</td>
                                <td className="py-2">{METHOD_LABEL[payment.method] ?? payment.method}</td>
                            </tr>
                            {payment.note && (
                                <tr>
                                    <td className="py-2 text-slate-500">Catatan</td>
                                    <td className="py-2">{payment.note}</td>
                                </tr>
                            )}
                        </tbody>
                    </table>

                    {/* Nominal */}
                    <div className="mt-5 rounded-xl bg-brand-50 px-5 py-4 text-center">
                        <p className="text-xs font-medium uppercase tracking-wide text-brand-600">Jumlah Dibayar</p>
                        <p className="mt-1 text-3xl font-extrabold text-brand-700">{rupiah(payment.amount)}</p>
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
                        Kwitansi ini sah sebagai bukti pembayaran resmi.
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
