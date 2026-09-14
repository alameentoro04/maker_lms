import { Head } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

const nav = [
    { label: 'Dashboard', href: route('instructor.dashboard') },
];

export default function Dashboard() {
    return (
        <AuthenticatedLayout nav={nav} title="Instructor dashboard">
            <Head title="Instructor dashboard" />
            <div className="rounded-lg border border-dashed border-ink-300 bg-white p-8 text-center text-ink-500">
                Your courses, cohorts, and students will appear here starting Phase 3.
            </div>
        </AuthenticatedLayout>
    );
}
