import { FormEventHandler, useState } from 'react';
import { Head, useForm, usePage } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { adminNav } from '@/Navigation/adminNav';
import Textarea from '@/Components/ui/Textarea';
import Button from '@/Components/ui/Button';
import SecondaryButton from '@/Components/ui/SecondaryButton';
import { PageProps } from '@/types';

interface CertificateRow {
    id: number;
    certificate_id: string;
    holder_name: string;
    course_title: string;
    cohort_label: string | null;
    issued_at: string;
    status: string;
    auto_issued: boolean;
}

export default function Index({ certificates }: { certificates: CertificateRow[] }) {
    const { flash } = usePage<PageProps>().props;

    return (
        <AuthenticatedLayout nav={adminNav()} title="Certificates">
            <Head title="Certificates" />

            {flash.status && (
                <p className="mb-4 rounded-md bg-green-50 px-4 py-2.5 text-sm text-green-700">{flash.status}</p>
            )}

            <div className="rounded-lg border border-ink-100 bg-white overflow-hidden">
                <table className="w-full text-sm">
                    <thead className="bg-ink-50 text-left text-ink-500">
                        <tr>
                            <th className="px-5 py-3 font-medium">Certificate</th>
                            <th className="px-5 py-3 font-medium">Holder</th>
                            <th className="px-5 py-3 font-medium">Course</th>
                            <th className="px-5 py-3 font-medium">Issued</th>
                            <th className="px-5 py-3 font-medium">Status</th>
                            <th className="px-5 py-3 font-medium"></th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-ink-100">
                        {certificates.map((c) => <CertificateRow key={c.id} certificate={c} />)}
                        {certificates.length === 0 && (
                            <tr><td colSpan={6} className="px-5 py-8 text-center text-ink-500">No certificates issued yet.</td></tr>
                        )}
                    </tbody>
                </table>
            </div>
        </AuthenticatedLayout>
    );
}

function CertificateRow({ certificate }: { certificate: CertificateRow }) {
    const [revoking, setRevoking] = useState(false);
    const form = useForm({ revoked_reason: '' });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        form.post(route('admin.certificates.revoke', certificate.id), { onSuccess: () => setRevoking(false) });
    };

    return (
        <tr>
            <td className="px-5 py-3 font-mono text-xs text-ink-900">
                {certificate.certificate_id}
                {certificate.auto_issued && <span className="ml-2 text-xs text-ink-400">(auto)</span>}
            </td>
            <td className="px-5 py-3 text-ink-900">{certificate.holder_name}</td>
            <td className="px-5 py-3 text-ink-500">{certificate.course_title}{certificate.cohort_label && ` — ${certificate.cohort_label}`}</td>
            <td className="px-5 py-3 text-ink-500">{certificate.issued_at}</td>
            <td className="px-5 py-3">
                <span className={`text-xs font-medium ${certificate.status === 'active' ? 'text-green-700' : 'text-red-600'}`}>
                    {certificate.status}
                </span>
            </td>
            <td className="px-5 py-3 text-right">
                {certificate.status === 'active' && (
                    revoking ? (
                        <form onSubmit={submit} className="flex items-center gap-2">
                            <Textarea label="" name="revoked_reason" rows={1} value={form.data.revoked_reason}
                                onChange={(e) => form.setData('revoked_reason', e.target.value)} error={form.errors.revoked_reason} />
                            <Button type="submit" loading={form.processing} className="w-auto px-3">Confirm</Button>
                        </form>
                    ) : (
                        <SecondaryButton type="button" onClick={() => setRevoking(true)}>Revoke</SecondaryButton>
                    )
                )}
            </td>
        </tr>
    );
}
