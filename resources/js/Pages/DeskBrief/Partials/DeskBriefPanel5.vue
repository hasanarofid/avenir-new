<template>
  <div class="card span6" id="p5Card">
    <div class="chd">
      <div class="t"><b>5.</b>SIGNAL CONFLUENCE <span style="color:var(--faint);font-weight:400;letter-spacing:0;text-transform:none">— di mana sinyal sepakat</span></div>
      <div class="meta" id="p5Meta">{{ metaText }}</div>
    </div>
    <div class="p5head" id="p5Head" v-html="headText"></div>
    <table class="conf">
      <thead>
        <tr>
          <th>Sektor</th>
          <th title="Kuadran RRG terakhir (Panel 7)">Rotasi</th>
          <th title="Net asing 10 sesi / nilai transaksi">Smart<br>Money</th>
          <th title="P/E &amp; P/BV dihitung harian dari harga EOD; ROE dari laporan keuangan">Valuasi<br><span style="font-weight:400;text-transform:none;letter-spacing:0;color:var(--green)">harian</span></th>
          <th title="Nilai transaksi 10 sesi vs rata-rata 22 sesi">Likui-<br>ditas</th>
          <th title="Return 10 sesi relatif terhadap benchmark">Momen-<br>tum</th>
          <th>Confluence</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="o in rows" :key="o.s">
          <td class="sct">
            {{ o.short }}
            <b>{{ qStr(o.rot.q) }} · {{ o.val.n }} emiten · P/BV {{ o.val.pbv.toFixed(2) }} · ROE {{ o.val.roe.toFixed(1) }}%</b>
          </td>
          <td class="sig" :title="o.rot.q + ' — RS-Ratio ' + o.rot.x.toFixed(2) + ', RS-Mom ' + o.rot.y.toFixed(2) + (o.rot.sig === 0 ? '\n' + (o.rot.q === 'IMPROVING' ? 'Lemah tapi momentum membaik' : 'Kuat tapi momentum melemah') : '')" v-html="rotIco(o.rot)"></td>
          <td class="sig" :title="'Net asing 10 sesi: ' + (o.flow.net >= 0 ? '+' : '') + o.flow.net.toFixed(0) + ' M (' + (o.flow.inten >= 0 ? '+' : '') + o.flow.inten.toFixed(2) + '% dari nilai transaksi)'" v-html="ico(o.flow.sig)"></td>
          <td class="sig" :title="'P/E ' + o.val.pe.toFixed(2) + 'x (harian, EOD ' + fmtD(dates[dates.length - 1]) + ') · P/BV ' + o.val.pbv.toFixed(2) + 'x · ROE ' + o.val.roe.toFixed(1) + '% — skor ' + (o.val.score * 100).toFixed(0) + '/100\n22 sesi: ' + (o.val.d22 >= 0 ? '+' : '') + o.val.d22.toFixed(1) + '%\nMedian bulanan P/E: ' + getM6(o.val.m6)">
            <span v-html="ico(o.val.sig)"></span>
            <span v-html="spark(o.val.daily, o.val.d22)"></span>
            <span class="vtr">P/E <b style="color:var(--ink2)">{{ o.val.pe.toFixed(2) }}</b> <span :style="{ color: o.val.d1 > 0.3 ? 'var(--red)' : o.val.d1 < -0.3 ? 'var(--green)' : 'var(--muted)' }">{{ o.val.d1 >= 0 ? '+' : '' }}{{ o.val.d1.toFixed(2) }}%</span></span>
          </td>
          <td class="sig" :title="'Nilai transaksi 10 sesi rata-rata ' + o.liq.avg.toFixed(0) + ' M/hari, ' + (o.liq.chg >= 0 ? '+' : '') + o.liq.chg.toFixed(1) + '% vs rata-rata 22 sesi'" v-html="ico(o.liq.sig)"></td>
          <td class="sig" :title="'Return 10 sesi ' + (o.mom.abs_ >= 0 ? '+' : '') + o.mom.abs_.toFixed(2) + '%, relatif benchmark ' + (o.mom.rel >= 0 ? '+' : '') + o.mom.rel.toFixed(2) + '%'" v-html="ico(o.mom.sig)"></td>
          <td>
            <span :class="['confscore', CLS[o.label]]">{{ MARK[o.label] }} {{ o.label }}</span>
            <div class="tot">{{ o.tot >= 0 ? '+' : '' }}{{ o.tot }}</div>
          </td>
        </tr>
      </tbody>
    </table>
    <div class="note" id="p5Note" v-html="noteText"></div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { CONF } from './MockDataPanel567.js';

const props = defineProps({
  date: { type: String, default: null }
});

const rows = CONF.rows;
const dates = CONF.dates;

const fmtD = d => {
  const M = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
  const a = d.split('-');
  return +a[2] + ' ' + M[+a[1] - 1];
};

const qStr = (q) => {
  return q.charAt(0) + q.slice(1).toLowerCase();
};

const getM6 = (m6) => {
  return m6.map((x, i) => ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'][i] + ' ' + (x != null ? x.toFixed(1) : '—')).join(' · ');
};

const ico = v => v > 0 ? '<span class="sig sig-up">▲</span>'
  : v < 0 ? '<span class="sig sig-dn">▼</span>'
    : '<span class="sig sig-neu">●</span>';

const rotIco = (r) => {
  if (r.sig > 0) return '<span class="sig sig-up">▲</span>';
  if (r.sig < 0) return '<span class="sig sig-dn">▼</span>';
  return r.q === 'IMPROVING'
    ? '<span class="sig" style="color:#3E7A55;font-size:10px" title="Improving">◥</span>'
    : '<span class="sig" style="color:#8A6528;font-size:10px" title="Weakening">◢</span>';
};

const spark = (vals, chg) => {
  const v = vals.filter(x => x != null);
  if (v.length < 2) return '';
  const mn = Math.min(...v), mx = Math.max(...v), rng = (mx - mn) || 1;
  const W = 44, H = 13;
  const pts = vals.map((x, i) => x == null ? null :
    [(i / (vals.length - 1)) * W, H - ((x - mn) / rng) * (H - 3) - 1.5]).filter(Boolean);
  const d = pts.map((p, i) => (i ? 'L' : 'M') + p[0].toFixed(1) + ',' + p[1].toFixed(1)).join(' ');
  const c = chg < -2 ? '#46C46E' : chg > 2 ? '#E2705C' : '#7C7C76';
  const L = pts[pts.length - 1];
  return '<svg class="spark" width="' + W + '" height="' + H + '">' +
    '<path d="' + d + '" fill="none" stroke="' + c + '" stroke-width="1.2" stroke-linejoin="round"/>' +
    '<circle cx="' + L[0].toFixed(1) + '" cy="' + L[1].toFixed(1) + '" r="1.7" fill="' + c + '"/></svg>';
};

const CLS = { Strong: 'cs-strong', Building: 'cs-mod', Neutral: 'cs-neu', Caution: 'cs-avoid', Avoid: 'cs-avoid' };
const MARK = { Strong: '✓✓✓', Building: '✓✓', Neutral: '—', Caution: '✗', Avoid: '✗✗' };

const headText = computed(() => {
  const latestDate = props.date || dates[dates.length - 1];
  return 'Setiap sinyal dinilai <b>▲ positif / ● netral / ▼ negatif</b>, lalu dijumlah. ' +
    'Rotasi: <span style="color:#3E7A55">◥</span> improving · <span style="color:#8A6528">◢</span> weakening (skor 0). ' +
    'Sparkline = P/E harian ' + fmtD(dates[0]) + '–' + fmtD(latestDate) + ' (turun = makin murah). Hover untuk angka asli.';
});

const metaText = computed(() => {
  const latestDate = props.date || dates[dates.length - 1];
  return '5 sinyal × ' + rows.length + ' sektor · EOD ' + fmtD(latestDate);
});

const noteText = computed(() => {
  const strong = rows.filter(o => o.tot >= 3);
  const avoid = rows.filter(o => o.tot <= -3);
  const build = rows.filter(o => o.tot >= 1 && o.tot < 3);
  const cheap = [...rows].sort((a, b) => a.val.d22 - b.val.d22)[0];

  let t = '';
  if (strong.length) t += 'Confluence tertinggi di <b>' + strong.map(o => o.short).join(' & ') + '</b>.';
  if (build.length) t += (t ? ' ' : '') + 'Menguat: ' + build.map(o => o.short).join(', ') + '.';
  if (avoid.length) t += (t ? ' ' : '') + '<b>' + avoid.map(o => o.short).join(' & ') + '</b> — mayoritas sinyal negatif, hindari.';
  
  if (cheap) {
    t += ' Derating terdalam 22 sesi: <b>' + cheap.short + '</b> (' + cheap.val.d22.toFixed(1) + '%).';
  }
  
  t += ' <span style="color:var(--faint)">P/E &amp; P/BV dihitung harian: harga EOD Ringkasan Saham ÷ EPS &amp; Book Value dari laporan keuangan (basis ' +
    fmtD(CONF.base) + ', ' + CONF.nvalid + ' emiten). ROE dari laporan, berubah tiap kuartal. ' +
    'P/E negatif &amp; ' + CONF.stale + ' emiten dengan laporan &gt;3 kuartal lalu dikecualikan.</span>';
    
  return t;
});
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
.span6{grid-column:span 6}

.conf{width:100%;border-collapse:collapse;font-size:11.5px}
.conf th{text-align:center;font-size:8.5px;letter-spacing:.03em;text-transform:uppercase;color:var(--muted);font-weight:600;padding:7px 5px;border-bottom:1px solid var(--line2)}
.conf th:first-child{text-align:left}
.conf td{text-align:center;padding:9px 5px;border-bottom:1px solid var(--line)}
.conf tr:last-child td{border-bottom:none}
.conf .sct{text-align:left;font-weight:600;color:var(--ink)}
.conf .sct small{display:block;color:var(--faint);font-size:9px;font-weight:400}
.conf .sig{font-size:12px;font-weight:700}
.conf td.sig{cursor:help}
.conf .sct b{display:block;font-size:8.5px;color:var(--faint);font-weight:400;letter-spacing:0;text-transform:none;margin-top:2px}
.cs-neu{background:#1E1E1E;color:var(--muted)}
.conf .tot{font-family:var(--mono);font-size:10px;color:var(--muted);font-variant-numeric:tabular-nums}
.p5head{display:flex;align-items:center;gap:9px;margin-bottom:9px;font-size:9px;color:var(--faint);line-height:1.5}
.p5head b{color:var(--ink2);font-weight:600}
.spark{display:inline-block;vertical-align:middle;margin-left:5px}
.conf .vtr{font-size:8.5px;font-variant-numeric:tabular-nums;display:block;margin-top:1px}
.sig-up{color:var(--green)}.sig-dn{color:var(--red)}.sig-neu{color:var(--muted)}
.confscore{font-size:8.5px;font-weight:700;letter-spacing:.03em;text-transform:uppercase;padding:4px 8px;border-radius:5px;white-space:nowrap}
.cs-strong{background:var(--greensoft);color:var(--green)}
.cs-mod{background:rgba(217,155,62,.14);color:var(--amber)}
.cs-avoid{background:var(--redsoft);color:var(--red)}
</style>
