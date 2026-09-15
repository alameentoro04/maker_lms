import { FormEventHandler, useEffect } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { adminNav } from '@/Navigation/adminNav';
import TextField from '@/Components/ui/TextField';
import Select from '@/Components/ui/Select';
import Button from '@/Components/ui/Button';

interface CohortData {
    id: number;
    course_id: number;
    name: string;
    slug: string;
    start_date: string;
    end_date: string;
    enrollment_opens_at: string | null;
    enrollment_closes_at: string | null;
    capacity: number;
    live_platform: string;
    learning_model: string;
    status: string;
    instructor_ids: number[];
}

interface FormProps {
    cohort: CohortData | null;
    courses: { id: number; title: string }[];
    statuses: string[];
    instructors: { id: number; name: string }[];
}

function slugify(value: string): string {
    return value.toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
}

export default function Form({ cohort, courses, statuses, instructors }: FormProps) {
    const isEditing = !!cohort;

    const { data, setData, post, put, processing, errors } = useForm({
        course_id: cohort?.course_id ?? (courses[0]?.id ?? ''),
        name: cohort?.name ?? '',
        slug: cohort?.slug ?? '',
        start_date: cohort?.start_date ?? '',
        end_date: cohort?.end_date ?? '',
        enrollment_opens_at: cohort?.enrollment_opens_at ?? '',
        enrollment_closes_at: cohort?.enrollment_closes_at ?? '',
        capacity: cohort?.capacity ?? 30,
        live_platform: cohort?.live_platform ?? 'google_meet',
        learning_model: cohort?.learning_model ?? 'recorded_and_live',
        status: cohort?.status ?? 'draft',
        instructor_ids: cohort?.instructor_ids ?? [],
    });

    useEffect(() => {
        if (!isEditing) setData('slug', slugify(data.name));
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [data.name]);

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        if (isEditing) {
            put(route('admin.cohorts.update', cohort!.id));
        } else {
            post(route('admin.cohorts.store'));
        }
    };

    const toggleInstructor = (id: number) => {
        setData('instructor_ids', data.instructor_ids.includes(id)
            ? data.instructor_ids.filter((i) => i !== id)
            : [...data.instructor_ids, id]);
    };

    return (
        <AuthenticatedLayout nav={adminNav()} title={isEditing ? `Edit: ${cohort!.name}` : 'New cohort'}>
            <Head title={isEditing ? cohort!.name : 'New cohort'} />

            <Link href={route('admin.cohorts.index')} className="text-sm text-ink-500 hover:text-gold-600">
                ← All cohorts
            </Link>
            {isEditing && (
                <Link href={route('admin.cohorts.exam.edit', cohort!.id)} className="ml-4 text-sm text-ink-500 hover:text-gold-600">
                    Configure final exam →
                </Link>
            )}

            <form onSubmit={submit} className="mt-4 max-w-2xl space-y-4">
                <Select label="Course" name="course_id" value={data.course_id} onChange={(e) => setData('course_id', e.target.value as any)} error={errors.course_id}>
                    {courses.map((c) => <option key={c.id} value={c.id}>{c.title}</option>)}
                </Select>

                <TextField label="Cohort name" name="name" value={data.name} onChange={(e) => setData('name', e.target.value)} error={errors.name} />
                <TextField label="Slug" name="slug" value={data.slug} onChange={(e) => setData('slug', e.target.value)} error={errors.slug} />

                <div className="grid grid-cols-2 gap-4">
                    <TextField label="Start date" name="start_date" type="date" value={data.start_date} onChange={(e) => setData('start_date', e.target.value)} error={errors.start_date} />
                    <TextField label="End date" name="end_date" type="date" value={data.end_date} onChange={(e) => setData('end_date', e.target.value)} error={errors.end_date} />
                </div>

                <div className="grid grid-cols-2 gap-4">
                    <TextField label="Enrollment opens" name="enrollment_opens_at" type="datetime-local" value={data.enrollment_opens_at ?? ''} onChange={(e) => setData('enrollment_opens_at', e.target.value)} error={errors.enrollment_opens_at} />
                    <TextField label="Enrollment closes" name="enrollment_closes_at" type="datetime-local" value={data.enrollment_closes_at ?? ''} onChange={(e) => setData('enrollment_closes_at', e.target.value)} error={errors.enrollment_closes_at} />
                </div>

                <TextField label="Capacity" name="capacity" type="number" min={1} value={data.capacity} onChange={(e) => setData('capacity', Number(e.target.value))} error={errors.capacity} />

                <div className="grid grid-cols-2 gap-4">
                    <Select label="Live platform" name="live_platform" value={data.live_platform} onChange={(e) => setData('live_platform', e.target.value)}>
                        <option value="google_meet">Google Meet</option>
                    </Select>
                    <Select label="Learning model" name="learning_model" value={data.learning_model} onChange={(e) => setData('learning_model', e.target.value)}>
                        <option value="recorded">Recorded only</option>
                        <option value="live">Live only</option>
                        <option value="recorded_and_live">Recorded + live</option>
                    </Select>
                </div>

                <Select label="Status" name="status" value={data.status} onChange={(e) => setData('status', e.target.value)}>
                    {statuses.map((s) => <option key={s} value={s}>{s.replace(/_/g, ' ')}</option>)}
                </Select>

                <div>
                    <p className="block text-sm font-medium text-ink-900">Instructors</p>
                    <div className="mt-1.5 space-y-1.5">
                        {instructors.map((i) => (
                            <label key={i.id} className="flex items-center gap-2 text-sm text-ink-700">
                                <input type="checkbox" checked={data.instructor_ids.includes(i.id)} onChange={() => toggleInstructor(i.id)}
                                    className="rounded border-ink-300 text-gold-500 focus:ring-gold-500/40" />
                                {i.name}
                            </label>
                        ))}
                    </div>
                </div>

                <Button type="submit" loading={processing}>{isEditing ? 'Save changes' : 'Create cohort'}</Button>
            </form>
        </AuthenticatedLayout>
    );
}
