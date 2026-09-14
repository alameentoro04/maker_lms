import { Head } from '@inertiajs/react';
import PublicLayout from '@/Layouts/PublicLayout';

interface ShowcaseItem {
    title: string;
    author_name: string;
    description: string | null;
    image_path: string | null;
    external_url: string | null;
    course: string | null;
}

export default function Showcase({ items }: { items: ShowcaseItem[] }) {
    return (
        <PublicLayout>
            <Head title="Student showcase" />

            <div className="mx-auto max-w-6xl px-6 py-14">
                <h1 className="font-display text-3xl text-ink-900">Student showcase</h1>
                <p className="mt-2 text-ink-500 max-w-xl">Real work from Makers cohorts.</p>

                {items.length === 0 ? (
                    <p className="mt-10 text-ink-500">No published work yet — check back once the first cohort graduates.</p>
                ) : (
                    <div className="mt-10 grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        {items.map((item) => (
                            <div key={item.title} className="rounded-lg border border-ink-100 bg-white overflow-hidden">
                                <div className="aspect-[4/3] bg-ink-100" />
                                <div className="p-4">
                                    <p className="text-sm font-medium text-ink-900">{item.title}</p>
                                    <p className="text-xs text-ink-500">
                                        {item.author_name}{item.course && ` · ${item.course}`}
                                    </p>
                                    {item.description && (
                                        <p className="mt-2 text-sm text-ink-700 leading-relaxed">{item.description}</p>
                                    )}
                                </div>
                            </div>
                        ))}
                    </div>
                )}
            </div>
        </PublicLayout>
    );
}
