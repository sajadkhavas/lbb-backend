# F14-BE-B Cleanup Status

## Completed in B1

- Removed the complete `/api/v1` inherited route surface and controllers.
- Removed the legacy API middleware and configuration flag.
- Removed inherited legacy models, Filament resources and schema migrations.
- Removed the unrelated React/Vite storefront source from the backend repository.
- Quarantined imported contracts, audits and deployment documentation under reference folders.
- Replaced active imported configuration with fail-closed `config/lbb.php`.
- Reset Composer checks to an LBB foundation audit.

## Remaining in B2

- Rename Bakery catalog/content classes and tables to neutral LBB names.
- Remove food-only fields and chilled delivery behavior.
- Remove city SEO pages.
- Rebuild catalog and Filament tests around apparel requirements.
