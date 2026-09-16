import { FormEventHandler } from 'react';
import { Head, useForm, usePage } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import TextField from '@/Components/ui/TextField';
import Textarea from '@/Components/ui/Textarea';
import Button from '@/Components/ui/Button';
import { PageProps } from '@/types';

interface MyShowcase {
    id: number;
    title: string;
    is_published: boolean;
}

export default function Index({ mine }: { mine: MyShowcase[] }) {
    const { flash } = usePage<PageProps>().props;
    const nav = [{ label: 'Dashboard', href: route('student.dashboard') }];
    const form = useForm({
        title: '', description: '', external_url: '', image: null as File | null,
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        form.post(route('student.showcase.store'), { forceFormData: true, onSuccess: () => form.reset() });
    };

    return (
        <AuthenticatedLayout nav={nav} title="Project showcase">
            <Head title="Showcase" />

            {flash.status && <p className="mb-4 rounded-md bg-green-50 px-4 py-2.5 text-sm text-green-700">{flash.status}</p>}

            <div className="grid lg:grid-cols-[1fr_360px] gap-8">
                <form onSubmit={submit} className="rounded-lg border border-ink-100 bg-white p-5 space-y-4" encType="multipart/form-data">
                    <h2 className="text-sm font-semibold text-ink-900">Submit your work</h2>
                    <TextField label="Title" name="title" value={form.data.title} onChange={(e) => form.setData('title', e.target.value)} error={form.errors.title} />
                    <Textarea label="Description (optional)" name="description" rows={4} value={form.data.description} onChange={(e) => form.setData('description', e.target.value)} error={form.errors.description} />
                    <TextField label="Link (optional)" name="external_url" value={form.data.external_url} onChange={(e) => form.setData('external_url', e.target.value)} error={form.errors.external_url} />
                    <div>
                        <label className="block text-sm font-medium text-ink-900">Image (optional)</label>
                        <input type="file" accept="image/*" onChange={(e) => form.setData('image', e.target.files?.[0] ?? null)} className="mt-1.5 block w-full text-sm" />
                        {form.errors.image && <p className="mt-1.5 text-sm text-red-600">{form.errors.image}</p>}
                    </div>
                    <Button type="submit" loading={form.processing}>Submit for review</Button>
                </form>

                <div>
                    <h2 className="text-sm font-semibold text-ink-900">My submissions</h2>
                    <div className="mt-3 space-y-2">
                        {mine.map((s) => (
                            <div key={s.id} className="rounded-lg border border-ink-100 bg-white p-3 flex items-center justify-between">
                                <p className="text-sm text-ink-900">{s.title}</p>
                                <span className={`text-xs font-medium ${s.is_published ? 'text-green-700' : 'text-ink-500'}`}>
                                    {s.is_published ? 'Published' : 'Pending review'}
                                </span>
                            </div>
                        ))}
                        {mine.length === 0 && <p className="text-sm text-ink-500">Nothing submitted yet.</p>}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
