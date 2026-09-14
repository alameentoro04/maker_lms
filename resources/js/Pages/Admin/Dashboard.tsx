import { Head } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { adminNav } from '@/Navigation/adminNav';

export default function Dashboard() {
    return (
        <AuthenticatedLayout nav={adminNav()} title="Admin dashboard">
            <Head title="Admin dashboard" />
            <div className="rounded-lg border border-dashed border-ink-300 bg-white p-8 text-center text-ink-500">
                Revenue, enrollment, and submission metrics land here once Payments (Phase 5)
                and Learning (Phase 4) exist. Courses, cohorts, and enrollments are manageable now — see the nav.
            </div>
        </AuthenticatedLayout>
    );
}
