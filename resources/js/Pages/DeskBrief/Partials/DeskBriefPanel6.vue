<template>
  <div class="card span6" id="p6Card">
    <div class="chd">
      <div class="t"><b>6.</b>FOREIGN FLOW DIVERGENCE <span style="color:var(--faint);font-weight:400;letter-spacing:0;text-transform:none">— arah harga vs arah uang asing</span></div>
      <div class="meta" id="p6Meta">{{ metaText }}</div>
    </div>
    <div class="p6bar">
      <div class="p6seg" id="p6Seg">
        <button :class="{ on: win === '5' }" @click="win = '5'">5 sesi</button>
        <button :class="{ on: win === '10' }" @click="win = '10'">10 sesi</button>
        <button :class="{ on: win === '22' }" @click="win = '22'">22 sesi</button>
      </div>
      <div class="p6range" id="p6Range" v-html="rangeText"></div>
    </div>
    <div class="p6agg" id="p6Agg" v-html="aggText"></div>
    <div class="smlens" id="p6Lens">
      <div class="smlblock">
        <div class="h pos">▲ Akumulasi Senyap <span style="color:var(--muted);font-weight:400;text-transform:none;letter-spacing:0">· harga ≤ +1%, asing net beli</span></div>
        <template v-if="currentData.acc.length">
          <div v-for="r in currentData.acc" :key="r.c" class="smlrow" :title="r.n">
            <span class="tk">{{ r.c }}</span>
            <span :class="['px', r.chg >= 0 ? 'pos' : 'neg']">{{ r.chg >= 0 ? '+' : '' }}{{ r.chg.toFixed(1) }}%</span>
            <span class="fl" :style="{ color: r.net >= 0 ? 'var(--green)' : 'var(--amber)' }">{{ money(r.net) }}</span>
            <span class="in">{{ r.int_ >= 0 ? '+' : '' }}{{ r.int_.toFixed(1) }}%</span>
            <span class="sc">{{ r.s }}</span>
          </div>
        </template>
        <div v-else style="font-size:10px;color:var(--faint);padding:6px 0;font-style:italic">Tidak ada emiten yang memenuhi kriteria pada window ini.</div>
      </div>
      <div class="smlblock">
        <div class="h" style="color:var(--amber)">⚠ Distribusi saat Menguat <span style="color:var(--muted);font-weight:400;text-transform:none;letter-spacing:0">· harga ≥ +3%, asing net jual</span></div>
        <template v-if="currentData.dis.length">
          <div v-for="r in currentData.dis" :key="r.c" class="smlrow" :title="r.n">
            <span class="tk">{{ r.c }}</span>
            <span :class="['px', r.chg >= 0 ? 'pos' : 'neg']">{{ r.chg >= 0 ? '+' : '' }}{{ r.chg.toFixed(1) }}%</span>
            <span class="fl" :style="{ color: r.net >= 0 ? 'var(--green)' : 'var(--amber)' }">{{ money(r.net) }}</span>
            <span class="in">{{ r.int_ >= 0 ? '+' : '' }}{{ r.int_.toFixed(1) }}%</span>
            <span class="sc">{{ r.s }}</span>
          </div>
        </template>
        <div v-else style="font-size:10px;color:var(--faint);padding:6px 0;font-style:italic">Tidak ada emiten yang memenuhi kriteria pada window ini.</div>
      </div>
    </div>
    <div class="note warn" id="p6Note" v-html="noteText"></div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { FFD } from './MockDataPanel567.js';

const props = defineProps({
  ffd: { type: Object, default: null }
});

const win = ref('5');

const fmtD = d => {
  const M = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
  const a = d.split('-');
  return +a[2] + ' ' + M[+a[1] - 1];
};

const money = v => {
  const a = Math.abs(v);
  return (v < 0 ? '−' : '+') + 'Rp ' + (a >= 1000 ? (a / 1000).toFixed(2) + ' T' : a.toFixed(0) + ' M');
};

const currentData = computed(() => {
  const mock = FFD[win.value];
  if (props.ffd && props.ffd[win.value]) {
    return {
      ...mock,
      from: props.ffd[win.value].from,
      to: props.ffd[win.value].to,
      tot: props.ffd[win.value].tot,
    };
  }
  return mock;
});

const metaText = computed(() => `Net asing · ${win.value} sesi`);

const rangeText = computed(() => {
  const D = currentData.value;
  return `<b>${fmtD(D.from)} – ${fmtD(D.to)}</b><br>${D.n} emiten likuid (≥Rp1 M/hari)`;
});

const aggText = computed(() => {
  const D = currentData.value;
  const c = D.tot >= 0 ? 'var(--green)' : 'var(--red)';
  return `<span>Net asing agregat</span>
          <span class="big" style="color:${c}">${money(D.tot)}</span>
          <span style="margin-left:auto">${D.npos} emiten net beli · ${D.nneg} net jual</span>`;
});

const noteText = computed(() => {
  const D = currentData.value;
  let txt = '';
  if (D.dis.length) {
    const t = D.dis[0];
    txt += `<b>${t.c}</b> naik ${t.chg.toFixed(1)}% tapi asing keluar ${money(t.net).replace('−', '')} — kenaikan tidak dikonfirmasi arus asing.`;
  }
  if (D.acc.length) {
    const a = D.acc[0];
    txt += (txt ? ' Sebaliknya, ' : '') + `<b>${a.c}</b> ` +
           (a.chg >= 0 ? `nyaris datar (${a.chg.toFixed(1)}%)` : `turun ${Math.abs(a.chg).toFixed(1)}%`) +
           ` namun diserap asing ${money(a.net)}.`;
  }
  txt += ' <span style="color:var(--faint)">Basis: kolom Foreign Buy/Sell IDX (pasar reguler), tidak mencakup transaksi negosiasi.</span>';
  return txt;
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

.smlens{display:flex;flex-direction:column;gap:13px}
.smlblock .h{font-size:9.5px;font-weight:700;letter-spacing:.03em;text-transform:uppercase;margin-bottom:7px;display:flex;align-items:center;gap:7px}
.smlrow{display:flex;align-items:center;gap:9px;padding:7px 0;border-bottom:1px solid var(--line);font-size:11.5px}
.smlrow:last-child{border-bottom:none}
.smlrow .tk{font-weight:700;color:var(--ink);width:46px}
.smlrow .px{width:56px;text-align:right;font-variant-numeric:tabular-nums;font-size:11px}
.smlrow .fl{flex:1;text-align:right;font-size:10.5px;font-weight:600}
.smlrow .nt{font-size:9px;color:var(--muted);width:84px;text-align:right}
.p6bar{display:flex;align-items:center;gap:8px;margin-bottom:11px}
.p6seg{display:flex;background:var(--inset);border:1px solid var(--line2);border-radius:7px;padding:2px;gap:2px}
.p6seg button{background:transparent;border:0;color:var(--muted);font-size:10px;font-weight:600;padding:5px 11px;border-radius:5px;cursor:pointer;transition:background .13s,color .13s;font-family:inherit}
.p6seg button:hover{color:var(--ink2)}
.p6seg button.on{background:#242424;color:var(--ink)}
.p6range{font-size:9px;color:var(--faint);margin-left:auto;text-align:right;line-height:1.5}
.p6range b{color:var(--ink2);font-weight:600}
.p6agg{display:flex;align-items:baseline;gap:6px;font-size:9.5px;color:var(--muted);padding:7px 10px;background:#121212;border:1px solid var(--line);border-radius:6px;margin-bottom:11px}
.p6agg .big{font-size:13px;font-weight:700;font-variant-numeric:tabular-nums}
.smlrow .sc{font-size:8.5px;color:var(--faint);width:76px;text-align:right}
.smlrow .in{width:52px;text-align:right;font-size:9.5px;color:var(--muted);font-variant-numeric:tabular-nums}
.mvr .ff{font-size:10px;font-weight:700;width:14px;text-align:center;flex-shrink:0}
.ff-ok{color:var(--green)}.ff-warn{color:var(--amber)}
.pos{color:var(--green)}.neg{color:var(--red)}.amb{color:var(--amber)}
</style>
