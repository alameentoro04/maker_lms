import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { adminNav } from '@/Navigation/adminNav';

interface CohortRow {
    id: number;
    name: string;
    course: string;
    status: string;
    start_date: string;
    capacity: number;
    enrolled: number;
}

export default function Index({ cohorts }: { cohorts: CohortRow[] }) {
    return (
        <AuthenticatedLayout nav={adminNav()} title="Cohorts">
            <Head title="Cohorts" />

            <div className="flex justify-end mb-4">
                <Link href={route('admin.cohorts.create')} className="rounded-md bg-ink-900 px-4 py-2 text-sm font-medium text-white hover:bg-ink-700">
                    New cohort
                </Link>
            </div>

            <div className="rounded-lg border border-ink-100 bg-white overflow-hidden">
                <table className="w-full text-sm">
                    <thead className="bg-ink-50 text-left text-ink-500">
                        <tr>
                            <th className="px-5 py-3 font-medium">Cohort</th>
                            <th className="px-5 py-3 font-medium">Course</th>
                            <th className="px-5 py-3 font-medium">Status</th>
                            <th className="px-5 py-3 font-medium">Starts</th>
                            <th className="px-5 py-3 font-medium">Enrolled</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-ink-100">
                        {cohorts.map((cohort) => (
                            <tr key={cohort.id} className="hover:bg-ink-50">
                                <td className="px-5 py-3">
                                    <Link href={route('admin.cohorts.edit', cohort.id)} className="font-medium text-ink-900 hover:text-gold-600">
                                        {cohort.name}
                                    </Link>
                                </td>
                                <td className="px-5 py-3 text-ink-500">{cohort.course}</td>
                                <td className="px-5 py-3 text-ink-500">{cohort.status}</td>
                                <td className="px-5 py-3 text-ink-500">{cohort.start_date}</td>
                                <td className="px-5 py-3 text-ink-500">{cohort.enrolled} / {cohort.capacity}</td>
                            </tr>
                        ))}
                        {cohorts.length === 0 && (
                            <tr><td colSpan={5} className="px-5 py-8 text-center text-ink-500">No cohorts yet.</td></tr>
                        )}
                    </tbody>
                </table>
            </div>
        </AuthenticatedLayout>
    );
}
