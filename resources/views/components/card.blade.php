<?php
/**
 * Kartu.
 *
 * DESIGN.md section 4: kartu tidak berbayang, pembatasnya border. Bayangan
 * hanya untuk hal yang benar-benar melayang (toast, modal, dropdown).
 * Alasan: bayangan pada kartu membuat seluruh halaman terasa melayang dan
 * menghilangkan batas halaman.
 *
 * @var string|null $title      Judul card. Null = tanpa header.
 * @var string|null $subtitle   Penjelas di bawah judul, bukan placeholder.
 * @var string|null $action     HTML tombol/link aksi di kanan header.
 * @var string|null $footer     Dipisah garis, untuk paginasi atau total.
 * @var bool        $pad        False =-card jadi wrapper untuk tabel penuh.
 */
?>
@props([
    'title'    => null,
    'subtitle' => null,
    'action'   => null,
    'footer'   => null,
    'pad'      => true,
])

<div {{ $attributes->class([
        'bg-paper-50 border border-paper-300 rounded-lg',
        $pad ? 'p-4' : '',
    ]) }}>
    {{-- Slot action dipakai kalau pemanggil menulis <x-slot:action>.
         Isi slot itu masuk ke variabel $action, bukan ke $slot, jadi yang
         dicek adalah $action. --}}
    @php $hasAction = trim((string) $action) !== ''; @endphp
    @if ($title || $hasAction)
        <div class="flex items-start justify-between gap-3 mb-4">
            <div class="min-w-0">
                @if ($title)
                    <h2 class="text-[15px] font-semibold text-paper-800 truncate">{{ $title }}</h2>
                @endif
                @if ($subtitle)
                    <p class="text-xs text-paper-600 mt-0.5 leading-snug">{{ $subtitle }}</p>
                @endif
            </div>
            @if ($hasAction)
                <div class="shrink-0">{{ $action }}</div>
            @endif
        </div>
    @endif

    {{ $slot }}

    @if ($footer)
        <div class="mt-4 pt-3 border-t border-paper-300 text-sm text-paper-600">
            {{ $footer }}
        </div>
    @endif
</div>
