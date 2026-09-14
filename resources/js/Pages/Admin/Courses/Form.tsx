import { FormEventHandler, useEffect } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { adminNav } from '@/Navigation/adminNav';
import TextField from '@/Components/ui/TextField';
import Textarea from '@/Components/ui/Textarea';
import Select from '@/Components/ui/Select';
import Button from '@/Components/ui/Button';
import CurriculumBuilder, { ModuleWithLessons } from './Partials/CurriculumBuilder';

interface CourseData {
    id: number;
    category_id: number | null;
    title: string;
    slug: string;
    summary: string;
    description: string;
    objectives: string[];
    requirements: string[];
    level: string;
    duration_weeks: number;
    status: string;
    price: number;
    currency: string;
    instructor_ids: number[];
}

interface FormProps {
    course: CourseData | null;
    categories: { id: number; name: string }[];
    instructors: { id: number; name: string }[];
    modules?: ModuleWithLessons[];
}

function slugify(value: string): string {
    return value.toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
}

export default function Form({ course, categories, instructors, modules }: FormProps) {
    const isEditing = !!course;

    const { data, setData, post, put, processing, errors } = useForm({
        category_id: course?.category_id ?? '',
        title: course?.title ?? '',
        slug: course?.slug ?? '',
        summary: course?.summary ?? '',
        description: course?.description ?? '',
        objectives: (course?.objectives ?? []).join('\n'),
        requirements: (course?.requirements ?? []).join('\n'),
        level: course?.level ?? 'beginner',
        duration_weeks: course?.duration_weeks ?? 4,
        status: course?.status ?? 'draft',
        price: course?.price ?? 0,
        currency: course?.currency ?? 'NGN',
        instructor_ids: course?.instructor_ids ?? [],
    });

    useEffect(() => {
        if (!isEditing) {
            setData('slug', slugify(data.title));
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [data.title]);

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        const payload = {
            ...data,
            objectives: data.objectives.split('\n').map((s) => s.trim()).filter(Boolean),
            requirements: data.requirements.split('\n').map((s) => s.trim()).filter(Boolean),
        };

        if (isEditing) {
            put(route('admin.courses.update', course!.id), { data: payload as any });
        } else {
            post(route('admin.courses.store'), { data: payload as any });
        }
    };

    const toggleInstructor = (id: number) => {
        setData('instructor_ids', data.instructor_ids.includes(id)
            ? data.instructor_ids.filter((i) => i !== id)
            : [...data.instructor_ids, id]);
    };

    return (
        <AuthenticatedLayout nav={adminNav()} title={isEditing ? `Edit: ${course!.title}` : 'New course'}>
            <Head title={isEditing ? course!.title : 'New course'} />

            <Link href={route('admin.courses.index')} className="text-sm text-ink-500 hover:text-gold-600">
                ← All courses
            </Link>

            <form onSubmit={submit} className="mt-4 grid lg:grid-cols-[1fr_320px] gap-8">
                <div className="space-y-4">
                    <TextField label="Title" name="title" value={data.title} onChange={(e) => setData('title', e.target.value)} error={errors.title} />
                    <TextField label="Slug" name="slug" value={data.slug} onChange={(e) => setData('slug', e.target.value)} error={errors.slug} />
                    <TextField label="Summary (catalog card)" name="summary" value={data.summary} onChange={(e) => setData('summary', e.target.value)} error={errors.summary} />
                    <Textarea label="Full description" name="description" rows={6} value={data.description} onChange={(e) => setData('description', e.target.value)} error={errors.description} />
                    <Textarea
                        label="Objectives (one per line — “what you'll learn”)"
                        name="objectives"
                        rows={4}
                        value={data.objectives}
                        onChange={(e) => setData('objectives', e.target.value)}
                        error={errors.objectives}
                    />
                    <Textarea
                        label="Requirements (one per line)"
                        name="requirements"
                        rows={3}
                        value={data.requirements}
                        onChange={(e) => setData('requirements', e.target.value)}
                        error={errors.requirements}
                    />
                </div>

                <div className="space-y-4">
                    <Select label="Category" name="category_id" value={data.category_id} onChange={(e) => setData('category_id', e.target.value as any)}>
                        <option value="">None</option>
                        {categories.map((c) => <option key={c.id} value={c.id}>{c.name}</option>)}
                    </Select>

                    <Select label="Level" name="level" value={data.level} onChange={(e) => setData('level', e.target.value)}>
                        <option value="beginner">Beginner</option>
                        <option value="intermediate">Intermediate</option>
                        <option value="advanced">Advanced</option>
                    </Select>

                    <TextField label="Duration (weeks)" name="duration_weeks" type="number" min={1} max={52}
                        value={data.duration_weeks} onChange={(e) => setData('duration_weeks', Number(e.target.value))} error={errors.duration_weeks} />

                    <Select label="Status" name="status" value={data.status} onChange={(e) => setData('status', e.target.value)}>
                        <option value="draft">Draft</option>
                        <option value="published">Published</option>
                        <option value="archived">Archived</option>
                    </Select>

                    <div className="grid grid-cols-2 gap-3">
                        <TextField label="Price (minor units)" name="price" type="number" min={0}
                            value={data.price} onChange={(e) => setData('price', Number(e.target.value))} error={errors.price} />
                        <TextField label="Currency" name="currency" maxLength={3}
                            value={data.currency} onChange={(e) => setData('currency', e.target.value.toUpperCase())} error={errors.currency} />
                    </div>
                    <p className="text-xs text-ink-500 -mt-2">
                        Price is stored but not enforced anywhere yet — checkout lands in Phase 5.
                    </p>

                    <div>
                        <p className="block text-sm font-medium text-ink-900">Instructors</p>
                        <div className="mt-1.5 space-y-1.5">
                            {instructors.map((i) => (
                                <label key={i.id} className="flex items-center gap-2 text-sm text-ink-700">
                                    <input
                                        type="checkbox"
                                        checked={data.instructor_ids.includes(i.id)}
                                        onChange={() => toggleInstructor(i.id)}
                                        className="rounded border-ink-300 text-gold-500 focus:ring-gold-500/40"
                                    />
                                    {i.name}
                                </label>
                            ))}
                            {instructors.length === 0 && <p className="text-sm text-ink-500">No instructor accounts yet.</p>}
                        </div>
                    </div>

                    <Button type="submit" loading={processing}>{isEditing ? 'Save changes' : 'Create course'}</Button>
                </div>
            </form>

            {isEditing && (
                <div className="mt-10">
                    <h2 className="text-lg font-semibold text-ink-900">Curriculum</h2>
                    <p className="mt-1 text-sm text-ink-500">Modules and lessons for this course.</p>
                    <div className="mt-4">
                        <CurriculumBuilder courseId={course!.id} modules={modules ?? []} />
                    </div>
                </div>
            )}
        </AuthenticatedLayout>
    );
}
