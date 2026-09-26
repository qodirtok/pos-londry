{{--
    Sel header tabel. Dipakai di dalam slot head pada komponen table.

    Baris header adalah lantai, bukan fokus, jadi warnanya netral dan tidak
    pernah aksen teal (DESIGN.md section 2: aksen hanya di tombol utama, angka
    total, dan link aktif).
--}}
@props([
    'align' => 'left',
])

<th scope="col" {{ $attributes->class([
        'x-th text-[12px] font-semibold uppercase tracking-[.04em] text-paper-600',
        'px-3 py-2.5 whitespace-nowrap',
        $align === 'right'  ? 'text-right' : '',
        $align === 'center' ? 'text-center' : '',
    ]) }}>
    {{ $slot }}
</th>
