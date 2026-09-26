<?php
/**
 * Pita status.
 *
 * Boleh jadi badge di sini karena isinya status nyata dari database, bukan
 * hiasan (R-09). Warna PUNYA makna, bukan dekorasi:
 *   - netral  = status biasa, tidak butuh tindakan sekarang
 *   - amber   = "siap diambil", butuh pelanggan datang
 *   - danger  = belum bayar atau dibatalkan, ada masalah
 *   - success = lunas atau selesai, aman
 *
 * Amber dipakai untuk "siap diambil" karena itu aksi yang harus diburu,
 * bukan status teknis, dan amber jelas berbeda dari merah error supaya kasir
 * tidak salah baca (DESIGN.md section 2).
 *
 * Radius 4px (rounded-sm), lebih kecil dari induknya, sesuai DESIGN.md
 * section 4: badge adalah anak dari baris atau card, bukan sejajar dengannya.
 *
 * @var string $tone neutral|amber|danger|success|accent
 */
?>
@props([
    'tone' => 'neutral',
    'dot'  => true,
])

@php
    $tones = [
        // Netral untuk status yang sudah beres. Baris beres tidak diberi
        // pita apa-apa di halaman daftar (DESIGN.md section 8), jadi netral
        // dipakai kalau status memang perlu disebut, bukan untuk penanda lain.
        'neutral' => 'bg-paper-200 text-paper-700 border-paper-400',
        // Amber = perlu diburu. Satu-satunya warna non-aksen yang说了算.
        'amber'   => 'bg-amber-50 text-amber-800 border-amber-300',
        'danger'  => 'bg-rose-50 text-rose-800 border-rose-300',
        'success' => 'bg-emerald-50 text-emerald-800 border-emerald-300',
        // Aksen. Hanya untuk hal yang sedang dikejar kasir sekarang, bukan
        // untuk status yang sudah selesai.
        'accent'  => 'bg-teal-50 text-teal-800 border-teal-300',
    ];

    $dotTones = [
        'neutral' => 'bg-paper-500',
        'amber'   => 'bg-amber-600',
        'danger'  => 'bg-rose-600',
        'success' => 'bg-emerald-600',
        'accent'  => 'bg-teal-600',
    ];

    $toneClass = $tones[$tone] ?? $tones['neutral'];
    $dotClass  = $dotTones[$tone] ?? $dotTones['neutral'];
@endphp

<span {{ $attributes->class([
        'inline-flex items-center gap-1.5 rounded-sm border px-2 py-0.5',
        'text-[11px] font-semibold leading-tight',
        $toneClass,
    ]) }}>
    @if ($dot)
        <span class="w-1.5 h-1.5 rounded-full shrink-0 {{ $dotClass }}" aria-hidden="true"></span>
    @endif
    {{ $slot }}
</span>
