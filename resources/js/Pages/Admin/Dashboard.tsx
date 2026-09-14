import { Head } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

const nav = [
    { label: 'Dashboard', href: route('admin.dashboard') },
];

export default function Dashboard() {
    return (
        <AuthenticatedLayout nav={nav} title="Admin dashboard">
            <Head title="Admin dashboard" />
            <div className="rounded-lg border border-dashed border-ink-300 bg-white p-8 text-center text-ink-500">
                Platform metrics (students, revenue, pending payments, cohorts) land here
                once courses, cohorts, and payments exist in Phase 3–5.
            </div>
        </AuthenticatedLayout>
    );
}
