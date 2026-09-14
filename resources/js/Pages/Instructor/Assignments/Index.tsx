import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { instructorNav } from '@/Navigation/instructorNav';

interface AssignmentRow {
    id: number;
    lesson_title: string;
    course_title: string;
    due_at: string | null;
    submissions_count: number;
    ungraded_count: number;
}

export default function Index({ assignments }: { assignments: AssignmentRow[] }) {
    return (
        <AuthenticatedLayout nav={instructorNav()} title="Assignments to grade">
            <Head title="Assignments" />

            <div className="rounded-lg border border-ink-100 bg-white overflow-hidden">
                <table className="w-full text-sm">
                    <thead className="bg-ink-50 text-left text-ink-500">
                        <tr>
                            <th className="px-5 py-3 font-medium">Assignment</th>
                            <th className="px-5 py-3 font-medium">Course</th>
                            <th className="px-5 py-3 font-medium">Due</th>
                            <th className="px-5 py-3 font-medium">Submissions</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-ink-100">
                        {assignments.map((a) => (
                            <tr key={a.id} className="hover:bg-ink-50">
                                <td className="px-5 py-3">
                                    <Link href={route('instructor.assignments.submissions', a.id)} className="font-medium text-ink-900 hover:text-gold-600">
                                        {a.lesson_title}
                                    </Link>
                                </td>
                                <td className="px-5 py-3 text-ink-500">{a.course_title}</td>
                                <td className="px-5 py-3 text-ink-500">{a.due_at ?? '—'}</td>
                                <td className="px-5 py-3 text-ink-500">
                                    {a.submissions_count} total
                                    {a.ungraded_count > 0 && (
                                        <span className="ml-2 rounded-full bg-gold-50 px-2 py-0.5 text-xs font-medium text-gold-600">
                                            {a.ungraded_count} to grade
                                        </span>
                                    )}
                                </td>
                            </tr>
                        ))}
                        {assignments.length === 0 && (
                            <tr><td colSpan={4} className="px-5 py-8 text-center text-ink-500">No assignments configured yet.</td></tr>
                        )}
                    </tbody>
                </table>
            </div>
        </AuthenticatedLayout>
    );
}
