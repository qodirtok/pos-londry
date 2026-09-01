import './bootstrap';
import './pwa';
import Swal from 'sweetalert2';

// Flat UI SVG icon helper — replaces emoji icons everywhere
window.icon = function(name, opts={}) {
  const icons = {
    'dashboard':'<path d="M3 13.5V6a2 2 0 012-2h14a2 2 0 012 2v7.5M3 13.5l9 3 9-3M3 13.5V11m18 2.5l-9 3-9-3"/>',
    'pos':'<path d="M3 8h18M3 8l3-4h12l3 4M6 8v10a2 2 0 002 2h8a2 2 0 002-2V8"/>',
    'orders':'<path d="M3 3h18v2a2 2 0 01-2 2H5a2 2 0 01-2-2V3z"/><path d="M5 7h14v12a2 2 0 01-2 2H7a2 2 0 01-2-2V7z"/>',
    'customers':'<path d="M17 21v-2a4 4 0 00-4-4H7a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3.7-4 7 7 0 00-.3 2v4h4z"/>',
    'products':'<path d="M3 12h18M3 12l7-7 7 7m-7 7v-6"/>',
    'categories':'<path d="M11 17h2M11 17V7h2v10zm0 0l-3-3m3 3l3-3"/>',
    'cash':'<path d="M3 7v10a2 2 0 002 2h14a2 2 0 012-2V7a2 2 0 00-2-2H5a2 2 0 01-2 2z"/><path d="M8 10a4 4 0 118 0 4 4 0 01-8 0z"/>',
    'shifts':'<path d="M12 8v4l3 3M6 2h12a4 4 0 014 4v12H2V6a4 4 0 014-4z"/>',
    'reports':'<path d="M3 3v18h18V3H3z"/><path d="M7 11h2v2H7zm4-4h2v6H11zm4 1h2v5h-2z"/>',
    'settings':'<circle cx="12" cy="12" r="3"/><path d="M12 1v6m0 10v6M4.22 4.22l4.24 4.24m7.08 7.08l4.24 4.24M1 12h6m10 0h6"/>',
    'merchants':'<path d="M3 12l8-8v5h4v-5l8 8"/>',
    'branches':'<path d="M3 7a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/><path d="M8 3a2 2 0 012 2v2H6V5a2 2 0 012-2zm8 0a2 2 0 012 2v2H10V5a2 2 0 012-2z"/>',
    'users':'<path d="M17 21v-2a4 4 0 00-3-3.87M9 7h.01M9 7a4 4 0 013.8-3.9 1 1 0 01.7.4l1 1a1 1 0 01.4.7V13m-4 5h.01M9 7a4 4 0 013.8-3.9 1 1 0 01.7.4l1 1a1 1 0 01.4.7V13m-4 5h.01"/>',
    'search':'<path d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 1110.5 3a7.5 7.5 0 0110.5 10.5z"/>',
    'plus':'<path d="M12 5v14M5 12h14"/>',
    'edit':'<path d="M11 5H4a2 2 0 00-2 2v13a2 2 0 002 2h13a2 2 0 002-2v-7m-7.75-9.75a2.5 2.5 0 013.536 0l2.624 2.624a2.5 2 0 010 3.536l-8.5 8.5A2 2 0 015 18V13a2 2 0 012-2h3z"/>',
    'trash':'<path d="M3 6h18M9 2h6a2 2 0 012 2v1H7V4a2 2 0 012-2h2zm2 2h10v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6h4zm4 2v10m0 0l-2-2m2 2l2-2"/>',
    'whatsapp':'<path d="M12.04 2.01A9.94 9.94 0 0 0 5.46 3.9 9.93 9.93 0 0 0 2 12c0 1.66.43 3.21 1.2 4.55L2 22l5.5-1.47a9.9 9.9 0 0 0 4.54 1.1h.01a9.94 9.94 0 0 0 6.37-15.5 9.9 9.9 0 0 0-1.35-1.82 9.94 9.94 0 0 0-4.5-3.2zm5.45 14.78a8.42 8.42 0 0 1-2.37.33 8.3 8.3 0 0 1-4.07-1.03l-.03-.02 3.18-3.18v-.02a2.5 2.5 0 0 1 2.5-2.5 2.5 2.5 0 0 1 2.5 2.5 2.5 2.5 0 0 1-1.82 2.44l-.03.02a8.3 8.3 0 0 1-.93 1.83v-.01z"/>',
    'print':'<path d="M3 9a2 2 0 012-2h14a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path d="M18 3H6v6h12V3z"/><path d="M6 12v7h12v-7"/>',
    'close':'<path d="M18 6L6 18M6 6l12 12"/>',
    'chevron-down':'<path d="M19 9l-7 7-7-7"/>',
    'chevron-right':'<path d="M9 19l7-7-7-7"/>',
    'chevron-left':'<path d="M15 19l-7-7 7-7"/>',
    'laundry':'<path d="M3 3h18v2a2 2 0 01-2 2H5a2 2 0 01-2-2V3zm0 4h18v12a2 2 0 01-2 2H5a2 2 0 01-2-2V7h4z"/>',
    'save':'<path d="M19 3H5a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2V5a2 2 0 00-2-2z"/><path d="M9 13l2 2 4-4"/>',
    'cancel':'<path d="M18 6L6 18M6 6l12 12"/>',
    'check':'<path d="M20 6L9 17l-4-4"/>',
    'menu':'<path d="M3 6h18M3 12h18M3 18h18"/>',
    'logout':'<path d="M9 21c0 .55.45 1 1 1h6c.55 0 1-.45 1-1v-1H9v1zm3-2h6v-2H9v2zm8-2V7a2 2 0 00-2-2H8a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2z"/>'
  };
  const s = opts.size || 16;
  const cls = opts.class || '';
  const color = opts.color || 'currentColor';
  return `<svg xmlns="http://www.w3.org/2000/svg" width="${s}" height="${s}" viewBox="0 0 24 24" fill="none" stroke="${color}" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="${cls}">${icons[name]||icons['search']}</svg>`;
};

// Reusable modal via SweetAlert2
window.modal = {
  alert(title, html, type='info') {
    return Swal.fire({
      title, html, icon: type, width: typeof html==='string' && html.length>200 ? 520 : 420,
      confirmButtonText: 'OK', confirmButtonColor: '#4f46e5',
      customClass: { popup: 'rounded-2xl' }
    });
  },
  confirm(title, html, confirmText='Ya', cancelText='Batal') {
    return Swal.fire({
      title, html,
      icon: 'warning', showCancelButton: true,
      confirmButtonText: confirmText, cancelButtonText: cancelText,
      confirmButtonColor: '#4f46e5', cancelButtonColor: '#6474a5',
      customClass: { popup: 'rounded-2xl' }
    });
  },
  prompt(title, html, input='text') {
    return Swal.fire({
      title, html, input,
      inputLabel: typeof html==='string' ? undefined : html.inputLabel,
      showCancelButton: true,
      confirmButtonText: 'Simpan', cancelButtonText: 'Batal',
      confirmButtonColor: '#4f46e5', cancelButtonColor: '#6474a5',
      customClass: { popup: 'rounded-2xl' },
      didOpen: () => { if(input==='number') Swal.getInput()?.focus(); }
    });
  }
};

window.Swal = Swal;
