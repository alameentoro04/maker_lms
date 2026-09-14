import { FormEventHandler, useState } from 'react';
import { Head, router } from '@inertiajs/react';
import PublicLayout from '@/Layouts/PublicLayout';
import TextField from '@/Components/ui/TextField';
import Button from '@/Components/ui/Button';

export default function VerifyLookup() {
    const [certificateId, setCertificateId] = useState('');
    const [submitting, setSubmitting] = useState(false);

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        setSubmitting(true);
        router.get(route('verify', certificateId.trim()), {}, { onFinish: () => setSubmitting(false) });
    };

    return (
        <PublicLayout>
            <Head title="Verify a certificate" />

            <div className="mx-auto max-w-md px-6 py-14">
                <h1 className="font-display text-3xl text-ink-900">Verify a certificate</h1>
                <p className="mt-2 text-ink-500">
                    Enter the certificate ID printed on the certificate (e.g. MKR-GD-2026-000001).
                </p>

                <form onSubmit={submit} className="mt-6 space-y-4">
                    <TextField
                        label="Certificate ID"
                        name="certificate_id"
                        value={certificateId}
                        onChange={(e) => setCertificateId(e.target.value)}
                        placeholder="MKR-GD-2026-000001"
                        autoFocus
                    />
                    <Button type="submit" loading={submitting} disabled={!certificateId.trim()}>
                        Verify
                    </Button>
                </form>
            </div>
        </PublicLayout>
    );
}
