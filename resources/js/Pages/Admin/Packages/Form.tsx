import { FormEventHandler, useEffect } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { adminNav } from '@/Navigation/adminNav';
import TextField from '@/Components/ui/TextField';
import Textarea from '@/Components/ui/Textarea';
import Button from '@/Components/ui/Button';

interface PackageData {
    id: number;
    title: string;
    slug: string;
    description: string | null;
    price: number;
    currency: string;
    is_published: boolean;
    cohort_ids: number[];
}

function slugify(v: string) { return v.toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, ''); }

export default function Form({ package: pkg, cohorts }: { package: PackageData | null; cohorts: { id: number; label: string }[] }) {
    const isEditing = !!pkg;
    const { data, setData, post, put, processing, errors } = useForm({
        title: pkg?.title ?? '', slug: pkg?.slug ?? '', description: pkg?.description ?? '',
        price: pkg?.price ?? 0, currency: pkg?.currency ?? 'NGN', is_published: pkg?.is_published ?? false,
        cohort_ids: pkg?.cohort_ids ?? [] as number[],
    });

    useEffect(() => { if (!isEditing) setData('slug', slugify(data.title)); /* eslint-disable-next-line */ }, [data.title]);

    const toggleCohort = (id: number) => {
        setData('cohort_ids', data.cohort_ids.includes(id) ? data.cohort_ids.filter((c) => c !== id) : [...data.cohort_ids, id]);
    };

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        if (isEditing) put(route('admin.packages.update', pkg!.id));
        else post(route('admin.packages.store'));
    };

    return (
        <AuthenticatedLayout nav={adminNav()} title={isEditing ? pkg!.title : 'New package'}>
            <Head title={isEditing ? pkg!.title : 'New package'} />
            <Link href={route('admin.packages.index')} className="text-sm text-ink-500 hover:text-gold-600">← All packages</Link>

            <form onSubmit={submit} className="mt-4 max-w-xl space-y-4">
                <TextField label="Title" name="title" value={data.title} onChange={(e) => setData('title', e.target.value)} error={errors.title} />
                <TextField label="Slug" name="slug" value={data.slug} onChange={(e) => setData('slug', e.target.value)} error={errors.slug} />
                <Textarea label="Description" name="description" rows={3} value={data.description ?? ''} onChange={(e) => setData('description', e.target.value)} error={errors.description} />
                <div className="grid grid-cols-2 gap-3">
                    <TextField label="Price (minor units)" name="price" type="number" value={data.price} onChange={(e) => setData('price', Number(e.target.value))} error={errors.price} />
                    <TextField label="Currency" name="currency" value={data.currency} onChange={(e) => setData('currency', e.target.value.toUpperCase())} error={errors.currency} />
                </div>
                <label className="flex items-center gap-2 text-sm text-ink-700">
                    <input type="checkbox" checked={data.is_published} onChange={(e) => setData('is_published', e.target.checked)} />
                    Published
                </label>

                <div>
                    <p className="text-sm font-medium text-ink-900">Included cohorts (at least 2)</p>
                    <div className="mt-2 space-y-1.5 max-h-64 overflow-y-auto">
                        {cohorts.map((c) => (
                            <label key={c.id} className="flex items-center gap-2 text-sm text-ink-700">
                                <input type="checkbox" checked={data.cohort_ids.includes(c.id)} onChange={() => toggleCohort(c.id)} />
                                {c.label}
                            </label>
                        ))}
                    </div>
                    {errors.cohort_ids && <p className="mt-1.5 text-sm text-red-600">{errors.cohort_ids}</p>}
                </div>

                <Button type="submit" loading={processing}>{isEditing ? 'Save changes' : 'Create package'}</Button>
            </form>
        </AuthenticatedLayout>
    );
}
