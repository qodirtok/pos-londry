<?php
/**
 * Isian form.
 *
 * DESIGN.md section 8: label selalu di atas, bukan placeholder-only, karena
 * placeholder hilang begitu kasir mulai mengetik dan dia lupa maksudnya.
 *
 * Kontrol dibuat min-height 44px karena R-03 mewajibkan target sentuh 44px
 * dan form ini diisi sambil berdiri di bawah sinar matahari, bukan duduk.
 *
 * Kolom uang memakai inputmode="numeric" supaya HP membuka keypad angka,
 * bukan keyboard huruf. Ini mengurangi salah ketik dari kasir baru.
 *
 * @var string      $name     Nama field untuk label dan error.
 * @var string|null $label    Label. Kalau null dan ada $placeholder, pakai itu.
 * @var string|null $help     Penjelas di bawah. Bukan contoh, bukan tips.
 * @var string|null $error    Pesan error, disambungkan ke input lewat aria.
 * @var string      $type     Tipe input, default text.
 * @var string|null $prefix   Teks di depan input, untuk "Rp".
 * @var bool        $money    True = inputmode angka + rata kanan tabular.
 */
?>
@props([
    'name'     => null,
    'label'    => null,
    'help'     => null,
    'error'    => null,
    'type'     => 'text',
    'prefix'   => null,
    'money'    => false,
    'required' => false,
])

@php
    $labelText = $label ?: $placeholder ?? null;
    $helpId    = $name ? $name.'_help' : null;
    $errorId   = $name ? $name.'_error' : null;
    $described = collect([$error ? $errorId : null, $help ? $helpId : null])->filter()->implode(' ');
@endphp

<div {{ $attributes->class(['w-full']) }}>
    @if ($labelText)
        <label @if ($name) for="{{ $name }}" @endif
               class="block text-[12px] font-semibold tracking-[.04em] text-paper-700 mb-1.5">
            {{ $labelText }}
            @if ($required)
                <span class="text-rose-600" aria-hidden="true">*</span>
                <span class="sr-only">wajib diisi</span>
            @endif
        </label>
    @endif

    <div class="relative">
        @if ($prefix)
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-paper-600 pointer-events-none select-none">
                {{ $prefix }}
            </span>
        @endif

        <input
            type="{{ $type }}"
            @if ($name) name="{{ $name }}" id="{{ $name }}" @endif
            @if ($required) required @endif
            @if ($money) inputmode="numeric" @endif
            @if ($described) aria-describedby="{{ $described }}" @endif
            @if ($error) aria-invalid="true" @endif
            {{ $attributes->class([
                'w-full h-11 rounded-md border text-[15px] text-paper-800 bg-paper-50',
                'placeholder:text-paper-500',
                'transition-[background-color,border-color] duration-[120ms]',
                'focus:outline-none focus:ring-2',
                $prefix ? 'pl-10' : 'px-3',
                'pr-3',
                $error
                    ? 'border-rose-500 focus:ring-rose-500/30'
                    : 'border-paper-400 focus:border-teal-600 focus:ring-teal-600/25',
                $money ? 'text-right font-mono tabular-nums' : '',
            ]) }}
        >
    </div>

    @if ($help && !$error)
        <p id="{{ $helpId }}" class="mt-1.5 text-xs text-paper-600 leading-snug">{{ $help }}</p>
    @endif

    @if ($error)
        <p id="{{ $errorId }}" class="mt-1.5 text-xs text-rose-700 font-medium leading-snug">{{ $error }}</p>
    @endif
</div>
