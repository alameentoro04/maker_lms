import { FormEventHandler, useState } from 'react';
import { Head, Link, router, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { adminNav } from '@/Navigation/adminNav';
import TextField from '@/Components/ui/TextField';
import Textarea from '@/Components/ui/Textarea';
import Button from '@/Components/ui/Button';
import SecondaryButton from '@/Components/ui/SecondaryButton';

interface ExamQuestion {
    id: number;
    type: string;
    question: string;
    options: { text: string; is_correct: boolean }[];
    points: number;
}

interface ExamData {
    id: number;
    title: string;
    instructions: string | null;
    passing_score: number | null;
    time_limit_minutes: number | null;
    attempt_limit: number;
    randomize_questions: boolean;
    questions: ExamQuestion[];
}

interface Props {
    cohort: { id: number; name: string };
    exam: ExamData | null;
}

export default function Edit({ cohort, exam }: Props) {
    const form = useForm({
        title: exam?.title ?? `${cohort.name} — Final Exam`,
        instructions: exam?.instructions ?? '',
        passing_score: exam?.passing_score ?? ('' as number | ''),
        time_limit_minutes: exam?.time_limit_minutes ?? ('' as number | ''),
        attempt_limit: exam?.attempt_limit ?? 1,
        randomize_questions: exam?.randomize_questions ?? false,
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        form.put(route('admin.cohorts.exam.upsert', cohort.id));
    };

    return (
        <AuthenticatedLayout nav={adminNav()} title={`Exam: ${cohort.name}`}>
            <Head title={`Exam — ${cohort.name}`} />

            <Link href={route('admin.cohorts.edit', cohort.id)} className="text-sm text-ink-500 hover:text-gold-600">
                ← {cohort.name}
            </Link>

            <form onSubmit={submit} className="mt-4 max-w-2xl space-y-4">
                <TextField label="Title" name="title" value={form.data.title} onChange={(e) => form.setData('title', e.target.value)} error={form.errors.title} />
                <Textarea label="Instructions" name="instructions" rows={3} value={form.data.instructions} onChange={(e) => form.setData('instructions', e.target.value)} error={form.errors.instructions} />
                <div className="grid grid-cols-3 gap-4">
                    <TextField label="Passing score (% — blank = platform default)" name="passing_score" type="number" min={0} max={100}
                        value={form.data.passing_score} onChange={(e) => form.setData('passing_score', e.target.value === '' ? '' : Number(e.target.value))} error={form.errors.passing_score} />
                    <TextField label="Time limit (min, optional)" name="time_limit_minutes" type="number" min={1}
                        value={form.data.time_limit_minutes} onChange={(e) => form.setData('time_limit_minutes', e.target.value === '' ? '' : Number(e.target.value))} error={form.errors.time_limit_minutes} />
                    <TextField label="Attempt limit" name="attempt_limit" type="number" min={1}
                        value={form.data.attempt_limit} onChange={(e) => form.setData('attempt_limit', Number(e.target.value))} error={form.errors.attempt_limit} />
                </div>
                <Button type="submit" loading={form.processing} className="w-auto px-5">Save exam settings</Button>
            </form>

            {exam && (
                <div className="mt-10 max-w-2xl">
                    <h2 className="text-sm font-semibold text-ink-900">Questions</h2>
                    <div className="mt-3 space-y-3">
                        {exam.questions.map((q, i) => (
                            <div key={q.id} className="rounded-lg border border-ink-100 bg-white p-4">
                                <div className="flex items-start justify-between">
                                    <p className="text-sm font-medium text-ink-900">{i + 1}. {q.question}</p>
                                    <button
                                        onClick={() => confirm('Delete this question?') && router.delete(route('admin.exams.questions.destroy', [exam.id, q.id]), { preserveScroll: true })}
                                        className="text-xs text-red-600 hover:text-red-700"
                                    >
                                        Delete
                                    </button>
                                </div>
                                <ul className="mt-2 space-y-1">
                                    {q.options.map((opt, oi) => (
                                        <li key={oi} className={`text-xs ${opt.is_correct ? 'text-green-700 font-medium' : 'text-ink-500'}`}>
                                            {opt.is_correct ? '✓ ' : '— '}{opt.text}
                                        </li>
                                    ))}
                                </ul>
                            </div>
                        ))}
                        {exam.questions.length === 0 && <p className="text-sm text-ink-500">No questions yet.</p>}
                    </div>

                    <QuestionForm examId={exam.id} />
                </div>
            )}

            {!exam && (
                <p className="mt-10 text-sm text-ink-500">Save the exam settings above before adding questions.</p>
            )}
        </AuthenticatedLayout>
    );
}

function QuestionForm({ examId }: { examId: number }) {
    const [type, setType] = useState<'multiple_choice' | 'true_false'>('multiple_choice');
    const form = useForm({
        type: 'multiple_choice',
        question: '',
        explanation: '',
        points: 1,
        options: [{ text: '', is_correct: true }, { text: '', is_correct: false }] as { text: string; is_correct: boolean }[],
    });

    const setQuestionType = (t: 'multiple_choice' | 'true_false') => {
        setType(t);
        form.setData({
            ...form.data,
            type: t,
            options: t === 'true_false'
                ? [{ text: 'True', is_correct: true }, { text: 'False', is_correct: false }]
                : [{ text: '', is_correct: true }, { text: '', is_correct: false }],
        });
    };

    const updateOption = (index: number, field: 'text' | 'is_correct', value: string | boolean) => {
        const options = form.data.options.map((o, i) => {
            if (field === 'is_correct') return { ...o, is_correct: i === index };
            return i === index ? { ...o, text: value as string } : o;
        });
        form.setData('options', options);
    };

    const addOption = () => form.setData('options', [...form.data.options, { text: '', is_correct: false }]);

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        form.post(route('admin.exams.questions.store', examId), {
            preserveScroll: true,
            onSuccess: () => form.reset(),
        });
    };

    return (
        <form onSubmit={submit} className="mt-4 rounded-lg border border-dashed border-ink-300 p-4 space-y-3">
            <p className="text-sm font-semibold text-ink-900">Add question</p>
            <div className="flex gap-4 text-sm">
                <label className="flex items-center gap-1.5">
                    <input type="radio" checked={type === 'multiple_choice'} onChange={() => setQuestionType('multiple_choice')} /> Multiple choice
                </label>
                <label className="flex items-center gap-1.5">
                    <input type="radio" checked={type === 'true_false'} onChange={() => setQuestionType('true_false')} /> True/False
                </label>
            </div>

            <Textarea label="Question" name="question" rows={2} value={form.data.question} onChange={(e) => form.setData('question', e.target.value)} error={form.errors.question} />

            <div className="space-y-2">
                <p className="text-sm font-medium text-ink-900">Options (select the correct one)</p>
                {form.data.options.map((opt, i) => (
                    <div key={i} className="flex items-center gap-2">
                        <input type="radio" name="correct" checked={opt.is_correct} onChange={() => updateOption(i, 'is_correct', true)} />
                        <input
                            type="text"
                            value={opt.text}
                            disabled={type === 'true_false'}
                            onChange={(e) => updateOption(i, 'text', e.target.value)}
                            className="flex-1 rounded-md border border-ink-100 bg-white px-3 py-1.5 text-sm disabled:bg-ink-50"
                        />
                    </div>
                ))}
                {type === 'multiple_choice' && (
                    <button type="button" onClick={addOption} className="text-xs text-ink-500 hover:text-gold-600">+ Add option</button>
                )}
                {form.errors.options && <p className="text-sm text-red-600">{form.errors.options}</p>}
            </div>

            <Textarea label="Explanation (optional)" name="explanation" rows={2} value={form.data.explanation} onChange={(e) => form.setData('explanation', e.target.value)} error={form.errors.explanation} />
            <TextField label="Points" name="points" type="number" min={1} value={form.data.points} onChange={(e) => form.setData('points', Number(e.target.value))} error={form.errors.points} />

            <Button type="submit" loading={form.processing} className="w-auto px-5">Add question</Button>
        </form>
    );
}
