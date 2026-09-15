import { useState } from 'react';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import Button from '@/Components/ui/Button';
import { PageProps } from '@/types';

interface ExamQuestionForStudent {
    id: number;
    type: string;
    question: string;
    options: { text: string }[];
}

interface ExamData {
    id: number;
    title: string;
    instructions: string | null;
    passing_score: number;
    time_limit_minutes: number | null;
    attempt_limit: number;
    attempts_used: number;
    attempts_remaining: number;
    in_progress_attempt: { id: number; started_at: string; questions: ExamQuestionForStudent[] } | null;
    latest_result: { score: number; passed: boolean; submitted_at: string } | null;
}

interface Props {
    course: { title: string; slug: string };
    exam: ExamData;
    certificate: { certificate_id: string } | null;
}

export default function Show({ course, exam, certificate }: Props) {
    const { flash } = usePage<PageProps>().props;
    const [answers, setAnswers] = useState<Record<number, number>>({});
    const [starting, setStarting] = useState(false);
    const submitForm = useForm({});

    const nav = [{ label: 'Dashboard', href: route('student.dashboard') }];

    const start = () => {
        setStarting(true);
        router.post(route('learn.exam.start', course.slug), {}, { onFinish: () => setStarting(false) });
    };

    const submit = () => {
        submitForm.transform(() => ({ answers }));
        submitForm.post(route('learn.exam.submit', exam.in_progress_attempt!.id));
    };

    return (
        <AuthenticatedLayout nav={nav} title={exam.title}>
            <Head title={exam.title} />

            {flash.status && (
                <p className="mb-4 rounded-md bg-green-50 px-4 py-2.5 text-sm text-green-700">{flash.status}</p>
            )}

            <div className="max-w-2xl">
                <p className="text-sm text-ink-500">{course.title}</p>
                {exam.instructions && <p className="mt-3 text-sm text-ink-700 whitespace-pre-line">{exam.instructions}</p>}

                <dl className="mt-4 flex gap-6 text-xs text-ink-500">
                    <div>Passing score: <span className="font-medium text-ink-900">{exam.passing_score}%</span></div>
                    {exam.time_limit_minutes && <div>Time limit: <span className="font-medium text-ink-900">{exam.time_limit_minutes} min</span></div>}
                    <div>Attempts: <span className="font-medium text-ink-900">{exam.attempts_used}/{exam.attempt_limit}</span></div>
                </dl>

                {certificate && (
                    <div className="mt-6 rounded-lg border border-green-200 bg-green-50 p-5">
                        <p className="text-sm font-medium text-green-700">Certificate issued: {certificate.certificate_id}</p>
                        <a
                            href={route('verify.download', certificate.certificate_id)}
                            className="mt-2 inline-block text-sm text-green-700 underline"
                        >
                            Download PDF
                        </a>
                    </div>
                )}

                {exam.latest_result && !certificate && (
                    <div className={`mt-6 rounded-lg p-5 ${exam.latest_result.passed ? 'bg-green-50' : 'bg-ink-50'}`}>
                        <p className="text-sm font-medium text-ink-900">
                            {exam.latest_result.passed ? 'Passed' : 'Not passed'} — {exam.latest_result.score}%
                        </p>
                        <p className="text-xs text-ink-500">{exam.latest_result.submitted_at}</p>
                    </div>
                )}

                {exam.in_progress_attempt ? (
                    <div className="mt-6 space-y-6">
                        {exam.in_progress_attempt.questions.map((q, i) => (
                            <div key={q.id} className="rounded-lg border border-ink-100 bg-white p-4">
                                <p className="text-sm font-medium text-ink-900">{i + 1}. {q.question}</p>
                                <div className="mt-2 space-y-1.5">
                                    {q.options.map((opt, optIndex) => (
                                        <label key={optIndex} className="flex items-center gap-2 text-sm text-ink-700">
                                            <input
                                                type="radio"
                                                name={`q-${q.id}`}
                                                checked={answers[q.id] === optIndex}
                                                onChange={() => setAnswers((a) => ({ ...a, [q.id]: optIndex }))}
                                                className="text-gold-500 focus:ring-gold-500/40"
                                            />
                                            {opt.text}
                                        </label>
                                    ))}
                                </div>
                            </div>
                        ))}
                        <Button onClick={submit} loading={submitForm.processing}>Submit exam</Button>
                    </div>
                ) : (
                    exam.attempts_remaining > 0 && !certificate && (
                        <Button onClick={start} loading={starting} className="mt-6 w-auto px-6">
                            {exam.latest_result ? 'Retake exam' : 'Start exam'}
                        </Button>
                    )
                )}

                {exam.attempts_remaining === 0 && !exam.latest_result?.passed && (
                    <p className="mt-6 text-sm text-ink-500">
                        You've used all your attempts. <Link href={route('contact')} className="underline text-ink-900">Contact support</Link> if you need another.
                    </p>
                )}
            </div>
        </AuthenticatedLayout>
    );
}
