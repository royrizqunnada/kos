/** Header kwitansi/tagihan: logo + nama kos + tagline + jenis dokumen. */
export default function ReceiptHeader({ name, tagline, subtitle }: { name: string; tagline: string; subtitle: string }) {
    return (
        <div className="border-b-2 border-slate-800 pb-4 text-center">
            <img src="/logo.png" alt={name} className="mx-auto mb-2 h-16 w-16 object-contain" />
            <h1 className="text-2xl font-extrabold leading-tight tracking-tight text-slate-800">{name}</h1>
            {tagline && <p className="mt-0.5 text-[11px] font-medium uppercase tracking-[0.18em] text-slate-500">{tagline}</p>}
            <p className="mt-3 inline-block rounded-full bg-brand-50 px-3 py-1 text-[11px] font-bold uppercase tracking-widest text-brand-600">
                {subtitle}
            </p>
        </div>
    );
}
