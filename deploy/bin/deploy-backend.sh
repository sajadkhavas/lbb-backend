#!/usr/bin/env bash
set -Eeuo pipefail
umask 027

RELEASE_SOURCE=${1:-}
DEPLOY_ROOT=${2:-/var/www/winimi/backend}
KEEP_RELEASES=${KEEP_RELEASES:-5}
BACKEND_RESTART_COMMAND=${BACKEND_RESTART_COMMAND:-}
BACKEND_HEALTH_URL=${BACKEND_HEALTH_URL:-}
BACKEND_RUN_MIGRATIONS=${BACKEND_RUN_MIGRATIONS:-true}
BACKEND_MAINTENANCE=${BACKEND_MAINTENANCE:-true}

if [[ -z "$RELEASE_SOURCE" ]]; then
  echo "Usage: deploy-backend.sh <verified-release-directory> [deploy-root]" >&2
  exit 64
fi
if [[ ! "$KEEP_RELEASES" =~ ^[2-9][0-9]*$ ]]; then
  echo "KEEP_RELEASES must be an integer of at least 2." >&2
  exit 64
fi
if [[ "$BACKEND_RUN_MIGRATIONS" != "true" && "$BACKEND_RUN_MIGRATIONS" != "false" ]]; then
  echo "BACKEND_RUN_MIGRATIONS must be true or false." >&2
  exit 64
fi

SCRIPT_ROOT=$(cd "$(dirname "$0")/../.." && pwd)
RELEASE_SOURCE=$(realpath "$RELEASE_SOURCE")
php "$SCRIPT_ROOT/scripts/verify-backend-release.php" "$RELEASE_SOURCE"
RELEASE_ID=$(php -r '$m=json_decode(file_get_contents($argv[1]), true, 512, JSON_THROW_ON_ERROR); echo $m["releaseId"];' "$RELEASE_SOURCE/release-manifest.json")

mkdir -p "$DEPLOY_ROOT/releases" "$DEPLOY_ROOT/shared/storage/app/public" "$DEPLOY_ROOT/shared/storage/framework/cache" "$DEPLOY_ROOT/shared/storage/framework/sessions" "$DEPLOY_ROOT/shared/storage/framework/views" "$DEPLOY_ROOT/shared/storage/logs"
chmod 0755 "$DEPLOY_ROOT" "$DEPLOY_ROOT/releases" "$DEPLOY_ROOT/shared"
chmod -R 0770 "$DEPLOY_ROOT/shared/storage"

if [[ ! -f "$DEPLOY_ROOT/shared/.env" ]]; then
  echo "Private backend environment is missing: $DEPLOY_ROOT/shared/.env" >&2
  exit 78
fi

TARGET="$DEPLOY_ROOT/releases/$RELEASE_ID"
STAGING="$DEPLOY_ROOT/releases/.${RELEASE_ID}.staging.$$"
PREVIOUS_TARGET=""
if [[ -L "$DEPLOY_ROOT/current" ]]; then
  PREVIOUS_TARGET=$(readlink "$DEPLOY_ROOT/current")
fi

if [[ -e "$TARGET" ]]; then
  php "$SCRIPT_ROOT/scripts/verify-backend-release.php" "$TARGET"
else
  trap 'rm -rf "$STAGING"' EXIT
  mkdir -p "$STAGING"
  cp -a "$RELEASE_SOURCE/." "$STAGING/"
  php "$SCRIPT_ROOT/scripts/verify-backend-release.php" "$STAGING"
  chmod -R u=rwX,go=rX "$STAGING"
  mv "$STAGING" "$TARGET"
  trap - EXIT
fi

APP_DIR="$TARGET/app"
rm -f "$APP_DIR/.env"
ln -s "$DEPLOY_ROOT/shared/.env" "$APP_DIR/.env"
rm -rf "$APP_DIR/storage"
ln -s "$DEPLOY_ROOT/shared/storage" "$APP_DIR/storage"
mkdir -p "$APP_DIR/bootstrap/cache"
chmod 0770 "$APP_DIR/bootstrap/cache"

maintenance_started=false
if [[ "$BACKEND_MAINTENANCE" == "true" && -n "$PREVIOUS_TARGET" && -f "$DEPLOY_ROOT/current/artisan" ]]; then
  (cd "$DEPLOY_ROOT/current" && php artisan down --retry=60 --no-interaction) || true
  maintenance_started=true
fi

cleanup_maintenance() {
  if [[ "$maintenance_started" == "true" && -L "$DEPLOY_ROOT/current" && -f "$DEPLOY_ROOT/current/artisan" ]]; then
    (cd "$DEPLOY_ROOT/current" && php artisan up --no-interaction) || true
  fi
}
trap cleanup_maintenance EXIT

(
  cd "$APP_DIR"
  php artisan optimize:clear --no-interaction
  if [[ "$BACKEND_RUN_MIGRATIONS" == "true" ]]; then
    php artisan migrate --force --no-interaction
  fi
  php artisan storage:link --force --no-interaction
  php artisan config:cache --no-interaction
  php artisan route:cache --no-interaction
  php artisan view:cache --no-interaction
  php artisan backend:readiness --json
)

activate_release() {
  local target=$1
  ln -s "$target" "$DEPLOY_ROOT/.current.$$.new"
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

activate_release "releases/$RELEASE_ID"
if ! restart_runtime || ! check_health; then
  echo "Backend restart or health check failed; restoring previous release symlink." >&2
  if [[ -n "$PREVIOUS_TARGET" ]]; then
    activate_release "$PREVIOUS_TARGET"
    restart_runtime || true
  fi
  echo "Database migrations are not automatically reversed; inspect migration compatibility before retrying." >&2
  exit 1
fi

(cd "$DEPLOY_ROOT/current" && php artisan queue:restart --no-interaction) || true
cleanup_maintenance
maintenance_started=false
printf '%s\n' "$RELEASE_ID" > "$DEPLOY_ROOT/active-release"
chmod 0644 "$DEPLOY_ROOT/active-release"

mapfile -t releases < <(find "$DEPLOY_ROOT/releases" -mindepth 1 -maxdepth 1 -type d -printf '%T@ %f\n' | sort -nr | awk '{print $2}')
active=$(basename "$(readlink -f "$DEPLOY_ROOT/current")")
kept=0
for release in "${releases[@]}"; do
  if [[ "$release" == "$active" ]]; then
    continue
  fi
  kept=$((kept + 1))
  if (( kept >= KEEP_RELEASES )); then
    rm -rf -- "$DEPLOY_ROOT/releases/$release"
  fi
done

trap - EXIT
echo "Activated backend release $RELEASE_ID at $DEPLOY_ROOT/current."
