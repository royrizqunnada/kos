import type { AuthUser } from '@/types';

/** Super admin melewati semua gate (lihat AuthServiceProvider). */
export function can(user: AuthUser | null | undefined, permission: string): boolean {
    if (!user) return false;
    if (user.roles?.includes('super_admin')) return true;
    return user.permissions?.includes(permission) ?? false;
}
