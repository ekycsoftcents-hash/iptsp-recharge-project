# IPTSP Recharge SaaS Project

This is a starter architecture for a multi-tenant recharge SaaS that is rented to clients on a monthly subscription. Each tenant manages its own customers, provider credentials, wallet, and recharge operations. The application is independent of WHMCS.

## Core model

The platform owner manages subscription plans, tenants, access, and billing. Each tenant has isolated data through a required `tenant_id` on tenant-owned tables. Provider credentials are stored in the `tenant_provider_credentials` table and should be encrypted using Laravel's `Crypt` facade or encrypted casts.

| Area | Responsibility |
|---|---|
| Platform admin | Plans, tenants, subscriptions, payment review, platform settings |
| Tenant owner/staff | Customers, provider credentials, wallet deposits, recharge requests |
| PipraPay | Monthly subscription payments and optional tenant wallet deposits |
| Provider adapter | A common interface for iTalk, Ranksitt, WebVoice, or future providers |

## Suggested Laravel setup

```bash
laravel new recharge-saas
cp -R laravel-recharge-saas/app recharge-saas/
cp -R laravel-recharge-saas/database/migrations recharge-saas/database/
cp -R laravel-recharge-saas/routes recharge-saas/
php artisan migrate
php artisan storage:link
```

Use PHP 8.2+ and a current Laravel release. Do not commit `.env`, API keys, passwords, or production SQL dumps.

## Environment variables

```dotenv
APP_URL=https://your-domain.example
DB_CONNECTION=mysql
DB_DATABASE=recharge_saas
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

PIPRAPAY_BASE_URL=https://your-piprapay-host.example
PIPRAPAY_API_KEY=replace_me
PIPRAPAY_WEBHOOK_SECRET=replace_me
PIPRAPAY_RETURN_URL=https://your-domain.example/payments/piprapay/return
PIPRAPAY_CANCEL_URL=https://your-domain.example/payments/piprapay/cancel
```

PipraPay's official documentation describes API-key authentication, dynamic payment links, transaction management, and webhook notifications. Keep the API key server-side and verify payment status server-side before activating a subscription or crediting a wallet. See the official documentation: https://docs.piprapay.com/reference/overview

## Billing workflow

A tenant selects a monthly plan. The server creates a pending `payment_orders` row and starts a PipraPay payment. The return page is informational only. The webhook or server-side verification endpoint is the source of truth. After a verified successful payment, the application marks the payment order as paid, creates or renews the tenant subscription, and records the event in `activity_logs`. Webhook handling must be idempotent so that the same event cannot credit a tenant twice.

For wallet deposits, use a separate `payment_orders.order_type = wallet_deposit`; after verification, create one immutable credit in `wallet_transactions`. Recharge debits must use a database transaction and row locking so concurrent requests cannot overspend the wallet.

## Tenant isolation rules

All tenant-owned queries must be scoped by the authenticated user's `tenant_id`. A global admin may access all tenants through explicit admin routes. Never trust a tenant ID supplied by a browser request. Resolve it from the authenticated user or a server-side admin context. Add policy checks and feature tests before exposing any production endpoint.

## Provider integration

The project intentionally uses a provider adapter boundary. Add one class per provider under `app/Services/Providers` and implement a shared interface. Provider credentials must be encrypted at rest, masked in logs, and never returned to browser responses. Do not place credentials in JavaScript, templates, or public configuration.

## Production checklist

Configure HTTPS, queue workers, scheduled jobs for subscription expiry, database backups, rate limiting, audit logging, webhook signature verification, and alerts for failed recharge calls. Test PipraPay webhooks in a staging environment before enabling live payments. The included migrations are a foundation, not a complete production payment system.
