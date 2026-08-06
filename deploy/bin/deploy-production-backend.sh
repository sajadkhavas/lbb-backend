#!/usr/bin/env bash
set -Eeuo pipefail
umask 027

RELEASE_SOURCE=${1:-}
DEPLOY_ROOT=${2:-/var/www/winimi/backend}
PHP_FPM_SERVICE=${PHP_FPM_SERVICE:-php8.3-fpm.service}
QUEUE_SERVICE=${BACKEND_QUEUE_SERVICE:-winimi-backend-queue.service}
SCHEDULER_TIMER=${BACKEND_SCHEDULER_TIMER:-winimi-backend-scheduler.timer}
HEALTH_URL=${BACKEND_INTERNAL_HEALTH_URL:-https://api.winimibakery.com/api/system/ready}

if [[ -z "$RELEASE_SOURCE" ]]; then
  echo "Usage: deploy-production-backend.sh <verified-release-directory> [deploy-root]" >&2
  exit 64
fi

for command in php curl systemctl sudo realpath; do
  command -v "$command" >/dev/null 2>&1 || {
    echo "Required command is missing: $command" >&2
    exit 69
  }
done

systemctl cat "$PHP_FPM_SERVICE" >/dev/null
systemctl cat "$QUEUE_SERVICE" >/dev/null
systemctl cat "$SCHEDULER_TIMER" >/dev/null

export BACKEND_RESTART_COMMAND="sudo systemctl restart $PHP_FPM_SERVICE && sudo systemctl restart $QUEUE_SERVICE && sudo systemctl start $SCHEDULER_TIMER"
export BACKEND_HEALTH_URL="$HEALTH_URL"
export BACKEND_RUN_MIGRATIONS=true
export BACKEND_MAINTENANCE=true

SCRIPT_ROOT=$(cd "$(dirname "$0")/../.." && pwd)
bash "$SCRIPT_ROOT/deploy/bin/deploy-backend.sh" "$RELEASE_SOURCE" "$DEPLOY_ROOT"

sudo systemctl is-active --quiet "$PHP_FPM_SERVICE"
sudo systemctl is-active --quiet "$QUEUE_SERVICE"
sudo systemctl is-active --quiet "$SCHEDULER_TIMER"
curl --fail --silent --show-error --retry 20 --retry-delay 1 "$HEALTH_URL" >/dev/null

echo "Production backend deployment is active and healthy."
