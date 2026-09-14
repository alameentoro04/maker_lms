import { Head, Link } from '@inertiajs/react';
import PublicLayout from '@/Layouts/PublicLayout';

interface CourseCard {
    title: string;
    slug: string;
    summary: string;
    level: string;
    duration_weeks: number;
    price: string;
    category: string | null;
}

export default function Index({ courses }: { courses: CourseCard[]; categories: { name: string; slug: string }[] }) {
    return (
        <PublicLayout>
            <Head title="Courses" />

            <div className="mx-auto max-w-6xl px-6 py-14">
                <h1 className="font-display text-3xl text-ink-900">Courses</h1>
                <p className="mt-2 text-ink-500 max-w-xl">
                    Every course runs as a scheduled cohort — you learn alongside the same
                    group from start to finish, not on your own timeline.
                </p>

                {courses.length === 0 ? (
                    <p className="mt-10 text-ink-500">No published courses yet — check back soon.</p>
                ) : (
                    <div className="mt-10 grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        {courses.map((course) => (
                            <Link
                                key={course.slug}
                                href={route('courses.show', course.slug)}
                                className="rounded-lg border border-ink-100 bg-white p-6 hover:border-gold-400 transition"
                            >
                                {course.category && (
                                    <p className="text-xs font-medium text-gold-600">{course.category}</p>
                                )}
                                <h2 className="mt-1 text-lg font-semibold text-ink-900">{course.title}</h2>
                                <p className="mt-2 text-sm text-ink-500 leading-relaxed">{course.summary}</p>
                                <div className="mt-4 flex items-center justify-between text-sm text-ink-500">
                                    <span>{course.duration_weeks} weeks · {course.level}</span>
                                    <span className="font-medium text-ink-900">{course.price}</span>
                                </div>
                            </Link>
                        ))}
                    </div>
                )}
            </div>
        </PublicLayout>
    );
}
