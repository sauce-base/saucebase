declare namespace Modules.Billing.Data {
export type PaymentMethodData = {
providerPaymentMethodId: string;
type: Modules.Billing.Enums.PaymentMethodType;
details: Modules.Billing.Data.PaymentMethodDetails;
};
export type PaymentMethodDetails = {
brand: string | null;
last4: string | null;
expMonth: number | null;
expYear: number | null;
wallet: string | null;
funding: string | null;
email: string | null;
bankName: string | null;
country: string | null;
};
}
declare namespace Modules.Billing.Enums {
export type CheckoutSessionStatus = 'pending' | 'completed' | 'abandoned' | 'expired';
export type Currency = 'USD' | 'EUR' | 'GBP' | 'BRL';
export type InvoiceStatus = 'draft' | 'posted' | 'paid' | 'unpaid' | 'voided';
export type PaymentMethodType = 'card' | 'paypal' | 'sepa_debit' | 'us_bank_account' | 'bacs_debit' | 'link' | 'cashapp' | 'apple_pay' | 'google_pay' | 'bancontact' | 'ideal' | 'unknown';
export type PaymentStatus = 'pending' | 'succeeded' | 'failed' | 'refunded';
export type SubscriptionStatus = 'pending' | 'active' | 'past_due' | 'cancelled';
}
