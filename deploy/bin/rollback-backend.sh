#!/usr/bin/env bash
set -Eeuo pipefail
umask 027

DEPLOY_ROOT=${1:-/var/www/winimi/backend}
REQUESTED_RELEASE=${2:-}
BACKEND_RESTART_COMMAND=${BACKEND_RESTART_COMMAND:-}
BACKEND_HEALTH_URL=${BACKEND_HEALTH_URL:-}
BACKEND_ROLLBACK_CONFIRMED=${BACKEND_ROLLBACK_CONFIRMED:-false}

if [[ "$BACKEND_ROLLBACK_CONFIRMED" != "true" ]]; then
  echo "Backend rollback requires BACKEND_ROLLBACK_CONFIRMED=true after migration compatibility or backup recovery is reviewed." >&2
  exit 78
fi
if [[ ! -L "$DEPLOY_ROOT/current" ]]; then
  echo "Current backend release symlink is missing: $DEPLOY_ROOT/current" >&2
  exit 1
fi

SCRIPT_ROOT=$(cd "$(dirname "$0")/../.." && pwd)
current=$(basename "$(readlink -f "$DEPLOY_ROOT/current")")
if [[ -n "$REQUESTED_RELEASE" ]]; then
  target="$REQUESTED_RELEASE"
else
  mapfile -t candidates < <(
    find "$DEPLOY_ROOT/releases" -mindepth 1 -maxdepth 1 -type d -printf '%T@ %f\n' |
      sort -nr |
      awk -v current="$current" '$2 != current { print $2 }'
  )
  target=${candidates[0]:-}
fi

if [[ -z "$target" ]]; then
  echo "No previous backend release is available for rollback." >&2
  exit 1
fi
if [[ ! "$target" =~ ^[a-f0-9]{20}$ ]]; then
  echo "Rollback release ID is invalid: $target" >&2
  exit 64
fi

TARGET_DIR="$DEPLOY_ROOT/releases/$target"
php "$SCRIPT_ROOT/scripts/verify-backend-release.php" "$TARGET_DIR"
if [[ ! -L "$TARGET_DIR/app/.env" || ! -L "$TARGET_DIR/app/storage" ]]; then
  echo "Rollback target is not linked to shared environment and storage." >&2
  exit 1
fi

activate_release() {
  local release=$1
  ln -s "releases/$release" "$DEPLOY_ROOT/.current.$$.new"
  mv -Tf "$DEPLOY_ROOT/.current.$$.new" "$DEPLOY_ROOT/current"
}
restart_runtime() {
  if [[ -n "$BACKEND_RESTART_COMMAND" ]]; then
    BACKEND_CURRENT="$DEPLOY_ROOT/current" bash -Eeuo pipefail -c "$BACKEND_RESTART_COMMAND"
  fi
}
check_health() {
  if [[ -n "$BACKEND_HEALTH_URL" ]]; then
    curl --fail --silent --show-error --retry 20 --retry-delay 1 "$BACKEND_HEALTH_URL" >/dev/null
  fi
}

(cd "$DEPLOY_ROOT/current" && php artisan down --retry=60 --no-interaction) || true
activate_release "$target"
(
  cd "$DEPLOY_ROOT/current"
  php artisan optimize:clear --no-interaction
  php artisan config:cache --no-interaction
  php artisan route:cache --no-interaction
  php artisan view:cache --no-interaction
  php artisan backend:readiness --json
)

if ! restart_runtime || ! check_health; then
  echo "Backend rollback health failed; restoring release $current." >&2
  activate_release "$current"
  restart_runtime || true
  (cd "$DEPLOY_ROOT/current" && php artisan up --no-interaction) || true
  exit 1
fi

(cd "$DEPLOY_ROOT/current" && php artisan queue:restart --no-interaction) || true
(cd "$DEPLOY_ROOT/current" && php artisan up --no-interaction) || true
printf '%s\n' "$target" > "$DEPLOY_ROOT/active-release"
chmod 0644 "$DEPLOY_ROOT/active-release"

echo "Rolled backend back from $current to $target. Database state was not changed."
