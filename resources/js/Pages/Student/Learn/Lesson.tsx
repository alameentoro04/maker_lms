import { FormEventHandler, useState } from 'react';
import { Head, Link, router, useForm } from '@inertiajs/react';
import LearnLayout from '@/Layouts/LearnLayout';
import Button from '@/Components/ui/Button';
import Textarea from '@/Components/ui/Textarea';
import TextField from '@/Components/ui/TextField';

interface NavLesson {
    id: number;
    title: string;
    completed: boolean;
    is_current: boolean;
}

interface NavModule {
    title: string;
    lessons: NavLesson[];
}

interface Submission {
    id: number;
    status: string;
    grade: number | null;
    instructor_feedback: string | null;
    submitted_at: string;
    has_file: boolean;
    attempt_number: number;
}

interface QuizQuestionForStudent {
    id: number;
    type: string;
    question: string;
    options: { id: number; option_text: string }[];
}

interface QuizData {
    id: number;
    passing_score: number;
    time_limit_minutes: number | null;
    attempt_limit: number;
    attempts_used: number;
    attempts_remaining: number;
    in_progress_attempt: { id: number; started_at: string; questions: QuizQuestionForStudent[] } | null;
    latest_result: { score: number; passed: boolean; submitted_at: string } | null;
}

interface LessonProps {
    course: { title: string; slug: string };
    lesson: {
        id: number;
        title: string;
        type: string;
        content: string | null;
        completed: boolean;
        video_stream_url: string | null;
        resources: { title: string; url: string | null }[];
        assignment: {
            id: number;
            instructions: string;
            due_at: string | null;
            past_due: boolean;
            allow_resubmission: boolean;
            my_submission: Submission | null;
        } | null;
        quiz: QuizData | null;
    };
    navigation: {
        modules: NavModule[];
        previous_lesson_id: number | null;
        next_lesson_id: number | null;
    };
}

export default function Lesson({ course, lesson, navigation }: LessonProps) {
    const [completing, setCompleting] = useState(false);

    const markComplete = () => {
        setCompleting(true);
        router.post(route('learn.lessons.complete', lesson.id), {}, {
            preserveScroll: true,
            onFinish: () => setCompleting(false),
        });
    };

    return (
        <LearnLayout courseTitle={course.title} courseSlug={course.slug}>
            <Head title={lesson.title} />

            <div className="grid lg:grid-cols-[260px_1fr] gap-8">
                <aside className="hidden lg:block">
                    <div className="rounded-lg border border-ink-100 bg-white divide-y divide-ink-100 sticky top-6">
                        {navigation.modules.map((module) => (
                            <div key={module.title} className="p-3">
                                <p className="px-2 text-xs font-semibold uppercase tracking-wide text-ink-500">{module.title}</p>
                                <div className="mt-1">
                                    {module.lessons.map((l) => (
                                        <Link
                                            key={l.id}
                                            href={route('learn.lesson', [course.slug, l.id])}
                                            className={`flex items-center gap-2 rounded-md px-2 py-1.5 text-sm ${
                                                l.is_current ? 'bg-ink-50 font-medium text-ink-900' : 'text-ink-700 hover:bg-ink-50'
                                            }`}
                                        >
                                            <span className={`h-1.5 w-1.5 rounded-full ${l.completed ? 'bg-green-500' : 'bg-ink-200'}`} />
                                            {l.title}
                                        </Link>
                                    ))}
                                </div>
                            </div>
                        ))}
                    </div>
                </aside>

                <div>
                    <h1 className="font-display text-2xl text-ink-900">{lesson.title}</h1>

                    {lesson.type === 'video' && (
                        <div className="mt-4 aspect-video rounded-lg bg-ink-900 flex items-center justify-center text-center p-6">
                            {lesson.video_stream_url ? (
                                <p className="text-sm text-ink-300 max-w-sm">
                                    Video playback uses a mock provider in this build — the signed,
                                    enrollment-gated URL is real and expires; a real player (Bunny/Cloudflare
                                    Stream) drops in here once configured.
                                </p>
                            ) : (
                                <p className="text-sm text-ink-300">No video uploaded for this lesson yet.</p>
                            )}
                        </div>
                    )}

                    {(lesson.type === 'text' || lesson.type === 'mixed') && lesson.content && (
                        <div className="mt-6 prose prose-sm max-w-none text-ink-700 whitespace-pre-line leading-relaxed">
                            {lesson.content}
                        </div>
                    )}

                    {lesson.resources.length > 0 && (
                        <div className="mt-6">
                            <h2 className="text-sm font-semibold text-ink-900">Resources</h2>
                            <ul className="mt-2 space-y-1">
                                {lesson.resources.map((r) => (
                                    <li key={r.title}>
                                        {r.url ? (
                                            <a href={r.url} target="_blank" rel="noreferrer" className="text-sm text-ink-700 underline hover:text-gold-600">
                                                {r.title}
                                            </a>
                                        ) : (
                                            <span className="text-sm text-ink-500">{r.title}</span>
                                        )}
                                    </li>
                                ))}
                            </ul>
                        </div>
                    )}

                    {lesson.assignment && <AssignmentPanel assignment={lesson.assignment} />}

                    {lesson.quiz && <QuizPanel quiz={lesson.quiz} />}

                    <div className="mt-8 flex items-center justify-between border-t border-ink-100 pt-6">
                        {navigation.previous_lesson_id ? (
                            <Link href={route('learn.lesson', [course.slug, navigation.previous_lesson_id])} className="text-sm text-ink-500 hover:text-gold-600">
                                ← Previous
                            </Link>
                        ) : <span />}

                        {!lesson.completed ? (
                            <Button onClick={markComplete} loading={completing} className="w-auto px-5">
                                Mark as complete
                            </Button>
                        ) : (
                            <span className="text-sm font-medium text-green-700">✓ Completed</span>
                        )}

                        {navigation.next_lesson_id ? (
                            <Link href={route('learn.lesson', [course.slug, navigation.next_lesson_id])} className="text-sm text-ink-900 font-medium hover:text-gold-600">
                                Next →
                            </Link>
                        ) : <span />}
                    </div>
                </div>
            </div>
        </LearnLayout>
    );
}

function AssignmentPanel({ assignment }: { assignment: NonNullable<LessonProps['lesson']['assignment']> }) {
    const { data, setData, post, processing, errors } = useForm({
        text_response: '',
        external_link: '',
        file: null as File | null,
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('learn.assignments.submit', assignment.id), { forceFormData: true });
    };

    const canSubmit = !assignment.my_submission || assignment.allow_resubmission;

    return (
        <div className="mt-8 rounded-lg border border-ink-100 bg-white p-5">
            <div className="flex items-center justify-between">
                <h2 className="text-sm font-semibold text-ink-900">Assignment</h2>
                {assignment.due_at && (
                    <span className={`text-xs ${assignment.past_due ? 'text-red-600' : 'text-ink-500'}`}>
                        Due {assignment.due_at}
                    </span>
                )}
            </div>
            <p className="mt-2 text-sm text-ink-700 whitespace-pre-line leading-relaxed">{assignment.instructions}</p>

            {assignment.my_submission && (
                <div className="mt-4 rounded-md bg-ink-50 p-4">
                    <p className="text-sm font-medium text-ink-900">
                        Submitted {assignment.my_submission.submitted_at} (attempt {assignment.my_submission.attempt_number})
                    </p>
                    {assignment.my_submission.status === 'graded' ? (
                        <p className="mt-1 text-sm text-ink-700">
                            Grade: <span className="font-semibold">{assignment.my_submission.grade}/100</span>
                            {assignment.my_submission.instructor_feedback && ` — ${assignment.my_submission.instructor_feedback}`}
                        </p>
                    ) : (
                        <p className="mt-1 text-sm text-ink-500">Awaiting grading.</p>
                    )}
                </div>
            )}

            {canSubmit && (
                <form onSubmit={submit} className="mt-4 space-y-3" encType="multipart/form-data">
                    <Textarea
                        label="Text response (optional)"
                        name="text_response"
                        rows={4}
                        value={data.text_response}
                        onChange={(e) => setData('text_response', e.target.value)}
                        error={errors.text_response}
                    />
                    <TextField
                        label="External link (optional)"
                        name="external_link"
                        value={data.external_link}
                        onChange={(e) => setData('external_link', e.target.value)}
                        error={errors.external_link}
                    />
                    <div>
                        <label className="block text-sm font-medium text-ink-900">File (optional)</label>
                        <input
                            type="file"
                            onChange={(e) => setData('file', e.target.files?.[0] ?? null)}
                            className="mt-1.5 block w-full text-sm text-ink-700"
                        />
                        {errors.file && <p className="mt-1.5 text-sm text-red-600">{errors.file}</p>}
                    </div>
                    <Button type="submit" loading={processing} className="w-auto px-5">
                        {assignment.my_submission ? 'Resubmit' : 'Submit assignment'}
                    </Button>
                </form>
            )}
        </div>
    );
}

function QuizPanel({ quiz }: { quiz: QuizData }) {
    const [answers, setAnswers] = useState<Record<number, number>>({});
    const [starting, setStarting] = useState(false);
    const submitForm = useForm({});

    const start = () => {
        setStarting(true);
        router.post(route('learn.quizzes.start', quiz.id), {}, { preserveScroll: true, onFinish: () => setStarting(false) });
    };

    const submit = () => {
        submitForm.transform(() => ({ answers }));
        submitForm.post(route('learn.quizzes.submit', quiz.in_progress_attempt!.id), { preserveScroll: true });
    };

    return (
        <div className="mt-8 rounded-lg border border-ink-100 bg-white p-5">
            <div className="flex items-center justify-between">
                <h2 className="text-sm font-semibold text-ink-900">Quiz</h2>
                <span className="text-xs text-ink-500">
                    Pass: {quiz.passing_score}% · {quiz.attempts_remaining} of {quiz.attempt_limit} attempt(s) left
                </span>
            </div>

            {quiz.latest_result && (
                <div className={`mt-3 rounded-md p-3 text-sm ${quiz.latest_result.passed ? 'bg-green-50 text-green-700' : 'bg-ink-50 text-ink-700'}`}>
                    {quiz.latest_result.passed ? 'Passed' : 'Not passed'} — {quiz.latest_result.score}% ({quiz.latest_result.submitted_at})
                </div>
            )}

            {quiz.in_progress_attempt ? (
                <div className="mt-4 space-y-5">
                    {quiz.in_progress_attempt.questions.map((q, i) => (
                        <div key={q.id}>
                            <p className="text-sm font-medium text-ink-900">{i + 1}. {q.question}</p>
                            <div className="mt-2 space-y-1.5">
                                {q.options.map((opt) => (
                                    <label key={opt.id} className="flex items-center gap-2 text-sm text-ink-700">
                                        <input
                                            type="radio"
                                            name={`q-${q.id}`}
                                            checked={answers[q.id] === opt.id}
                                            onChange={() => setAnswers((a) => ({ ...a, [q.id]: opt.id }))}
                                            className="text-gold-500 focus:ring-gold-500/40"
                                        />
                                        {opt.option_text}
                                    </label>
                                ))}
                            </div>
                        </div>
                    ))}
                    <Button onClick={submit} loading={submitForm.processing} className="w-auto px-5">Submit quiz</Button>
                </div>
            ) : (
                quiz.attempts_remaining > 0 && (
                    <Button onClick={start} loading={starting} className="mt-4 w-auto px-5">
                        {quiz.latest_result ? 'Retake quiz' : 'Start quiz'}
                    </Button>
                )
            )}
        </div>
    );
}
