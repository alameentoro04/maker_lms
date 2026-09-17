import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

interface Bookmark {
    course_title: string;
    course_slug: string;
    summary: string;
}

export default function Index({ bookmarks }: { bookmarks: Bookmark[] }) {
    const nav = [{ label: 'Dashboard', href: route('student.dashboard') }];

    return (
        <AuthenticatedLayout nav={nav} title="Bookmarks">
            <Head title="Bookmarks" />

            <div className="space-y-3">
                {bookmarks.map((b) => (
                    <Link key={b.course_slug} href={route('courses.show', b.course_slug)} className="block rounded-lg border border-ink-100 bg-white p-4 hover:border-gold-400">
                        <p className="text-sm font-medium text-ink-900">{b.course_title}</p>
                        <p className="text-xs text-ink-500">{b.summary}</p>
                    </Link>
                ))}
                {bookmarks.length === 0 && <p className="text-sm text-ink-500">No bookmarks yet.</p>}
            </div>
        </AuthenticatedLayout>
    );
}
