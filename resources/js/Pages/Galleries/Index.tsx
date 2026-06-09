import { Head, router, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Button, Card, Field, Input, PageHeader, Select } from '@/Components/ui';
import { FormEvent } from 'react';
import { Trash2, ImagePlus } from 'lucide-react';

interface Photo { id: number; category: string; caption: string | null; path: string }
interface Opt { value: string; label: string }

export default function Index({ photos, categories }: { photos: Photo[]; categories: Opt[] }) {
    const { data, setData, post, processing, errors, reset } = useForm<{ category: string; caption: string; photos: File[] }>({
        category: 'kos', caption: '', photos: [],
    });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        post('/galleries', { forceFormData: true, onSuccess: () => reset('caption', 'photos') });
    };
    const hapus = (p: Photo) => {
        if (confirm('Hapus foto ini?')) router.delete(`/galleries/${p.id}`);
    };

    const grouped = categories.map((c) => ({ ...c, items: photos.filter((p) => p.category === c.value) }));
    const fileErr = (errors as Record<string, string>)['photos.0'] ?? errors.photos;

    return (
        <AuthenticatedLayout>
            <Head title="Galeri" />
            <PageHeader title="Galeri Foto" subtitle="Foto kos & fasilitas yang tampil di web profil publik (cozycornerliving.id)" />
            <div className="grid gap-6 lg:grid-cols-3">
                <Card className="p-6 lg:col-span-1">
                    <h2 className="mb-4 text-sm font-semibold text-slate-700">Upload Foto</h2>
                    <form onSubmit={submit} className="space-y-4">
                        <Field label="Kategori" error={errors.category}>
                            <Select value={data.category} onChange={(e) => setData('category', e.target.value)}>
                                {categories.map((c) => <option key={c.value} value={c.value}>{c.label}</option>)}
                            </Select>
                        </Field>
                        <Field label="Keterangan (opsional)" error={errors.caption}>
                            <Input value={data.caption} onChange={(e) => setData('caption', e.target.value)} placeholder="mis. Kamar Tipe A / Dapur bersama" />
                        </Field>
                        <Field label="Foto (boleh pilih banyak sekaligus)" error={fileErr}>
                            <Input type="file" accept="image/*" multiple onChange={(e) => setData('photos', Array.from(e.target.files ?? []))} />
                        </Field>
                        <Button type="submit" disabled={processing || data.photos.length === 0} className="w-full"><ImagePlus size={16} /> {processing ? 'Mengupload…' : 'Upload'}</Button>
                        <p className="text-xs text-slate-400">Maksimal 5 MB per foto. Format gambar (JPG/PNG).</p>
                    </form>
                </Card>

                <div className="space-y-6 lg:col-span-2">
                    {grouped.map((g) => (
                        <Card key={g.value} className="p-5">
                            <h3 className="mb-3 text-sm font-semibold text-slate-700">{g.label} <span className="text-slate-400">({g.items.length})</span></h3>
                            {g.items.length === 0 ? (
                                <p className="text-sm text-slate-400">Belum ada foto. Upload lewat form di kiri.</p>
                            ) : (
                                <div className="grid grid-cols-2 gap-3 sm:grid-cols-3">
                                    {g.items.map((p) => (
                                        <div key={p.id} className="group relative overflow-hidden rounded-xl border border-slate-100">
                                            <img src={`/storage/${p.path}`} alt={p.caption ?? ''} className="aspect-square w-full object-cover" />
                                            {p.caption && <div className="absolute inset-x-0 bottom-0 truncate bg-black/50 px-2 py-1 text-xs text-white">{p.caption}</div>}
                                            <button onClick={() => hapus(p)} className="absolute right-1.5 top-1.5 grid h-7 w-7 place-items-center rounded-lg bg-white/90 text-rose-600 shadow active:bg-rose-50"><Trash2 size={14} /></button>
                                        </div>
                                    ))}
                                </div>
                            )}
                        </Card>
                    ))}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
