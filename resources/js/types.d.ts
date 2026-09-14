export interface AuthUser {
    id: number;
    name: string;
    email: string;
    role: 'super_admin' | 'admin' | 'instructor' | 'staff' | 'student';
    email_verified: boolean;
}

export interface PageProps {
    auth: { user: AuthUser | null };
    flash: { status?: string | null };
}
