import { RefObject, useState } from 'react';
import { saveImage, shareImage } from '@/lib/shareImage';

/** Tombol aksi untuk kwitansi/tagihan: kirim gambar ke WA, simpan gambar, cetak, kembali. */
export default function ReceiptActions({
    targetRef,
    filename,
    waText,
}: {
    targetRef: RefObject<HTMLElement | null>;
    filename: string;
    waText: string;
}) {
    const [busy, setBusy] = useState<null | 'share' | 'save'>(null);

    const run = async (mode: 'share' | 'save') => {
        if (!targetRef.current) return;
        setBusy(mode);
        try {
            if (mode === 'share') await shareImage(targetRef.current, filename, waText);
            else await saveImage(targetRef.current, filename);
        } catch {
            alert('Gagal membuat gambar. Coba lagi atau pakai tombol Cetak.');
        } finally {
            setBusy(null);
        }
    };

    return (
        <div className="no-print flex flex-wrap justify-center gap-2 bg-slate-100 p-4">
            <button
                onClick={() => run('share')}
                disabled={busy !== null}
                className="rounded-xl bg-green-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-green-700 disabled:opacity-50"
            >
                {busy === 'share' ? 'Menyiapkan…' : '📲 Kirim ke WhatsApp'}
            </button>
            <button
                onClick={() => run('save')}
                disabled={busy !== null}
                className="rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-700 disabled:opacity-50"
            >
                {busy === 'save' ? 'Menyiapkan…' : '🖼️ Simpan Gambar'}
            </button>
            <button
                onClick={() => window.print()}
                className="rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50"
            >
                🖨️ Cetak
            </button>
            <button
                onClick={() => window.history.back()}
                className="rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50"
            >
                Kembali
            </button>
        </div>
    );
}
