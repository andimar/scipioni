export type AdminEvent = {
  id: number;
  title: string;
  slug: string;
  subtitle: string | null;
  short_description: string | null;
  full_description: string;
  cover_image_path: string | null;
  cover_image_url: string | null;
  starts_at: string | null;
  ends_at: string | null;
  venue_name: string;
  venue_address: string | null;
  capacity: number;
  price: string;
  status: string;
  booking_status: string;
  requires_approval: boolean;
  is_featured: boolean;
  bookings_count: number;
  category_id: number | null;
  category: {
    id: number;
    name: string;
    slug: string;
  } | null;
};

export type AdminEventCategory = {
  id: number;
  name: string;
  slug: string;
};

export type AdminEventPayload = {
  category_id: number | null;
  title: string;
  subtitle: string;
  short_description: string;
  full_description: string;
  cover_image_path: string;
  venue_name: string;
  venue_address: string;
  starts_at: string;
  ends_at: string;
  capacity: number;
  price: number;
  booking_status: string;
  status: string;
  requires_approval: boolean;
  is_featured: boolean;
};

export type AdminFieldErrors = Partial<Record<keyof AdminEventPayload | 'general', string>>;
