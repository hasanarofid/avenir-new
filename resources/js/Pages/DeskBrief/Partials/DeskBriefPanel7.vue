<template>
  <div class="card span12" id="p7Card">
    <div class="chd">
      <div class="t"><b>7.</b>SECTOR ROTATION <span style="color:var(--faint);font-weight:400;text-transform:none;letter-spacing:0">— Relative Rotation Graph</span></div>
      <div class="meta"><span id="p7Meta">{{ metaText }}</span></div>
    </div>
    <div class="p7bar">
      <span class="plab">Tampilkan</span>
      <div class="p7dd" id="p7Dd" :class="{ open: isDropdownOpen }">
        <button class="p7dd-btn" id="p7DdBtn" @click.stop="isDropdownOpen = !isDropdownOpen">
          <span id="p7DdLabel">{{ ddLabel }}</span><span class="arw">▾</span>
        </button>
        <div class="p4dd-menu" id="p7DdMenu">
          <div class="p4dd-item hdr">Level Sektor</div>
          <div class="p4dd-item" :class="{ on: mode === 'sector' }" @click="selectScope(null, 'Semua Sektor')">
            <span>Semua Sektor</span><span class="p4dd-check">✓</span>
          </div>
          <div class="p4dd-item hdr">Level Emiten — pilih sektor</div>
          <div v-for="(lbl, key) in activeRrgs.short" :key="key" class="p4dd-item" :class="{ on: mode === 'stock' && cur === key }" @click="selectScope(key, lbl)">
            <span>{{ lbl }}</span><span class="p4dd-check">✓</span>
          </div>
        </div>
      </div>
      <span class="p7dd-hint" v-if="mode === 'sector'">← klik untuk lihat <b>RRG per emiten</b> di dalam satu sektor</span>
      <div class="p7scope" id="p7Scope" style="margin-left:auto;text-align:right" v-html="scopeText"></div>
    </div>
    <div class="rrgnarr" id="p7Narr" v-html="narrativeHtml"></div>
    <div class="rrgwrap">
      <div class="rrgstage" id="p7Stage" 
           @mousemove="onMouseMove" 
           @mouseleave="onMouseLeave"
           @touchstart.passive="onTouchMove"
           @touchmove.passive="onTouchMove"
           @touchend="onTouchEnd">
        <canvas ref="canvasRef"></canvas>
        <div class="rrgtip" id="p7Tip" :style="{ opacity: tipOpacity, left: tipLeft + 'px', top: tipTop + 'px' }" v-html="tipHtml"></div>
      </div>
      <div class="rrgside">
        <div class="rrgctl">
          <div class="lab">Panjang Tail</div>
          <div class="rrgtail">
            <input type="range" id="p7Tail" min="1" :max="N || 15" v-model="tail" step="1">
            <span class="v" id="p7TailV">{{ tail }} sesi</span>
          </div>
        </div>
        <div class="rrgctl" style="flex:1;min-height:0;display:flex;flex-direction:column">
          <div class="lab"><span id="p7LegLab">{{ legLabel }}</span><span style="float:right;font-weight:400;letter-spacing:0;text-transform:none;cursor:pointer;color:var(--blue)" @click="toggleAllOff">semua</span></div>
          <div class="rrglegend" id="p7Legend">
            <div v-for="(item, i) in legendItems" :key="item.s.key" :class="['rrgli', mode === 'stock' ? 'stk' : '', off.has(item.originalIndex) ? 'off' : '']" :title="item.title" @click="toggleItem(item.originalIndex)" @mouseenter="hover = item.originalIndex" @mouseleave="hover = -1">
              <span class="dot" :style="{ background: item.s.color }"></span>
              <span :class="mode === 'stock' ? 'cd' : 'nm'">{{ item.s.lbl }}</span>
              <span v-if="mode === 'stock'" class="ch" :style="{ color: item.s.chg >= 0 ? '#46C46E' : '#E2705C' }">{{ item.s.chg >= 0 ? '+' : '' }}{{ item.s.chg.toFixed(1) }}%</span>
              <span :class="['q', item.qc]">{{ item.qs }}</span>
              <span v-if="item.ev" :class="['rotbadge', item.ev.status]">{{ item.ev.status === 'fresh' ? 'BARU' : '✓ ' + item.ev.dwell + 'x' }}</span>
            </div>
          </div>
        </div>
        <div class="rrgnote" id="p7Note" v-html="noteHtml"></div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch, onUnmounted } from 'vue';
import { RRG, RRGS, ROTEV } from './MockDataPanel567.js';

const props = defineProps({
  rrgSector: { type: Object, default: null },
  rrgStocks: { type: Object, default: null }
});

const canvasRef = ref(null);
const isDropdownOpen = ref(false);
const mode = ref('sector');
const cur = ref(null);
const ddLabel = ref('Semua Sektor');
const tail = ref(5);
const hover = ref(-1);
const off = ref(new Set());

const activeRrg = computed(() => props.rrgSector || RRG);
const activeRrgs = computed(() => props.rrgStocks || RRGS);

const activeRotev = computed(() => {
  const sectorEv = {};
  const stockEv = {};
  
  if (props.rrgSector && props.rrgSector.rotev) {
    Object.assign(sectorEv, props.rrgSector.rotev);
  } else {
    Object.assign(sectorEv, ROTEV.sector);
  }
  
  const rs = activeRrgs.value;
  if (rs && rs.sectors) {
    for (const sec in rs.sectors) {
      if (rs.sectors[sec].rotev) {
        stockEv[sec] = rs.sectors[sec].rotev;
      }
    }
  }
  
  if (Object.keys(stockEv).length === 0) {
    Object.assign(stockEv, ROTEV.stock);
  }
  
  return { sector: sectorEv, stock: stockEv };
});

const PAL = ['#C9A227', '#E2705C', '#46C46E', '#D99B3E', '#5FA0D8', '#8FD4A8', '#A98BD0', '#6BC5C0', '#D08BA8', '#7C9BE0', '#B0B0A8', '#E09A6B', '#79C7E8', '#C4A6E0', '#8FBF7A', '#E0728F', '#6FAFD0', '#D4C06A', '#9AD0B0', '#C08BB0'];
const PAD = { l: 52, r: 16, t: 16, b: 34 };
const QC = { LEADING: ['q-lead', '#46C46E'], WEAKENING: ['q-weak', '#D99B3E'], LAGGING: ['q-lag', '#E2705C'], IMPROVING: ['q-imp', '#5FA0D8'] };
const QS = { LEADING: 'Lead', WEAKENING: 'Weak', LAGGING: 'Lag', IMPROVING: 'Impr' };
const QARR = { LEADING: '#46C46E', WEAKENING: '#D99B3E', LAGGING: '#E2705C', IMPROVING: '#5FA0D8' };

let ctx = null;
let W = 1096, H = 470, PW = 0, PH = 0;
let B = null;
let S = [];
const sVersion = ref(0);
let N = 0;

const sx = v => PAD.l + (v - B.x0) / (B.x1 - B.x0) * PW;
const sy = v => PAD.t + (1 - (v - B.y0) / (B.y1 - B.y0)) * PH;
const quad = (x, y) => x >= 100 ? (y >= 100 ? 'LEADING' : 'WEAKENING') : (y >= 100 ? 'IMPROVING' : 'LAGGING');

const evFor = (key) => mode.value === 'stock' ? (activeRotev.value.stock[cur.value] || {})[key] : activeRotev.value.sector[key];

const fmt = d => {
  if (!d) return '—';
  const M = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
  const a = d.split('-'); return +a[2] + ' ' + M[+a[1] - 1];
};

const metaText = computed(() => {
  const r = activeRrg.value;
  const from = r.meta?.from || r.dates[0];
  const to = r.meta?.to || r.dates[r.dates.length - 1];
  const sessions = r.meta?.sessions || r.dates.length;
  return `${fmt(from)}–${fmt(to)} · ${sessions} sesi`;
});
const legLabel = computed(() => mode.value === 'sector' ? 'Sektor' : 'Emiten');
const scopeText = computed(() => {
  const r = activeRrg.value;
  const rs = activeRrgs.value;
  return mode.value === 'sector'
    ? `Benchmark: <b>IHSG (Composite)</b> · ${r.meta?.universe || 900} emiten, cap-weighted`
    : `Benchmark: <b>index ${rs.short[cur.value]}</b> · 15 emiten teraktif (median nilai transaksi)`;
});

const tipOpacity = ref('0');
const tipLeft = ref(0);
const tipTop = ref(0);
const tipHtml = ref('');

const load = () => {
  const r = activeRrg.value;
  const rs = activeRrgs.value;
  if (mode.value === 'sector') {
    S = r.series.map(s => ({
      key: s.name, lbl: s.short, full: s.name, color: s.color, pts: s.pts,
      chg: null, val: null, flow: s.flow ? s.flow[s.flow.length - 1] : null
    }));
  } else {
    const D = rs.sectors[cur.value];
    if (D && D.series) {
      S = D.series.map((e, i) => ({
        key: e.code, lbl: e.code, full: e.name, color: PAL[i % PAL.length],
        pts: e.pts, chg: e.chg, val: e.val, flow: e.flow
      }));
    } else {
      S = [];
    }
  }
  if (S.length > 0) {
    N = S[0].pts.length;
    if (tail.value > N) tail.value = N;
  }
  off.value = new Set();
  hover.value = -1;
  sVersion.value++;
};

const bounds = () => {
  let a = [], b = [];
  S.forEach((s, i) => { if (off.value.has(i)) return; s.pts.slice(-tail.value).forEach(p => { a.push(p.x); b.push(p.y); }); });
  if (!a.length) { a = [98, 102]; b = [98, 102]; }
  let x0 = Math.min(...a, 99.2), x1 = Math.max(...a, 100.8), y0 = Math.min(...b, 99.2), y1 = Math.max(...b, 100.8);
  const mx = (x1 - x0) * 0.14 || 0.4, my = (y1 - y0) * 0.14 || 0.4;
  x0 -= mx; x1 += mx; y0 -= my; y1 += my;
  const cx = (x0 + x1) / 2, cy = (y0 + y1) / 2, r = Math.max(x1 - x0, y1 - y0) / 2;
  return { x0: cx - r, x1: cx + r, y0: cy - r, y1: cy + r };
};

const draw = () => {
  if (!ctx) return;
  const isMobile = typeof window !== 'undefined' && window.innerWidth <= 768;
  B = bounds();
  ctx.clearRect(0, 0, W, H);
  const x100 = sx(100), y100 = sy(100);
  [['#46C46E', x100, PAD.t, PAD.l + PW - x100, y100 - PAD.t],
  ['#D99B3E', x100, y100, PAD.l + PW - x100, PAD.t + PH - y100],
  ['#E2705C', PAD.l, y100, x100 - PAD.l, PAD.t + PH - y100],
  ['#5FA0D8', PAD.l, PAD.t, x100 - PAD.l, y100 - PAD.t]]
    .forEach(([c, x, y, w, h]) => { if (w > 0 && h > 0) { ctx.fillStyle = c + '0D'; ctx.fillRect(x, y, w, h); } });
  
  ctx.font = isMobile ? '700 11px Arial' : '700 9px Arial'; 
  ctx.textBaseline = 'top';
  [['LEADING', '#46C46E', PAD.l + PW - 8, PAD.t + 6, 'right'],
  ['WEAKENING', '#D99B3E', PAD.l + PW - 8, PAD.t + PH - (isMobile ? 20 : 16), 'right'],
  ['LAGGING', '#E2705C', PAD.l + 8, PAD.t + PH - (isMobile ? 20 : 16), 'left'],
  ['IMPROVING', '#5FA0D8', PAD.l + 8, PAD.t + 6, 'left']]
    .forEach(([t, c, x, y, al]) => { ctx.fillStyle = c + 'aa'; ctx.textAlign = al; ctx.fillText(t, x, y); });
  ctx.strokeStyle = '#242424'; ctx.lineWidth = 1; ctx.setLineDash([2, 3]);
  const sp = B.x1 - B.x0, step = sp > 6 ? 2 : sp > 3 ? 1 : 0.5;
  for (let v = Math.ceil(B.x0 / step) * step; v < B.x1; v += step) {
    if (Math.abs(v - 100) < 1e-9) continue;
    const X = Math.round(sx(v)) + .5; ctx.beginPath(); ctx.moveTo(X, PAD.t); ctx.lineTo(X, PAD.t + PH); ctx.stroke();
  }
  for (let v = Math.ceil(B.y0 / step) * step; v < B.y1; v += step) {
    if (Math.abs(v - 100) < 1e-9) continue;
    const Y = Math.round(sy(v)) + .5; ctx.beginPath(); ctx.moveTo(PAD.l, Y); ctx.lineTo(PAD.l + PW, Y); ctx.stroke();
  }
  ctx.setLineDash([]);
  ctx.strokeStyle = '#3A3A3A'; ctx.lineWidth = 1.2;
  ctx.beginPath(); ctx.moveTo(Math.round(x100) + .5, PAD.t); ctx.lineTo(Math.round(x100) + .5, PAD.t + PH); ctx.stroke();
  ctx.beginPath(); ctx.moveTo(PAD.l, Math.round(y100) + .5); ctx.lineTo(PAD.l + PW, Math.round(y100) + .5); ctx.stroke();
  ctx.font = isMobile ? '10px Arial' : '9px Arial'; ctx.fillStyle = '#565651';
  ctx.textAlign = 'center'; ctx.textBaseline = 'top';
  for (let v = Math.ceil(B.x0 / step) * step; v < B.x1; v += step) ctx.fillText(v.toFixed(step < 1 ? 1 : 0), sx(v), PAD.t + PH + 7);
  ctx.textAlign = 'right'; ctx.textBaseline = 'middle';
  for (let v = Math.ceil(B.y0 / step) * step; v < B.y1; v += step) ctx.fillText(v.toFixed(step < 1 ? 1 : 0), PAD.l - 8, sy(v));
  ctx.textAlign = 'center'; ctx.textBaseline = 'top'; ctx.fillStyle = '#7C7C76'; ctx.font = isMobile ? '700 10px Arial' : '700 9px Arial';
  ctx.fillText('RS-RATIO  →  kekuatan relatif', PAD.l + PW / 2, PAD.t + PH + 18);
  ctx.save(); ctx.translate(13, PAD.t + PH / 2); ctx.rotate(-Math.PI / 2);
  ctx.textBaseline = 'top'; ctx.fillText('RS-MOMENTUM  →  arah', 0, 0); ctx.restore();

  const many = S.length > 14;
  S.forEach((s, i) => {
    if (off.value.has(i)) return;
    const pts = s.pts.slice(-tail.value); if (!pts.length) return;
    const hl = hover.value === i, dim = hover.value >= 0 && !hl;
    ctx.lineWidth = hl ? (isMobile ? 3.2 : 2.4) : (isMobile ? 2.2 : 1.5); ctx.lineJoin = 'round'; ctx.lineCap = 'round';
    for (let k = 1; k < pts.length; k++) {
      const a = (k / (pts.length - 1)) * 0.6 + 0.16;
      ctx.strokeStyle = s.color + Math.round((dim ? a * 0.22 : a) * 255).toString(16).padStart(2, '0');
      ctx.beginPath(); ctx.moveTo(sx(pts[k - 1].x), sy(pts[k - 1].y)); ctx.lineTo(sx(pts[k].x), sy(pts[k].y)); ctx.stroke();
    }
    for (let k = 0; k < pts.length - 1; k++) {
      ctx.fillStyle = s.color + Math.round((dim ? 0.12 : 0.4) * 255).toString(16).padStart(2, '0');
      ctx.beginPath(); ctx.arc(sx(pts[k].x), sy(pts[k].y), hl ? 3 : 2.2, 0, 6.284); ctx.fill();
    }
    const L = pts[pts.length - 1], X = sx(L.x), Y = sy(L.y);
    ctx.globalAlpha = dim ? 0.28 : 1;
    ctx.fillStyle = s.color; ctx.beginPath(); ctx.arc(X, Y, hl ? (isMobile ? 9 : 7) : (isMobile ? 7 : 5.2), 0, 6.284); ctx.fill();
    ctx.strokeStyle = '#0A0A0A'; ctx.lineWidth = 1.6; ctx.stroke();
    if (!many || hl || !dim) {
      ctx.font = (hl ? '700 ' : '600 ') + (isMobile ? '11px Arial' : '9.5px Arial'); ctx.textAlign = 'left'; ctx.textBaseline = 'middle';
      const tw = ctx.measureText(s.lbl).width;
      let lx = X + 10, ly = Y;
      if (lx + tw > PAD.l + PW - 4) lx = X - 10 - tw;
      ctx.fillStyle = '#0A0A0Ac4'; ctx.fillRect(lx - 3, ly - (isMobile ? 8 : 6.5), tw + 6, isMobile ? 16 : 13);
      ctx.fillStyle = hl ? '#EAEAE7' : '#B6B6B0'; ctx.fillText(s.lbl, lx, ly);
    }
    ctx.globalAlpha = 1;
    s._hx = X; s._hy = Y;
  });
};

const legendItems = computed(() => {
  sVersion.value;
  return S.map((s, i) => ({ s, originalIndex: i }))
    .sort((a, b) => b.s.pts[b.s.pts.length - 1].x - a.s.pts[a.s.pts.length - 1].x)
    .map(item => {
      const L = item.s.pts[item.s.pts.length - 1];
      const q = quad(L.x, L.y);
      const ev = evFor(item.s.key);
      const validEv = (ev && ev.frm && ev.frm !== ev.to) ? ev : null;
      return {
        ...item,
        q,
        qc: QC[q][0],
        qs: QS[q],
        ev: validEv,
        title: item.s.full + (validEv ? ` — ${validEv.frm} → ${validEv.to}` : '')
      };
    });
});

const noteHtml = computed(() => {
  sVersion.value;
  const cnt = { LEADING: 0, WEAKENING: 0, LAGGING: 0, IMPROVING: 0 };
  S.forEach(s => { const L = s.pts[s.pts.length - 1]; cnt[quad(L.x, L.y)]++; });
  const top = [...S].sort((a, b) => b.pts[b.pts.length - 1].x - a.pts[a.pts.length - 1].x);
  if(!top.length) return '—';
  const r = activeRrg.value;
  const sessions = r.meta?.sessions || r.dates.length;
  const lag = r.lag || 8;
  return `<b>${top[0].lbl}</b> &amp; <b>${top[1].lbl}</b> memimpin; <b>${top[top.length - 1].lbl}</b> paling tertinggal.<br>${cnt.LEADING} leading · ${cnt.IMPROVING} improving · ${cnt.WEAKENING} weakening · ${cnt.LAGGING} lagging.<br><span style="color:var(--amber)">⚠</span> Basis ${sessions} sesi harian (bukan mingguan). Momentum lag ${lag} sesi — disamakan dengan EMA-8.`;
});

const narrativeHtml = computed(() => {
  sVersion.value;
  const confirmed = [], fresh = [];
  S.forEach(s => {
    const ev = evFor(s.key); if (!ev || !ev.frm || ev.frm === ev.to) return;
    (ev.status === 'confirmed' ? confirmed : fresh).push({ s, ev });
  });
  confirmed.sort((a, b) => b.ev.dwell - a.ev.dwell);
  fresh.sort((a, b) => b.s.pts[b.s.pts.length - 1].x - a.s.pts[a.s.pts.length - 1].x);

  let html = `<div class="hd"><b>⟳ Rotasi Kuadran${mode.value === 'stock' ? ' — ' + activeRrgs.value.short[cur.value] : ''}</b></div>`;
  if (!confirmed.length && !fresh.length) {
    html += '<div class="rrgnarr-empty">Belum ada rotasi kuadran pada rentang data ini.</div>';
  } else {
    const mkEv = (s, ev) => `<div class="ev"><span style="color:${s.color};font-weight:700">${s.lbl}</span><span class="arr">${ev.frm} → </span><b style="color:${QARR[ev.to]}">${ev.to}</b>${ev.status === 'confirmed' ? `<span class="rotbadge confirmed">bertahan ${ev.dwell} sesi</span>` : '<span class="rotbadge fresh">baru, awasi</span>'}</div>`;
    if (confirmed.length) html += confirmed.slice(0, 4).map(({ s, ev }) => mkEv(s, ev)).join('');
    if (fresh.length) html += fresh.slice(0, 3).map(({ s, ev }) => mkEv(s, ev)).join('');
  }
  return html;
});

const selectScope = (key, label) => {
  mode.value = key ? 'stock' : 'sector';
  cur.value = key;
  ddLabel.value = label;
  isDropdownOpen.value = false;
  load();
  draw();
};

const toggleAllOff = () => {
  if (off.value.size) off.value = new Set();
  else {
    const newOff = new Set();
    S.forEach((s, i) => { if (i) newOff.add(i); });
    off.value = newOff;
  }
  draw();
};

const toggleItem = (i) => {
  const newOff = new Set(off.value);
  if (newOff.has(i)) newOff.delete(i); else newOff.add(i);
  off.value = newOff;
  draw();
};

const updateCanvasSize = () => {
  const cv = canvasRef.value;
  const stage = cv?.parentElement;
  if (!cv || !stage) return;

  const containerWidth = stage.clientWidth || 1096;
  const isMobile = window.innerWidth <= 768;

  if (isMobile) {
    W = containerWidth;
    H = Math.max(380, Math.round(containerWidth * 1.08));
  } else {
    W = 1096;
    H = 470;
  }

  const DPR = window.devicePixelRatio || 1;
  cv.width = W * DPR;
  cv.height = H * DPR;
  cv.style.width = '100%';
  cv.style.height = isMobile ? H + 'px' : 'auto';

  ctx = cv.getContext('2d');
  ctx.scale(DPR, DPR);

  PW = W - PAD.l - PAD.r;
  PH = H - PAD.t - PAD.b;

  draw();
};

const handlePointerPos = (clientX, clientY) => {
  if (!canvasRef.value) return;
  const r = canvasRef.value.getBoundingClientRect();
  if (!r.width || !r.height) return;

  const scaleX = W / r.width;
  const scaleY = H / r.height;
  const mx = (clientX - r.left) * scaleX;
  const my = (clientY - r.top) * scaleY;

  let best = -1, bd = 1e9;
  S.forEach((s, i) => {
    if (off.value.has(i) || s._hx == null) return;
    const d = Math.hypot(s._hx - mx, s._hy - my);
    if (d < bd) { bd = d; best = i; }
  });

  const isMobile = window.innerWidth <= 768;
  const touchThreshold = isMobile ? 32 : 16;

  if (bd < touchThreshold && best >= 0) {
    if (hover.value !== best) { hover.value = best; draw(); }
    const s = S[best], L = s.pts[s.pts.length - 1], q = quad(L.x, L.y);
    let html = `<div class="tn" style="color:${s.color}">${s.lbl}</div>`;
    if (mode.value === 'stock') html += `<div style="font-size:9px;color:var(--faint);margin:-2px 0 4px;max-width:150px">${s.full}</div>`;
    html += `<div class="tr"><span>RS-Ratio</span><b>${L.x.toFixed(2)}</b></div>
             <div class="tr"><span>RS-Mom</span><b>${L.y.toFixed(2)}</b></div>
             <div class="tr"><span>Kuadran</span><b style="color:${QC[q][1]}">${q}</b></div>`;
    if (s.chg != null) html += `<div class="tr"><span>Chg 11 sesi</span><b style="color:${s.chg >= 0 ? '#46C46E' : '#E2705C'}">${s.chg >= 0 ? '+' : ''}${s.chg.toFixed(2)}%</b></div>`;
    if (s.val != null) html += `<div class="tr"><span>Nilai/hari</span><b>${s.val.toFixed(1)} M</b></div>`;
    if (s.flow != null) html += `<div class="tr"><span>Net asing</span><b style="color:${s.flow >= 0 ? '#46C46E' : '#E2705C'}">${s.flow >= 0 ? '+' : ''}${s.flow.toFixed(1)} M</b></div>`;
    tipHtml.value = html;
    tipOpacity.value = '1';

    let tx = s._hx + 16, ty = s._hy - 10;
    if (tx + 170 > W) tx = s._hx - 180;
    if (ty < 4) ty = 4; if (ty + 120 > H) ty = H - 122;
    tipLeft.value = (tx / W) * r.width;
    tipTop.value = (ty / H) * r.height;
  } else {
    tipOpacity.value = '0';
    if (hover.value !== -1) { hover.value = -1; draw(); }
  }
};

const onMouseMove = (e) => handlePointerPos(e.clientX, e.clientY);
const onTouchMove = (e) => {
  if (e.touches && e.touches[0]) {
    handlePointerPos(e.touches[0].clientX, e.touches[0].clientY);
  }
};
const onTouchEnd = () => {
  setTimeout(() => {
    tipOpacity.value = '0';
    hover.value = -1;
    draw();
  }, 2500);
};

const closeDropdown = () => { isDropdownOpen.value = false; };

onMounted(() => {
  load();
  updateCanvasSize();
  window.addEventListener('resize', updateCanvasSize);
  document.addEventListener('click', closeDropdown);
});

onUnmounted(() => {
  window.removeEventListener('resize', updateCanvasSize);
  document.removeEventListener('click', closeDropdown);
});

watch(tail, () => {
  draw();
});

watch(hover, () => {
  draw();
});

watch([() => props.rrgSector, () => props.rrgStocks], () => {
  load();
  draw();
}, { deep: true });
</script>

<style scoped>
.card{background:var(--card);border:1px solid var(--line);border-radius:10px;padding:15px 16px;min-width:0;display:flex;flex-direction:column}
.chd{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px}
.chd .t{font-size:11px;letter-spacing:.1em;text-transform:uppercase;color:var(--ink2);font-weight:700}
.chd .t b{color:#888888;font-weight:700;margin-right:5px}
.chd .meta{font-size:10px;color:var(--muted);display:flex;align-items:center;gap:6px}
.note{font-size:11px;color:var(--muted);margin-top:14px;line-height:1.45}
.note.warn{border-left:2px solid var(--amber);padding-left:10px;color:var(--amber)}
.num{font-variant-numeric:tabular-nums}
.span12{grid-column:span 12}

.rrgwrap{display:grid;grid-template-columns:1fr 232px;gap:16px;margin-top:4px}
.rrgstage{position:relative;background:var(--inset);border:1px solid var(--line);border-radius:8px;overflow:hidden;min-width:0}
.rrgstage canvas{display:block;width:100%;height:auto}
.rrgtip{position:absolute;pointer-events:none;background:#0D0D0Dfa;border:1px solid var(--line3);border-radius:6px;padding:7px 9px;font-size:10px;line-height:1.5;opacity:0;transition:opacity .12s;z-index:5;min-width:132px}
.rrgtip .tn{font-weight:700;font-size:10.5px;margin-bottom:3px}
.rrgtip .tr{display:flex;justify-content:space-between;gap:12px;color:var(--muted)}
.rrgtip .tr b{color:var(--ink2);font-weight:600}
.rrgside{display:flex;flex-direction:column;gap:9px}
.rrgctl{background:var(--card2);border:1px solid var(--line);border-radius:7px;padding:9px 10px}
.rrgctl .lab{font-size:8.5px;letter-spacing:.14em;text-transform:uppercase;color:var(--faint);font-weight:700;margin-bottom:6px}
.rrgtail{display:flex;align-items:center;gap:8px;width:100%}
.rrgtail input[type=range]{flex:1;min-width:0;accent-color:var(--blue);height:3px}
.rrgtail .v{font-size:10px;color:var(--ink2);white-space:nowrap;flex-shrink:0;text-align:right;font-variant-numeric:tabular-nums}
.rrglegend{display:flex;flex-direction:column;gap:1px;max-height:238px;overflow-y:auto}
.rrgli{display:grid;grid-template-columns:9px 1fr auto;align-items:center;gap:7px;padding:3px 4px;border-radius:4px;cursor:pointer;font-size:10px;transition:background .1s;position:relative}
.rrgli:hover{background:#1E1E1E}
.rrgli.off{opacity:.32}
.rrgli .dot{width:8px;height:8px;border-radius:50%}
.rrgli .nm{color:var(--ink2);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.rrgli .q{font-size:8px;font-weight:700;letter-spacing:.04em;padding:1px 4px;border-radius:3px;position:relative}
.q-lead{background:rgba(70,196,110,.16);color:#46C46E}
.q-weak{background:rgba(217,155,62,.16);color:#D99B3E}
.q-lag{background:rgba(226,112,92,.16);color:#E2705C}
.q-imp{background:rgba(95,160,216,.16);color:#5FA0D8}
.rrgli .cd{font-family:var(--mono);font-weight:700;font-size:9.5px;color:var(--ink2)}
.rrgli .ch{font-size:9px;font-variant-numeric:tabular-nums;text-align:right}
.rrgli.stk{grid-template-columns:9px 40px 1fr auto}
.rrgnote{font-size:9px;color:var(--faint);line-height:1.55;padding:8px 10px;background:#121212;border:1px solid var(--line);border-radius:7px}
:deep(.rrgnote b){color:var(--ink2);font-weight:600}
.rrgnarr{background:linear-gradient(180deg,#171512,#141210);border:1px solid rgba(217,155,62,.28);border-radius:8px;padding:10px 12px;margin-bottom:11px;font-size:10.5px;line-height:1.6;color:var(--ink2)}
:deep(.rrgnarr .hd){display:flex;align-items:center;gap:7px;margin-bottom:6px}
:deep(.rrgnarr .hd b){font-size:9px;letter-spacing:.13em;text-transform:uppercase;color:var(--amber);font-weight:700}
:deep(.rrgnarr .ev){display:flex;align-items:baseline;gap:6px;padding:3px 0}
:deep(.rrgnarr .ev .arr){color:var(--faint);font-size:9px}
:deep(.rotbadge){font-size:7.5px;font-weight:700;letter-spacing:.03em;padding:1px 4px;border-radius:3px;margin-left:5px;white-space:nowrap}
.rotbadge{font-size:7.5px;font-weight:700;letter-spacing:.03em;padding:1px 4px;border-radius:3px;margin-left:5px;white-space:nowrap}
:deep(.rotbadge.fresh){background:rgba(217,155,62,.18);color:var(--amber);border:1px solid rgba(217,155,62,.4)}
.rotbadge.fresh{background:rgba(217,155,62,.18);color:var(--amber);border:1px solid rgba(217,155,62,.4)}
:deep(.rotbadge.confirmed){background:rgba(70,196,110,.14);color:#46C46E;border:1px solid rgba(70,196,110,.32)}
.rotbadge.confirmed{background:rgba(70,196,110,.14);color:#46C46E;border:1px solid rgba(70,196,110,.32)}
:deep(.rrgnarr-empty){color:var(--faint);font-style:italic}
.rrgbadge{font-size:8.5px;font-weight:700;letter-spacing:.08em;padding:2px 6px;border-radius:3px;background:rgba(217,155,62,.14);color:var(--amber);border:1px solid rgba(217,155,62,.3)}


.p4dd-menu{position:absolute;top:calc(100% + 6px);right:0;min-width:150px;background:#161616;border:1px solid var(--line3);border-radius:10px;padding:5px;z-index:40;opacity:0;visibility:hidden;transform:translateY(-6px);transition:opacity .14s,transform .14s;box-shadow:0 14px 40px rgba(0,0,0,.6)}
.p4dd-item{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:8px 11px;border-radius:7px;font-size:11.5px;color:var(--ink2);cursor:pointer}
.p4dd-item:hover{background:#1F1F1F;color:var(--ink)}
.p4dd-item.on{color:var(--green)}
.p4dd-item .p4dd-dot{width:7px;height:7px;border-radius:50%;flex-shrink:0}
.p4dd-item .p4dd-check{font-size:11px;opacity:0}
.p4dd-item.on .p4dd-check{opacity:1}


.p7bar{display:flex;align-items:center;gap:11px;margin-bottom:11px;padding:9px 11px;background:var(--card2);border:1px solid var(--line2);border-radius:8px}
.p7bar .plab{font-size:9px;letter-spacing:.14em;text-transform:uppercase;color:var(--faint);font-weight:700;flex-shrink:0}
.p7dd{position:relative}
.p7dd .p4dd-menu{left:0;right:auto;min-width:196px;max-height:326px;overflow-y:auto}
.p7dd.open .p4dd-menu{opacity:1;visibility:visible;transform:translateY(0)}
.p7dd-btn{display:flex;align-items:center;justify-content:space-between;gap:10px;background:#1E1E1E;border:1px solid var(--line3);color:var(--ink);padding:7px 11px;border-radius:7px;font-size:11.5px;font-weight:600;cursor:pointer;min-width:186px;transition:border-color .14s,background .14s}
.p7dd-btn:hover{background:#252525;border-color:var(--green2)}
.p7dd.open .p7dd-btn{border-color:var(--green2);background:#252525}
.p7dd-btn .arw{font-size:9px;color:var(--muted);transition:transform .18s}
.p7dd.open .arw{transform:rotate(180deg)}
.p7dd-hint{font-size:9.5px;color:var(--muted);flex-shrink:0}
.p7dd-hint b{color:var(--green);font-weight:600}
.p4dd-item.hdr{font-size:8.5px;letter-spacing:.13em;text-transform:uppercase;color:var(--faint);font-weight:700;padding:7px 11px 4px;cursor:default;pointer-events:none;border-top:1px solid var(--line);margin-top:4px}
.p4dd-item.hdr:first-child{border-top:0;margin-top:0}
.p7scope{font-size:9.5px;color:var(--faint);line-height:1.5}
.p7scope b{color:var(--ink2);font-weight:600}

@media (max-width: 768px) {
  .p7bar {
    flex-wrap: wrap;
    align-items: stretch;
    gap: 8px;
  }
  .p7dd-btn {
    width: 100%;
    min-width: unset;
  }
  .p7dd-hint {
    display: none;
  }
  .p7scope {
    margin-left: 0 !important;
    text-align: left !important;
    width: 100%;
  }
  .rrgwrap {
    grid-template-columns: 1fr;
    gap: 12px;
  }
  .rrgside {
    width: 100%;
  }
  .rrglegend {
    max-height: 200px;
  }
}
</style>
