import { Head } from '@inertiajs/react';
import PublicLayout from '@/Layouts/PublicLayout';

interface VerifyProps {
    certificateId: string;
    result:
        | { found: false }
        | {
              found: true;
              holder_name: string;
              course_title: string;
              cohort_label: string | null;
              issued_at: string;
              status: string;
              valid: boolean;
          };
}

export default function Verify({ certificateId, result }: VerifyProps) {
    return (
        <PublicLayout>
            <Head title={`Verify ${certificateId}`} />

            <div className="mx-auto max-w-md px-6 py-14">
                <h1 className="font-display text-2xl text-ink-900">Certificate verification</h1>
                <p className="mt-1 text-sm text-ink-500">{certificateId}</p>

                {result.found ? (
                    <div className={`mt-6 rounded-lg border p-6 ${result.valid ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50'}`}>
                        <p className={`text-sm font-semibold ${result.valid ? 'text-green-700' : 'text-red-700'}`}>
                            {result.valid ? 'Valid certificate' : `Not valid — ${result.status}`}
                        </p>
                        <dl className="mt-4 space-y-2 text-sm">
                            <div className="flex justify-between">
                                <dt className="text-ink-500">Holder</dt>
                                <dd className="font-medium text-ink-900">{result.holder_name}</dd>
                            </div>
                            <div className="flex justify-between">
                                <dt className="text-ink-500">Course</dt>
                                <dd className="font-medium text-ink-900">{result.course_title}</dd>
                            </div>
                            {result.cohort_label && (
                                <div className="flex justify-between">
                                    <dt className="text-ink-500">Cohort</dt>
                                    <dd className="font-medium text-ink-900">{result.cohort_label}</dd>
                                </div>
                            )}
                            <div className="flex justify-between">
                                <dt className="text-ink-500">Issued</dt>
                                <dd className="font-medium text-ink-900">{result.issued_at}</dd>
                            </div>
                        </dl>
                    </div>
                ) : (
                    <div className="mt-6 rounded-lg border border-ink-200 bg-ink-50 p-6">
                        <p className="text-sm font-medium text-ink-900">No certificate found</p>
                        <p className="mt-1 text-sm text-ink-500">
                            Double-check the ID and try again, or contact support if you believe this is an error.
                        </p>
                    </div>
                )}
            </div>
        </PublicLayout>
    );
}
