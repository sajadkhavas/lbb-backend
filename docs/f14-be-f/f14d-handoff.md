# F14D handoff — frozen backend contract

Frontend F14D must start only from a BE-G accepted Wave 3 SHA that preserves contract version `2026-08-09-f14-be-f`.

## Frontend may rely on

- `/api/v1` paths documented in `docs/openapi.json`.
- Public catalog data being publication/evidence gated by the backend.
- Price, inventory, cart validation, quotes, totals, orders and payment state being backend authoritative.
- Integer Toman API money objects.
- Authenticated commerce returning 401/403 when no valid customer session exists.
- Checkout commit and payment initiation requiring `Idempotency-Key` (16–120 characters, `[A-Za-z0-9:_-]`).
- Payment capabilities failing closed when a real provider is unavailable.

## Frontend must not

- Reintroduce frontend-authoritative price or stock.
- Invent endpoints, fields, provider success, tracking data, shipping promises or refund success.
- Treat legacy unversioned compatibility endpoints as the F14D contract.
- Assume `backendComplete`, `frontendIntegrated` or `productionDeployed` before BE-G / deployment registration says so.

## Integration order

1. Build a typed client from the frozen OpenAPI/contract.
2. Replace prototype catalog adapters route by route.
3. Wire auth/account/order flows against real responses.
4. Replace cart/sessionStorage commerce truth with server validation and quotes.
5. Wire checkout commit and payment only through backend capabilities.
6. Run frontend regression, SEO, RTL, accessibility and fail-closed tests before removing prototype fallbacks.
