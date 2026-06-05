export interface Paginated<T> {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
    meta?: { current_page: number; last_page: number; total: number; from: number; to: number };
    current_page: number;
    last_page: number;
    total: number;
    from: number;
    to: number;
}

export interface Option {
    value: string;
    label: string;
    months?: number;
}

export interface AuthUser {
    id: number;
    name: string;
    email: string;
    roles: string[];
    permissions: string[];
}

export type PageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    auth: { user: AuthUser | null };
    flash: { success: string | null; error: string | null };
};

export interface Room {
    id: number;
    room_number: string;
    type: string;
    price: string;
    status: string;
    facilities: string[] | null;
    description: string | null;
    photos?: { id: number; path: string; is_primary: boolean }[];
}

export interface Tenant {
    id: number;
    name: string;
    nik: string;
    phone: string;
    email: string | null;
    address: string | null;
    ktp_photo: string | null;
    emergency_contact_name: string | null;
    emergency_contact_phone: string | null;
    leases?: Lease[];
}

export interface Lease {
    id: number;
    room_id: number;
    tenant_id: number;
    start_date: string;
    end_date: string;
    ended_at: string | null;
    duration: string;
    monthly_price: string;
    deposit: string;
    deposit_refunded: string;
    deposit_deduction: string;
    status: string;
    notes: string | null;
    checkout_notes: string | null;
    room?: Room;
    tenant?: Tenant;
    invoices?: Invoice[];
}

export interface Invoice {
    id: number;
    invoice_number: string;
    period_start: string;
    period_end: string;
    due_date: string;
    amount: string;
    paid_amount: string;
    status: string;
    lease?: Lease;
    items?: { id: number; description: string; quantity: number; unit_price: string; amount: string }[];
    payments?: Payment[];
}

export interface Payment {
    id: number;
    amount: string;
    method: string;
    paid_at: string;
    proof_path: string | null;
    note: string | null;
    invoice?: Invoice;
    recorded_by_user?: AuthUser;
}

export interface Expense {
    id: number;
    category: string;
    amount: string;
    description: string;
    spent_at: string;
}

export interface ReminderTemplate {
    id: number;
    channel: string;
    offset_days: number;
    subject: string | null;
    body: string;
    is_active: boolean;
}
