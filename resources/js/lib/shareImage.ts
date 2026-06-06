import { toPng } from 'html-to-image';

/** Render sebuah elemen DOM jadi PNG (resolusi 2x biar tajam). */
async function nodeToBlob(node: HTMLElement): Promise<Blob> {
    const dataUrl = await toPng(node, {
        pixelRatio: 2,
        backgroundColor: '#ffffff',
        cacheBust: true,
    });
    const res = await fetch(dataUrl);
    return res.blob();
}

/** Simpan gambar ke perangkat (download PNG). */
export async function saveImage(node: HTMLElement, filename: string): Promise<void> {
    const blob = await nodeToBlob(node);
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    a.remove();
    URL.revokeObjectURL(url);
}

/**
 * Bagikan gambar langsung (mobile: muncul pilihan WhatsApp lewat Web Share API).
 * Kalau perangkat tak mendukung share file, fallback: download gambar + buka WhatsApp teks.
 */
export async function shareImage(node: HTMLElement, filename: string, text: string): Promise<void> {
    const blob = await nodeToBlob(node);
    const file = new File([blob], filename, { type: 'image/png' });
    const nav = navigator as Navigator & { canShare?: (d: ShareData) => boolean };

    if (nav.canShare && nav.canShare({ files: [file] })) {
        try {
            await nav.share({ files: [file], text });
            return;
        } catch {
            // user batal / gagal — lanjut ke fallback.
        }
    }

    // Fallback: simpan gambar lalu buka WhatsApp (user tinggal lampirkan gambarnya).
    await saveImage(node, filename);
    window.open(`https://wa.me/?text=${encodeURIComponent(text)}`, '_blank');
}
