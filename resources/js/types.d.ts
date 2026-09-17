export interface AuthUser {
    id: number;
    name: string;
    email: string;
    role: 'super_admin' | 'admin' | 'instructor' | 'staff' | 'student';
    email_verified: boolean;
    referral_code: string | null;
}

export interface PageProps {
    auth: { user: AuthUser | null };
    flash: { status?: string | null };
}
