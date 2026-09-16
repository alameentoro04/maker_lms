import { Head, router, usePage } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { adminNav } from '@/Navigation/adminNav';
import Button from '@/Components/ui/Button';
import SecondaryButton from '@/Components/ui/SecondaryButton';
import { PageProps } from '@/types';

interface Report {
    id: number;
    reason: string;
    reported_by: string;
    content_type: string;
    content_body: string | null;
    content_author: string | null;
    content_hidden: boolean;
}

export default function Index({ reports }: { reports: Report[] }) {
    const { flash } = usePage<PageProps>().props;

    return (
        <AuthenticatedLayout nav={adminNav()} title="Moderation">
            <Head title="Moderation" />

            {flash.status && <p className="mb-4 rounded-md bg-green-50 px-4 py-2.5 text-sm text-green-700">{flash.status}</p>}

            <div className="space-y-4">
                {reports.map((r) => (
                    <div key={r.id} className="rounded-lg border border-ink-100 bg-white p-4">
                        <p className="text-xs text-ink-500">
                            {r.content_type} by {r.content_author} — reported by {r.reported_by}
                        </p>
                        <p className="mt-1 text-sm font-medium text-red-600">Reason: {r.reason}</p>
                        <p className="mt-2 text-sm text-ink-700 rounded-md bg-ink-50 p-3">{r.content_body}</p>
                        {!r.content_hidden && (
                            <div className="mt-3 flex gap-3">
                                <Button onClick={() => router.post(route('admin.moderation.hide', r.id))} className="w-auto px-4">Hide content</Button>
                                <SecondaryButton onClick={() => router.post(route('admin.moderation.dismiss', r.id))}>Dismiss report</SecondaryButton>
                            </div>
                        )}
                        {r.content_hidden && <p className="mt-2 text-xs text-ink-500">Already hidden.</p>}
                    </div>
                ))}
                {reports.length === 0 && <p className="text-sm text-ink-500">No pending reports.</p>}
            </div>
        </AuthenticatedLayout>
    );
}
