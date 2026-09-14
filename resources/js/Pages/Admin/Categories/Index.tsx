import { FormEventHandler } from 'react';
import { Head, router, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { adminNav } from '@/Navigation/adminNav';
import TextField from '@/Components/ui/TextField';
import Button from '@/Components/ui/Button';

interface Category {
    id: number;
    name: string;
    slug: string;
    courses_count: number;
}

export default function Index({ categories }: { categories: Category[] }) {
    const { data, setData, post, processing, errors, reset } = useForm({ name: '' });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('admin.categories.store'), { onSuccess: () => reset() });
    };

    return (
        <AuthenticatedLayout nav={adminNav()} title="Categories">
            <Head title="Categories" />

            <div className="grid lg:grid-cols-[1fr_320px] gap-8">
                <div className="rounded-lg border border-ink-100 bg-white divide-y divide-ink-100">
                    {categories.length === 0 && <p className="p-6 text-sm text-ink-500">No categories yet.</p>}
                    {categories.map((c) => (
                        <div key={c.id} className="flex items-center justify-between px-5 py-3">
                            <div>
                                <p className="text-sm font-medium text-ink-900">{c.name}</p>
                                <p className="text-xs text-ink-500">{c.courses_count} course(s)</p>
                            </div>
                            <button
                                onClick={() => {
                                    if (confirm(`Delete "${c.name}"?`)) {
                                        router.delete(route('admin.categories.destroy', c.id));
                                    }
                                }}
                                className="text-sm text-red-600 hover:text-red-700"
                                disabled={c.courses_count > 0}
                                title={c.courses_count > 0 ? 'Move or delete its courses first' : 'Delete'}
                            >
                                Delete
                            </button>
                        </div>
                    ))}
                </div>

                <form onSubmit={submit} className="rounded-lg border border-ink-100 bg-white p-5 space-y-4 h-fit">
                    <h2 className="text-sm font-semibold text-ink-900">Add category</h2>
                    <TextField
                        label="Name"
                        name="name"
                        value={data.name}
                        onChange={(e) => setData('name', e.target.value)}
                        error={errors.name}
                    />
                    <Button type="submit" loading={processing}>Add</Button>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}
