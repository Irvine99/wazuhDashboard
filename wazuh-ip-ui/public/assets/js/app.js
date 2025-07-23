function buildDiscoverUrl(ip) {
  // Discover avec kuery "srcip:\"IP\"" (adapter le champ si besoin)
  const query = encodeURIComponent(`srcip:"${ip}"`);
  const g = encodeURIComponent('(time:(from:now-24h,to:now))');
  const a = encodeURIComponent(`(query:(language:kuery,query:'${query}'))`);
  return `${window.DASHBOARD_BASE}/app/discover#/?_g=${g}&_a=${a}`;
}

async function loadIPs() {
  try {
    const r = await fetch('/index.php?ajax=1', { cache: 'no-cache' });
    const ips = await r.json();
    const box = document.getElementById('ip-container');
    box.innerHTML = '';
    if (!ips.length) {
      box.innerHTML = '<div class="text-gray-500">Aucune IP pour le moment.</div>';
      return;
    }
    ips.forEach(ip => {
      const btn = document.createElement('button');
      btn.className = 'px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded shadow transition ip-btn';
      btn.textContent = ip;
      btn.onclick = () => window.open(buildDiscoverUrl(ip), '_blank', 'noopener');
      box.appendChild(btn);
    });
  } catch (e) {
    console.error(e);
  }
}

document.addEventListener('DOMContentLoaded', () => {
  if (document.getElementById('ip-container')) {
    loadIPs();
    setInterval(loadIPs, 60000);
  }
});