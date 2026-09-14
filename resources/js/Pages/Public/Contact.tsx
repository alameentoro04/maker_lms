import { FormEventHandler } from 'react';
import { Head, useForm, usePage } from '@inertiajs/react';
import PublicLayout from '@/Layouts/PublicLayout';
import TextField from '@/Components/ui/TextField';
import Button from '@/Components/ui/Button';
import { PageProps } from '@/types';

export default function Contact() {
    const { flash } = usePage<PageProps>().props;
    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        email: '',
        subject: '',
        message: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('contact.submit'), { onSuccess: () => reset() });
    };

    return (
        <PublicLayout>
            <Head title="Contact" />

            <div className="mx-auto max-w-xl px-6 py-14">
                <h1 className="font-display text-3xl text-ink-900">Get in touch</h1>
                <p className="mt-2 text-ink-500">
                    Questions about a course or cohort? Send us a message and we'll reply by email.
                </p>

                {flash.status && (
                    <p className="mt-6 rounded-md bg-green-50 px-4 py-2.5 text-sm text-green-700">{flash.status}</p>
                )}

                <form onSubmit={submit} className="mt-8 space-y-4">
                    <TextField
                        label="Name"
                        name="name"
                        value={data.name}
                        onChange={(e) => setData('name', e.target.value)}
                        error={errors.name}
                    />
                    <TextField
                        label="Email"
                        type="email"
                        name="email"
                        value={data.email}
                        onChange={(e) => setData('email', e.target.value)}
                        error={errors.email}
                    />
                    <TextField
                        label="Subject"
                        name="subject"
                        value={data.subject}
                        onChange={(e) => setData('subject', e.target.value)}
                        error={errors.subject}
                    />
                    <div>
                        <label htmlFor="message" className="block text-sm font-medium text-ink-900">
                            Message
                        </label>
                        <textarea
                            id="message"
                            name="message"
                            rows={5}
                            value={data.message}
                            onChange={(e) => setData('message', e.target.value)}
                            className="mt-1.5 w-full rounded-md border border-ink-100 bg-white px-3 py-2 text-sm text-ink-900 shadow-sm focus:border-gold-500 focus:outline-none focus:ring-2 focus:ring-gold-500/30"
                        />
                        {errors.message && <p className="mt-1.5 text-sm text-red-600">{errors.message}</p>}
                    </div>
                    <Button type="submit" loading={processing}>Send message</Button>
                </form>
            </div>
        </PublicLayout>
    );
}
