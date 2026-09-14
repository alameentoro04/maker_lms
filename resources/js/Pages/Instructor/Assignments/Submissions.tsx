import { FormEventHandler, useState } from 'react';
import { Head } from '@inertiajs/react';
import { useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { instructorNav } from '@/Navigation/instructorNav';
import TextField from '@/Components/ui/TextField';
import Textarea from '@/Components/ui/Textarea';
import Button from '@/Components/ui/Button';

interface SubmissionRow {
    id: number;
    student: string;
    submitted_at: string;
    text_response: string | null;
    external_link: string | null;
    has_file: boolean;
    status: string;
    grade: number | null;
    instructor_feedback: string | null;
}

interface Props {
    assignment: { id: number; lesson_title: string; course_title: string; instructions: string };
    submissions: SubmissionRow[];
}

export default function Submissions({ assignment, submissions }: Props) {
    return (
        <AuthenticatedLayout nav={instructorNav()} title={assignment.lesson_title}>
            <Head title={`Grade: ${assignment.lesson_title}`} />

            <p className="text-sm text-ink-500">{assignment.course_title}</p>
            <p className="mt-2 text-sm text-ink-700 max-w-2xl whitespace-pre-line">{assignment.instructions}</p>

            <div className="mt-6 space-y-4">
                {submissions.map((s) => <SubmissionCard key={s.id} submission={s} />)}
                {submissions.length === 0 && (
                    <p className="rounded-lg border border-dashed border-ink-300 bg-white p-8 text-center text-ink-500">
                        No submissions yet.
                    </p>
                )}
            </div>
        </AuthenticatedLayout>
    );
}

function SubmissionCard({ submission }: { submission: SubmissionRow }) {
    const [expanded, setExpanded] = useState(submission.status !== 'graded');
    const { data, setData, post, processing, errors } = useForm({
        grade: submission.grade ?? ('' as number | ''),
        instructor_feedback: submission.instructor_feedback ?? '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('instructor.submissions.grade', submission.id), { preserveScroll: true });
    };

    return (
        <div className="rounded-lg border border-ink-100 bg-white p-5">
            <button type="button" onClick={() => setExpanded((v) => !v)} className="flex w-full items-center justify-between text-left">
                <div>
                    <p className="text-sm font-medium text-ink-900">{submission.student}</p>
                    <p className="text-xs text-ink-500">{submission.submitted_at}</p>
                </div>
                <span className={`rounded-full px-2 py-0.5 text-xs font-medium ${submission.status === 'graded' ? 'bg-green-50 text-green-700' : 'bg-gold-50 text-gold-600'}`}>
                    {submission.status === 'graded' ? `Graded: ${submission.grade}/100` : 'Ungraded'}
                </span>
            </button>

            {expanded && (
                <div className="mt-4 border-t border-ink-100 pt-4 space-y-3">
                    {submission.text_response && (
                        <p className="text-sm text-ink-700 whitespace-pre-line">{submission.text_response}</p>
                    )}
                    {submission.external_link && (
                        <a href={submission.external_link} target="_blank" rel="noreferrer" className="block text-sm text-ink-700 underline hover:text-gold-600">
                            {submission.external_link}
                        </a>
                    )}
                    {submission.has_file && (
                        <a href={route('learn.submissions.download', submission.id)} className="block text-sm text-ink-700 underline hover:text-gold-600">
                            Download submitted file
                        </a>
                    )}

                    <form onSubmit={submit} className="grid sm:grid-cols-[120px_1fr_auto] gap-3 items-start pt-2">
                        <TextField label="Grade (0-100)" name="grade" type="number" min={0} max={100}
                            value={data.grade} onChange={(e) => setData('grade', Number(e.target.value))} error={errors.grade} />
                        <Textarea label="Feedback" name="instructor_feedback" rows={2}
                            value={data.instructor_feedback} onChange={(e) => setData('instructor_feedback', e.target.value)} error={errors.instructor_feedback} />
                        <div className="pt-6">
                            <Button type="submit" loading={processing} className="w-auto px-5">Save grade</Button>
                        </div>
                    </form>
                </div>
            )}
        </div>
    );
}
