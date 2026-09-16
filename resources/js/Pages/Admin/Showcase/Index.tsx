import { Head, router, usePage } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { adminNav } from '@/Navigation/adminNav';
import Button from '@/Components/ui/Button';
import SecondaryButton from '@/Components/ui/SecondaryButton';
import { PageProps } from '@/types';

interface Submission {
    id: number;
    title: string;
    author_name: string;
    description: string | null;
    is_published: boolean;
}

export default function Index({ submissions }: { submissions: Submission[] }) {
    const { flash } = usePage<PageProps>().props;
    const pending = submissions.filter((s) => !s.is_published);
    const published = submissions.filter((s) => s.is_published);

    return (
        <AuthenticatedLayout nav={adminNav()} title="Showcase submissions">
            <Head title="Showcase submissions" />

            {flash.status && <p className="mb-4 rounded-md bg-green-50 px-4 py-2.5 text-sm text-green-700">{flash.status}</p>}

            <h2 className="text-sm font-semibold text-ink-900">Pending review</h2>
            <div className="mt-3 space-y-3">
                {pending.map((s) => (
                    <div key={s.id} className="rounded-lg border border-ink-100 bg-white p-4">
                        <p className="text-sm font-medium text-ink-900">{s.title}</p>
                        <p className="text-xs text-ink-500">{s.author_name}</p>
                        {s.description && <p className="mt-2 text-sm text-ink-700">{s.description}</p>}
                        <div className="mt-3 flex gap-3">
                            <Button onClick={() => router.post(route('admin.showcase.approve', s.id))} className="w-auto px-4">Publish</Button>
                            <SecondaryButton onClick={() => router.post(route('admin.showcase.reject', s.id))}>Keep unpublished</SecondaryButton>
                        </div>
                    </div>
                ))}
                {pending.length === 0 && <p className="text-sm text-ink-500">Nothing waiting on review.</p>}
            </div>

            {published.length > 0 && (
                <>
                    <h2 className="mt-8 text-sm font-semibold text-ink-900">Published</h2>
                    <div className="mt-3 space-y-2">
                        {published.map((s) => (
                            <div key={s.id} className="rounded-lg border border-ink-100 bg-white p-3 flex items-center justify-between">
                                <p className="text-sm text-ink-900">{s.title}</p>
                                <p className="text-xs text-ink-500">{s.author_name}</p>
                            </div>
                        ))}
                    </div>
                </>
            )}
        </AuthenticatedLayout>
    );
}
