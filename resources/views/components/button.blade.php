<?php
/**
 * Tombol. Satu-satunya tombol di aplikasi.
 *
 * Radius 6px (rounded-md), bukan rounded-2xl. DESIGN.md section 4: radius
 * adalah alat hierarki, input dan tombol lebih tegas dari kartu karena area
 * yang sering diketik dan sering ditargetkan jari.
 *
 * Aksen teal dipakai HANYA di varian primary, sesuai DESIGN.md section 2:
 * aksen hanya di tombol aksi utama, angka total, dan link aktif. Varian
 * lain netral supaya halaman tidak jadi penuh teal.
 *
 * Ukuran default `md` memakai min-height 44px karena R-03 mewajibkan target
 * sentuh minimal 44px dan kasir sini dipakai sambil berdiri, bukan duduk.
 *
 * @var string      $variant primary|secondary|ghost|danger
 * @var string      $size    sm|md|lg
 * @var string|null $icon    Nama icon dari helper icon().
 * @var bool        $block   True = lebar penuh, dipakai di form mobile.
 * @var bool        $loading True = tampil spinner dan nonaktif.
 * @var string|null $loadingLabel Teks saat loading, default "Tersimpan".
 */
?>
@props([
    'variant'      => 'secondary',
    'size'         => 'md',
    'icon'         => null,
    'block'        => false,
    'loading'      => false,
    'loadingLabel' => null,
    'type'         => 'button',
])

@php
    $variants = [
        // Aksi utama. Teal muncul di sini supaya mata langsung tahu
        // tombol mana yang mengakhiri satu alur, seperti Simpan atau Bayar.
        'primary'   => 'bg-teal-600 text-white hover:bg-teal-700 active:bg-teal-800 border-transparent',
        // Aksi sekunder, sering. Netral supaya aksen tetap langka (R-29).
        'secondary' => 'bg-paper-50 text-paper-800 hover:bg-paper-200 border-paper-400',
        // Aksi yang muncul saat ini saja, atau di dalam menu. Tanpa latar
        // supaya tidak bersaing dengan aksi utama yang terlihat lebih penting.
        'ghost'     => 'bg-transparent text-paper-700 hover:bg-paper-200 border-transparent',
        // Hapus. Merah dipakai karena akibatnya nyata dan tidak bisa dibatalkan.
        // Tidak pakai teal supaya tidak tertukar dengan tombol biasa.
        'danger'    => 'bg-rose-600 text-white hover:bg-rose-700 active:bg-rose-800 border-transparent',
    ];

    $sizes = [
        'sm' => 'h-9 px-3 text-[13px] gap-1.5',
        'md' => 'h-11 px-4 text-sm gap-2',
        'lg' => 'h-12 px-5 text-[15px] gap-2',
    ];

    $variantClass = $variants[$variant] ?? $variants['secondary'];
    $sizeClass    = $sizes[$size] ?? $sizes['md'];
@endphp

<button
    type="{{ $type }}"
    @if ($loading) disabled aria-busy="true" @endif
    {{ $attributes->class([
        'inline-flex items-center justify-center rounded-md border font-medium',
        'transition-[background-color,border-color] duration-[120ms]',
        'active:scale-[.98] active:duration-[80ms]',
        'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-teal-600 focus-visible:ring-offset-2',
        'disabled:opacity-50 disabled:pointer-events-none',
        $variantClass, $sizeClass,
        $block ? 'w-full' : '',
    ]) }}
>
    @if ($loading)
        <span
            class="inline-block w-4 h-4 rounded-full border-2 border-current border-r-transparent animate-spin"
            aria-hidden="true"
        ></span>
        <span>{{ $loadingLabel ?? 'Tersimpan' }}</span>
    @else
        @if ($icon)
            <span class="shrink-0 -ml-0.5" aria-hidden="true">{!! icon($icon, ['size' => $size === 'sm' ? 15 : 17, 'label' => '']) !!}</span>
        @endif
        {{ $slot }}
    @endif
</button>
