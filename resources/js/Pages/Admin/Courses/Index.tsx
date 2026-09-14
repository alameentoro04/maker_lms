import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { adminNav } from '@/Navigation/adminNav';

interface CourseRow {
    id: number;
    title: string;
    slug: string;
    status: string;
    category: string | null;
    cohorts_count: number;
}

export default function Index({ courses }: { courses: CourseRow[] }) {
    return (
        <AuthenticatedLayout nav={adminNav()} title="Courses">
            <Head title="Courses" />

            <div className="flex justify-end mb-4">
                <Link
                    href={route('admin.courses.create')}
                    className="rounded-md bg-ink-900 px-4 py-2 text-sm font-medium text-white hover:bg-ink-700"
                >
                    New course
                </Link>
            </div>

            <div className="rounded-lg border border-ink-100 bg-white overflow-hidden">
                <table className="w-full text-sm">
                    <thead className="bg-ink-50 text-left text-ink-500">
                        <tr>
                            <th className="px-5 py-3 font-medium">Title</th>
                            <th className="px-5 py-3 font-medium">Category</th>
                            <th className="px-5 py-3 font-medium">Status</th>
                            <th className="px-5 py-3 font-medium">Cohorts</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-ink-100">
                        {courses.map((course) => (
                            <tr key={course.id} className="hover:bg-ink-50">
                                <td className="px-5 py-3">
                                    <Link href={route('admin.courses.edit', course.id)} className="font-medium text-ink-900 hover:text-gold-600">
                                        {course.title}
                                    </Link>
                                </td>
                                <td className="px-5 py-3 text-ink-500">{course.category ?? '—'}</td>
                                <td className="px-5 py-3">
                                    <span className={`rounded-full px-2 py-0.5 text-xs font-medium ${
                                        course.status === 'published' ? 'bg-green-50 text-green-700' : 'bg-ink-100 text-ink-500'
                                    }`}>
                                        {course.status}
                                    </span>
                                </td>
                                <td className="px-5 py-3 text-ink-500">{course.cohorts_count}</td>
                            </tr>
                        ))}
                        {courses.length === 0 && (
                            <tr><td colSpan={4} className="px-5 py-8 text-center text-ink-500">No courses yet.</td></tr>
                        )}
                    </tbody>
                </table>
            </div>
        </AuthenticatedLayout>
    );
}
