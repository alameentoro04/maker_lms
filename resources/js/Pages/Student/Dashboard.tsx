import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

interface EnrollmentCard {
    course_title: string;
    course_slug: string;
    cohort_name: string | null;
    status: string;
    progress_percent: number;
    total_lessons: number;
    completed_lessons: number;
    next_lesson_title: string | null;
}

const nav = [
    { label: 'Dashboard', href: route('student.dashboard') },
    { label: 'Showcase', href: route('student.showcase.index') },
];

export default function Dashboard({ enrollments }: { enrollments: EnrollmentCard[] }) {
    return (
        <AuthenticatedLayout nav={nav} title="Your courses">
            <Head title="Dashboard" />

            {enrollments.length === 0 ? (
                <div className="rounded-lg border border-dashed border-ink-300 bg-white p-8 text-center text-ink-500">
                    You're not enrolled in anything yet. Browse the{' '}
                    <Link href={route('courses.index')} className="text-ink-900 underline hover:text-gold-600">course catalog</Link>{' '}
                    or contact us to join a cohort.
                </div>
            ) : (
                <div className="grid sm:grid-cols-2 gap-5">
                    {enrollments.map((e) => (
                        <div key={e.course_slug} className="rounded-lg border border-ink-100 bg-white p-5">
                            <p className="text-xs font-medium text-gold-600">{e.cohort_name ?? 'Self-paced'}</p>
                            <h2 className="mt-1 text-lg font-semibold text-ink-900">{e.course_title}</h2>

                            <div className="mt-4">
                                <div className="flex justify-between text-xs text-ink-500">
                                    <span>{e.completed_lessons} / {e.total_lessons} lessons</span>
                                    <span>{e.progress_percent}%</span>
                                </div>
                                <div className="mt-1.5 h-2 rounded-full bg-ink-100 overflow-hidden">
                                    <div className="h-full bg-gold-500" style={{ width: `${e.progress_percent}%` }} />
                                </div>
                            </div>

                            {e.next_lesson_title && (
                                <p className="mt-3 text-sm text-ink-500">Next: {e.next_lesson_title}</p>
                            )}

                            <Link
                                href={route('learn.show', e.course_slug)}
                                className="mt-4 block text-center rounded-md bg-ink-900 px-4 py-2.5 text-sm font-medium text-white hover:bg-ink-700"
                            >
                                Continue learning
                            </Link>
                        </div>
                    ))}
                </div>
            )}
        </AuthenticatedLayout>
    );
}
