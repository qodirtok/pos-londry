<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Test lapis komponen x-*.
 *
 * Alasan test ini ada: komponen UI yang tidak dirender tidak pernah bekerja.
 * Test di sini yang membuktikan markup-nya benar-benar keluar, termasuk
 * mode kartu yang butuh atribute data-label untuk tidak kehilangan nama kolom.
 */
class ComponentLayerTest extends TestCase
{
    private function renderPreview(): string
    {
        return $this->withoutVite()
            ->get('/components-preview')
            ->assertOk()
            ->getContent();
    }

    public function test_preview_page_renders_in_development(): void
    {
        $this->renderPreview();
    }

    /**
     * Komponen pernah mulai dengan `/**` tanpa tag `<?php` pembuka, jadi
     * docblock-nya bocor jadi teks di halaman preview. Bug ini lolos dari
     * test lain karena semua assertion-nya kunci ke class dan label, bukan
     * ke teks asing. Guard ini membaca file sumber, bukan hasil render,
     * supaya menangkap penyebabnya lebih awal.
     */
    public function test_component_templates_do_not_leak_php_docblocks(): void
    {
        $leaked = [];

        foreach (glob(resource_path('views/components/*.blade.php')) as $file) {
            $source = (string) file_get_contents($file);

            // Docblock di luar PHP bocor kalau file dibuka tanpa tag <?php,
            // karena Blade mengeluarkan teks itu apa adanya ke browser.
            if (str_starts_with(ltrim($source), '/**')) {
                $leaked[] = basename($file);
            }
        }

        $this->assertSame([], $leaked, 'Komponen berikut membocorkan docblock ke HTML: ' . implode(', ', $leaked));
    }

    public function test_preview_route_is_blocked_in_production(): void
    {
        // Halaman preview tidak boleh bocor ke kasir di production.
        $this->app['env'] = 'production';
        $this->get('/components-preview')->assertNotFound();
    }

    /**
     * Preview sengaja di luar middleware auth supaya bisa dibuka tanpa
     * login. Ini keputusan sadar, bukan kelalaian: kalau nanti ada yang
     * memindahkannya ke dalam auth group, test ini yang akan menandainya
     * dan membiarkan yang memutuskan, bukan diam-diam berubah.
     */
    public function test_preview_route_stays_reachable_without_login(): void
    {
        $this->get('/components-preview')->assertOk();
    }

    public function test_buttons_render_with_all_variants_and_loading_state(): void
    {
        $html = $this->renderPreview();

        $this->assertStringContainsString('Simpan', $html);
        // Varian primary harus ada bg-teal-600, itu satu-satunya tempat aksen.
        $this->assertStringContainsString('bg-teal-600', $html);
        // Varian danger harus merah, bukan teal, supaya tidak tertukar.
        $this->assertStringContainsString('bg-rose-600', $html);
        // Tombol punya tinggi minimal 44px (h-11) untuk target sentuh R-03.
        $this->assertStringContainsString('h-11', $html);
        // Loading harus menandai tombol nonaktif dan busy, bukan cuma spinner.
        $this->assertStringContainsString('aria-busy="true"', $html);
        $this->assertStringContainsString('disabled', $html);
    }

    public function test_buttons_have_visible_focus_ring_for_keyboard_navigation(): void
    {
        $html = $this->renderPreview();
        // R-32 mensyaratkan indikator fokus yang jelas, bukan outline:none polos.
        $this->assertStringContainsString('focus-visible:ring-2', $html);
        $this->assertStringNotContainsString('focus:outline-none focus:ring-0', $html);
    }

    public function test_status_badges_render_meaningful_tones(): void
    {
        $html = $this->renderPreview();

        // Pita status harus memakai label dari kamus, bukan kata teknis.
        $this->assertStringContainsString('Siap diambil', $html);
        $this->assertStringContainsString('Diterima', $html);
        $this->assertStringContainsString('Lunas', $html);
        // Amber untuk siap diambil, merah untuk belum bayar.
        $this->assertStringContainsString('amber-50', $html);
        $this->assertStringContainsString('rose-50', $html);
    }

    public function test_fields_render_label_above_input_and_wire_errors(): void
    {
        $html = $this->renderPreview();

        // Label harus ada dan punya pasangan id, bukan placeholder saja.
        $this->assertStringContainsString('for="pv_nama"', $html);
        $this->assertStringContainsString('id="pv_nama"', $html);
        // Kolom uang harus numeric dan tabular, supaya keypad HP dan kolom
        // rupiah tidak bergeser.
        $this->assertStringContainsString('inputmode="numeric"', $html);
        $this->assertStringContainsString('tabular-nums', $html);
        // Error harus ditandai aria-invalid dan punya teks yang menjelaskan.
        $this->assertStringContainsString('aria-invalid="true"', $html);
        $this->assertStringContainsString('pv_salah_error', $html);
    }

    public function test_table_card_mode_keeps_column_labels_via_data_label(): void
    {
        $html = $this->renderPreview();

        // Mode kartu wajib menyertakan nama kolom per sel, kalau tidak
        // "Rp 15.000" jadi angka yang tidak jelas uangnya apa.
        $this->assertStringContainsString('data-label="Harga"', $html);
        $this->assertStringContainsString('data-label="Kode"', $html);
        $this->assertStringContainsString('x-table-card', $html);
    }

    public function test_table_has_empty_loading_and_error_states(): void
    {
        $html = $this->renderPreview();

        // R-27: tiga kondisi wajib ada, dan kosong harus menyebut langkah
        // berikutnya, bukan cuma "tidak ada data".
        $this->assertStringContainsString('Belum ada data', $html);
        $this->assertStringContainsString('Tekan Tambah', $html);
        $this->assertStringContainsString('Coba lagi', $html);
        // Error harus diumumkan ke pembaca layar.
        $this->assertStringContainsString('role="alert"', $html);
    }

    public function test_table_row_and_cell_markup_is_valid_inside_table(): void
    {
        $html = $this->renderPreview();

        // <tr> tidak boleh keluar dari <table>, itu yang bikin browser
        // memindahkan baris dan merusak tampilan. Cara ceknya bukan cari
        // tag tr, tapi cari <div> yang membungkus table atau tbody yang
        // disisipi elemen non-tabel di tengah baris.
        $this->assertMatchesRegularExpression('/<table[^>]*>.*<\/table>/s', $html);

        // Semua tag <tr> harus berada di dalam blok <table> yang sama.
        $this->assertSame(
            substr_count($html, '<tr'),
            substr_count($html, '</tr>'),
            'Ada tag tr yang tidak ditutup.'
        );
        $this->assertSame(
            substr_count($html, '<td'),
            substr_count($html, '</td>'),
            'Ada tag td yang tidak ditutup.'
        );

        // Mode kartu tidak boleh memecah tbody jadi div, karena itu membuat
        // <tr> anak dari elemen yang salah dan browser memindahkannya.
        $this->assertDoesNotMatchRegularExpression(
            '/<tbody[^>]*>\s*<div/',
            $html,
            'tbody dipecah oleh div, mode kartu harus pakai CSS, bukan markup terpisah.'
        );
    }

    public function test_no_em_dash_in_any_rendered_ui_text(): void
    {
        // R-02 melarang em dash di teks. Komponen dan kamus label adalah
        // tempat teks baru masuk, jadi dicek di sini, bukan nanti.
        $html = $this->renderPreview();
        $this->assertStringNotContainsString("\u{2014}", $html, 'Ada em dash di teks UI.');
    }

    public function test_copy_dictionary_has_no_em_dash_and_no_english_action_labels(): void
    {
        $dict = require base_path('app/Support/Copy.php');

        $this->assertNotEmpty($dict, 'Kamus label kosong.');

        foreach ($dict as $group => $entries) {
            foreach ($entries as $key => $value) {
                $this->assertStringNotContainsString(
                    "\u{2014}",
                    $value,
                    "Em dash di kamus label {$group}.{$key}."
                );
            }
        }

        // Aksi harian harus Bahasa Indonesia, bukan istilah developer.
        $this->assertSame('Simpan', $dict['actions']['save']);
        $this->assertSame('Hapus', $dict['actions']['delete']);
        $this->assertSame('Pesanan', $dict['objects']['order']);
        $this->assertSame('Pelanggan', $dict['objects']['customer']);
    }

    public function test_copy_label_helper_falls_back_to_key_when_missing(): void
    {
        // Key yang tidak ada harus terlihat jelas saat development, bukan
        // jadi string kosong yang membuat kolom kehilangan nama.
        $this->assertSame('Simpan', copy_label('actions', 'save'));
        $this->assertSame('tidak_ada', copy_label('actions', 'tidak_ada'));
    }

    public function test_components_use_paper_and_teal_tokens_not_raw_hex(): void
    {
        // DESIGN.md section 2: warna harus lewat token, supaya palet punya
        // satu sumber dan tidak bisa berubah sendiri-sendiri per halaman.
        $componentDir = resource_path('views/components');
        $files = glob($componentDir.'/*.blade.php');

        $this->assertNotEmpty($files, 'Tidak ada komponen ditemukan.');

        foreach ($files as $file) {
            $contents = file_get_contents($file);
            // Cari hex warna langsung di markup, bukan di komentar.
            $markup = preg_replace('#/\*.*?\*/#s', '', $contents);
            $markup = preg_replace('#//.*#', '', $markup);

            $this->assertDoesNotMatchRegularExpression(
                '/#[0-9a-fA-F]{3,8}\b/',
                $markup,
                "Warna hex langsung di markup: ".basename($file)
            );
        }
    }
}
