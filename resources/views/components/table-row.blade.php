{{--
    Baris tabel. Dipakai di dalam komponen table.

    Baris yang butuh tindakan boleh diberi pita status, baris yang sudah beres
    tidak diberi apa-apa (DESIGN.md section 8). Kelas untuk itu ada di
    app.css lewat x-tr--flagged dan x-tr--quiet.

    @var bool $flagged Baris yang perlu tindakan kasir, diberi garis kiri tipis.
    @var bool $quiet   Baris yang sudah beres, diredupkan pelan.
--}}
@props([
    'flagged' => false,
    'quiet'   => false,
])

<tr {{ $attributes->class([
        'x-tr',
        $flagged ? 'x-tr--flagged' : '',
        $quiet   ? 'x-tr--quiet' : '',
    ]) }}>
    {{ $slot }}
</tr>
