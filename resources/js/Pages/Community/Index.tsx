import { FormEventHandler, useState } from 'react';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import Textarea from '@/Components/ui/Textarea';
import Button from '@/Components/ui/Button';
import { PageProps } from '@/types';

interface Comment {
    id: number;
    author: string;
    body: string;
    created_at: string;
}

interface Post {
    id: number;
    author: string;
    author_id: number;
    body: string;
    created_at: string;
    comments: Comment[];
}

export default function Index({ cohort, posts }: { cohort: { id: number; name: string; slug: string } | null; posts: Post[] }) {
    const { auth } = usePage<PageProps>().props;
    const nav = [{ label: 'Dashboard', href: route('dashboard') }];
    const form = useForm({ body: '' });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        const url = cohort ? route('community.cohort.store', cohort.id) : route('community.store');
        form.post(url, { onSuccess: () => form.reset() });
    };

    return (
        <AuthenticatedLayout nav={nav} title={cohort ? `${cohort.name} — Community` : 'Community'}>
            <Head title="Community" />

            {!cohort && (
                <p className="mb-4 text-sm text-ink-500">General discussion, open to everyone.</p>
            )}

            <form onSubmit={submit} className="rounded-lg border border-ink-100 bg-white p-4">
                <Textarea label="Share something" name="body" rows={3} value={form.data.body} onChange={(e) => form.setData('body', e.target.value)} error={form.errors.body} />
                <Button type="submit" loading={form.processing} className="mt-3 w-auto px-5">Post</Button>
            </form>

            <div className="mt-6 space-y-4">
                {posts.map((post) => <PostCard key={post.id} post={post} currentUserId={auth.user?.id} />)}
                {posts.length === 0 && <p className="text-sm text-ink-500">No posts yet — be the first.</p>}
            </div>
        </AuthenticatedLayout>
    );
}

function PostCard({ post, currentUserId }: { post: Post; currentUserId?: number }) {
    const [showComment, setShowComment] = useState(false);
    const [showReport, setShowReport] = useState(false);
    const commentForm = useForm({ body: '' });
    const reportForm = useForm({ reason: '' });

    const submitComment: FormEventHandler = (e) => {
        e.preventDefault();
        commentForm.post(route('community.comments.store', post.id), {
            preserveScroll: true,
            onSuccess: () => { commentForm.reset(); setShowComment(false); },
        });
    };

    const submitReport: FormEventHandler = (e) => {
        e.preventDefault();
        reportForm.post(route('community.report', post.id), {
            preserveScroll: true,
            onSuccess: () => { reportForm.reset(); setShowReport(false); },
        });
    };

    return (
        <div className="rounded-lg border border-ink-100 bg-white p-4">
            <div className="flex items-center justify-between">
                <p className="text-sm font-medium text-ink-900">{post.author}</p>
                <span className="text-xs text-ink-500">{post.created_at}</span>
            </div>
            <p className="mt-2 text-sm text-ink-700 whitespace-pre-line">{post.body}</p>

            <div className="mt-3 flex gap-4 text-xs text-ink-500">
                <button onClick={() => setShowComment((v) => !v)} className="hover:text-gold-600">Comment</button>
                {post.author_id !== currentUserId && (
                    <button onClick={() => setShowReport((v) => !v)} className="hover:text-red-600">Report</button>
                )}
            </div>

            {post.comments.length > 0 && (
                <div className="mt-3 space-y-2 border-t border-ink-100 pt-3">
                    {post.comments.map((c) => (
                        <div key={c.id} className="text-sm">
                            <span className="font-medium text-ink-900">{c.author}: </span>
                            <span className="text-ink-700">{c.body}</span>
                        </div>
                    ))}
                </div>
            )}

            {showComment && (
                <form onSubmit={submitComment} className="mt-3 flex gap-2">
                    <input
                        type="text"
                        value={commentForm.data.body}
                        onChange={(e) => commentForm.setData('body', e.target.value)}
                        className="flex-1 rounded-md border border-ink-100 px-3 py-1.5 text-sm"
                        placeholder="Write a comment…"
                    />
                    <Button type="submit" loading={commentForm.processing} className="w-auto px-4">Send</Button>
                </form>
            )}

            {showReport && (
                <form onSubmit={submitReport} className="mt-3 flex gap-2">
                    <input
                        type="text"
                        value={reportForm.data.reason}
                        onChange={(e) => reportForm.setData('reason', e.target.value)}
                        className="flex-1 rounded-md border border-ink-100 px-3 py-1.5 text-sm"
                        placeholder="Why are you reporting this?"
                    />
                    <Button type="submit" loading={reportForm.processing} className="w-auto px-4">Submit</Button>
                </form>
            )}
        </div>
    );
}
