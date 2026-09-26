{{--
    Tabel daftar.

    DESIGN.md section 8: halaman daftar punya satu fokus, yaitu baris yang perlu
    tindakan. Tabel ini tidak memaksakan semua kolom sama pentingnya.

    DUA MODE, SATU MARKUP:
      mobile="card"   -> di HP tiap baris berubah jadi kartu bertumpuk.
                         Nama kolom muncul dari atribut data-label, jadi tidak
                         ada angka yang kehilangan keterangan konteksnya.
      mobile="scroll" -> di HP tabel tetap, digeser ke samping. Dipakai kalau
                         tabelnya sedikit kolom, jadi muatan sideways lebih
                         sedikit merepotkan kasir daripada kartu yang tinggi.

    Kenapa bukan render dua kali: satu markup berarti kasir dan developer
    tidak bisa melihat dua tabel yang berbeda isi, dan HTML tetap valid karena
    tr tidak pernah dipindah keluar dari table.

    Perubahan mode cuma ganti satu atribut, jadi keputusan ini bisa diubah
    di halaman mana pun tanpa menulis ulang tabelnya.

    Pemakaian ada di resources/views/components-preview.blade.php. Tag
    komponen tidak boleh ditulis di komentar docblock PHP, karena Blade
    mengeksekusinya sebagai komponen sungguhan dan menyebabkan error.
--}}
@props([
    'mobile'    => 'card',
    'loading'   => false,
    'error'     => null,
    'empty'     => null,
    'emptyHint' => null,
])

@php
    // Slot body dicek isinya, bukan dikirim sebagai prop. Baris kosong yang
    // hanya berisi spasi dan newline tetap dianggap kosong supaya halaman
    // tidak menampilkan header tabel tanpa isi (R-27).
    // $head diisi otomatis oleh Blade dari slot head. Slot itu opsional,
    // jadi variabelnya bisa undefined kalau pemanggil tidak mengirim header.
    $hasRows  = trim((string) $slot) !== '';
    $headHtml = $head ?? null;
    $hasHead  = trim((string) $headHtml) !== '';
    $showBody = ! $loading && ! $error && $hasRows;
@endphp

<div {{ $attributes->class([
        'x-table w-full min-w-0',
        $mobile === 'card' ? 'x-table-card' : 'x-table-scroll',
    ]) }}>

    @if ($loading)
        {{-- R-27 loading: skeleton statis, tanpa shimmer, karena DESIGN.md
             section 6 melarang animasi loop dan MOTION di sini 1. --}}
        <div class="space-y-2" role="status" aria-busy="true">
            <span class="sr-only">Sedang memuat data.</span>
            @for ($i = 0; $i < 3; $i++)
                <div class="h-11 rounded-md bg-paper-200" aria-hidden="true"></div>
            @endfor
        </div>

    @elseif ($error)
        {{-- R-27 error: menyebut jalan keluarnya, bukan cuma "gagal". --}}
        <div class="py-8 text-center" role="alert">
            <p class="text-sm text-paper-700 font-medium max-w-xs mx-auto leading-relaxed">{{ $error }}</p>
        </div>

    @elseif (! $hasRows)
        {{-- R-27 empty + DESIGN.md section 10: kosong menjelaskan apa
             berikutnya, bukan menampilkan angka nol yang menyesatkan. --}}
        <div class="py-10 px-4 text-center">
            <p class="text-sm text-paper-600 leading-relaxed max-w-xs mx-auto">
                {{ $empty ?: $emptyHint ?: 'Belum ada data. Tekan Tambah untuk membuat yang pertama.' }}
            </p>
        </div>

    @else
        {{-- Area scroll hanya di mode scroll. Border-ish wrapper menjaga
             gestur geser tetap di dalam tabel, tidakjmembuat halaman
             geser seluruhnya (R-03). --}}
        <div @class([
            'x-table-scrollbox',
            $mobile === 'scroll' ? 'overflow-x-auto' : 'overflow-x-visible',
        ])>
            <table class="w-full text-sm">
                @if ($hasHead)
                    <thead>
                        <tr class="border-b border-paper-400">
                            {!! $headHtml !!}
                        </tr>
                    </thead>
                @endif
                <tbody>
                    {{ $slot }}
                </tbody>
            </table>
        </div>
    @endif
</div>
