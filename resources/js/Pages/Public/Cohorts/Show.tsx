import { Head, Link, usePage } from '@inertiajs/react';
import PublicLayout from '@/Layouts/PublicLayout';
import { PageProps } from '@/types';

interface CohortShowProps {
    cohort: {
        slug: string;
        name: string;
        start_date: string;
        end_date: string;
        status: string;
        accepting_enrollment: boolean;
        capacity: number;
        live_platform: string;
        learning_model: string;
    };
    course: { title: string; slug: string; price: string };
    instructors: { name: string; headline: string | null }[];
}

export default function Show({ cohort, course, instructors }: CohortShowProps) {
    const { auth } = usePage<PageProps>().props;
    return (
        <PublicLayout>
            <Head title={cohort.name} />

            <div className="mx-auto max-w-3xl px-6 py-14">
                <Link href={route('courses.show', course.slug)} className="text-sm text-ink-500 hover:text-gold-600">
                    ← {course.title}
                </Link>

                <h1 className="mt-3 font-display text-3xl text-ink-900">{cohort.name}</h1>
                <p className="mt-2 text-sm font-medium text-gold-600">{cohort.status}</p>

                <dl className="mt-8 grid sm:grid-cols-2 gap-6 text-sm">
                    <div>
                        <dt className="text-ink-500">Dates</dt>
                        <dd className="mt-1 font-medium text-ink-900">{cohort.start_date} – {cohort.end_date}</dd>
                    </div>
                    <div>
                        <dt className="text-ink-500">Cohort size</dt>
                        <dd className="mt-1 font-medium text-ink-900">Up to {cohort.capacity} students</dd>
                    </div>
                    <div>
                        <dt className="text-ink-500">Learning model</dt>
                        <dd className="mt-1 font-medium text-ink-900 capitalize">{cohort.learning_model}</dd>
                    </div>
                    <div>
                        <dt className="text-ink-500">Live classes</dt>
                        <dd className="mt-1 font-medium text-ink-900 capitalize">{cohort.live_platform.replace('_', ' ')}</dd>
                    </div>
                    <div>
                        <dt className="text-ink-500">Price</dt>
                        <dd className="mt-1 font-medium text-ink-900">{course.price}</dd>
                    </div>
                </dl>

                {instructors.length > 0 && (
                    <div className="mt-8">
                        <h2 className="text-sm font-semibold text-ink-900">Taught by</h2>
                        <div className="mt-2 space-y-1">
                            {instructors.map((i) => (
                                <p key={i.name} className="text-sm text-ink-700">
                                    {i.name}{i.headline && <span className="text-ink-500"> — {i.headline}</span>}
                                </p>
                            ))}
                        </div>
                    </div>
                )}

                <div className="mt-10 rounded-lg border border-ink-100 bg-ink-50 p-6">
                    {cohort.accepting_enrollment ? (
                        <>
                            <p className="text-sm text-ink-700">Enrollment is open for this cohort.</p>
                            {!auth.user && (
                                <Link
                                    href={route('register')}
                                    className="mt-4 inline-block rounded-md bg-ink-900 px-5 py-2.5 text-sm font-medium text-white hover:bg-ink-700"
                                >
                                    Create an account to enroll
                                </Link>
                            )}
                            {auth.user && auth.user.role === 'student' && (
                                <Link
                                    href={route('checkout.create', cohort.slug)}
                                    className="mt-4 inline-block rounded-md bg-gold-500 px-5 py-2.5 text-sm font-medium text-ink-900 hover:bg-gold-400"
                                >
                                    {course.price === 'Free' ? 'Enroll — free' : `Enroll — ${course.price}`}
                                </Link>
                            )}
                            {auth.user && auth.user.role !== 'student' && (
                                <p className="mt-2 text-sm text-ink-500">
                                    Signed in as {auth.user.role} — checkout is for student accounts.
                                </p>
                            )}
                        </>
                    ) : (
                        <p className="text-sm text-ink-500">
                            Enrollment for this cohort is not currently open.{' '}
                            <Link href={route('contact')} className="text-ink-900 underline hover:text-gold-600">
                                Contact us
                            </Link>{' '}
                            to be notified about the next one.
                        </p>
                    )}
                </div>
            </div>
        </PublicLayout>
    );
}
