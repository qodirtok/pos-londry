<?php

/**
 * Kamus label untuk kasir yang belum biasa dengan aplikasi.
 *
 * Alasan file ini ada: di 44 view, tombol dan judul yang sama ditulis ulang
 * dengan beberapa ejaan berbeda. Kasir baru berpindah halaman, ejaan yang
 * berubah membuat dia berhenti dan berpikir. Satu sumber = satu ejaan.
 *
 * Jangan pakai nama teknis di UI. "Order" bukan "Pesanan", "Submit" bukan
 * "Simpan". Nama teknis untuk developer, bukan untuk kasir.
 *
 * Aturan penulisan, semua bisa ditulis satu baris alasannya (R-31):
 *  - Maksimal 3 kata. Orang membacanya sambil berdiri, bukan duduk tenang.
 *  - Bahasa Indonesia untuk aksi yang kasir lakukan setiap hari.
 *  - Tidak ada em dash (R-02). Pakai koma atau titik.
 *  - Key endingan _help dipakai untuk kalimat penjelas, bukan untuk label.
 */

return [

    /*
     | Aksi. Label tombol, dipakai di semua halaman.
     */
    'actions' => [
        'save'          => 'Simpan',
        'add'           => 'Tambah',
        'cancel'        => 'Batal',
        'delete'        => 'Hapus',
        'edit'          => 'Ubah',
        'close'         => 'Tutup',
        'search'        => 'Cari',
        'apply'         => 'Terapkan',
        'confirm'       => 'Ya, lanjutkan',
        'back'          => 'Kembali',
        'next'          => 'Lanjut',
        'print'         => 'Cetak',
        'download'      => 'Unduh',
        'export'        => 'Unduh laporan',
        'copy'          => 'Salin',
        'refresh'       => 'Muat ulang',
        'detail'        => 'Lihat detail',
        'retry'         => 'Coba lagi',
        'clear'         => 'Kosongkan',
        'clear_filters' => 'Hapus filter',
    ],

    /*
     | Objek. Apa yang sedang dikerjakan.
     */
    'objects' => [
        'order'         => 'Pesanan',
        'orders'        => 'Pesanan',
        'customer'      => 'Pelanggan',
        'customers'     => 'Pelanggan',
        'product'       => 'Layanan',
        'products'      => 'Layanan',
        'category'      => 'Kategori',
        'categories'    => 'Kategori',
        'laundry_type'  => 'Jenis laundry',
        'laundry_types' => 'Jenis laundry',
        'branch'        => 'Cabang',
        'branches'      => 'Cabang',
        'merchant'      => 'Merchant',
        'merchants'     => 'Merchant',
        'user'          => 'Pengguna',
        'users'         => 'Pengguna',
        'shift'         => 'Shift',
        'shifts'        => 'Shift',
        'cash'          => 'Kas',
        'reports'       => 'Laporan',
        'settings'      => 'Pengaturan',
    ],

    /*
     | Status. Pita status untuk baris yang perlu tindakan.
     | Boleh jadi badge karena status ini data nyata, bukan hiasan (R-09).
     */
    'status' => [
        'received'       => 'Diterima',
        'received_help'  => 'Sudah masuk, belum dikerjakan',
        'washing'        => 'Sedang dicuci',
        'washing_help'   => 'Sudah masuk mesin atau tangan',
        'ready'          => 'Siap diambil',
        'ready_help'     => 'Sudah selesai, pelanggan bisa ambil',
        'completed'      => 'Sudah diambil',
        'completed_help' => 'Pelanggan sudah datang mengambil',
        'cancelled'      => 'Dibatalkan',
        'cancelled_help' => 'Pesanan ini tidak dilanjutkan',
        'paid'           => 'Lunas',
        'paid_help'      => 'Uang sudah diterima semua',
        'unpaid'         => 'Belum bayar',
        'unpaid_help'    => 'Uang belum genap',
        'active'         => 'Aktif',
        'active_help'    => 'Bisa dipakai',
        'inactive'       => 'Nonaktif',
        'inactive_help'  => 'Sudah tidak dipakai',
    ],

    /*
     | Uang. Label kolom, tidak mengarang angka (R-17).
     | Helping text menjelaskan dari mana angka itu datang, supaya kasir
     | tidak menghitung ulang atau ragu saat terima pembayaran.
     */
    'money' => [
        'total'         => 'Total',
        'total_help'    => 'Yang harus dibayar pelanggan',
        'paid'          => 'Sudah dibayar',
        'paid_help'     => 'Uang yang sudah masuk',
        'subtotal'      => 'Harga layanan',
        'subtotal_help' => 'Jumlah harga semua item sebelum potongan',
    ],

    /*
     | Feedback. Setiap pesan menyebut apa yang harus diklik berikutnya (R-27).
     */
    'feedback' => [
        'empty'         => 'Belum ada data. Tekan Tambah untuk membuat yang pertama.',
        'empty_search'  => 'Tidak ada yang cocok. Coba kata lain.',
        'loading'       => 'Sedang memuat data.',
        'error'         => 'Data tidak bisa dimuat. Tekan Coba lagi.',
        'saved'         => 'Tersimpan.',
        'deleted'       => 'Sudah dihapus.',
    ],

    /*
     | Field. Label di atas input, tidak pernah placeholder saja
     | karena placeholder hilang saat diisi (DESIGN.md section 8).
     */
    'fields' => [
        'name'            => 'Nama',
        'name_full'       => 'Nama lengkap',
        'phone'           => 'Nomor HP',
        'phone_help'      => 'Dipakai untuk kirim struk ke WhatsApp',
        'address'         => 'Alamat',
        'address_help'    => 'Boleh dikosongkan',
        'code'            => 'Kode',
        'code_help'       => 'Kode pendek untuk kasir, misalnya ABC',
        'price'           => 'Harga',
        'price_help'      => 'Tulis 15000 untuk Rp 15.000',
        'quantity'        => 'Jumlah',
        'quantity_help'   => 'Banyaknya item yang dicuci',
        'discount'        => 'Potongan',
        'discount_help'   => 'Potongan dalam rupiah, tulis 0 kalau tidak ada',
        'status'          => 'Status',
        'status_help'     => 'Aktif atau nonaktif',
        'payment_method'  => 'Cara bayar',
        'paid_amount'     => 'Uang yang dibayar',
        'paid_amount_help'=> 'Uang yang benar-benar diterima',
        'branch'          => 'Cabang',
        'merchant'        => 'Merchant',
    ],
];
