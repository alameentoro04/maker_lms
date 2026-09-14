import { Head, Link } from '@inertiajs/react';
import PublicLayout from '@/Layouts/PublicLayout';

interface InstructorShowProps {
    instructor: {
        name: string;
        headline: string | null;
        bio: string | null;
        social_links: Record<string, string>;
    };
    courses: { title: string; slug: string; summary: string }[];
}

export default function Show({ instructor, courses }: InstructorShowProps) {
    return (
        <PublicLayout>
            <Head title={instructor.name} />

            <div className="mx-auto max-w-3xl px-6 py-14">
                <h1 className="font-display text-3xl text-ink-900">{instructor.name}</h1>
                {instructor.headline && <p className="mt-1 text-gold-600 font-medium">{instructor.headline}</p>}
                {instructor.bio && <p className="mt-4 text-ink-700 leading-relaxed">{instructor.bio}</p>}

                {courses.length > 0 && (
                    <div className="mt-10">
                        <h2 className="text-sm font-semibold text-ink-900">Teaches</h2>
                        <div className="mt-3 space-y-3">
                            {courses.map((c) => (
                                <Link
                                    key={c.slug}
                                    href={route('courses.show', c.slug)}
                                    className="block rounded-lg border border-ink-100 bg-white p-4 hover:border-gold-400"
                                >
                                    <p className="text-sm font-medium text-ink-900">{c.title}</p>
                                    <p className="text-xs text-ink-500">{c.summary}</p>
                                </Link>
                            ))}
                        </div>
                    </div>
                )}
            </div>
        </PublicLayout>
    );
}
