<template>
  <div class="card span12 stmap-wrap">
    <div class="stmap-head">
      <div class="chd" style="margin:0"><div class="t"><b>11.</b>PETA BURSA — STOCK HEATMAP <span style="color:var(--faint);font-weight:400;text-transform:none;letter-spacing:0">(1D · ukuran = market cap · warna = return)</span></div></div>
      <div class="seg" id="stmapSeg">
        <button :class="{ on: sortMode === 'mcap' }" @click="sortMode = 'mcap'; draw()">Market Cap</button>
        <button :class="{ on: sortMode === 'ret' }" @click="sortMode = 'ret'; draw()">Return</button>
      </div>
    </div>
    <div id="stmap" ref="stmapContainer">
      <div class="stmap-fab" :class="{ show: currentZoom }" @click="resetZoom"><span class="ar">←</span><span>Semua sektor</span><span class="sub" id="stmapFabSub">{{ currentZoom }}</span></div>
      <svg class="stmap" ref="stmapSvg"></svg>
    </div>
    <div class="scale" style="margin-top:10px"><span>−5%</span><span id="stmapScale" style="display:flex;height:8px;width:180px;border-radius:3px;overflow:hidden"></span><span>+5%</span></div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, nextTick } from 'vue';
import * as d3 from 'd3';

const props = defineProps({
  sectors: { type: Array, default: () => [] }
});

const stmapContainer = ref(null);
const stmapSvg = ref(null);
const sortMode = ref('mcap');
const currentZoom = ref(null);

function getColor(c) {
  if (c > 0.05) return d3.scaleLinear().domain([0, 1, 3, 5]).range(["#183021", "#2E9E55", "#3FB36B", "#46C46E"])(c);
  if (c < -0.05) return d3.scaleLinear().domain([-5, -3, -1, 0]).range(["#E2705C", "#B8503F", "#6E332A", "#241a1d"])(c);
  return '#222222';
}

function draw() {
  if (!stmapSvg.value || !stmapContainer.value) return;
  const container = stmapContainer.value;
  const W = container.clientWidth;
  const H = 560;
  const svg = d3.select(stmapSvg.value);
  svg.selectAll('*').remove();
  
  if (W === 0) return; // Prevent drawing if not visible/rendered

  // Transform props.sectors into hierarchy format
  const children = props.sectors.map(s => ({
    name: s.name,
    wret: s.change,
    value: Math.abs(s.change) * 100 + 10 // mock value since we don't have mcap in basic props
  }));

  const root = d3.hierarchy({ name: "IHSG", children: children });
  
  if (sortMode.value === 'ret') {
    root.sum(d => d.wret ? Math.max(0.1, Math.abs(d.wret)) : 0);
  } else {
    root.sum(d => d.value || 1);
  }

  d3.treemap()
    .tile(d3.treemapBinary)
    .size([W, H])
    .paddingInner(2)
    .round(true)(root);

  const nodes = svg.selectAll("g")
    .data(root.leaves())
    .enter()
    .append("g")
    .attr("transform", d => `translate(${d.x0},${d.y0})`);

  nodes.append("rect")
    .attr("width", d => Math.max(0, d.x1 - d.x0))
    .attr("height", d => Math.max(0, d.y1 - d.y0))
    .attr("fill", d => getColor(d.data.wret))
    .attr("rx", 4)
    .attr("ry", 4)
    .style("cursor", "pointer")
    .on("click", (e, d) => {
      currentZoom.value = d.data.name;
    });

  nodes.append("text")
    .attr("class", "sec-name")
    .attr("x", 8)
    .attr("y", 20)
    .attr("fill", "#ffffff")
    .text(d => d.data.name);

  nodes.append("text")
    .attr("class", "sec-ret")
    .attr("x", 8)
    .attr("y", 36)
    .attr("fill", "#ffffff")
    .text(d => (d.data.wret > 0 ? '+' : '') + (d.data.wret || 0) + '%');
}

function resetZoom() {
  currentZoom.value = null;
  draw();
}

onMounted(() => {
  nextTick(() => {
      draw();
      // Draw scale
      const scale = d3.select("#stmapScale");
      scale.selectAll('*').remove();
      for(let i = -5; i <= 5; i += 0.5) {
        scale.append("span")
          .style("flex", "1")
          .style("background", getColor(i));
      }
  });
  window.addEventListener('resize', draw);
});

watch(() => props.sectors, draw, { deep: true });
</script>

<style scoped>
.chd{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px}
.chd .t{font-size:11px;letter-spacing:.1em;text-transform:uppercase;color:var(--ink2);font-weight:700}
.chd .t b{color:#888888;font-weight:700;margin-right:5px}
.stmap-wrap{position:relative}
.stmap-head{display:flex;align-items:center;gap:14px;margin-bottom:12px}
.stmap-head .seg{display:flex;background:#161616;border:1px solid var(--line2);border-radius:7px;overflow:hidden;margin-left:auto}
.stmap-head .seg button{background:none;border:none;color:var(--muted);padding:6px 12px;font-size:11px;font-weight:600;cursor:pointer;font-family:var(--sans)}
.stmap-head .seg button+button{border-left:1px solid var(--line2)}
.stmap-head .seg button.on{background:#1E1E1E;color:var(--green);box-shadow:inset 0 -2px 0 var(--green)}
#stmap{width:100%;height:560px;position:relative;border-radius:8px;overflow:hidden;background:var(--inset)}
.stmap .sec-name{font-family:var(--sans);font-weight:700;fill:#fff;font-size:14px;}
.stmap .sec-ret{font-family:var(--mono);font-weight:700;fill:#fff;font-size:12px;}
#stmap svg{width:100%;height:100%;display:block}
.stmap-fab{position:absolute;top:12px;left:12px;z-index:30;display:none;width:max-content;align-items:center;gap:8px;background:rgba(20,20,20,.96);border:1px solid var(--green2);border-radius:20px;padding:8px 15px 8px 12px;cursor:pointer;font-size:12.5px;font-weight:600;color:var(--ink);box-shadow:0 6px 18px rgba(0,0,0,.5)}
.stmap-fab.show{display:flex}
.stmap-fab .ar{color:var(--green);font-size:15px}
.stmap-fab .sub{font-size:10.5px;color:var(--muted);font-weight:400;font-family:var(--mono)}
.scale{display:flex;justify-content:space-between;align-items:center;font-size:9px;color:var(--muted);margin-top:11px}
</style>
