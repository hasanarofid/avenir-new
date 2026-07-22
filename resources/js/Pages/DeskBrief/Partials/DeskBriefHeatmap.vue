<template>
  <div class="card span12 stmap-wrap">
    <div class="stmap-head">
      <div class="chd" style="margin:0">
        <div class="t">
          <b>11.</b>PETA BURSA — STOCK HEATMAP 
          <span style="color:var(--faint);font-weight:400;text-transform:none;letter-spacing:0">
            (1D · ukuran = market cap · warna = return · klik sektor untuk zoom)
          </span>
        </div>
      </div>
      <div class="seg" id="stmapSeg">
        <button :class="{ on: sortMode === 'mcap' }" @click="sortMode = 'mcap'; draw()">Market Cap</button>
        <button :class="{ on: sortMode === 'ret' }" @click="sortMode = 'ret'; draw()">Return</button>
      </div>
    </div>

    <div id="stmap" ref="stmapContainer">
      <!-- FAB Back Button to Reset Zoom -->
      <div class="stmap-fab" :class="{ show: currentZoom }" @click="resetZoom">
        <span class="ar">←</span>
        <span>SEMUA SEKTOR</span>
        <span class="sub" id="stmapFabSub" v-if="currentZoom">({{ currentZoom }})</span>
      </div>

      <!-- D3 SVG Container -->
      <svg class="stmap" ref="stmapSvg"></svg>
    </div>

    <div class="scale" style="margin-top:10px">
      <span>−5%</span>
      <span id="stmapScale" style="display:flex;height:8px;width:180px;border-radius:3px;overflow:hidden"></span>
      <span>+5%</span>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, nextTick } from 'vue';
import * as d3 from 'd3';

const props = defineProps({
  sectors: { type: Array, default: () => [] },
  sectorStocks: { type: Object, default: () => ({}) }
});

const stmapContainer = ref(null);
const stmapSvg = ref(null);
const sortMode = ref('mcap');
const currentZoom = ref(null);

// Sector Name Normalization Map
const sectorNameMap = {
  'Banking': 'Financials',
  'Telecom': 'Infrastructures',
  'Consumer Staples': 'Consumer Non-Cyclicals',
  'Retail': 'Consumer Cyclicals',
  'Mining': 'Energy',
  'Industrial': 'Industrials',
  'Transportation': 'Transportation & Logistic',
  'Property': 'Properties & Real Estate',
  'Healthcare': 'Healthcare',
  'Basic Materials': 'Basic Materials',
  'Technology': 'Technology',
};

// Fallback stock dataset per sector to guarantee rich treemaps for all 11 IDX sectors
const fallbackStockMap = {
  'Financials': [
    { code: 'BBCA', name: 'Bank Central Asia Tbk', change: 1.25, marketCap: 1000 },
    { code: 'BBRI', name: 'Bank Rakyat Indonesia Tbk', change: 2.04, marketCap: 850 },
    { code: 'BMRI', name: 'Bank Mandiri Tbk', change: 1.79, marketCap: 780 },
    { code: 'BBNI', name: 'Bank Negara Indonesia Tbk', change: 0.92, marketCap: 450 },
    { code: 'BRIS', name: 'Bank Syariah Indonesia Tbk', change: 2.15, marketCap: 220 },
    { code: 'BDMN', name: 'Bank Danamon Indonesia Tbk', change: 1.20, marketCap: 150 },
    { code: 'BBTN', name: 'Bank Tabungan Negara Tbk', change: 0.80, marketCap: 140 },
    { code: 'PNBN', name: 'Bank Pan Indonesia Tbk', change: 1.10, marketCap: 130 },
    { code: 'ARTO', name: 'Bank Jago Tbk', change: -1.50, marketCap: 160 },
    { code: 'MEGA', name: 'Bank Mega Tbk', change: 0.50, marketCap: 170 },
    { code: 'CASA', name: 'Capital Financial Indonesia Tbk', change: -0.80, marketCap: 110 },
    { code: 'BTPS', name: 'Bank BTPN Syariah Tbk', change: -1.00, marketCap: 95 },
    { code: 'BFIN', name: 'BFI Finance Indonesia Tbk', change: -1.70, marketCap: 90 },
    { code: 'NISP', name: 'Bank OCBC NISP Tbk', change: 1.40, marketCap: 85 },
    { code: 'SRTG', name: 'Saratoga Investama Sedaya Tbk', change: 1.80, marketCap: 120 },
    { code: 'AGRO', name: 'Bank Raya Indonesia Tbk', change: -0.90, marketCap: 75 },
    { code: 'AMAR', name: 'Bank Amar Indonesia Tbk', change: 0.60, marketCap: 60 },
  ],
  'Energy': [
    { code: 'ADRO', name: 'Adaro Energy Indonesia Tbk', change: 3.48, marketCap: 520 },
    { code: 'PTBA', name: 'Bukit Asam Tbk', change: 2.10, marketCap: 280 },
    { code: 'ITMG', name: 'Indo Tambangraya Megah Tbk', change: 1.85, marketCap: 240 },
    { code: 'PGAS', name: 'Perusahaan Gas Negara Tbk', change: 1.45, marketCap: 290 },
    { code: 'MEDC', name: 'Medco Energi Internasional Tbk', change: 4.10, marketCap: 260 },
    { code: 'AKRA', name: 'AKR Corporindo Tbk', change: 0.95, marketCap: 230 },
    { code: 'INDI', name: 'Indika Energy Tbk', change: 1.70, marketCap: 140 },
    { code: 'HRUM', name: 'Harum Energy Tbk', change: 2.80, marketCap: 150 },
    { code: 'BUMI', name: 'Bumi Resources Tbk', change: 5.20, marketCap: 180 },
    { code: 'BRMS', name: 'Bumi Resources Minerals Tbk', change: 3.90, marketCap: 170 },
    { code: 'CUAN', name: 'Petrindo Jaya Kreasi Tbk', change: 6.40, marketCap: 310 },
  ],
  'Consumer Non-Cyclicals': [
    { code: 'UNVR', name: 'Unilever Indonesia Tbk', change: 0.76, marketCap: 450 },
    { code: 'ICBP', name: 'Indofood CBP Sukses Makmur Tbk', change: 1.15, marketCap: 420 },
    { code: 'INDF', name: 'Indofood Sukses Makmur Tbk', change: 0.85, marketCap: 380 },
    { code: 'MYOR', name: 'Mayora Indah Tbk', change: 1.40, marketCap: 260 },
    { code: 'AMRT', name: 'Sumber Alfaria Trijaya Tbk', change: 2.10, marketCap: 390 },
    { code: 'CPIN', name: 'Charoen Pokphand Indonesia Tbk', change: 1.30, marketCap: 310 },
    { code: 'JPFA', name: 'Japfa Comfeed Indonesia Tbk', change: 2.40, marketCap: 190 },
    { code: 'GGRM', name: 'Gudang Garam Tbk', change: -0.60, marketCap: 200 },
    { code: 'HMSP', name: 'Hanjaya Mandala Sampoerna Tbk', change: -0.40, marketCap: 210 },
    { code: 'CMRY', name: 'Cisarua Mountain Dairy Tbk', change: 1.90, marketCap: 180 },
  ],
  'Consumer Cyclicals': [
    { code: 'ACES', name: 'Aspirasi Hidup Indonesia Tbk', change: 1.36, marketCap: 210 },
    { code: 'MAPI', name: 'Mitra Adiperkasa Tbk', change: 2.40, marketCap: 230 },
    { code: 'MAPA', name: 'MAP Aktif Adiperkasa Tbk', change: 1.90, marketCap: 180 },
    { code: 'ERAA', name: 'Erajaya Swasembada Tbk', change: 0.80, marketCap: 120 },
    { code: 'AUTO', name: 'Astra Otoparts Tbk', change: 1.50, marketCap: 130 },
    { code: 'DRMA', name: 'Dharma Polimetal Tbk', change: 2.10, marketCap: 110 },
    { code: 'FILM', name: 'MD Pictures Tbk', change: -2.30, marketCap: 160 },
    { code: 'SCMA', name: 'Surya Citra Media Tbk', change: -1.20, marketCap: 95 },
  ],
  'Basic Materials': [
    { code: 'TPIA', name: 'Chandra Asri Pacific Tbk', change: 2.76, marketCap: 720 },
    { code: 'BRPT', name: 'Barito Pacific Tbk', change: 3.10, marketCap: 380 },
    { code: 'INKP', name: 'Indah Kiat Pulp & Paper Tbk', change: 1.45, marketCap: 290 },
    { code: 'TKIM', name: 'Pabrik Kertas Tjiwi Kimia Tbk', change: 1.20, marketCap: 180 },
    { code: 'MDKA', name: 'Merdeka Copper Gold Tbk', change: 3.50, marketCap: 310 },
    { code: 'ANTM', name: 'Aneka Tambang Tbk', change: 2.80, marketCap: 270 },
    { code: 'INCO', name: 'Vale Indonesia Tbk', change: 1.90, marketCap: 240 },
    { code: 'MBMA', name: 'Merdeka Battery Materials Tbk', change: 4.20, marketCap: 220 },
    { code: 'NCKL', name: 'Trimegah Bangun Persada Tbk', change: 3.10, marketCap: 250 },
    { code: 'SMGR', name: 'Semen Indonesia Tbk', change: -1.20, marketCap: 190 },
    { code: 'INTP', name: 'Indocement Tunggal Prakarsa Tbk', change: -0.90, marketCap: 160 },
  ],
  'Technology': [
    { code: 'GOTO', name: 'GoTo Gojek Tokopedia Tbk', change: 2.25, marketCap: 450 },
    { code: 'BUKA', name: 'Bukalapak.com Tbk', change: 1.30, marketCap: 160 },
    { code: 'EMTK', name: 'Elang Mahkota Teknologi Tbk', change: 0.90, marketCap: 180 },
    { code: 'BELI', name: 'Global Digital Niaga Tbk (Blibli)', change: 0.50, marketCap: 210 },
    { code: 'MTDL', name: 'Metrodata Electronics Tbk', change: 1.80, marketCap: 110 },
    { code: 'DNET', name: 'Indoretail Makmur International Tbk', change: -3.50, marketCap: 190 },
    { code: 'WIFI', name: 'Solusi Sinergi Digital Tbk', change: 4.10, marketCap: 85 },
  ],
  'Infrastructures': [
    { code: 'TLKM', name: 'Telkom Indonesia Tbk', change: 1.79, marketCap: 680 },
    { code: 'ISAT', name: 'Indosat Tbk', change: 2.30, marketCap: 320 },
    { code: 'EXCL', name: 'XL Axiata Tbk', change: 1.60, marketCap: 210 },
    { code: 'TOWR', name: 'Sarana Menara Nusantara Tbk', change: 0.90, marketCap: 240 },
    { code: 'TBIG', name: 'Tower Bersama Infrastructure Tbk', change: 1.10, marketCap: 220 },
    { code: 'JSMR', name: 'Jasa Marga Tbk', change: 1.40, marketCap: 190 },
    { code: 'ADHI', name: 'Adhi Karya Tbk', change: 0.60, marketCap: 70 },
    { code: 'WIKA', name: 'Wijaya Karya Tbk', change: 0.50, marketCap: 65 },
  ],
  'Healthcare': [
    { code: 'KLBF', name: 'Kalbe Farma Tbk', change: -1.33, marketCap: 380 },
    { code: 'MIKA', name: 'Mitra Keluarga Karyasehat Tbk', change: -0.90, marketCap: 260 },
    { code: 'HEAL', name: 'Medikaloka Hermina Tbk', change: -1.10, marketCap: 220 },
    { code: 'SILO', name: 'Siloam International Hospitals Tbk', change: -0.50, marketCap: 190 },
    { code: 'SAME', name: 'Sarana Meditama Metropolitan Tbk', change: -1.80, marketCap: 85 },
    { code: 'PRDA', name: 'Prodia Widyahusada Tbk', change: 0.40, marketCap: 75 },
    { code: 'KAEF', name: 'Kimia Farma Tbk', change: -2.10, marketCap: 90 },
  ],
  'Properties & Real Estate': [
    { code: 'BSDE', name: 'Bumi Serpong Damai Tbk', change: 0.80, marketCap: 240 },
    { code: 'CTRA', name: 'Ciputra Development Tbk', change: 1.20, marketCap: 260 },
    { code: 'PWON', name: 'Pakuwon Jati Tbk', change: 0.90, marketCap: 210 },
    { code: 'SMRA', name: 'Summarecon Agung Tbk', change: 0.60, marketCap: 160 },
    { code: 'ASRI', name: 'Alam Sutera Realty Tbk', change: 0.40, marketCap: 95 },
    { code: 'DILD', name: 'Intiland Development Tbk', change: -0.80, marketCap: 70 },
  ],
  'Industrials': [
    { code: 'ASII', name: 'Astra International Tbk', change: 1.79, marketCap: 620 },
    { code: 'UNTR', name: 'United Tractors Tbk', change: 2.10, marketCap: 410 },
    { code: 'HEXA', name: 'Hexindo Adiperkasa Tbk', change: 1.50, marketCap: 95 },
    { code: 'IMAS', name: 'Indomobil Sukses Internasional Tbk', change: 0.80, marketCap: 110 },
  ],
  'Transportation & Logistic': [
    { code: 'BIRD', name: 'Blue Bird Tbk', change: 1.50, marketCap: 120 },
    { code: 'GIAA', name: 'Garuda Indonesia Tbk', change: -1.20, marketCap: 85 },
    { code: 'SMDR', name: 'Samudera Indonesia Tbk', change: 2.40, marketCap: 110 },
    { code: 'TMAS', name: 'Temas Tbk', change: 1.90, marketCap: 95 },
    { code: 'ASSA', name: 'Adi Sarana Armada Tbk', change: 0.80, marketCap: 90 },
  ]
};

function getColor(c) {
  if (c > 0.05) return d3.scaleLinear().domain([0, 1, 3, 5]).range(["#183021", "#2E9E55", "#3FB36B", "#46C46E"])(c);
  if (c < -0.05) return d3.scaleLinear().domain([-5, -3, -1, 0]).range(["#E2705C", "#B8503F", "#6E332A", "#241a1d"])(c);
  return '#222222';
}

function getSectorData() {
  const mapSectors = (props.sectors && props.sectors.length) ? props.sectors : [
    { name: 'Financials', change: 0.57 },
    { name: 'Energy', change: 3.48 },
    { name: 'Basic Materials', change: 2.76 },
    { name: 'Technology', change: 2.25 },
    { name: 'Industrials', change: 1.79 },
    { name: 'Consumer Cyclicals', change: 1.36 },
    { name: 'Infrastructures', change: 1.79 },
    { name: 'Properties & Real Estate', change: 0.80 },
    { name: 'Consumer Non-Cyclicals', change: 0.76 },
    { name: 'Transportation & Logistic', change: 1.50 },
    { name: 'Healthcare', change: -1.33 },
  ];

  return mapSectors.map(s => {
    const stdName = sectorNameMap[s.name] || s.name;
    return {
      name: stdName,
      rawName: s.name,
      wret: Number(s.change || 0),
      value: Math.abs(Number(s.change || 0)) * 100 + 30
    };
  });
}

function getStocksForSector(secName) {
  const stdName = sectorNameMap[secName] || secName;
  const propList = props.sectorStocks ? (props.sectorStocks[secName] || props.sectorStocks[stdName]) : null;
  if (propList && propList.length) {
    return propList.map(s => ({
      code: s.code,
      name: s.name || s.code,
      wret: Number(s.change || 0),
      value: Number(s.marketCap || 50)
    }));
  }
  
  const fallback = fallbackStockMap[stdName] || fallbackStockMap['Financials'];
  return fallback.map(s => ({
    code: s.code,
    name: s.name,
    wret: s.change,
    value: s.marketCap
  }));
}

function draw() {
  if (!stmapSvg.value || !stmapContainer.value) return;
  const container = stmapContainer.value;
  const W = container.clientWidth;
  const H = 560;
  const svg = d3.select(stmapSvg.value);
  svg.selectAll('*').remove();

  if (W === 0) return;

  let root;

  // OVERVIEW MODE: Render 11 Sectors
  if (!currentZoom.value) {
    const sectorData = getSectorData();
    root = d3.hierarchy({ name: "IHSG", children: sectorData });

    if (sortMode.value === 'ret') {
      root.sum(d => d.wret ? Math.max(0.1, Math.abs(d.wret)) : 0);
    } else {
      root.sum(d => d.value || 1);
    }

    d3.treemap()
      .tile(d3.treemapBinary)
      .size([W, H])
      .paddingInner(10)
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
      .attr("rx", 6)
      .attr("ry", 6)
      .attr("stroke", "var(--line2)")
      .attr("stroke-width", 1)
      .style("cursor", "pointer")
      .on("click", (e, d) => {
        currentZoom.value = d.data.name;
        draw();
      });

    // Sector Name Label
    nodes.append("text")
      .attr("class", "sec-name")
      .attr("x", 12)
      .attr("y", 26)
      .attr("fill", "#ffffff")
      .style("font-size", d => (d.x1 - d.x0) > 100 ? "15px" : "12px")
      .style("font-weight", "700")
      .style("pointer-events", "none")
      .text(d => d.data.name);

    // Sector Change Return %
    nodes.append("text")
      .attr("class", "sec-ret")
      .attr("x", 12)
      .attr("y", 46)
      .attr("fill", "#ffffff")
      .style("font-size", d => (d.x1 - d.x0) > 100 ? "13px" : "11px")
      .style("font-weight", "700")
      .style("pointer-events", "none")
      .text(d => (d.data.wret > 0 ? '+' : '') + d.data.wret.toFixed(2) + '%');
  } 
  // ZOOMED MODE: Render Stocks in Selected Sector
  else {
    const stocks = getStocksForSector(currentZoom.value);
    root = d3.hierarchy({ name: currentZoom.value, children: stocks });

    if (sortMode.value === 'ret') {
      root.sum(d => d.wret ? Math.max(0.1, Math.abs(d.wret)) : 0);
    } else {
      root.sum(d => d.value || 1);
    }

    d3.treemap()
      .tile(d3.treemapBinary)
      .size([W, H])
      .paddingInner(5)
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
      .attr("stroke", "rgba(0,0,0,0.3)")
      .attr("stroke-width", 1);

    // Stock Code Label
    nodes.append("text")
      .attr("class", "stk-code")
      .attr("x", d => Math.max(6, (d.x1 - d.x0) / 2))
      .attr("y", d => Math.max(16, (d.y1 - d.y0) / 2 - 4))
      .attr("text-anchor", "middle")
      .attr("fill", "#ffffff")
      .style("font-size", d => Math.min(15, Math.max(10, (d.x1 - d.x0) / 4)) + "px")
      .style("font-weight", "800")
      .style("pointer-events", "none")
      .text(d => (d.x1 - d.x0 > 30 && d.y1 - d.y0 > 20) ? d.data.code : '');

    // Stock Return % Label
    nodes.append("text")
      .attr("class", "stk-ret")
      .attr("x", d => Math.max(6, (d.x1 - d.x0) / 2))
      .attr("y", d => Math.max(30, (d.y1 - d.y0) / 2 + 12))
      .attr("text-anchor", "middle")
      .attr("fill", "#ffffff")
      .style("font-size", d => Math.min(12, Math.max(9, (d.x1 - d.x0) / 5)) + "px")
      .style("font-weight", "600")
      .style("pointer-events", "none")
      .text(d => (d.x1 - d.x0 > 40 && d.y1 - d.y0 > 35) ? ((d.data.wret > 0 ? '+' : '') + d.data.wret.toFixed(1) + '%') : '');
  }
}

function resetZoom() {
  currentZoom.value = null;
  draw();
}

onMounted(() => {
  nextTick(() => {
    draw();
    // Render color scale bar
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

watch(() => [props.sectors, props.sectorStocks], draw, { deep: true });
</script>

<style scoped>
.chd{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px}
.chd .t{font-size:11px;letter-spacing:.1em;text-transform:uppercase;color:var(--ink2);font-weight:700}
.chd .t b{color:#888888;font-weight:700;margin-right:5px}
.stmap-wrap{position:relative}
.stmap-head{display:flex;align-items:center;gap:14px;margin-bottom:12px;flex-wrap:wrap;}
.stmap-head .seg{display:flex;background:#161616;border:1px solid var(--line2);border-radius:7px;overflow:hidden;margin-left:auto}
.stmap-head .seg button{background:none;border:none;color:var(--muted);padding:6px 12px;font-size:11px;font-weight:600;cursor:pointer;font-family:var(--sans)}
.stmap-head .seg button+button{border-left:1px solid var(--line2)}
.stmap-head .seg button.on{background:#1E1E1E;color:var(--green);box-shadow:inset 0 -2px 0 var(--green)}
#stmap{width:100%;height:560px;position:relative;border-radius:8px;overflow:hidden;background:var(--inset)}
.stmap .sec-name{font-family:var(--sans);font-weight:700;fill:#fff;}
.stmap .sec-ret{font-family:var(--mono);font-weight:700;fill:#fff;}
.stmap .stk-code{font-family:var(--sans);font-weight:800;fill:#fff;}
.stmap .stk-ret{font-family:var(--mono);font-weight:600;fill:#fff;}
#stmap svg{width:100%;height:100%;display:block}
.stmap-fab{position:absolute;top:12px;left:12px;z-index:30;display:none;width:max-content;align-items:center;gap:8px;background:rgba(20,20,20,.96);border:1px solid var(--green2);border-radius:20px;padding:8px 15px 8px 12px;cursor:pointer;font-size:12.5px;font-weight:600;color:var(--ink);box-shadow:0 6px 18px rgba(0,0,0,.5);transition:all .15s ease}
.stmap-fab:hover{background:rgba(30,30,30,.98);border-color:var(--green)}
.stmap-fab.show{display:flex}
.stmap-fab .ar{color:var(--green);font-size:15px;font-weight:bold}
.stmap-fab .sub{font-size:10.5px;color:var(--muted);font-weight:400;font-family:var(--mono)}
.scale{display:flex;justify-content:space-between;align-items:center;font-size:9px;color:var(--muted);margin-top:11px}

@media (max-width: 600px) {
  .stmap-head {
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
  }
  .stmap-head .seg {
    margin-left: 0;
    width: 100%;
  }
  .stmap-head .seg button {
    flex: 1;
  }
  #stmap {
    height: 400px;
  }
}
</style>
