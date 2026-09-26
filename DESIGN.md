# DESIGN.md

Arah desain untuk Londry POS. Berkas ini adalah sumber arah, `antislop.md` adalah filter. Kalau ada yang bertentangan, arah menang kecuali bentroknya perlu ditanyakan.

Terakhir diperbarui: 2026-09-26

---

## 1. Identitas

POS laundry untuk warung dan cabang, dipakai kasir yang berdiri, sometimes sambil pegang HP. Bukan software akuntansi, bukan dashboard analytics. Alat kerja.

**Untuk siapa:** kasir laundry, termasuk yang baru belajar. Banyak yang bukan orang IT. Sebagian besar berdiri, sebagian besar di bawah sinar matahari, sebagian besar di HP yang layarnya kecil.

**Persona:** tenang dan tegas. Tenang supaya tidak menekan, tegas supaya tidak ambigu. Bukan "cantik", bukan "mewah", tidak juga "mainan".

**Kalimat pengenal:** *kartu struk, bukan kartu statistik.*

**Yang harus terasa:** seperti kasir yang sudah hafal alurnya. Tidak meminta perhatian. Tidak menuntut. Beres.

---

## 2. Palet

Tiga warna inti, satu aksen.anggu-Netral tidak dihitung sebagai inti.

| Peran | Nilai | Dipakai di |
|---|---|---|
| Kertas (netral dasar) | `#FFFDF9` | Latar halaman. Hangat, bukan putih murni, supaya tidak terasa klinis |
| Tinta (teks) | `#2B2320` | Teks utama. Hangat gelap, bukan hitam murni |
| **Aksen** | **`#0F766E`** | **Hanya: tombol utama, total uang, link aktif** |
| Status siap | `#B45309` | Status "siap diambil". Amber = hangat + terlihat jelas. Bukan aksen, tapi warna status |
| Garis | `#E7E2D9` | Border 1px. Menggantikan abu Tailwind |
| Garis kuat | `#D4CCBF` | Border yang butuh ditebuskan |

### Kenapa warna-warna ini

- `#0F766E` teal tobas dipilih karena gleichzeitig Reading "terawat" dan Reading "terpisah" dari semua warna status (hijau/merah/amber). Jadi ketika aksen muncul, itu pasti interaksi, bukan status. Alasan: teal tobas cukupoz depth untuk teks putih (5.47:1) dan cukup jenuh untuk dipakai di atas kertas hangat tanpa terlihatispatcher di Elementary.
- Amber `#B45309` untuk "siap diambil" karena "siap" itu IActionnya, bukan status teknis. Amber terlihat hangat danIFFerent dari error merah, jadi kasir tidak salah baca.
- Kertas `#FFFDF9` bukan `#FFFFFF` karena putih murni di layar HP di bawah matahari berkedip dan terasa seperti klinik atau rumah sakit.

### Aturan aksen (penting)

Aksen **hanya** di tiga tempat: tombol aksi utama, angka total yang sedang dikejar, dan link yang sedang aktif. Bukan di setiap border, bukan di setiap ikon, bukan di setiap badge.

Kalau halaman terasa terlalu banyak teal, berarti aksen Bocor. Tarik kembali.

---

## 3. Tipografi

**Mengganti Inter, pakai system font stack.**

```
font-family: system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
```

**Alasan satu baris:** POS ini dipakai di koneksi Indonesia yang tidak selalu stabil, jadi font harus tampil tanpa menunggu unduhan, dan system font sudah punya angka tabular yang benar untuk kolom rupiah.

**Angka uang:** `font-variant-numeric: tabular-nums`. Kolom total harus sejajar ke kanan, tidak bergeser-geser.

**Nomor order / SKU:** `ui-monospace, SFMono-Regular, Menlo, monospace`. Bukan karena estetika terminal, tapi karena nomor order dan SKU itu identifier yang perlu dibaca karakter per karakter saat mencari di tumpukan kertas.

**Skala:**

| Peran | Ukuran | Berat |
|---|---|---|
| Angka total | 24-32px | 700 |
| Heading halaman | 20px | 600 |
| Isi | 15-16px | 400 |
| Label | 12px | 600, `letter-spacing: .04em` |
| Caption | 11px | 400 |

Tidak ada monospace heading besar. Tidak ada uppercase dengan tracking lebar kecuali label kecil di atas section.

---

## 4. Bentuk

### Radius

Radius adalah alat hierarki, bukan dekorasi.

| Elemen | Radius | Alasan |
|---|---|---|
| Input, tombol | `6px` |WD lebih tegas dari kartu, yang benar untuk area yang sering diketik |
| Kartu, panel | `8px` | Pembatas surface, tidak membulat berlebihan |
| Badge, chip status | `4px` | Lebih kecil dari induknya |
| Filter kategori | `pill` (999px) | Satur satu-satunya yang memang berbentuk pil, karena meniru pilihan tab |
| Modal | `12px` di atas, penuh di bawah (mobile) | Mengikuti bentuk sheet dari bawah |

Tidak ada `rounded-2xl` (16px) lagi. Tidak ada semua elemen membulat penuh kecuali filter dan FAB.

### Garis

Border `1px` dengan warna Garis `#E7E2D9`. Border adalah pembatas utama di seluruh aplikasi, bukan bayangan.

### Bayangan

Hanya untuk hal yang benar-benar melayang: toast, modal, dropdown, dan FAB. Kartu **tidak** berbayang.-Alasan: bayangan pada kartu membuat seluruh halaman terasa melayang dan menghilangkan batas halaman.

Shadow yang boleh dipakai:

| Elemen | Shadow |
|---|---|
| Toast | `0 8px 24px rgba(43,35,32,.12)` |
| Modal | `0 20px 60px rgba(43,35,32,.20)` |
| Dropdown | `0 4px 16px rgba(43,35,32,.10)` |
| FAB (mobile) | `0 6px 20px rgba(43,35,32,.18)` |

---

## 5. Motif Identitas

**Satu motif: kartu bertanda.**

Di laundry, setiap order punya tag fisik: kertas kecil, ditulis tangan, lalu dipegang purchaser sampai diambil kembali. Dari situ asal-usul produk ini.

Motifnya, dan diulang konsisten di semua tempat:

1. **Nomor tag** (nomor order) selalu tampil dalam tabular/mono, selalu dengan pemisah, selalu dianggap sebagai identitas bukan angka biasa.
2. **Garis putus-putus** sebagai pemisah seksi, hanya di tag dan struk, tidak di tempat lain.
3. **Pita status** sebagai penanda state (received / ready / selesai) dengan warna status, bukan warna aksen.

Kalau nama produk dan logo diganti, motif ini masih akan terasa laundry. Itu.target R-20.

Yang **bukan** motif: garis grid, dot pattern, gradient, glow, blob.

---

## 6. Motion

MOTION 1. Hanya hover dan transisi status. Tidak ada loop, tidak ada pulse, tidak ada animasi berjalan sendiri.

| Aksi | Transisi |
|---|---|
| Hover tombol/kartu | `background-color` 120ms, `border-color` 120ms |
| Tekan tombol | `transform: scale(.98)` 80ms |
| Buka modal | `opacity` + `translateY(8px)` 180ms |
| Tutup modal | `opacity` 120ms |
| Toast masuk | `translateY(-8px)` + `opacity` 160ms |
| Cart drawer (mobile) | `translateY` 240ms `cubic-bezier(.22,1,.36,1)` |

Tidak ada animasi chart, tidak ada skeleton shimmer, tidak ada pulse pada status dot.

---

## 7. Tiga Dials

| Dial | Nilai | Alasan |
|---|---|---|
| **ENERGY** | **1** | Alat kerja, bukan halaman=jualan. Tenang, tidakWGZiv, tidakWGZiv |
| **RHYTHM** | **2** | Layout POS sudah dua panel (produk + keranjang) dan itu memang berbeda. Section yang lain boleh seragam karena isinya memang seragam |
| **MOTION** | **1** | Hanya hover dan transisi. Tidak ada loop |

**Design Read:**

> *Reading this as: internal POS tool for laundry shop staff on their feet, warm-and-clear working language, dial ENERGY 1 / RHYTHM 2 / MOTION 1.*

---

## 8. Aturan per layar

**POS.** Satu fokus: keranjang. Panel produk adalah alat (cari, tap), bukan tujuan. Panel keranjang adalah tempat mata settles. Di mobile, keranjang jadi bottom sheet supaya produk punya ruang penuh.

**Dashboard.** Satu fokus: antrian yang perlu dikerjakan. Omzet ada tapi sekunder, karena omzet dipantau akhir hari, antrian itu pekerjaan sekarang. Jangan pakai grid 4 stat card seragam. Urutan: antrian dulu, angka besar, laporan di bawah.

**Daftar (orders, customers, products).** Satu fokus: baris yang perlu tindakan. Baris yang butuh tindakan boleh diberi pita status; baris yang beres tidak diberiapa-apa.

**Form.** Satu fokus: field yang sedang diisi. Label selalu di atas, bukan placeholder-only (placeholder hilang saat mengisi, kasir lupa maksudnya).

---

## 9. Yang Dilarang

- Grid, dot pattern, atau garis teknis di latar
- Gradient sebagai warna utama
- Glow pada lebih dari 1 elemen
- Blur pada lebih dari 1-2 elemen
- Badge capsule yang isinya cuma hiasan
- Emoji sebagai navigasi atau di teks UI
- Panah `→` pada setiap tombol
- Semua elemen radius sama
- Bayangan pada kartu biasa
- Angka yang tidak berasal dari data
- Dark mode sebagai gaya

---

## 10. Angka

Semua angka di UI berasal dari query database. Tidak ada placeholder, tidak ada contoh yang terlihat seperti data asli, tidak ada tren rekaan. Kalau datanya belum ada, tampilkan "Belum ada" beserta apa yang harus dilakukan berikutnya, bukan angka 0 yang menyesatkan.
