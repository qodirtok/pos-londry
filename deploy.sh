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

# Semua nilai di bawah bisa dioverride dari luar, jadi script yang sama
# jalan di server lama maupun server aaPanel tanpa perlu edit file.
PROJECT_DIR="${PROJECT_DIR:-/www/wwwroot/pos.azelsq.my.id}"
WEB_USER="${WEB_USER:-}"
PHP_BIN="${PHP_BIN:-php8.2}"
BRANCH="${BRANCH:-main}"
LOG="storage/logs/laravel.log"
PUBLIC_URL="${PUBLIC_URL:-https://pos.azelsq.my.id}"

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
# Kalau dipanggil dari dalam project tanpa PROJECT_DIR, pakai cwd.
if [ -z "$PROJECT_DIR" ] && [ -f artisan ]; then
  PROJECT_DIR="$PWD"
fi
[ -d "$PROJECT_DIR" ] || { echo "Project dir tidak ada: $PROJECT_DIR" >&2; exit 1; }
cd "$PROJECT_DIR"
[ -f artisan ] || { echo "bukan project Laravel (artisan tidak ada): $PROJECT_DIR" >&2; exit 1; }

# User web: aaPanel biasanya pakai www, server Debian pakai www-data.
# Jangan chown ke user yang salah, itu bisa mengunci PHP dari menulis file.
if [ -z "$WEB_USER" ]; then
  WEB_USER="$(stat -c '%U' public 2>/dev/null || stat -f '%Su' public 2>/dev/null || true)"
fi
if [ -z "$WEB_USER" ] || [ "$WEB_USER" = "root" ]; then
  for candidate in www www-data nginx apache; do
    if id "$candidate" >/dev/null 2>&1; then
      WEB_USER="$candidate"
      break
    fi
  done
fi
[ -n "$WEB_USER" ] || WEB_USER="www-data"

for bin in git composer node; do
  command -v "$bin" >/dev/null 2>&1 || die "binary '$bin' tidak ada di PATH server"
done
command -v "$PHP_BIN" >/dev/null 2>&1 || die "binary '$PHP_BIN' tidak ada di PATH server"
command -v npm >/dev/null 2>&1 || warn "npm tidak ada di PATH, build akan pakai node langsung"

note "project: $PROJECT_DIR"
note "user web: $WEB_USER"
note "php:     $("$PHP_BIN" -r 'echo PHP_VERSION;' 2>/dev/null || echo '?') | node: $(node -v 2>/dev/null || echo '?')"

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

# Build dengan cara yang tidak bergantung pada hak eksekusi file di
# node_modules/.bin. Di server aaPanel, .bin/vite pernah datang tanpa
# bit +x sehingga "vite build" gagal dengan Permission denied.
# Memanggil vite.js lewat `node` tidak butuh bit itu sama sekali.
build_frontend() {
  local vite_entry="node_modules/vite/bin/vite.js"

  if [ ! -f "$vite_entry" ]; then
    echo "vite tidak terpasang: $vite_entry tidak ada" >&2
    return 1
  fi

  # Perbaiki bit +x kalau bisa, biar langkah berikutnya (npm run build
  # manual oleh manusia) tidak ikut gagal. Kalau tidak bisa, jalan saja.
  if [ ! -x "$vite_entry" ]; then
    chmod +x "$vite_entry" 2>/dev/null \
      || true
  fi

  node "$vite_entry" build
}

build_frontend || die "build frontend gagal. Periksa:
  - node tersedia: node -v
  - node_modules terpasang: ls node_modules/vite
  - panel aaPanel punya Node versi lain, jalankan dari terminal panel"

[ -f public/build/manifest.json ] \
  || die "public/build/manifest.json tidak ada setelah build.
 View memakai @vite, tanpa manifest ini setiap halaman 500."

# Asset hasil build harus bisa dibaca user web. Nama hash di manifest
# berubah tiap build, jadi file lama tidak pernah terpakai lagi.
chown -R "$WEB_USER":"$WEB_USER" public/build 2>/dev/null \
  || warn "gagal chown public/build, cek user web server"

# ---------------------------------------------------------------- 5. storage
step "Permission storage & bootstrap/cache"
mkdir -p storage/framework/views \
         storage/framework/cache/data \
         storage/framework/sessions \
         storage/framework/logs \
         bootstrap/cache
chown -R "$WEB_USER":"$WEB_USER" storage bootstrap/cache 2>/dev/null \
  || warn "gagal chown storage, cek user web server (deteksi: $WEB_USER)"
chmod -R 775 storage/framework/views storage/framework/cache storage/framework/sessions 2>/dev/null \
  || warn "gagal chmod storage"

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
  chown "$WEB_USER":"$WEB_USER" "$LOG" 2>/dev/null || true
fi

# Reload PHP-FPM supaya opcache melepas bytecode lama. Nama service
# berbeda antar distro: Debian pakai php8.2-fpm, aaPanel/panel lain
# bisa php-fpm-82 atau php-fpm. Coba semua, jangan gagalkan deploy kalau
# tidak ada satupun yang cocok, tapi tetap beri tahu kasir.
FPM_RELOADED=0
for unit in "${PHP_BIN}-fpm" "php-fpm-${PHP_BIN//php/}" "php-fpm" "php${PHP_BIN#php}"; do
  if systemctl list-units --full --all "$unit.service" 2>/dev/null | grep -q "$unit"; then
    if systemctl reload "$unit" 2>/dev/null; then
      note "php-fpm reload: $unit"
      FPM_RELOADED=1
      break
    fi
  fi
done
if [ "$FPM_RELOADED" -eq 0 ]; then
  if command -v service >/dev/null 2>&1; then
    for unit in "${PHP_BIN}-fpm" php-fpm; do
      service "$unit" reload >/dev/null 2>&1 && {
        note "php-fpm reload: service $unit"
        FPM_RELOADED=1
        break
      }
    done
  fi
fi
[ "$FPM_RELOADED" -eq 1 ] \
  || warn "tidak bisa reload php-fpm. Kalau halaman masih versi lama, restart manual dari panel aaPanel."

# Nginx: -t dulu supaya config rusak tidak downed site.
if command -v nginx >/dev/null 2>&1; then
  if nginx -t 2>/dev/null; then
    nginx -s reload 2>/dev/null || systemctl reload nginx 2>/dev/null || warn "gagal reload nginx"
  else
    die "config nginx tidak valid, nginx tidak di-reload (site tidak di-down)"
  fi
else
  warn "binary nginx tidak ada di PATH, lewati reload nginx"
fi

# ---------------------------------------------------------------- 9. smoke test
step "Smoke test"
# Cek lewat localhost dulu, bukan domain publik. Domain bisa gagal karena
# TLS/sertifikat, itu bukan masalah deploy. Yang kita mau tahu: PHP bisa
# boot, route ada, dan @vite nemu manifest.
smoke_one() {
  local url="$1" label="$2" code
  code="$(curl -sS -o /dev/null -w '%{http_code}' --max-time 15 "$url" 2>/dev/null || echo 000)"
  if [ "$code" = "200" ] || [ "$code" = "302" ]; then
    note "$label -> HTTP $code"
    return 0
  fi
  warn "$label -> HTTP $code"
  return 1
}

SMOKE_OK=0
if [ -n "${LOCAL_URL:-}" ]; then
  smoke_one "$LOCAL_URL/login" "localhost/login" && SMOKE_OK=1
fi
if [ "$SMOKE_OK" -eq 0 ] && command -v curl >/dev/null 2>&1; then
  for host in "http://127.0.0.1" "http://localhost" "$PUBLIC_URL"; do
    if smoke_one "$host/login" "$host/login"; then
      SMOKE_OK=1
      break
    fi
  done
fi

# Cek asset benar-benar ada dan terbaca, karena @vite ambil nama file
# dari manifest. Manifest ada tapi file-nya hilang = CSS 404 = halaman polos.
if [ -f public/build/manifest.json ]; then
  MISSING_ASSET=0
  while IFS= read -r asset; do
    [ -n "$asset" ] || continue
    if [ ! -f "public/build/$asset" ]; then
      warn "asset hilang: public/build/$asset"
      MISSING_ASSET=1
    fi
  done <<EOF
$(grep -o '"file": *"[^"]*"' public/build/manifest.json | sed 's/.*"file": *"//; s/"$//')
EOF
  if [ "$MISSING_ASSET" -eq 0 ]; then
    ASSET_COUNT="$(grep -c '"file":' public/build/manifest.json || echo 0)"
    note "asset terbangun: $ASSET_COUNT file ada di disk"
  fi
else
  warn "public/build/manifest.json tidak ada, halaman akan 500 di @vite"
fi

if [ "$SMOKE_OK" -eq 0 ]; then
  warn "smoke test tidak bisa menghubungi server dari dalam VPS.
 Cek manual dari laptop: $PUBLIC_URL/login
 Tail log: $LOG"
fi

printf '\n=== DEPLOY OK ===\n'
printf 'Project:   %s\n' "$PROJECT_DIR"
printf 'Commit:    %s (%s)\n' "$(git rev-parse HEAD)" "$(git rev-parse --abbrev-ref HEAD)"
printf 'Check:     %s\n' "$PUBLIC_URL"
printf 'Log tail:  tail -f %s/%s\n' "$PROJECT_DIR" "$LOG"
