# SaaS Architecture

## Tenant boundary

A tenant is one rented client business. Platform administrators have `tenant_id = null`; tenant owners and staff have a non-null `tenant_id`. Every tenant-owned table includes `tenant_id`, and all tenant routes use authenticated-user context rather than accepting arbitrary tenant IDs from requests.

## Monthly subscription lifecycle

| Status | Meaning |
|---|---|
| `pending` | Tenant registered but payment has not been verified |
| `active` | Current monthly period is paid and access is enabled |
| `past_due` | Renewal was not verified by the due date |
| `cancelled` | Tenant or platform owner cancelled renewal |
| `expired` | Current period ended without a successful renewal |

A scheduled Laravel command should find subscriptions whose `current_period_end` has passed and mark them `past_due` or `expired`. Recharge routes should use a subscription-access middleware or policy and reject expired tenants.

## Payment rules

There are two payment order types: `subscription` for monthly rent and `wallet_deposit` for recharge funds. A PipraPay callback must not directly trust a browser return request. The application should verify the transaction with PipraPay, compare the verified amount/currency/order ID to the local row, and then process it once inside a database transaction.

The unique `merchant_order_id`, optional unique gateway transaction ID, and a processed-event/idempotency strategy prevent duplicate credit. Store the raw gateway response only if sensitive values are redacted or access is restricted.

## Wallet rules

The wallet balance is a cached current balance. Every change must also create an immutable `wallet_transactions` record containing the before and after balances. Credit and debit operations should use `DB::transaction()` and `lockForUpdate()` on the wallet row. A recharge should debit only after the provider operation is accepted according to the chosen business rule; failed operations must be compensated or refunded exactly once.

## Credential rules

`tenant_provider_credentials.credentials` and `customers.pin_encrypted` must be encrypted before saving. Use Laravel encrypted casts or `Crypt::encryptString()`. Never log credentials or expose them in API responses. Provider calls should use a queued job with retry limits, timeouts, and a redacted provider response.
