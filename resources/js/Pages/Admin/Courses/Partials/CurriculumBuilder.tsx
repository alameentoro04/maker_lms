import { FormEventHandler, useState } from 'react';
import { router, useForm } from '@inertiajs/react';
import TextField from '@/Components/ui/TextField';
import Select from '@/Components/ui/Select';
import Button from '@/Components/ui/Button';
import SecondaryButton from '@/Components/ui/SecondaryButton';

interface LessonRow {
    id: number;
    title: string;
    type: string;
    order: number;
    is_preview: boolean;
    is_published: boolean;
}

export interface ModuleWithLessons {
    id: number;
    title: string;
    order: number;
    lessons: LessonRow[];
}

const LESSON_TYPES = ['video', 'text', 'mixed', 'quiz', 'assignment', 'resource'];

/**
 * Inline curriculum editor for a single course. Deliberately not a separate
 * page per module/lesson — a course's curriculum is always edited in
 * context of the whole course, never browsed on its own.
 */
export default function CurriculumBuilder({ courseId, modules }: { courseId: number; modules: ModuleWithLessons[] }) {
    const moduleForm = useForm({ title: '' });
    const [openModuleId, setOpenModuleId] = useState<number | null>(modules[0]?.id ?? null);

    const addModule: FormEventHandler = (e) => {
        e.preventDefault();
        moduleForm.post(route('admin.courses.modules.store', courseId), {
            onSuccess: () => moduleForm.reset(),
            preserveScroll: true,
        });
    };

    const deleteModule = (moduleId: number) => {
        if (confirm('Delete this module and all its lessons?')) {
            router.delete(route('admin.courses.modules.destroy', [courseId, moduleId]), { preserveScroll: true });
        }
    };

    return (
        <div className="space-y-4">
            {modules.map((module) => (
                <div key={module.id} className="rounded-lg border border-ink-100 bg-white">
                    <button
                        type="button"
                        onClick={() => setOpenModuleId(openModuleId === module.id ? null : module.id)}
                        className="flex w-full items-center justify-between px-5 py-3 text-left"
                    >
                        <span className="text-sm font-semibold text-ink-900">{module.title}</span>
                        <span className="text-xs text-ink-500">{module.lessons.length} lesson(s)</span>
                    </button>

                    {openModuleId === module.id && (
                        <div className="border-t border-ink-100 px-5 py-4">
                            <LessonList courseId={courseId} moduleId={module.id} lessons={module.lessons} />
                            <div className="mt-4 flex justify-end">
                                <SecondaryButton onClick={() => deleteModule(module.id)} type="button">
                                    Delete module
                                </SecondaryButton>
                            </div>
                        </div>
                    )}
                </div>
            ))}

            <form onSubmit={addModule} className="rounded-lg border border-dashed border-ink-300 p-4 flex items-end gap-3">
                <div className="flex-1">
                    <TextField
                        label="New module title"
                        name="new_module_title"
                        value={moduleForm.data.title}
                        onChange={(e) => moduleForm.setData('title', e.target.value)}
                        error={moduleForm.errors.title}
                    />
                </div>
                <Button type="submit" loading={moduleForm.processing} className="w-auto px-5">Add module</Button>
            </form>
        </div>
    );
}

function LessonList({ courseId, moduleId, lessons }: { courseId: number; moduleId: number; lessons: LessonRow[] }) {
    const lessonForm = useForm({ title: '', type: 'text', is_preview: false as boolean, is_published: false as boolean });

    const addLesson: FormEventHandler = (e) => {
        e.preventDefault();
        lessonForm.post(route('admin.courses.modules.lessons.store', [courseId, moduleId]), {
            onSuccess: () => lessonForm.reset(),
            preserveScroll: true,
        });
    };

    const toggle = (lesson: LessonRow, field: 'is_preview' | 'is_published') => {
        router.put(route('admin.courses.modules.lessons.update', [courseId, moduleId, lesson.id]), {
            title: lesson.title,
            type: lesson.type,
            is_preview: field === 'is_preview' ? !lesson.is_preview : lesson.is_preview,
            is_published: field === 'is_published' ? !lesson.is_published : lesson.is_published,
        }, { preserveScroll: true });
    };

    const deleteLesson = (lessonId: number) => {
        if (confirm('Delete this lesson?')) {
            router.delete(route('admin.courses.modules.lessons.destroy', [courseId, moduleId, lessonId]), { preserveScroll: true });
        }
    };

    return (
        <div>
            <div className="divide-y divide-ink-100">
                {lessons.map((lesson) => (
                    <div key={lesson.id} className="flex items-center justify-between py-2.5 text-sm">
                        <div>
                            <p className="font-medium text-ink-900">{lesson.title}</p>
                            <p className="text-xs text-ink-500 capitalize">{lesson.type}</p>
                        </div>
                        <div className="flex items-center gap-3">
                            <label className="flex items-center gap-1.5 text-xs text-ink-500">
                                <input type="checkbox" checked={lesson.is_preview} onChange={() => toggle(lesson, 'is_preview')}
                                    className="rounded border-ink-300 text-gold-500 focus:ring-gold-500/40" />
                                Preview
                            </label>
                            <label className="flex items-center gap-1.5 text-xs text-ink-500">
                                <input type="checkbox" checked={lesson.is_published} onChange={() => toggle(lesson, 'is_published')}
                                    className="rounded border-ink-300 text-gold-500 focus:ring-gold-500/40" />
                                Published
                            </label>
                            <button onClick={() => deleteLesson(lesson.id)} className="text-red-600 hover:text-red-700 text-xs">
                                Delete
                            </button>
                        </div>
                    </div>
                ))}
                {lessons.length === 0 && <p className="py-2 text-sm text-ink-500">No lessons in this module yet.</p>}
            </div>

            <form onSubmit={addLesson} className="mt-3 flex items-end gap-3">
                <div className="flex-1">
                    <TextField
                        label="New lesson title"
                        name="new_lesson_title"
                        value={lessonForm.data.title}
                        onChange={(e) => lessonForm.setData('title', e.target.value)}
                        error={lessonForm.errors.title}
                    />
                </div>
                <div className="w-40">
                    <Select label="Type" name="new_lesson_type" value={lessonForm.data.type} onChange={(e) => lessonForm.setData('type', e.target.value)}>
                        {LESSON_TYPES.map((t) => <option key={t} value={t}>{t}</option>)}
                    </Select>
                </div>
                <Button type="submit" loading={lessonForm.processing} className="w-auto px-5">Add lesson</Button>
            </form>
        </div>
    );
}
