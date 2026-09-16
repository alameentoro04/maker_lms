import { Head, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

interface LiveClassRow {
    id: number;
    title: string;
    description: string | null;
    starts_at: string;
    is_past: boolean;
    status: string;
    meet_url: string;
    recording_url: string | null;
    my_attendance_status: string | null;
    clicked_join: boolean;
}

export default function Index({ course, liveClasses }: { course: { title: string; slug: string }; liveClasses: LiveClassRow[] }) {
    const nav = [{ label: 'Dashboard', href: route('student.dashboard') }];

    const join = (lc: LiveClassRow) => {
        router.post(route('learn.live-classes.join', lc.id), {}, { preserveScroll: true });
        window.open(lc.meet_url, '_blank');
    };

    const upcoming = liveClasses.filter((lc) => !lc.is_past);
    const past = liveClasses.filter((lc) => lc.is_past);

    return (
        <AuthenticatedLayout nav={nav} title={`Live classes — ${course.title}`}>
            <Head title="Live classes" />

            <h2 className="text-sm font-semibold text-ink-900">Upcoming</h2>
            <div className="mt-3 space-y-3">
                {upcoming.map((lc) => (
                    <div key={lc.id} className="rounded-lg border border-ink-100 bg-white p-4 flex items-center justify-between">
                        <div>
                            <p className="text-sm font-medium text-ink-900">{lc.title}</p>
                            <p className="text-xs text-ink-500">{lc.starts_at}</p>
                        </div>
                        <button onClick={() => join(lc)} className="rounded-md bg-gold-500 px-4 py-2 text-sm font-medium text-ink-900 hover:bg-gold-400">
                            Join
                        </button>
                    </div>
                ))}
                {upcoming.length === 0 && <p className="text-sm text-ink-500">No upcoming classes scheduled.</p>}
            </div>

            <h2 className="mt-8 text-sm font-semibold text-ink-900">Past</h2>
            <div className="mt-3 space-y-3">
                {past.map((lc) => (
                    <div key={lc.id} className="rounded-lg border border-ink-100 bg-white p-4">
                        <div className="flex items-center justify-between">
                            <p className="text-sm font-medium text-ink-900">{lc.title}</p>
                            <p className="text-xs text-ink-500">{lc.my_attendance_status ?? 'not recorded'}</p>
                        </div>
                        <p className="text-xs text-ink-500 mt-1">{lc.starts_at}</p>
                        {lc.recording_url && (
                            <a href={lc.recording_url} target="_blank" rel="noreferrer" className="mt-2 inline-block text-sm text-ink-700 underline hover:text-gold-600">
                                Watch recording
                            </a>
                        )}
                    </div>
                ))}
                {past.length === 0 && <p className="text-sm text-ink-500">No past classes yet.</p>}
            </div>
        </AuthenticatedLayout>
    );
}
