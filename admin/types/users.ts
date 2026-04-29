export type AdminCustomerUser = {
  id: number;
  first_name: string;
  last_name: string;
  name: string;
  email: string;
  phone: string | null;
  is_active: boolean;
  bookings_count: number;
  last_login_at: string | null;
  created_at: string | null;
};

export type AdminStaffUser = {
  id: number;
  name: string;
  email: string;
  role: 'admin' | 'staff' | string;
  is_active: boolean;
  created_at: string | null;
};

export type CustomerUserPayload = {
  first_name: string;
  last_name: string;
  email: string;
  phone: string;
  is_active: boolean;
};

export type StaffUserPayload = {
  name: string;
  email: string;
  password: string;
  role: 'admin' | 'staff';
  is_active: boolean;
};

export type UserFieldErrors = Partial<Record<string, string>>;
