# Payment Handlers

Small PHP 7.4 library for processing `SALE` requests against Rafinita S2S CARD API with four result cases:
- `success`
- `declined`
- `redirect`
- `waiting`

## Requirements

- PHP 7.4+

## Environment

Create `.env.local` in the project root:

```bash
PAYMENT_URL=https://dev-api.rafinita.com/post
PUBLIC_KEY=your_public_key
PASS=your_pass
```

`.env.local` is ignored by git via `.gitignore`.

## Run (native PHP, one command)

```bash
set -a && source .env.local && set +a && php example.php
```

## What the example does

- Builds `PaymentHandlerRegistry` with:
  - `SuccessPaymentHandler`
  - `PendingPaymentHandler`
  - `FailedPaymentHandler`
  - `RedirectPaymentHandler`
- Creates `PaymentProcessor`
- Sends a `SALE` request
- Prints result status and message
- Prints redirect payload data when `status=redirect`
