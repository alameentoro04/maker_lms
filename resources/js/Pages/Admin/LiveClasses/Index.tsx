import { FormEventHandler, useState } from 'react';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { adminNav } from '@/Navigation/adminNav';
import TextField from '@/Components/ui/TextField';
import Textarea from '@/Components/ui/Textarea';
import Select from '@/Components/ui/Select';
import Button from '@/Components/ui/Button';
import { PageProps } from '@/types';

interface LiveClassRow {
    id: number;
    title: string;
    starts_at: string;
    status: string;
}

export default function Index({ cohort, liveClasses }: { cohort: { id: number; name: string }; liveClasses: LiveClassRow[] }) {
    const { flash } = usePage<PageProps>().props;
    const [showForm, setShowForm] = useState(false);
    const form = useForm({
        title: '', description: '', starts_at: '', ends_at: '', meet_url: '', status: 'scheduled', notes: '', recording_url: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        form.post(route('admin.cohorts.live-classes.store', cohort.id), {
            onSuccess: () => { form.reset(); setShowForm(false); },
        });
    };

    return (
        <AuthenticatedLayout nav={adminNav()} title={`Live classes — ${cohort.name}`}>
            <Head title={`Live classes — ${cohort.name}`} />

            {flash.status && <p className="mb-4 rounded-md bg-green-50 px-4 py-2.5 text-sm text-green-700">{flash.status}</p>}

            <div className="flex justify-end mb-4">
                <Button onClick={() => setShowForm((v) => !v)} className="w-auto px-5">
                    {showForm ? 'Cancel' : 'Schedule a class'}
                </Button>
            </div>

            {showForm && (
                <form onSubmit={submit} className="mb-6 rounded-lg border border-ink-100 bg-white p-5 space-y-4 max-w-xl">
                    <TextField label="Title" name="title" value={form.data.title} onChange={(e) => form.setData('title', e.target.value)} error={form.errors.title} />
                    <Textarea label="Description (optional)" name="description" rows={2} value={form.data.description} onChange={(e) => form.setData('description', e.target.value)} error={form.errors.description} />
                    <div className="grid grid-cols-2 gap-4">
                        <TextField label="Starts" name="starts_at" type="datetime-local" value={form.data.starts_at} onChange={(e) => form.setData('starts_at', e.target.value)} error={form.errors.starts_at} />
                        <TextField label="Ends" name="ends_at" type="datetime-local" value={form.data.ends_at} onChange={(e) => form.setData('ends_at', e.target.value)} error={form.errors.ends_at} />
                    </div>
                    <TextField label="Google Meet URL" name="meet_url" value={form.data.meet_url} onChange={(e) => form.setData('meet_url', e.target.value)} error={form.errors.meet_url} />
                    <Select label="Status" name="status" value={form.data.status} onChange={(e) => form.setData('status', e.target.value)}>
                        <option value="scheduled">Scheduled</option>
                        <option value="live">Live</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </Select>
                    <TextField label="Recording URL (optional, add after class)" name="recording_url" value={form.data.recording_url} onChange={(e) => form.setData('recording_url', e.target.value)} error={form.errors.recording_url} />
                    <Button type="submit" loading={form.processing}>Save — students will be notified</Button>
                </form>
            )}

            <div className="rounded-lg border border-ink-100 bg-white overflow-hidden">
                <table className="w-full text-sm">
                    <thead className="bg-ink-50 text-left text-ink-500">
                        <tr>
                            <th className="px-5 py-3 font-medium">Title</th>
                            <th className="px-5 py-3 font-medium">Starts</th>
                            <th className="px-5 py-3 font-medium">Status</th>
                            <th className="px-5 py-3 font-medium"></th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-ink-100">
                        {liveClasses.map((lc) => (
                            <tr key={lc.id}>
                                <td className="px-5 py-3 font-medium text-ink-900">{lc.title}</td>
                                <td className="px-5 py-3 text-ink-500">{lc.starts_at}</td>
                                <td className="px-5 py-3 text-ink-500">{lc.status}</td>
                                <td className="px-5 py-3 text-right">
                                    <Link href={route('admin.cohorts.live-classes.attendance', [cohort.id, lc.id])} className="text-sm text-ink-700 hover:text-gold-600">
                                        Attendance →
                                    </Link>
                                </td>
                            </tr>
                        ))}
                        {liveClasses.length === 0 && (
                            <tr><td colSpan={4} className="px-5 py-8 text-center text-ink-500">No live classes scheduled yet.</td></tr>
                        )}
                    </tbody>
                </table>
            </div>
        </AuthenticatedLayout>
    );
}
