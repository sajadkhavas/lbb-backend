# LBB P3 — Frontend Control Surface Matrix

Status: **AUDIT IN PROGRESS**

## Governing rule

Every frontend business/content/config/data surface must have an explicit backend owner, an admin control surface, a versioned API contract, and a frontend consumer. Developer-owned presentation structure stays in Git; merchant-editable truth must not be hardcoded as the authoritative live source.

## Ownership boundary

### Backend/Admin/API controlled
- catalog: products, variants, media, price, stock and collections
- navigation labels/targets and announcement content
- homepage/brand-intro content, hero media, CTAs and merchandising selections
- page content, FAQ, contact/social/trust values and legal/support copy
- journal/lookbook/editorial content
- category merchandising/SEO copy and page/global SEO values
- shipping/business settings
- customer, auth, cart, checkout, orders, returns, inventory, payment and notifications

### Developer controlled
- route/component/layout implementation
- design system/CSS and responsive behavior
- security/validation logic
- API implementation and contract structure
- accessibility/performance engineering

## Initial verified backend truth

- `StoreSetting`, `ContentPage`, `Faq` and `GalleryItem` models exist.
- Their database tables exist in `2026_07_20_000000_create_store_operations_tables.php`.
- Public legacy storefront endpoints exist for settings/pages/FAQ/lookbook.
- No Filament resources for these four CMS models were found in the accepted P2 baseline.
- Existing `/api/v1` catalog/auth/commerce routes remain the frozen P2 base contract; P3 storefront-content additions must be additive/versioned.

## Initial verified frontend truth

- product/catalog transport already supports backend mode.
- collection metadata, journal content and lookbook content are still resolved from local frontend editorial data even when a backend is configured.
- P3 must remove these local objects as the authoritative live source while retaining safe prototype/test fallback where explicitly required.

## Matrix

| Frontend surface | Backend owner | Admin | API | Frontend live consumer | P3 status |
| --- | --- | --- | --- | --- | --- |
| Products / variants / media / stock | Catalog domain | Existing Product/Inventory resources | Existing `/api/v1/products*` | Existing backend adapter | VERIFY |
| Collections | Collection domain | Existing Collection resource | Existing `/api/v1/collections*` | Metadata currently local | GAP |
| Journal | Article domain | Existing Article resource | Existing `/api/v1/journal*` | Currently local | GAP |
| Lookbook | Gallery/content domain | Missing CMS admin verified | Legacy endpoint + v1 lookbook | Currently local | GAP |
| FAQ | `Faq` | Missing | Legacy `/api/storefront/faq` | Currently local route copy | GAP |
| Static pages / page SEO | `ContentPage` | Missing | Legacy `/api/storefront/pages/{slug}` | Currently local | GAP |
| Global/contact/social/trust settings | `StoreSetting` | Missing | Legacy `/api/storefront/settings` | Currently local/hardcoded | GAP |
| Header announcement / navigation | Structured storefront settings/navigation | Missing/unknown | Missing/unknown | currently local | GAP |
| Homepage / hero / brand intro | Structured storefront settings/content | Missing/unknown | Missing/unknown | currently local | GAP |
| Footer/contact | Store settings/content | Missing | Legacy settings | currently local | GAP |
| Shipping display/rules | Delivery/settings domain | Existing commerce rules; admin coverage to verify | Existing shipping endpoints | partial | AUDIT |
| Auth/account/orders/cart/checkout/returns | Commerce/customer domains | Existing operational resources | Existing `/api/v1` | existing backend client | VERIFY |

A row is not PASS until the admin, API and live frontend consumer are all covered by tests.
