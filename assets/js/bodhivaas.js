/* ═══════════════════════════════════════
   Bodhivaas UI — Glass Edition JS
   ═══════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', function () {

  /* ── Theme toggle ───────────────────── */
  const themeBtn = document.querySelector('[data-toggle-theme]');
  const html     = document.documentElement;

  function applyTheme(t) {
    html.setAttribute('data-theme', t);
    if (themeBtn) {
      const icon = themeBtn.querySelector('i');
      if (icon) {
        icon.className = t === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-fill';
      }
    }
  }

  if (themeBtn) {
    const saved = localStorage.getItem('bv-theme') || 'light';
    applyTheme(saved);
    themeBtn.addEventListener('click', () => {
      const next = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
      applyTheme(next);
      localStorage.setItem('bv-theme', next);
    });
  } else {
    const saved = localStorage.getItem('bv-theme') || 'light';
    applyTheme(saved);
  }

  /* ── Sidebar collapse ───────────────── */
  const sbBtn = document.querySelector('[data-toggle-sidebar]');
  if (sbBtn) {
    sbBtn.addEventListener('click', () => document.body.classList.toggle('sidebar-collapsed'));
  }

  /* ── Tooltip init ───────────────────── */
  if (typeof bootstrap !== 'undefined') {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
      new bootstrap.Tooltip(el, { trigger: 'hover' });
    });
  }

  /* ── Active link highlight ──────────── */
  const currentPath = window.location.pathname.split('/').pop();
  document.querySelectorAll('.app-sidebar .nav-link').forEach(link => {
    const href = link.getAttribute('href');
    if (href && href.includes(currentPath) && currentPath !== '') {
      link.classList.add('active');
    }
  });

  /* ── Ripple effect on buttons ───────── */
  document.querySelectorAll('.btn').forEach(btn => {
    btn.addEventListener('click', function (e) {
      const ripple = document.createElement('span');
      const rect   = btn.getBoundingClientRect();
      const size   = Math.max(rect.width, rect.height);
      ripple.style.cssText = `
        position:absolute;border-radius:50%;background:rgba(255,255,255,0.35);
        width:${size}px;height:${size}px;
        left:${e.clientX - rect.left - size/2}px;
        top:${e.clientY - rect.top - size/2}px;
        animation:ripple .55s linear;pointer-events:none;
      `;
      btn.style.position = 'relative';
      btn.style.overflow = 'hidden';
      btn.appendChild(ripple);
      setTimeout(() => ripple.remove(), 600);
    });
  });

  /* inject ripple keyframe once */
  if (!document.getElementById('bv-ripple-style')) {
    const s = document.createElement('style');
    s.id = 'bv-ripple-style';
    s.textContent = '@keyframes ripple{from{transform:scale(0);opacity:1}to{transform:scale(2.5);opacity:0}}';
    document.head.appendChild(s);
  }
});

/* ── Chart helper ─────────────────────── */
function createLineChart(ctx, labels, data, opts = {}) {
  const color = getComputedStyle(document.documentElement)
    .getPropertyValue('--brand').trim() || '#6c5ce7';

  return new Chart(ctx, {
    type: 'line',
    data: {
      labels,
      datasets: [{
        label: opts.label || '',
        data,
        borderWidth: 2.5,
        tension: 0.38,
        borderColor: color,
        pointBackgroundColor: color,
        pointRadius: 3,
        pointHoverRadius: 6,
        fill: true,
        backgroundColor: (ctx2) => {
          const g = ctx2.chart.ctx.createLinearGradient(0, 0, 0, 200);
          g.addColorStop(0,   'rgba(108,92,231,0.20)');
          g.addColorStop(1,   'rgba(108,92,231,0.00)');
          return g;
        }
      }]
    },
    options: Object.assign({
      plugins: { legend: { display: false } },
      scales: {
        y: {
          beginAtZero: false,
          grid: { color: 'rgba(108,92,231,0.06)' },
          ticks: { font: { size: 11 } }
        },
        x: {
          grid: { display: false },
          ticks: { font: { size: 10 }, maxRotation: 0 }
        }
      },
      interaction: { mode: 'index', intersect: false },
      responsive: true
    }, opts.chartOptions || {})
  });
}
