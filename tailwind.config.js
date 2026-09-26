/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        // Aksen tunggal. Dipakai hanya untuk: tombol aksi utama,
        // angka total yang sedang dikejar, dan link aktif. DESIGN.md section 2.
        teal: {
          50:  '#f0fdfa',
          100: '#ccfbf1',
          200: '#99f6e4',
          300: '#5eead4',
          400: '#2dd4bf',
          500: '#14b8a6',
          600: '#0f766e',
          700: '#115e59',
          800: '#134e4a',
          900: '#0b3d3a',
        },
        // Netral kertas, bukan slate. DESIGN.md section 2.
        // Shade teks sudah diuji kontrasnya terhadap #faf7f1:
        // 500=5.5:1, 600=7.0:1, 700=11.3:1, 800=14.1:1.
        // Jangan menaikkan 500 ke warna lebih terang tanpa cek ulang.
        paper: {
          DEFAULT: '#fffdf9',
          50: '#fffdf9',
          100: '#faf7f1',
          200: '#f4efe6',
          300: '#e7e2d9',
          400: '#d4ccbf',
          500: '#6b6357',
          600: '#57503f',
          700: '#3b352e',
          800: '#2b2320',
          900: '#1a1512',
          // Shade untuk teks DI ATAS permukaan gelap (sidebar).
          // 400=6.4:1 dan 500=5.1:1 terhadap paper-900, jadi aman.
          'on-dark':      '#a89e8f',
          'on-dark-dim':  '#6b6357',
        },
      },
      borderRadius: {
        // Radius adalah alat hierarki, bukan dekorasi. DESIGN.md section 4.
        // DEFAULT sengaja tidak diubah: `rounded` bawaan Tailwind (8px)
        // sudah cocok untuk kartu. Yang di-override hanya yang terlalu bulat.
        sm: '4px',
        md: '6px',
        '2xl': '12px',
      },
      fontFamily: {
        // System font: tampil tanpa unduhan, sudah punya angka tabular.
        // DESIGN.md section 3.
        sans: ['system-ui', '-apple-system', 'Segoe UI', 'Roboto', 'Helvetica Neue', 'sans-serif'],
        mono: ['ui-monospace', 'SFMono-Regular', 'Menlo', 'Consolas', 'monospace'],
      },
    },
  },
  plugins: [],
}
