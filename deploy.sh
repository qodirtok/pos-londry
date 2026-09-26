#!/usr/bin/env bash
# pos-londry: deploy ke production VPS (nginx + PHP-FPM 8.2 + MySQL)
#
# Pakai:
#   ./deploy.sh            deploy origin/main ke server
#   ./deploy.sh --force    lanjutkan walau working tree di server dirty
#
# Idempotent, aman dijalankan berulang. Gagal di tengah = script berhenti,
# tidak lanjut ke langkah berikutnya dengan kondisi setengah jadi.

set -euo pipefail

PROJECT_DIR="/root/www/pos-londry"
WEB_USER="www-data"
PHP_BIN="php8.2"
NODE_VERSION="24"
BRANCH="main"
LOG="storage/logs/laravel.log"
PUBLIC_URL="https://pos.azelsq.my.id"

FORCE=0
[ "${1:-}" = "--force" ] && FORCE=1

STEP=0
CURRENT_STEP="startup"

step() {
  STEP=$((STEP + 1))
  CURRENT_STEP="$1"
  printf '\n[%d/9] %s\n' "$STEP" "$1"
}
note() { printf '      %s\n' "$1"; }
warn() { printf '      ! %s\n' "$1" >&2; }
die() {
  printf '\n=== DEPLOY GAGAL (langkah: %s) ===\n%s\n' "$CURRENT_STEP" "$1" >&2
  exit 1
}
on_error() { die "error di baris $LINENO. Tidak ada langkah lanjutan yang dijalankan."; }
trap on_error ERR

# ---------------------------------------------------------------- preflight
[ -d "$PROJECT_DIR" ] || { echo "Project dir tidak ada: $PROJECT_DIR" >&2; exit 1; }
cd "$PROJECT_DIR"

for bin in git composer; do
  command -v "$bin" >/dev/null 2>&1 || die "binary '$bin' tidak ada di PATH server"
done
command -v "$PHP_BIN" >/dev/null 2>&1 || die "binary '$PHP_BIN' tidak ada di PATH server"
command -v npm >/dev/null 2>&1 || warn "npm tidak ada di PATH, build frontend akan lewat nvm"

# ---------------------------------------------------------------- 1. sync kode
step "Sync kode dari origin/$BRANCH (reset --hard ke commit remote)"
git fetch --all --prune
LOCAL_SHA="$(git rev-parse HEAD)"
TARGET_SHA="$(git rev-parse "origin/$BRANCH")"

if [ -n "$(git status --porcelain)" ]; then
  if [ "$FORCE" -eq 0 ]; then
    git status --short >&2
    die "working tree di server ada perubahan lokal, reset --hard dibatalkan.
Commit/stash dulu di server, atau jalankan ./deploy.sh --force untuk menimpa."
  fi
  warn "memaksa reset walau ada perubahan lokal (--force)"
fi

git reset --hard "$TARGET_SHA"
git clean -fd

if [ "$LOCAL_SHA" = "$TARGET_SHA" ]; then
  note "server sudah di commit terbaru: $(git rev-parse --short HEAD)"
else
  note "commit: $(git rev-parse --short "$LOCAL_SHA") -> $(git rev-parse --short "$TARGET_SHA")"
  git log --oneline --no-decorate "$LOCAL_SHA..$TARGET_SHA" | sed 's/^/      + /'
fi

# ---------------------------------------------------------------- 2. composer
step "Composer install (rebuild autoloader dari composer.json/lock server)"
# Wajib setiap deploy, bukan hanya saat composer.json berubah. Autoloader
# adalah file di vendor/ yang gitignored, jadi git pull tidak pernah
# menyegarnya. Perubahan di app/ baru aktif kalau autoloader sudah
# dibangun ulang dari composer.json server.
composer install --no-interaction --prefer-dist --optimize-autoloader

# ---------------------------------------------------------------- 3. Gate
step "Gate: pastikan app benar-benar bisa boot sebelum lanjut"
# Kalau file PHP hilang, tidak bisa di-autoload, atau ada syntax error, halaman
# production akan 500 sementara asset sudah selesai dibangun lebih dulu.
# Mencegahnya di sini jauh lebih murah daripada deploy setengah jadi.
BOOT_CHECK="$(mktemp -t boot_check.XXXXXX.php)"
cat > "$BOOT_CHECK" <<'PHP'
<?php
// Cek 1: autoloader bisa jalan, dan class app/ yang ditambahkan commit
// terakhir benar-benar ter-resolve.
require 'vendor/autoload.php';

$base = 'app';
$missing = [];
$checked = 0;
$it = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($base, FilesystemIterator::SKIP_DOTS)
);
foreach ($it as $file) {
    if ($file->isDir() || $file->getExtension() !== 'php') {
        continue;
    }
    $rel = substr($file->getPathname(), strlen($base) + 1, -4);
    $class = 'App\\' . str_replace('/', '\\', $rel);

    // Hanya class/interface/trait/enum yang punya nama PSR-4. File yang
    // isinya return array (app/Support/Copy.php) atau fungsi
    // (app/helpers.php) dimuat lewat require, bukan autoloader.
    if (!preg_match('/^\s*(?:final\s+|abstract\s+|readonly\s+)?(?:class|interface|trait|enum)\s+\w/mi', file_get_contents($file->getPathname()))) {
        continue;
    }
    $checked++;
    if (!class_exists($class) && !interface_exists($class)
        && !trait_exists($class) && !enum_exists($class)) {
        $missing[] = $class;
    }
}

// Cek 2: file yang dimuat via require harus ada dan syntax-nya valid.
// app/helpers.php terdaftar di composer autoload "files" sejak lama, dan
// helper copy_label()/copy_dict() me-require app/Support/Copy.php per
# path. Dua-duanya hilang dari autoloader, jadi harus dicek langsung.
$required = [
    'app/helpers.php',
    'app/Support/Copy.php',
];
$brokenSyntax = [];
foreach ($required as $rel) {
    if (!is_file($rel)) {
        $brokenSyntax[] = "$rel (hilang)";
        continue;
    }
    exec(
        escapeshellcmd(PHP_BINARY) . ' -l ' . escapeshellarg($rel) . ' 2>&1',
        $out,
        $code
    );
    if ($code !== 0) {
        $brokenSyntax[] = "$rel -> " . trim(implode(' ', $out));
    }
    $out = [];
}

// Cek 3: helper global harus ada setelah autoload. Kalau hilang, setiap
// view yang pakai copy_label() akan 500 dengan "undefined function".
$missingFns = [];
foreach (['copy_label', 'copy_dict'] as $fn) {
    if (!function_exists($fn)) {
        $missingFns[] = $fn . '()';
    }
}

$fail = false;
if ($missing) {
    fwrite(STDERR, "class tidak bisa di-autoload:\n  " . implode("\n  ", $missing) . "\n");
    $fail = true;
}
if ($brokenSyntax) {
    fwrite(STDERR, "file wajib bermasalah:\n  " . implode("\n  ", $brokenSyntax) . "\n");
    $fail = true;
}
if ($missingFns) {
    fwrite(STDERR, "helper global hilang: " . implode(', ', $missingFns) . "\n");
    $fail = true;
}

fwrite(STDERR, sprintf("  dicek: %d class, %d file wajib\n", $checked, count($required)));
exit($fail ? 1 : 0);
PHP

if ! BOOT_OUT="$("$PHP_BIN" "$BOOT_CHECK" 2>&1)"; then
  warn "gate gagal, dump ulang autoloader lalu coba:"
  printf '%s\n' "$BOOT_OUT" | sed 's/^/        /' >&2
  "$PHP_BIN" artisan dump-autoload --optimize 2>&1 | sed 's/^/        /' >&2 || true
  if "$PHP_BIN" "$BOOT_CHECK" >/dev/null 2>&1; then
    warn "berhasil setelah dump-autoload, lanjut deploy"
  else
    rm -f "$BOOT_CHECK"
    die "gate masih gagal setelah dump-autoload. Deploy dibatalkan.
 Baris di atas menunjuk file yang belum ikut ter-pull atau ada syntax
 error. Jangan dipaksa lanjut, production akan 500."
  fi
fi
rm -f "$BOOT_CHECK"
printf '%s\n' "$BOOT_OUT" | sed 's/^/      /'
note "gate hijau, lanjut"

# ---------------------------------------------------------------- 4. frontend
step "Build frontend (Vite + Tailwind) -> public/build/"
if git diff --name-only "$LOCAL_SHA" "$TARGET_SHA" -- package.json package-lock.json | grep -q .; then
  npm ci
  note "package-lock.json berubah -> npm ci dijalankan"
else
  note "package.json & package-lock.json tidak berubah -> lewati npm ci"
fi
npm run build || bash -lc "source ~/.nvm/nvm.sh; nvm use $NODE_VERSION; npm run build"
[ -d public/build ] || die "public/build tidak ada setelah build, asset production tidak akan termekan"
[ -d public/build ] && chown -R "$WEB_USER":"$WEB_USER" public/build

# ---------------------------------------------------------------- 5. storage
step "Permission storage & bootstrap/cache"
mkdir -p storage/framework/views \
         storage/framework/cache/data \
         storage/framework/sessions \
         storage/framework/logs \
         bootstrap/cache
chown -R "$WEB_USER":"$WEB_USER" storage bootstrap/cache
chmod -R 775 storage/framework/views storage/framework/cache storage/framework/sessions

# ---------------------------------------------------------------- 6. migrasi
step "Migrasi database"
if "$PHP_BIN" artisan migrate --force; then
  note "tidak ada migrasi baru, atau migrasi baru saja dijalankan"
else
  warn "migrate gagal, script tetap lanjut. Cek manual: php artisan migrate --force"
fi

# ---------------------------------------------------------------- 7. cache
# Urutannya penting: clear dulu, baru cache. Kalau dicache dulu lalu
# di-clear, production akan tetap jalan dengan route/view/config lama
# walau kodenya sudah baru.
step "Bersihkan cache lama, lalu build ulang cache baru"
"$PHP_BIN" artisan optimize:clear
"$PHP_BIN" artisan config:cache
"$PHP_BIN" artisan route:cache
"$PHP_BIN" artisan view:cache
"$PHP_BIN" artisan event:cache
chown -R "$WEB_USER":"$WEB_USER" bootstrap/cache

# ---------------------------------------------------------------- 8. reload
step "Rotasi log & reload service"
if [ -f "$LOG" ]; then
  : > "$LOG"
  chown "$WEB_USER":"$WEB_USER" "$LOG"
fi
systemctl reload "php${PHP_BIN}-fpm" 2>/dev/null \
  || service "php${PHP_BIN}-fpm" reload \
  || warn "gagal reload php-fpm (opcache mungkin masih cache lama), cek manual"
nginx -t || die "config nginx tidak valid, nginx tidak di-reload"
nginx -s reload 2>/dev/null || systemctl reload nginx || warn "gagal reload nginx"

# ---------------------------------------------------------------- 9. smoke test
step "Smoke test"
LOGIN_CODE="$(curl -sS -o /dev/null -w '%{http_code}' "$PUBLIC_URL/login" 2>/dev/null || echo 000)"
if [ "$LOGIN_CODE" = "200" ]; then
  note "$PUBLIC_URL/login -> HTTP $LOGIN_CODE"
else
  warn "$PUBLIC_URL/login -> HTTP $LOGIN_CODE (periksa nginx/php-fpm + storage permission)"
fi
CSS_COUNT="$(find public/build/assets -name '*.css' 2>/dev/null | wc -l | tr -d ' ')"
[ "$CSS_COUNT" -gt 0 ] && note "asset CSS terbangun: $CSS_COUNT file" \
                      || warn "tidak ada CSS di public/build/assets, cek hasil npm run build"

printf '\n=== DEPLOY OK ===\n'
printf 'Project:   %s\n' "$PROJECT_DIR"
printf 'Commit:    %s (%s)\n' "$(git rev-parse HEAD)" "$(git rev-parse --abbrev-ref HEAD)"
printf 'Check:     %s\n' "$PUBLIC_URL"
printf 'Log tail:  tail -f %s/%s\n' "$PROJECT_DIR" "$LOG"
