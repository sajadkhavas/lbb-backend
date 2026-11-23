# LBB Iran production snapshot — 2026-11-23

This branch records the application source exported from the running Iran server.
It is the production reference for this snapshot. The repository default branch
is a different development line; do not deploy `main` over this snapshot without
reviewing the differences.

## Running deployment

- Host: `185.155.9.221` (Ubuntu 24.04, x86_64).
- Storefront: `https://lbbclo.com`; API: `https://api.lbbclo.com`.
- Node 24.22.2; PHP 8.3; MySQL 8.0; nginx; Laravel 12.
- Frontend symlink: `/var/www/lbb/current` → `releases/journal-6shBr8Mo`.
- Backend symlink: `/var/www/lbb/backend/current` → `releases/size-guide-image-AuggafOs`.
- Persistent Laravel storage: `/var/www/lbb/backend/shared/storage`.
- Laravel `.env`: `/var/www/lbb/backend/shared/.env` (not in Git).
- SSR rate-limit environment: `/etc/lbb/ssr-rate-limit.env` (not in Git).
- Frontend binds `127.0.0.1:5173`; PHP pool socket `/run/php/lbb-backend.sock`.
- Database, cache and sessions are local; queue connection is `database`.

## Operational state verified by server output

- Website, API, admin, journal, collections, lookbook and sitemap returned HTTP 200.
- All 51 registered local media originals existed; no broken application symlinks.
- SMS login and payment worked after the old foreign server was powered off.
- Scheduler timer enabled; scheduler exited successfully.
- Queue worker enabled: timeout 60s, database retry_after 90s, restart every hour.
- Certbot timer enabled. Staging renewal tests passed for the API and apex/www.
- ACME webroot `/var/www/certbot` and its challenge directories must be readable
  by nginx (directories 0755). Deployment hook validates and reloads nginx.

## Deployment cautions

Do not import the migration database backup over the live database: new orders
have been recorded after migration. Git records code, not customer data, uploads,
APP_KEY, payment/SMS credentials, TLS private keys, database dumps or sessions.
Back these up separately with restricted access and an off-server copy.

Keep the existing APP_KEY. Never run `key:generate` or seed a production database.
After a backend code release, restart `lbb-queue.service` to load the new code.
Do not run new migrations merely to restore this snapshot: the source snapshot
was deployed against the existing production schema.

The infra files in this commit are documented templates reconstructed from the
verified migration session, not an exact export of `/etc`. Existing nginx/PHP
configuration and certificates must be backed up separately. The SSR environment
file contains credentials; provision it securely and never commit its values.

The exported frontend did not contain `*.orig` patch leftovers in this Git branch;
those backup files were intentionally omitted. Unexported root documentation,
example configuration and support files are inherited from the closest source
branch, not asserted to be a complete byte-for-byte disk backup.
