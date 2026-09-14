import { FormEventHandler } from 'react';
import { Head, useForm, usePage } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { adminNav } from '@/Navigation/adminNav';
import Select from '@/Components/ui/Select';
import Button from '@/Components/ui/Button';
import { PageProps } from '@/types';

interface EnrollmentRow {
    id: number;
    student: string;
    course: string;
    cohort: string | null;
    status: string;
    enrolled_at: string | null;
    is_override: boolean;
}

interface CohortOption {
    id: number;
    label: string;
    status: string;
    capacity: number;
    enrolled: number;
}

interface IndexProps {
    enrollments: EnrollmentRow[];
    students: { id: number; name: string; email: string }[];
    cohorts: CohortOption[];
}

export default function Index({ enrollments, students, cohorts }: IndexProps) {
    const { flash } = usePage<PageProps>().props;
    const { data, setData, post, processing, errors, reset } = useForm({
        user_id: '' as number | '',
        cohort_id: '' as number | '',
        override: false,
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('admin.enrollments.store'), { onSuccess: () => reset() });
    };

    return (
        <AuthenticatedLayout nav={adminNav()} title="Enrollments">
            <Head title="Enrollments" />

            {flash.status && (
                <p className="mb-4 rounded-md bg-green-50 px-4 py-2.5 text-sm text-green-700">{flash.status}</p>
            )}

            <div className="grid lg:grid-cols-[1fr_360px] gap-8">
                <div className="rounded-lg border border-ink-100 bg-white overflow-hidden">
                    <table className="w-full text-sm">
                        <thead className="bg-ink-50 text-left text-ink-500">
                            <tr>
                                <th className="px-5 py-3 font-medium">Student</th>
                                <th className="px-5 py-3 font-medium">Cohort</th>
                                <th className="px-5 py-3 font-medium">Status</th>
                                <th className="px-5 py-3 font-medium">Enrolled</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-ink-100">
                            {enrollments.map((e) => (
                                <tr key={e.id}>
                                    <td className="px-5 py-3 font-medium text-ink-900">{e.student}</td>
                                    <td className="px-5 py-3 text-ink-500">{e.cohort ?? e.course}</td>
                                    <td className="px-5 py-3 text-ink-500">
                                        {e.status}{e.is_override && <span className="ml-1 text-xs text-gold-600">(override)</span>}
                                    </td>
                                    <td className="px-5 py-3 text-ink-500">{e.enrolled_at ?? '—'}</td>
                                </tr>
                            ))}
                            {enrollments.length === 0 && (
                                <tr><td colSpan={4} className="px-5 py-8 text-center text-ink-500">No enrollments yet.</td></tr>
                            )}
                        </tbody>
                    </table>
                </div>

                <form onSubmit={submit} className="rounded-lg border border-ink-100 bg-white p-5 space-y-4 h-fit">
                    <h2 className="text-sm font-semibold text-ink-900">Manually enroll a student</h2>
                    <p className="text-xs text-ink-500">
                        For bank-transfer/manual payment confirmations. Real checkout (Paystack/Flutterwave) lands in Phase 5.
                    </p>

                    <Select label="Student" name="user_id" value={data.user_id} onChange={(e) => setData('user_id', Number(e.target.value))} error={errors.user_id}>
                        <option value="">Select a student</option>
                        {students.map((s) => <option key={s.id} value={s.id}>{s.name} ({s.email})</option>)}
                    </Select>

                    <Select label="Cohort" name="cohort_id" value={data.cohort_id} onChange={(e) => setData('cohort_id', Number(e.target.value))} error={errors.cohort_id}>
                        <option value="">Select a cohort</option>
                        {cohorts.map((c) => (
                            <option key={c.id} value={c.id}>
                                {c.label} — {c.status} ({c.enrolled}/{c.capacity})
                            </option>
                        ))}
                    </Select>

                    <label className="flex items-center gap-2 text-sm text-ink-700">
                        <input type="checkbox" checked={data.override} onChange={(e) => setData('override', e.target.checked)}
                            className="rounded border-ink-300 text-gold-500 focus:ring-gold-500/40" />
                        Override capacity/deadline rules
                    </label>

                    <Button type="submit" loading={processing}>Enroll</Button>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}
