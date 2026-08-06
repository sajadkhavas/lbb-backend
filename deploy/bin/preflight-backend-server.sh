#!/usr/bin/env bash
set -Eeuo pipefail
umask 027

DEPLOY_ROOT=${BACKEND_DEPLOY_ROOT:-/var/www/winimi/backend}
ENV_FILE=${BACKEND_ENV_FILE:-/var/www/winimi/backend/shared/.env}
NGINX_SITE=${BACKEND_NGINX_SITE:-/etc/nginx/sites-enabled/winimi-api.conf}
PHP_FPM_SERVICE=${PHP_FPM_SERVICE:-php8.3-fpm.service}
QUEUE_SERVICE=${BACKEND_QUEUE_SERVICE:-winimi-backend-queue.service}
SCHEDULER_TIMER=${BACKEND_SCHEDULER_TIMER:-winimi-backend-scheduler.timer}
BACKUP_TIMER=${BACKEND_BACKUP_TIMER:-winimi-backend-backup.timer}
MIN_FREE_KIB=${BACKEND_MIN_FREE_KIB:-5242880}
TEST_MODE=${WINIMI_PREFLIGHT_TEST_MODE:-false}

errors=()
notes=()

require_command() {
  command -v "$1" >/dev/null 2>&1 || errors+=("missing command: $1")
}
for command in php composer nginx curl openssl realpath df awk grep stat systemctl systemd-analyze; do
  require_command "$command"
done

if command -v php >/dev/null 2>&1; then
  php_version=$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')
  [[ "$php_version" == "8.3" ]] || errors+=("production PHP must be 8.3, found $php_version")
  required_extensions=(bcmath ctype curl dom fileinfo gd intl mbstring openssl pdo tokenizer xml zip)
  for extension in "${required_extensions[@]}"; do
    php -m | grep -Eqi "^${extension}$" || errors+=("missing PHP extension: $extension")
  done
fi

if [[ "$TEST_MODE" != "true" ]]; then
  id winimi >/dev/null 2>&1 || errors+=("system user winimi is missing")
  getent group www-data >/dev/null 2>&1 || errors+=("group www-data is missing")
  [[ -f "$ENV_FILE" ]] || errors+=("private backend env is missing: $ENV_FILE")
  [[ -f "$NGINX_SITE" ]] || errors+=("Nginx API site is missing: $NGINX_SITE")
  [[ -d "$DEPLOY_ROOT/releases" ]] || errors+=("backend releases directory is missing")
  [[ -d "$DEPLOY_ROOT/shared/storage" ]] || errors+=("persistent backend storage is missing")

  for unit in "$PHP_FPM_SERVICE" "$QUEUE_SERVICE" "$SCHEDULER_TIMER" "$BACKUP_TIMER"; do
    systemctl cat "$unit" >/dev/null 2>&1 || errors+=("systemd unit is missing: $unit")
  done

  if [[ -f "$ENV_FILE" ]]; then
    mode=$(stat -c '%a' "$ENV_FILE")
    [[ "$mode" == "640" || "$mode" == "600" ]] || errors+=("backend env mode must be 0640 or 0600, found $mode")

    require_env() {
      local name=$1
      local expected=$2
      grep -qx "${name}=${expected}" "$ENV_FILE" || errors+=("backend env must set ${name}=${expected}")
    }
    require_env APP_ENV production
    require_env APP_DEBUG false
    require_env APP_URL https://api.winimibakery.com
    require_env SESSION_DOMAIN .winimibakery.com
    require_env SESSION_SECURE_COOKIE true
    require_env SESSION_HTTP_ONLY true
    require_env SESSION_ENCRYPT true
    require_env LEGACY_TOOLMASTER_API_ENABLED false
    require_env SEED_WINIMI_STAGING false
    require_env CHECKOUT_ENABLED false
    require_env PAYMENT_ENABLED false
    require_env PAYMENT_PROVIDER disabled
    require_env SMS_PROVIDER disabled
    require_env ORDER_SMS_PROVIDER disabled
    require_env OTP_EXPOSE_TEST_CODE false
    require_env ENAMAD_ENABLED false

    grep -Eq '^APP_KEY=base64:.+' "$ENV_FILE" || errors+=("APP_KEY is missing or invalid")
    grep -Eq '^DB_PASSWORD=.+$' "$ENV_FILE" || errors+=("DB_PASSWORD is empty")
    grep -Eq '^BACKUP_ENCRYPTION_PASSWORD=.+$' "$ENV_FILE" || errors+=("BACKUP_ENCRYPTION_PASSWORD is empty")
    grep -qx 'ZARINPAL_MERCHANT_ID=' "$ENV_FILE" || errors+=("Zarinpal credential must remain empty before Phase 20")
    grep -qx 'KAVENEGAR_API_KEY=' "$ENV_FILE" || errors+=("Kavenegar credential must remain empty before Phase 20")
    grep -qx 'ENAMAD_BADGE_CODE=' "$ENV_FILE" || errors+=("eNAMAD code must remain empty before Phase 20")
  fi

  nginx -t >/dev/null 2>&1 || errors+=("nginx -t failed")
  for file in /etc/systemd/system/winimi-backend-queue.service /etc/systemd/system/winimi-backend-scheduler.service /etc/systemd/system/winimi-backend-scheduler.timer /etc/systemd/system/winimi-backend-backup.service /etc/systemd/system/winimi-backend-backup.timer; do
    [[ -f "$file" ]] && systemd-analyze verify "$file" >/dev/null 2>&1 || errors+=("systemd verification failed: $file")
  done
fi

mkdir -p "$DEPLOY_ROOT" 2>/dev/null || true
free_kib=$(df -Pk "$DEPLOY_ROOT" 2>/dev/null | awk 'NR==2 {print $4}')
if [[ -n "${free_kib:-}" && "$free_kib" =~ ^[0-9]+$ ]]; then
  (( free_kib >= MIN_FREE_KIB )) || errors+=("less than ${MIN_FREE_KIB} KiB free at $DEPLOY_ROOT")
else
  notes+=("disk capacity could not be measured for $DEPLOY_ROOT")
fi

if ((${#errors[@]})); then
  printf 'Backend production preflight failed with %d issue(s):\n' "${#errors[@]}" >&2
  printf -- '- %s\n' "${errors[@]}" >&2
  exit 1
fi

printf 'Backend production preflight passed.\n'
if ((${#notes[@]})); then
  printf -- '- note: %s\n' "${notes[@]}"
fi
