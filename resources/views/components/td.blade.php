{{--
    Sel isi tabel. Dipakai di dalam table-row pada komponen table.

    Atribut data-label itu yang membuat mode kartu di HP tetap bisa dibaca.
    Karena itu label di sini WAJIB diisi untuk kolom yang hanya berarti kalau
    ada nama kolomnya, misalnya "Rp 15.000" yang harus tahu itu harga.

    @var string $align left|right|center
    @var bool $mono True untuk nomor order dan SKU. DESIGN.md section 3:
                   identifier dibaca karakter per karakter saat cari di tumpukan kertas.
    @var bool $hideOnMobile True untuk kolom sekunder yang tidak perlu di HP.
                             Kalau semua kolom disembunyikan, baris jadi tidak
                             informatif, jadi jangan dipakai berlebihan.
--}}
@props([
    'align' => 'left',
    'mono'  => false,
    'hideOnMobile' => false,
])

<td data-label="{{ $attributes->get('data-label') ?? '' }}"
    {{ $attributes->class([
        'x-td px-3 py-2.5 align-middle',
        $mono ? 'font-mono text-[13px]' : '',
        $hideOnMobile ? 'x-td--secondary' : '',
        $align === 'right'  ? 'text-right' : '',
        $align === 'center' ? 'text-center' : '',
    ]) }}>
    {{ $slot }}
</td>
