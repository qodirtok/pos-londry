<?php
/**
 * Halaman preview komponen.
 *
 * Halaman ini ada supaya komponen bisa dilihat dan diklik tanpa harus
 * menyentuhnya di 15 halaman daftar yang sudah jadi. Kalau sebuah komponen
 * tidak bisa dibuktikan di sini, komponen itu belum selesai.
 *
 * Hanya untuk development. Jangan pernah dibuka kasir, dan jangan jadi
 * contoh halaman untuk user. Halaman ini sengaja tidak rapi.
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<title>Preview Komponen</title>
@vite(['resources/css/app.css','resources/js/app.js'])
<style>
  body{font-family:system-ui,-apple-system,'Segoe UI',Roboto,sans-serif;font-variant-numeric:tabular-nums;-webkit-tap-highlight-color:transparent}
  .pv{padding:1rem;max-width:70rem;margin:0 auto}
  .pv h1{font-size:1.25rem;font-weight:700;margin:0 0 .25rem}
  .pv-intro{color:#57503f;font-size:.875rem;margin:0 0 1.5rem;line-height:1.5}
  .pv-sec{margin:0 0 2.5rem}
  .pv-h{font-size:.75rem;font-weight:700;letter-spacing:.04em;text-transform:uppercase;color:#6b6357;margin:0 0 .75rem;padding-bottom:.5rem;border-bottom:1px solid #e7e2d9}
  .pv-row{display:flex;flex-wrap:wrap;gap:.5rem;align-items:center}
  .pv-note{font-size:.75rem;color:#6b6357;margin:.5rem 0 0;line-height:1.5}
</style>
</head>
<body class="bg-paper-100 text-paper-800 antialiased">
<div class="pv">

    <h1>Preview Komponen</h1>
    <p class="pv-intro">
        Halaman development. Per kecilkan jendela HP, atau pakai mode perangkat,
        untuk melihat mode kartu pada tabel. Halaman ini bukan contoh halaman
        untuk kasir, dan tidak akan pernah muncul di navigasi.
    </p>

    {{-- x-button --}}
    <section class="pv-sec">
        <h2 class="pv-h">Tombol</h2>
        <div class="pv-row">
            <x-button variant="primary" icon="save">Simpan</x-button>
            <x-button variant="secondary" icon="plus">Tambah Pelanggan</x-button>
            <x-button variant="ghost" icon="close">Tutup</x-button>
            <x-button variant="danger" icon="trash">Hapus</x-button>
        </div>
        <div class="pv-row" style="margin-top:.5rem">
            <x-button variant="primary" size="sm">Kecil</x-button>
            <x-button variant="primary" size="md">Sedang</x-button>
            <x-button variant="primary" size="lg">Besar</x-button>
            <x-button variant="primary" loading>Bentar</x-button>
            <x-button variant="secondary" disabled>Tidak aktif</x-button>
        </div>
        <p class="pv-note">
            Aksen teal hanya di varian utama. Semua varian punya tinggi minimal
            44px karena kasir menyentuh sambil berdiri, dan ada ring fokus yang
            terlihat untuk navigasi keyboard.
        </p>
    </section>

    {{-- x-badge --}}
    <section class="pv-sec">
        <h2 class="pv-h">Pita Status</h2>
        <div class="pv-row">
            @foreach (['received','washing','ready','completed','cancelled','paid','unpaid'] as $s)
                <x-badge :tone="match($s){
                    'ready' => 'amber',
                    'cancelled','unpaid' => 'danger',
                    'completed','paid' => 'success',
                    default => 'neutral',
                }">{{ copy_label('status', $s) }}</x-badge>
            @endforeach
        </div>
        <p class="pv-note">
            Warna di sini punya arti, bukan hiasan. Amber berarti perlu diburu,
            merah berarti ada masalah, hijau berarti aman.
        </p>
    </section>

    {{-- x-field --}}
    <section class="pv-sec">
        <h2 class="pv-h">Isian Form</h2>
        <div class="grid gap-3 sm:grid-cols-2">
            <x-field name="pv_nama" label="Nama lengkap" placeholder="Contoh: Budi" required />
            <x-field name="pv_hp" label="Nomor HP" type="tel" inputmode="tel"
                     help="Dipakai untuk kirim struk ke WhatsApp" />
            <x-field name="pv_harga" label="Harga" money prefix="Rp" value="15000" />
            <x-field name="pv_salah" label="Kode" error="Kode sudah dipakai, ganti yang lain" />
        </div>
        <p class="pv-note">
            Label selalu di atas, bukan cuma placeholder, karena placeholder
            hilang begitu diketik. Kolom uang memakai keypad angka di HP.
        </p>
    </section>

    {{-- x-card --}}
    <section class="pv-sec">
        <h2 class="pv-h">Kartu</h2>
        <x-card title="Antrian hari ini" subtitle="Yang perlu dikerjakan sebelum pelanggan datang">
            <x-slot:action>
                <x-button size="sm" variant="secondary">Lihat semua</x-button>
            </x-slot:action>
            <p class="text-sm text-paper-700">Isi kartu ada di sini.</p>
        </x-card>
        <p class="pv-note">
            Kartu tidak berbayang, pembatasnya garis. Bayangan disimpan untuk
            toast dan modal yang benar-benar melayang.
        </p>
    </section>

    {{-- x-table, mode kartu --}}
    <section class="pv-sec">
        <h2 class="pv-h">Tabel, mode kartu</h2>
        <x-card pad="false" class="p-4">
            <x-table mobile="card" :empty="copy_dict('feedback')['empty']">
                <x-slot:head>
                    <x-th>Kode</x-th>
                    <x-th>Nama</x-th>
                    <x-th align="right">Harga</x-th>
                    <x-th>Status</x-th>
                </x-slot:head>

                <x-table-row flagged>
                    <x-td data-label="Kode" mono>ABC-001</x-td>
                    <x-td data-label="Nama">Budi Santoso</x-td>
                    <x-td data-label="Harga" align="right">Rp 15.000</x-td>
                    <x-td data-label="Status">
                        <x-badge tone="amber">Siap diambil</x-badge>
                    </x-td>
                </x-table-row>

                <x-table-row>
                    <x-td data-label="Kode" mono>ABC-002</x-td>
                    <x-td data-label="Nama">Siti Aminah</x-td>
                    <x-td data-label="Harga" align="right">Rp 22.500</x-td>
                    <x-td data-label="Status">
                        <x-badge tone="neutral">Diterima</x-badge>
                    </x-td>
                </x-table-row>

                <x-table-row quiet>
                    <x-td data-label="Kode" mono>ABC-003</x-td>
                    <x-td data-label="Nama">Andi Wijaya</x-td>
                    <x-td data-label="Harga" align="right">Rp 8.000</x-td>
                    <x-td data-label="Status">
                        <x-badge tone="success">Lunas</x-badge>
                    </x-td>
                </x-table-row>
            </x-table>
        </x-card>
        <p class="pv-note">
            Di HP tiap baris berubah jadi kartu, dan nama kolom muncul dari
            atribut data-label. Tidak ada angka yang kehilangan keterangan.
            Baris flagged dapat garis kiri teal, baris quiet diredupkan.
        </p>
    </section>

    {{-- x-table, mode scroll --}}
    <section class="pv-sec">
        <h2 class="pv-h">Tabel, mode geser</h2>
        <x-card pad="false" class="p-4">
            <x-table mobile="scroll">
                <x-slot:head>
                    <x-th>Kode</x-th>
                    <x-th>Nama</x-th>
                    <x-th align="right">Harga</x-th>
                    <x-th align="right">Jumlah</x-th>
                    <x-th>Status</x-th>
                </x-slot:head>
                <x-table-row>
                    <x-td data-label="Kode" mono>ABC-001</x-td>
                    <x-td data-label="Nama">Budi Santoso</x-td>
                    <x-td data-label="Harga" align="right">Rp 15.000</x-td>
                    <x-td data-label="Jumlah" align="right">3</x-td>
                    <x-td data-label="Status"><x-badge tone="neutral">Diterima</x-badge></x-td>
                </x-table-row>
            </x-table>
        </x-card>
        <p class="pv-note">
            Mode geser dipakai kalau tabelnya sedikit kolom. Ganti satu
            atribut, tanpa mengubah CSS atau menulis ulang barisnya.
        </p>
    </section>

    {{-- x-table, state kosong dan error --}}
    <section class="pv-sec">
        <h2 class="pv-h">Tabel, kondisi kosong dan gagal</h2>
        <div class="grid gap-4 sm:grid-cols-2">
            <x-card title="Belum ada data" subtitle="Kondisi paling sering dilihat kasir baru">
                <x-table mobile="card" />
            </x-card>
            <x-card title="Gagal memuat">
                <x-table mobile="card" error="{{ copy_dict('feedback')['error'] }}" />
            </x-card>
        </div>
        <p class="pv-note">
            Tiga kondisi wajib ada di setiap daftar (R-27): kosong, sedang memuat,
            dan gagal. Kosong selalu menyebut apa yang harus diklik berikutnya.
        </p>
    </section>

</div>
</body>
</html>
