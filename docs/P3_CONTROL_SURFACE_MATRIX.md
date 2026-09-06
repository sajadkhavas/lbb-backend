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

## Verified backend truth

- `StoreSetting`, `ContentPage`, `Faq` and `GalleryItem` models exist.
- Their database tables exist in `2026_07_20_000000_create_store_operations_tables.php`.
- Filament resources already exist for `StoreSetting`, `ContentPage`, `Faq` and `GalleryItem`; P3 must reuse them, not create duplicates.
- Existing Filament resources also cover Category, Collection, Post, DeliveryZone and the main operational commerce domains.
- Public legacy storefront endpoints exist for settings/pages/FAQ/gallery/posts.
- Existing `/api/v1` catalog/auth/commerce routes remain the frozen P2 base contract; P3 storefront-content additions must be additive/versioned.

## Verified frontend truth

- product/catalog transport already supports backend mode.
- category and collection API contracts already expose merchant-editable description/SEO fields.
- collection metadata, journal content and lookbook content are still resolved from local frontend data even when a backend is configured.
- brand/global SEO, announcement bar, navigation, homepage/hero/brand-intro and footer/contact content are still hardcoded/local in the frontend.
- P3 must remove local business objects as the authoritative live source while retaining explicit prototype/test fallback only.

## Matrix

| Frontend surface | Backend owner | Admin | API | Frontend live consumer | P3 status |
| --- | --- | --- | --- | --- | --- |
| Products / variants / media / stock | Catalog domain | Existing Product/Inventory resources | Existing `/api/v1/products*` | Existing backend adapter | VERIFY |
| Categories / category SEO | Category domain | Existing Category resource | Existing `/api/v1/categories*` | Local category copy still authoritative in places | GAP |
| Collections | Collection domain | Existing Collection resource | Existing `/api/v1/collections*` | Metadata currently local | GAP |
| Journal | `Post` | Existing Post resource | Legacy store posts; v1 content route needed | Currently local | GAP |
| Lookbook | `GalleryItem` | Existing GalleryItem resource | Legacy gallery; v1 content route needed | Currently local | GAP |
| FAQ | `Faq` | Existing Faq resource | Legacy store FAQs; v1 content route needed | Currently local route copy | GAP |
| Static pages / page SEO | `ContentPage` | Existing ContentPage resource | Legacy store pages; v1 content route needed | Currently local | GAP |
| Global/contact/social/trust settings | `StoreSetting` | Existing StoreSetting resource | Legacy settings; v1 bootstrap needed | currently local/hardcoded | GAP |
| Header announcement / navigation | Structured public `StoreSetting` JSON | Existing StoreSetting resource | v1 bootstrap needed | currently local | GAP |
| Homepage / hero / brand intro | Structured public `StoreSetting` + homepage content | Existing StoreSetting/ContentPage resources | v1 bootstrap/page contract needed | currently local | GAP |
| Footer/contact | Store settings/content | Existing StoreSetting/ContentPage resources | v1 bootstrap needed | currently local | GAP |
| Shipping display/rules | Delivery/settings domain | Existing DeliveryZone/StoreSetting resources | Existing `/api/v1/delivery/options` | partial | AUDIT |
| Auth/account/orders/cart/checkout/returns | Commerce/customer domains | Existing operational resources | Existing `/api/v1` | existing backend client | VERIFY |

## P3 implementation direction

- Keep domain data on its dedicated models/resources (`Product`, `Category`, `Collection`, `Post`, `GalleryItem`, delivery/commerce domains).
- Use public typed `StoreSetting` values for structured global storefront configuration such as brand, navigation, announcements, homepage controls, footer/contact and global SEO defaults.
- Add an additive `/api/v1/storefront` content surface; do not break the P2 frozen catalog/auth/commerce contract.
- A row is not PASS until the admin, API and live frontend consumer are all covered by tests.
