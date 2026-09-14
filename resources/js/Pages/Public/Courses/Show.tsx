import { Head, Link } from '@inertiajs/react';
import PublicLayout from '@/Layouts/PublicLayout';

interface CourseShowProps {
    course: {
        title: string;
        slug: string;
        summary: string;
        description: string;
        objectives: string[];
        requirements: string[];
        level: string;
        duration_weeks: number;
        price: string;
    };
    instructors: { id: number; name: string; headline: string | null }[];
    cohorts: {
        name: string;
        slug: string;
        start_date: string;
        end_date: string;
        status: string;
        accepting_enrollment: boolean;
        capacity: number;
    }[];
}

export default function Show({ course, instructors, cohorts }: CourseShowProps) {
    return (
        <PublicLayout>
            <Head title={course.title} />

            <div className="mx-auto max-w-6xl px-6 py-14 grid lg:grid-cols-[1fr_320px] gap-12">
                <div>
                    <p className="text-xs font-medium text-gold-600">{course.level} · {course.duration_weeks} weeks</p>
                    <h1 className="mt-1 font-display text-3xl text-ink-900">{course.title}</h1>
                    <p className="mt-3 text-ink-500 leading-relaxed">{course.summary}</p>

                    <div className="mt-8 prose prose-sm max-w-none text-ink-700">
                        <p className="leading-relaxed whitespace-pre-line">{course.description}</p>
                    </div>

                    {course.objectives.length > 0 && (
                        <div className="mt-8">
                            <h2 className="text-base font-semibold text-ink-900">What you'll learn</h2>
                            <ul className="mt-3 space-y-2 text-sm text-ink-700">
                                {course.objectives.map((o) => (
                                    <li key={o} className="flex gap-2">
                                        <span className="text-gold-500">—</span> {o}
                                    </li>
                                ))}
                            </ul>
                        </div>
                    )}

                    {course.requirements.length > 0 && (
                        <div className="mt-8">
                            <h2 className="text-base font-semibold text-ink-900">Requirements</h2>
                            <ul className="mt-3 space-y-2 text-sm text-ink-700">
                                {course.requirements.map((r) => (
                                    <li key={r} className="flex gap-2">
                                        <span className="text-gold-500">—</span> {r}
                                    </li>
                                ))}
                            </ul>
                        </div>
                    )}

                    {instructors.length > 0 && (
                        <div className="mt-10">
                            <h2 className="text-base font-semibold text-ink-900">Instructors</h2>
                            <div className="mt-3 flex flex-wrap gap-4">
                                {instructors.map((i) => (
                                    <Link
                                        key={i.id}
                                        href={route('instructors.show', i.id)}
                                        className="rounded-md border border-ink-100 bg-white px-4 py-3 hover:border-gold-400"
                                    >
                                        <p className="text-sm font-medium text-ink-900">{i.name}</p>
                                        {i.headline && <p className="text-xs text-ink-500">{i.headline}</p>}
                                    </Link>
                                ))}
                            </div>
                        </div>
                    )}
                </div>

                <aside className="space-y-4">
                    <div className="rounded-lg border border-ink-100 bg-white p-5">
                        <p className="text-sm text-ink-500">Price</p>
                        <p className="text-xl font-semibold text-ink-900">{course.price}</p>
                    </div>

                    <div>
                        <h2 className="text-sm font-semibold text-ink-900">Upcoming cohorts</h2>
                        {cohorts.length === 0 && (
                            <p className="mt-2 text-sm text-ink-500">No cohorts scheduled yet.</p>
                        )}
                        <div className="mt-3 space-y-3">
                            {cohorts.map((cohort) => (
                                <Link
                                    key={cohort.slug}
                                    href={route('cohorts.show', cohort.slug)}
                                    className="block rounded-lg border border-ink-100 bg-white p-4 hover:border-gold-400"
                                >
                                    <p className="text-xs font-medium text-gold-600">{cohort.status}</p>
                                    <p className="mt-1 text-sm font-medium text-ink-900">{cohort.start_date} – {cohort.end_date}</p>
                                    <p className="mt-1 text-xs text-ink-500">Up to {cohort.capacity} students</p>
                                </Link>
                            ))}
                        </div>
                    </div>
                </aside>
            </div>
        </PublicLayout>
    );
}
