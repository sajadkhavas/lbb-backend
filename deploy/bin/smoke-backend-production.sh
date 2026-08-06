#!/usr/bin/env bash
set -Eeuo pipefail
umask 027

API_ORIGIN=${API_ORIGIN:-https://api.winimibakery.com}
APP_ROOT=${BACKEND_CURRENT_ROOT:-/var/www/winimi/backend/current}
CURL_RESOLVE_API=${CURL_RESOLVE_API:-}

args=(--fail --silent --show-error --max-time 20 --retry 3 --retry-delay 2)
[[ -z "$CURL_RESOLVE_API" ]] || args+=(--resolve "$CURL_RESOLVE_API")

ready=$(curl "${args[@]}" "$API_ORIGIN/api/system/ready")
contracts=$(curl "${args[@]}" "$API_ORIGIN/api/system/contracts")
meta=$(curl "${args[@]}" "$API_ORIGIN/api/system/meta")

python3 - "$ready" "$contracts" "$meta" <<'PY'
import json, sys
ready, contracts, meta = map(json.loads, sys.argv[1:4])
if ready.get('success') is not True or (ready.get('data') or {}).get('ready') is not True:
    raise SystemExit('production API readiness failed')
if contracts.get('success') is not True:
    raise SystemExit('contracts endpoint failed')
contract_meta = contracts.get('meta') or {}
if contract_meta.get('contractVersion') != '2026-07-20-phase-16':
    raise SystemExit('backend contract drift detected')
launch = ((contracts.get('data') or {}).get('launch') or {})
if ((launch.get('internal_gates') or {}).get('production_deployed') or {}).get('status') not in {'not-started', 'ready'}:
    raise SystemExit('unexpected production gate state')
if meta.get('success') is not True:
    raise SystemExit('system meta endpoint failed')
PY

if [[ -f "$APP_ROOT/artisan" ]]; then
  (
    cd "$APP_ROOT"
    php artisan migrate:status --no-interaction >/dev/null
    php artisan backend:readiness --json >/dev/null
    php artisan route:list --except-vendor >/dev/null
    php artisan schedule:list >/dev/null
    php artisan queue:failed >/dev/null
  )
fi

printf 'Backend production smoke checks passed for %s.\n' "$API_ORIGIN"
