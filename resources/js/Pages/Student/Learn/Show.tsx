import { Head, Link } from '@inertiajs/react';
import LearnLayout from '@/Layouts/LearnLayout';

interface LessonRow {
    id: number;
    title: string;
    type: string;
    completed: boolean;
}

interface ModuleRow {
    id: number;
    title: string;
    lessons: LessonRow[];
}

export default function Show({ course, modules }: { course: { title: string; slug: string }; modules: ModuleRow[] }) {
    const allLessons = modules.flatMap((m) => m.lessons);
    const completedCount = allLessons.filter((l) => l.completed).length;
    const firstIncomplete = allLessons.find((l) => !l.completed) ?? allLessons[0];

    return (
        <LearnLayout courseTitle={course.title} courseSlug={course.slug}>
            <Head title={course.title} />

            <div className="flex items-start justify-between">
                <div>
                    <h1 className="font-display text-2xl text-ink-900">{course.title}</h1>
                    <p className="mt-1 text-sm text-ink-500">
                        {completedCount} of {allLessons.length} lessons complete
                    </p>
                </div>
                {firstIncomplete && (
                    <Link
                        href={route('learn.lesson', [course.slug, firstIncomplete.id])}
                        className="rounded-md bg-ink-900 px-5 py-2.5 text-sm font-medium text-white hover:bg-ink-700"
                    >
                        {completedCount === 0 ? 'Start course' : 'Continue'}
                    </Link>
                )}
            </div>

            <div className="mt-8 space-y-6">
                {modules.map((module) => (
                    <div key={module.id}>
                        <h2 className="text-sm font-semibold text-ink-900">{module.title}</h2>
                        <div className="mt-2 rounded-lg border border-ink-100 bg-white divide-y divide-ink-100">
                            {module.lessons.map((lesson) => (
                                <Link
                                    key={lesson.id}
                                    href={route('learn.lesson', [course.slug, lesson.id])}
                                    className="flex items-center justify-between px-5 py-3 hover:bg-ink-50"
                                >
                                    <div className="flex items-center gap-3">
                                        <span className={`h-2 w-2 rounded-full ${lesson.completed ? 'bg-green-500' : 'bg-ink-200'}`} />
                                        <span className="text-sm text-ink-900">{lesson.title}</span>
                                    </div>
                                    <span className="text-xs text-ink-500 capitalize">{lesson.type}</span>
                                </Link>
                            ))}
                            {module.lessons.length === 0 && (
                                <p className="px-5 py-3 text-sm text-ink-500">No published lessons yet.</p>
                            )}
                        </div>
                    </div>
                ))}
            </div>
        </LearnLayout>
    );
}
