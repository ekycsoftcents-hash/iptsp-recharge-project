# External integration findings

## PipraPay official documentation

- Overview: https://docs.piprapay.com/reference/overview
  - API requests require an API key.
  - The API supports dynamic payment links, transaction verification, and webhook notifications.
- Create charge: https://docs.piprapay.com/reference/create-charge
  - Sandbox endpoint shown as `POST https://sandbox.piprapay.com/api/create-charge`.
  - A successful payment redirects to a configured `pp_url`/return URL with an invoice identifier; payment details must be verified through the Verify Payment API.
- Redirect checkout: https://docs.piprapay.com/reference/redirect-checkout
  - Sandbox endpoint shown as `POST https://sandbox.piprapay.com/api/checkout/redirect`.
  - Required body fields: `full_name`, `email_address`, `mobile_number`, `amount`, `currency`, `metadata`, `return_url`, and `webhook_url`.
  - Required header: `MH-S-PIPRAPAY-API-KEY` as shown in the documentation page.
- Verify payments: https://docs.piprapay.com/reference/verify-payments
  - Sandbox endpoint shown as `POST https://sandbox.piprapay.com/api/verify-payments`.
- Validate webhook: https://docs.piprapay.com/reference/validate-webhook
  - Webhook payload includes `pp_id`, customer/payment fields, `amount`, `currency`, `metadata`, `transaction_id`, and `status`.
  - The example validates the API key from the `mh-piprapay-api-key` header and returns HTTP 200 after processing.

## Legacy provider findings from `/home/ubuntu/upload/ALLZIP.zip`

- Ranksitt API: login at `/api/Account/SignIn`; client list at `/api/ResellerClientsRetail/GetClientsRetailsList`; recharge at `/api/ResellerClients/AddPayment`.
- Ranksitt recharge payload includes `clientId`, `clientType: 32`, `amount`, `type: Payment`, timestamp, description, and flags `addToInvoice: false`, `sendConfirmation: false`.
- WebVoice API: login at `/api/login`; client list at `/api/clients`; recharge at `/api/clients/{accountId}/payment` with `{amount, type: credit, description}`.
- Legacy code disabled SSL certificate verification; production Laravel implementation must not copy that behavior and should verify TLS certificates.
- The legacy iTalk path is scraper-based and needs a provider-specific, carefully tested implementation before enabling live recharge.
