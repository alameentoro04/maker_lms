import { Head } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { instructorNav } from '@/Navigation/instructorNav';

export default function Dashboard() {
    return (
        <AuthenticatedLayout nav={instructorNav()} title="Instructor dashboard">
            <Head title="Instructor dashboard" />
            <div className="rounded-lg border border-dashed border-ink-300 bg-white p-8 text-center text-ink-500">
                Manage your courses' curriculum from "My courses", and grade student work
                from "Assignments to grade".
            </div>
        </AuthenticatedLayout>
    );
}
