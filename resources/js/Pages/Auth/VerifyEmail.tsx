import { FormEventHandler } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import GuestLayout from '@/Layouts/GuestLayout';
import Button from '@/Components/ui/Button';

export default function VerifyEmail({ status }: { status?: string }) {
    const { post, processing } = useForm({});

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('verification.send'));
    };

    return (
        <GuestLayout>
            <Head title="Verify email" />

            <h1 className="text-2xl font-semibold text-ink-900">Verify your email</h1>
            <p className="mt-2 text-sm text-ink-500 leading-relaxed">
                We sent a verification link to your email address. Follow it to activate
                your account. Didn't get it? We can send another.
            </p>

            {status === 'verification-link-sent' && (
                <p className="mt-4 rounded-md bg-green-50 px-3 py-2 text-sm text-green-700">
                    A new verification link has been sent.
                </p>
            )}

            <form onSubmit={submit} className="mt-6 space-y-4">
                <Button type="submit" loading={processing}>Resend verification email</Button>
            </form>

            <Link href={route('logout')} method="post" as="button" className="mt-4 block text-sm text-ink-500 hover:text-gold-600">
                Log out
            </Link>
        </GuestLayout>
    );
}
