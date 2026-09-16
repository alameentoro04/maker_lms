import { FormEventHandler, useState } from 'react';
import { Head, useForm, usePage } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { adminNav } from '@/Navigation/adminNav';
import Button from '@/Components/ui/Button';
import { PageProps } from '@/types';

interface StudentRow {
    user_id: number;
    name: string;
    status: string;
    verification_source: string | null;
    clicked_join: boolean;
}

const STATUSES = ['pending', 'present', 'absent', 'late', 'excused'];

export default function Attendance({ cohort, liveClass, students }: {
    cohort: { id: number; name: string };
    liveClass: { id: number; title: string };
    students: StudentRow[];
}) {
    const { flash } = usePage<PageProps>().props;
    const [records, setRecords] = useState<Record<number, string>>(
        Object.fromEntries(students.map((s) => [s.user_id, s.status === 'pending' ? 'present' : s.status]))
    );
    const form = useForm({});

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        form.transform(() => ({
            records: Object.entries(records).map(([user_id, status]) => ({ user_id: Number(user_id), status })),
        }));
        form.post(route('admin.live-classes.attendance.mark', liveClass.id));
    };

    return (
        <AuthenticatedLayout nav={adminNav()} title={`Attendance — ${liveClass.title}`}>
            <Head title={`Attendance — ${liveClass.title}`} />

            {flash.status && <p className="mb-4 rounded-md bg-green-50 px-4 py-2.5 text-sm text-green-700">{flash.status}</p>}

            <p className="text-sm text-ink-500">{cohort.name}</p>
            <p className="mt-1 text-xs text-ink-500">
                "Clicked join" is a raw LMS signal, not verified attendance — confirm each student below.
            </p>

            <form onSubmit={submit} className="mt-4 rounded-lg border border-ink-100 bg-white overflow-hidden">
                <table className="w-full text-sm">
                    <thead className="bg-ink-50 text-left text-ink-500">
                        <tr>
                            <th className="px-5 py-3 font-medium">Student</th>
                            <th className="px-5 py-3 font-medium">Clicked join?</th>
                            <th className="px-5 py-3 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-ink-100">
                        {students.map((s) => (
                            <tr key={s.user_id}>
                                <td className="px-5 py-3 font-medium text-ink-900">{s.name}</td>
                                <td className="px-5 py-3 text-ink-500">{s.clicked_join ? 'Yes' : 'No'}</td>
                                <td className="px-5 py-3">
                                    <select
                                        value={records[s.user_id]}
                                        onChange={(e) => setRecords((r) => ({ ...r, [s.user_id]: e.target.value }))}
                                        className="rounded-md border border-ink-100 bg-white px-2 py-1 text-sm"
                                    >
                                        {STATUSES.filter((st) => st !== 'pending').map((st) => <option key={st} value={st}>{st}</option>)}
                                    </select>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
                <div className="p-4 border-t border-ink-100">
                    <Button type="submit" loading={form.processing} className="w-auto px-5">Save attendance</Button>
                </div>
            </form>
        </AuthenticatedLayout>
    );
}
