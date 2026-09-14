import { Head } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

const nav = [
    { label: 'Dashboard', href: route('student.dashboard') },
];

export default function Dashboard() {
    return (
        <AuthenticatedLayout nav={nav} title="Welcome">
            <Head title="Dashboard" />
            <div className="rounded-lg border border-dashed border-ink-300 bg-white p-8 text-center text-ink-500">
                Once you enroll in the Graphic Design cohort, your course, progress, and
                schedule will show up here (Phase 4).
            </div>
        </AuthenticatedLayout>
    );
}
