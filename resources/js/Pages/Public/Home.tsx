import { Head, Link } from '@inertiajs/react';
import PublicLayout from '@/Layouts/PublicLayout';

interface HomeProps {
    featuredCourse: {
        title: string;
        slug: string;
        summary: string;
        level: string;
        duration_weeks: number;
        price: string;
    } | null;
    nextCohort: {
        name: string;
        slug: string;
        start_date: string;
        status: string;
        capacity: number;
    } | null;
    testimonials: { author_name: string; author_role: string | null; quote: string }[];
    showcaseItems: { title: string; author_name: string; image_path: string | null }[];
}

const principles = [
    {
        title: 'Cohort, not self-paced drift',
        body: 'You move through the material with the same group of people, on a fixed 4-week schedule — not an unlimited library you never finish.',
    },
    {
        title: 'A brief, not a tutorial',
        body: 'Every project mirrors a real client brief. You leave with work you can put in front of someone who is paying you, not just a "certificate of completion."',
    },
    {
        title: 'An instructor who grades your work',
        body: 'Assignments get real feedback from an instructor, not an auto-checker — and your final grade goes through admin review before your certificate is issued.',
    },
];

export default function Home({ featuredCourse, nextCohort, testimonials, showcaseItems }: HomeProps) {
    return (
        <PublicLayout>
            <Head title="Makers by Al-Ismail" />

            {/* Hero — the one deliberately bold moment on this page */}
            <section className="mx-auto max-w-6xl px-6 pt-16 pb-20 grid lg:grid-cols-2 gap-12 items-center">
                <div>
                    <h1 className="font-display text-4xl sm:text-5xl leading-[1.1] text-ink-900">
                        Learn a craft. Ship real work. Get paid for it.
                    </h1>
                    <p className="mt-5 text-ink-500 text-base leading-relaxed max-w-md">
                        Makers by Al-Ismail runs small, cohort-based design and software
                        training out of Bauchi — built around real briefs, live feedback,
                        and a portfolio piece worth showing a client.
                    </p>
                    <div className="mt-8 flex gap-3">
                        {featuredCourse && (
                            <Link
                                href={route('courses.show', featuredCourse.slug)}
                                className="rounded-md bg-ink-900 px-5 py-3 text-sm font-medium text-white hover:bg-ink-700"
                            >
                                Explore {featuredCourse.title}
                            </Link>
                        )}
                        <Link
                            href={route('courses.index')}
                            className="rounded-md border border-ink-300 px-5 py-3 text-sm font-medium text-ink-900 hover:border-gold-400"
                        >
                            See all courses
                        </Link>
                    </div>
                </div>

                {featuredCourse && nextCohort && (
                    <div className="rounded-xl border border-ink-100 bg-white p-6 shadow-sm">
                        <p className="text-xs font-medium text-gold-600">{nextCohort.status}</p>
                        <h2 className="mt-2 text-xl font-semibold text-ink-900">{featuredCourse.title}</h2>
                        <p className="mt-2 text-sm text-ink-500">{featuredCourse.summary}</p>
                        <dl className="mt-5 grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <dt className="text-ink-500">Starts</dt>
                                <dd className="font-medium text-ink-900">{nextCohort.start_date}</dd>
                            </div>
                            <div>
                                <dt className="text-ink-500">Duration</dt>
                                <dd className="font-medium text-ink-900">{featuredCourse.duration_weeks} weeks</dd>
                            </div>
                            <div>
                                <dt className="text-ink-500">Cohort size</dt>
                                <dd className="font-medium text-ink-900">Up to {nextCohort.capacity} students</dd>
                            </div>
                            <div>
                                <dt className="text-ink-500">Price</dt>
                                <dd className="font-medium text-ink-900">{featuredCourse.price}</dd>
                            </div>
                        </dl>
                        <Link
                            href={route('cohorts.show', nextCohort.slug)}
                            className="mt-6 block text-center rounded-md bg-gold-500 px-4 py-2.5 text-sm font-medium text-ink-900 hover:bg-gold-400"
                        >
                            View this cohort
                        </Link>
                    </div>
                )}
            </section>

            {/* Principles — three distinct claims, not a numbered sequence */}
            <section className="border-y border-ink-100 bg-ink-50">
                <div className="mx-auto max-w-6xl px-6 py-14 grid md:grid-cols-3 gap-10">
                    {principles.map((p) => (
                        <div key={p.title}>
                            <h3 className="font-medium text-ink-900">{p.title}</h3>
                            <p className="mt-2 text-sm text-ink-500 leading-relaxed">{p.body}</p>
                        </div>
                    ))}
                </div>
            </section>

            {testimonials.length > 0 && (
                <section className="mx-auto max-w-6xl px-6 py-16">
                    <h2 className="text-xl font-semibold text-ink-900">What students say</h2>
                    <div className="mt-6 grid md:grid-cols-3 gap-6">
                        {testimonials.map((t) => (
                            <blockquote key={t.author_name} className="rounded-lg border border-ink-100 bg-white p-6">
                                <p className="text-sm text-ink-700 leading-relaxed">&ldquo;{t.quote}&rdquo;</p>
                                <footer className="mt-4 text-sm font-medium text-ink-900">
                                    {t.author_name}
                                    {t.author_role && <span className="text-ink-500 font-normal"> — {t.author_role}</span>}
                                </footer>
                            </blockquote>
                        ))}
                    </div>
                </section>
            )}

            {showcaseItems.length > 0 && (
                <section className="bg-ink-50 border-t border-ink-100">
                    <div className="mx-auto max-w-6xl px-6 py-16">
                        <div className="flex items-center justify-between">
                            <h2 className="text-xl font-semibold text-ink-900">Student work</h2>
                            <Link href={route('showcase.index')} className="text-sm font-medium text-ink-700 hover:text-gold-600">
                                View the full showcase
                            </Link>
                        </div>
                        <div className="mt-6 grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
                            {showcaseItems.map((item) => (
                                <div key={item.title} className="rounded-lg border border-ink-100 bg-white overflow-hidden">
                                    <div className="aspect-[4/3] bg-ink-100" />
                                    <div className="p-4">
                                        <p className="text-sm font-medium text-ink-900">{item.title}</p>
                                        <p className="text-xs text-ink-500">{item.author_name}</p>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>
                </section>
            )}
        </PublicLayout>
    );
}
