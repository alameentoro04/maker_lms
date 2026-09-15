import { FormEventHandler, useState } from 'react';
import { Head, router, useForm, usePage } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { adminNav } from '@/Navigation/adminNav';
import Textarea from '@/Components/ui/Textarea';
import Button from '@/Components/ui/Button';
import SecondaryButton from '@/Components/ui/SecondaryButton';
import { PageProps } from '@/types';

interface Submission {
    id: number;
    student: string;
    course: string;
    cohort: string | null;
    amount: string;
    provider: string;
    has_proof: boolean;
    note: string | null;
    status: string;
    submitted_at: string;
}

export default function Index({ submissions }: { submissions: Submission[] }) {
    const { flash } = usePage<PageProps>().props;
    const pending = submissions.filter((s) => s.status === 'pending');
    const reviewed = submissions.filter((s) => s.status !== 'pending');

    return (
        <AuthenticatedLayout nav={adminNav()} title="Manual payments">
            <Head title="Manual payments" />

            {flash.status && (
                <p className="mb-4 rounded-md bg-green-50 px-4 py-2.5 text-sm text-green-700">{flash.status}</p>
            )}

            <h2 className="text-sm font-semibold text-ink-900">Pending review</h2>
            <div className="mt-3 space-y-3">
                {pending.map((s) => <SubmissionCard key={s.id} submission={s} />)}
                {pending.length === 0 && <p className="text-sm text-ink-500">Nothing waiting on review.</p>}
            </div>

            {reviewed.length > 0 && (
                <>
                    <h2 className="mt-8 text-sm font-semibold text-ink-900">Reviewed</h2>
                    <div className="mt-3 rounded-lg border border-ink-100 bg-white overflow-hidden">
                        <table className="w-full text-sm">
                            <tbody className="divide-y divide-ink-100">
                                {reviewed.map((s) => (
                                    <tr key={s.id}>
                                        <td className="px-5 py-3 font-medium text-ink-900">{s.student}</td>
                                        <td className="px-5 py-3 text-ink-500">{s.course}</td>
                                        <td className="px-5 py-3 text-ink-500">{s.amount}</td>
                                        <td className="px-5 py-3">
                                            <span className={`text-xs font-medium ${s.status === 'approved' ? 'text-green-700' : 'text-red-600'}`}>
                                                {s.status}
                                            </span>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </>
            )}
        </AuthenticatedLayout>
    );
}

function SubmissionCard({ submission }: { submission: Submission }) {
    const [rejecting, setRejecting] = useState(false);
    const form = useForm({ review_note: '' });

    const approve = () => {
        if (confirm(`Approve ${submission.student}'s payment and enroll them?`)) {
            router.post(route('admin.payments.manual.approve', submission.id));
        }
    };

    const reject: FormEventHandler = (e) => {
        e.preventDefault();
        form.post(route('admin.payments.manual.reject', submission.id));
    };

    return (
        <div className="rounded-lg border border-ink-100 bg-white p-5">
            <div className="flex items-center justify-between">
                <div>
                    <p className="text-sm font-medium text-ink-900">{submission.student}</p>
                    <p className="text-xs text-ink-500">{submission.course}{submission.cohort && ` — ${submission.cohort}`}</p>
                </div>
                <p className="text-sm font-semibold text-ink-900">{submission.amount}</p>
            </div>

            <p className="mt-2 text-xs text-ink-500 capitalize">{submission.provider.replace('_', ' ')} · {submission.submitted_at}</p>
            {submission.note && <p className="mt-2 text-sm text-ink-700">{submission.note}</p>}
            {submission.has_proof && (
                <a href={route('admin.payments.manual.download', submission.id)} className="mt-2 inline-block text-sm text-ink-700 underline hover:text-gold-600">
                    Download proof
                </a>
            )}

            <div className="mt-4 flex items-center gap-3">
                <Button onClick={approve} className="w-auto px-5">Approve & enroll</Button>
                <SecondaryButton type="button" onClick={() => setRejecting((v) => !v)}>Reject</SecondaryButton>
            </div>

            {rejecting && (
                <form onSubmit={reject} className="mt-3 space-y-2">
                    <Textarea
                        label="Reason (optional)"
                        name="review_note"
                        rows={2}
                        value={form.data.review_note}
                        onChange={(e) => form.setData('review_note', e.target.value)}
                        error={form.errors.review_note}
                    />
                    <Button type="submit" loading={form.processing} className="w-auto px-5">Confirm rejection</Button>
                </form>
            )}
        </div>
    );
}
