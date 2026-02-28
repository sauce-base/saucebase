export interface PriceMetadata {
    badge?: string;
    label?: string;
    original_price?: string;
}

export interface ProductMetadata {
    badge?: string;
    tagline?: string;
    cta_label?: string;
    cta_url?: string;
    [key: string]: any;
}

export interface Price {
    id: number;
    amount: number;
    currency: string;
    interval: string | null;
    interval_count?: number;
    provider_price_id?: string;
    is_active?: boolean;
    metadata?: PriceMetadata;
}

export interface Product {
    id: number;
    name: string;
    slug?: string;
    description: string | null;
    features: string[];
    is_highlighted?: boolean;
    prices: Price[];
    metadata?: ProductMetadata;
}

export interface CheckoutSession {
    id: number;
    uuid: string;
    price: Price & { product: Product };
    status: string;
    expires_at: string | null;
}

export type PaymentMethodCategory = 'card' | 'bank' | 'wallet' | 'unknown';

export interface PaymentMethod {
    type: string;
    category: PaymentMethodCategory;
    details: Modules.Billing.Data.PaymentMethodDetails | null;
}

export interface Subscription {
    id: number;
    status: string;
    current_period_starts_at: string | null;
    current_period_ends_at: string | null;
    cancelled_at: string | null;
    ends_at: string | null;
    plan_name: string | null;
    interval: string | null;
}

export interface Invoice {
    id: number;
    number: string | null;
    total: number;
    currency: string | null;
    status: string;
    paid_at: string | null;
    hosted_invoice_url: string | null;
}
