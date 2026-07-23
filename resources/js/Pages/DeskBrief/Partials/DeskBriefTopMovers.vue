<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  topMovers: {
    type: Object,
    default: () => ({
      gainers: [],
      losers: [],
      values: [],
      volumes: [],
      frequencies: [],
      net_buy: [],
      net_sell: []
    })
  }
});

const activeTab = ref('gainer'); // 'gainer' | 'loser' | 'value' | 'volume' | 'net_buy' | 'net_sell'
const isExpanded = ref(false);
const failedLogos = ref({});

const tabs = [
  { id: 'gainer', name: 'Top Gainer' },
  { id: 'loser', name: 'Top Loser' },
  { id: 'value', name: 'Top Value' },
  { id: 'volume', name: 'Top Volume' },
  { id: 'net_buy', name: 'Top Net Buy Foreign' },
  { id: 'net_sell', name: 'Top Net Sell Foreign' }
];

const getStockLogo = (symbol) => `https://assets.stockbit.com/logos/companies/${symbol}.png`;

// Fallback Mock Data matching master stocks with logo_url
const fallbackData = {
  gainer: [
    { symbol: 'ZATA', name: 'Bersama Zatta Jaya Tbk.', last_close: 67, change_abs: 17, price_pct: 34.00, badge: '!', badge_type: 'warn', logo_url: getStockLogo('ZATA') },
    { symbol: 'SWID', name: 'Saraswanti Indoland Development Tbk.', last_close: 118, change_abs: 26, price_pct: 28.26, badge: 'C', badge_type: 'purp', logo_url: getStockLogo('SWID') },
    { symbol: 'MDIA', name: 'Intermedia Capital Tbk.', last_close: 104, change_abs: 22, price_pct: 26.83, badge: '!', badge_type: 'warn', logo_url: getStockLogo('MDIA') },
    { symbol: 'ALDO', name: 'Alkindo Naratama Tbk.', last_close: 900, change_abs: 180, price_pct: 25.00, badge: 'C', badge_type: 'purp', logo_url: getStockLogo('ALDO') },
    { symbol: 'MLPT', name: 'Multipolar Technology Tbk.', last_close: 1625, change_abs: 325, price_pct: 25.00, badge: 'C', badge_type: 'purp', logo_url: getStockLogo('MLPT') },
    { symbol: 'HUMI', name: 'Humpuss Maritim Internasional Tbk.', last_close: 78, change_abs: 15, price_pct: 23.81, badge: '⚑', badge_type: 'ok', logo_url: getStockLogo('HUMI') },
    { symbol: 'CUAN', name: 'Petrindo Jaya Kreasi Tbk.', last_close: 7850, change_abs: 1425, price_pct: 22.18, badge: 'C', badge_type: 'purp', logo_url: getStockLogo('CUAN') },
    { symbol: 'BREN', name: 'Barito Renewables Energy Tbk.', last_close: 8900, change_abs: 1500, price_pct: 20.27, badge: '⚑', badge_type: 'ok', logo_url: getStockLogo('BREN') },
    { symbol: 'TPIA', name: 'Chandra Asri Pacific Tbk.', last_close: 9125, change_abs: 1475, price_pct: 19.28, badge: 'C', badge_type: 'purp', logo_url: getStockLogo('TPIA') },
    { symbol: 'PANI', name: 'Pantai Indah Kapuk Dua Tbk.', last_close: 12450, change_abs: 1950, price_pct: 18.57, badge: '⚑', badge_type: 'ok', logo_url: getStockLogo('PANI') },
  ],
  loser: [
    { symbol: 'GOTO', name: 'GoTo Gojek Tokopedia Tbk.', last_close: 52, change_abs: -6, price_pct: -10.34, badge: '!', badge_type: 'danger', logo_url: getStockLogo('GOTO') },
    { symbol: 'BUKA', name: 'PT Bukalapak.com Tbk.', last_close: 120, change_abs: -12, price_pct: -9.09, badge: '⚠', badge_type: 'danger', logo_url: getStockLogo('BUKA') },
    { symbol: 'ARTO', name: 'Bank Jago Tbk.', last_close: 2450, change_abs: -210, price_pct: -7.89, badge: 'C', badge_type: 'purp', logo_url: getStockLogo('ARTO') },
    { symbol: 'ADRO', name: 'Adaro Energy Indonesia Tbk.', last_close: 2380, change_abs: -170, price_pct: -6.67, badge: '!', badge_type: 'danger', logo_url: getStockLogo('ADRO') },
    { symbol: 'MEDC', name: 'Medco Energi Internasional Tbk.', last_close: 1120, change_abs: -65, price_pct: -5.49, badge: 'C', badge_type: 'purp', logo_url: getStockLogo('MEDC') },
    { symbol: 'INDF', name: 'Indofood Sukses Makmur Tbk.', last_close: 6450, change_abs: -325, price_pct: -4.80, badge: '⚠', badge_type: 'danger', logo_url: getStockLogo('INDF') },
    { symbol: 'PTBA', name: 'Bukit Asam Tbk.', last_close: 2410, change_abs: -110, price_pct: -4.37, badge: '!', badge_type: 'danger', logo_url: getStockLogo('PTBA') },
    { symbol: 'PGAS', name: 'Perusahaan Gas Negara Tbk.', last_close: 1540, change_abs: -60, price_pct: -3.75, badge: 'C', badge_type: 'purp', logo_url: getStockLogo('PGAS') },
    { symbol: 'INKP', name: 'Indah Kiat Pulp & Paper Tbk.', last_close: 7825, change_abs: -275, price_pct: -3.39, badge: '⚠', badge_type: 'danger', logo_url: getStockLogo('INKP') },
    { symbol: 'MDKA', name: 'Merdeka Copper Gold Tbk.', last_close: 2280, change_abs: -70, price_pct: -2.98, badge: '!', badge_type: 'danger', logo_url: getStockLogo('MDKA') },
  ],
  value: [
    { symbol: 'TPIA', name: 'Chandra Asri Pacific Tbk.', last_close: 2280, value_str: '2,28 T', value: 2280000000000, change_abs: 30, price_pct: 1.33, badge: 'C', badge_type: 'purp', logo_url: getStockLogo('TPIA') },
    { symbol: 'DSSA', name: 'Dian Swastatika Sentosa Tbk.', last_close: 905, value_str: '1,45 T', value: 1450000000000, change_abs: -54, price_pct: -3.72, badge: 'C', badge_type: 'purp', logo_url: getStockLogo('DSSA') },
    { symbol: 'BBCA', name: 'Bank Central Asia Tbk.', last_close: 6500, value_str: '1,25 T', value: 1250000000000, change_abs: -25, price_pct: -0.38, badge: 'C', badge_type: 'purp', logo_url: getStockLogo('BBCA') },
    { symbol: 'BRPT', name: 'Barito Pacific Tbk.', last_close: 1830, value_str: '980,4 M', value: 980400000000, change_abs: -29, price_pct: -1.61, badge: 'C', badge_type: 'purp', logo_url: getStockLogo('BRPT') },
    { symbol: 'BBRI', name: 'Bank Rakyat Indonesia Tbk.', last_close: 3020, value_str: '850,2 M', value: 850200000000, change_abs: -49, price_pct: -1.63, badge: 'C', badge_type: 'purp', logo_url: getStockLogo('BBRI') },
    { symbol: 'BMRI', name: 'Bank Mandiri Tbk.', last_close: 6400, value_str: '842,1 M', value: 842100000000, change_abs: 100, price_pct: 1.59, badge: 'C', badge_type: 'purp', logo_url: getStockLogo('BMRI') },
    { symbol: 'TLKM', name: 'Telkom Indonesia Tbk.', last_close: 2850, value_str: '625,8 M', value: 625800000000, change_abs: 50, price_pct: 1.79, badge: 'C', badge_type: 'purp', logo_url: getStockLogo('TLKM') },
  ],
  volume: [
    { symbol: 'BRMS', name: 'Bumi Resources Minerals Tbk.', last_close: 340, volume_str: '4,8M lot', volume: 4800000, change_abs: 18, price_pct: 5.59, badge: 'C', badge_type: 'purp', logo_url: getStockLogo('BRMS') },
    { symbol: 'DEWA', name: 'Dewa Mining Tbk.', last_close: 185, volume_str: '3,9M lot', volume: 3900000, change_abs: 12, price_pct: 6.94, badge: '!', badge_type: 'warn', logo_url: getStockLogo('DEWA') },
    { symbol: 'BUMI', name: 'Bumi Resources Tbk.', last_close: 142, volume_str: '3,2M lot', volume: 3200000, change_abs: 4, price_pct: 2.90, badge: 'C', badge_type: 'purp', logo_url: getStockLogo('BUMI') },
    { symbol: 'ENRG', name: 'Energi Mega Persada Tbk.', last_close: 260, volume_str: '2,5M lot', volume: 2500000, change_abs: 14, price_pct: 5.69, badge: 'C', badge_type: 'purp', logo_url: getStockLogo('ENRG') },
    { symbol: 'PSAB', name: 'J Resources Asia Pasifik Tbk.', last_close: 298, volume_str: '2,1M lot', volume: 2100000, change_abs: 16, price_pct: 5.67, badge: '!', badge_type: 'warn', logo_url: getStockLogo('PSAB') },
  ],
  net_buy: [
    { symbol: 'BBCA', name: 'Bank Central Asia Tbk.', last_close: 9850, net_flow: '+450,2 M', value: 450200000000, change_abs: 125, price_pct: 1.29, badge: '⚑', badge_type: 'ok', logo_url: getStockLogo('BBCA') },
    { symbol: 'BMRI', name: 'Bank Mandiri Tbk.', last_close: 6400, net_flow: '+310,8 M', value: 310800000000, change_abs: 100, price_pct: 1.59, badge: '⚑', badge_type: 'ok', logo_url: getStockLogo('BMRI') },
    { symbol: 'TLKM', name: 'Telkom Indonesia Tbk.', last_close: 2850, net_flow: '+185,5 M', value: 185500000000, change_abs: 50, price_pct: 1.79, badge: '⚑', badge_type: 'ok', logo_url: getStockLogo('TLKM') },
    { symbol: 'AMMN', name: 'Amman Mineral Internasional Tbk.', last_close: 8900, net_flow: '+142,1 M', value: 142100000000, change_abs: 250, price_pct: 2.89, badge: '⚑', badge_type: 'ok', logo_url: getStockLogo('AMMN') },
    { symbol: 'BBNI', name: 'Bank Negara Indonesia Tbk.', last_close: 4950, net_flow: '+98,4 M', value: 98400000000, change_abs: 60, price_pct: 1.23, badge: '⚑', badge_type: 'ok', logo_url: getStockLogo('BBNI') },
  ],
  net_sell: [
    { symbol: 'ADRO', name: 'Adaro Energy Indonesia Tbk.', last_close: 2380, net_flow: '-185,4 M', value: -185400000000, change_abs: -170, price_pct: -6.67, badge: '⚠', badge_type: 'danger', logo_url: getStockLogo('ADRO') },
    { symbol: 'BUKA', name: 'PT Bukalapak.com Tbk.', last_close: 120, net_flow: '-92,1 M', value: -92100000000, change_abs: -12, price_pct: -9.09, badge: '⚠', badge_type: 'danger', logo_url: getStockLogo('BUKA') },
    { symbol: 'GOTO', name: 'GoTo Gojek Tokopedia Tbk.', last_close: 52, net_flow: '-78,6 M', value: -78600000000, change_abs: -6, price_pct: -10.34, badge: '⚠', badge_type: 'danger', logo_url: getStockLogo('GOTO') },
    { symbol: 'ASII', name: 'Astra International Tbk.', last_close: 4750, net_flow: '-64,2 M', value: -64200000000, change_abs: -25, price_pct: -0.52, badge: '⚠', badge_type: 'danger', logo_url: getStockLogo('ASII') },
    { symbol: 'UNVR', name: 'Unilever Indonesia Tbk.', last_close: 2210, net_flow: '-45,8 M', value: -45800000000, change_abs: -30, price_pct: -1.34, badge: '⚠', badge_type: 'danger', logo_url: getStockLogo('UNVR') },
  ]
};

const currentItems = computed(() => {
  let list = [];
  const keyMap = {
    gainer: 'gainers',
    loser: 'losers',
    value: 'values',
    volume: 'volumes',
    net_buy: 'net_buy',
    net_sell: 'net_sell'
  };
  
  const propKey = keyMap[activeTab.value];
  if (props.topMovers && props.topMovers[propKey] && props.topMovers[propKey].length > 0) {
    list = props.topMovers[propKey];
  } else {
    list = fallbackData[activeTab.value] || [];
  }

  return isExpanded.value ? list : list.slice(0, 5);
});

function resolveLogoUrl(item) {
  if (item.logo_url) return item.logo_url;
  if (item.symbol) return getStockLogo(item.symbol);
  return null;
}

function formatPrice(val) {
  if (val === null || val === undefined) return '-';
  return Number(val).toLocaleString('id-ID');
}

function formatMoneyValue(val) {
  if (val === null || val === undefined || isNaN(val)) return null;
  const num = Number(val);
  const abs = Math.abs(num);
  const prefix = num < 0 ? '-' : (num > 0 ? '+' : '');

  if (abs >= 1_000_000_000_000) {
    return prefix + (abs / 1_000_000_000_000).toFixed(2).replace('.', ',') + ' T';
  } else if (abs >= 1_000_000_000) {
    return prefix + (abs / 1_000_000_000).toFixed(1).replace('.', ',') + ' M';
  } else if (abs >= 1_000_000) {
    return prefix + (abs / 1_000_000).toFixed(1).replace('.', ',') + ' Jt';
  }
  return prefix + abs.toLocaleString('id-ID');
}

function formatVolumeValue(val) {
  if (val === null || val === undefined || isNaN(val)) return null;
  const num = Number(val);
  const abs = Math.abs(num);
  if (abs >= 1_000_000) {
    return (abs / 1_000_000).toFixed(1).replace('.', ',') + 'M lot';
  } else if (abs >= 1_000) {
    return (abs / 1_000).toFixed(1).replace('.', ',') + 'rb lot';
  }
  return abs.toLocaleString('id-ID') + ' lot';
}

function formatDisplayValue(item) {
  if (!item) return '-';

  // For Gainer & Loser: Display Stock Last Close Price
  if (activeTab.value === 'gainer' || activeTab.value === 'loser') {
    return formatPrice(item.last_close);
  }

  // For Top Value
  if (activeTab.value === 'value') {
    if (item.value_str) return item.value_str;
    if (item.value !== undefined) return formatMoneyValue(item.value);
    if (item.val !== undefined) return formatMoneyValue(item.val);
    if (item.value_traded !== undefined) return formatMoneyValue(item.value_traded);
    return formatPrice(item.last_close);
  }

  // For Top Volume
  if (activeTab.value === 'volume') {
    if (item.volume_str) return item.volume_str;
    if (item.volume !== undefined) return formatVolumeValue(item.volume);
    if (item.vol !== undefined) return formatVolumeValue(item.vol);
    return formatPrice(item.last_close);
  }

  // For Top Net Buy Foreign
  if (activeTab.value === 'net_buy') {
    if (item.net_flow) return item.net_flow;
    if (item.net_buy !== undefined) return formatMoneyValue(item.net_buy);
    if (item.value !== undefined) return formatMoneyValue(item.value);
    return formatPrice(item.last_close);
  }

  // For Top Net Sell Foreign
  if (activeTab.value === 'net_sell') {
    if (item.net_flow) return item.net_flow;
    if (item.net_sell !== undefined) return formatMoneyValue(item.net_sell);
    if (item.value !== undefined) return formatMoneyValue(item.value);
    return formatPrice(item.last_close);
  }

  return formatPrice(item.last_close);
}

function formatDisplaySub(item) {
  if (!item) return '-';

  const pctVal = item.price_pct !== undefined ? Number(item.price_pct) : 0;
  const pctStr = item.price_pct !== undefined ? (pctVal > 0 ? `+${pctVal.toFixed(2)}%` : `${pctVal.toFixed(2)}%`) : '';

  // For Gainer & Loser: show change_abs + price_pct e.g. "+30(+1.33%)"
  if (activeTab.value === 'gainer' || activeTab.value === 'loser') {
    const abs = item.change_abs !== undefined 
      ? (item.change_abs > 0 ? `+${Number(item.change_abs).toLocaleString('id-ID')}` : `${Number(item.change_abs).toLocaleString('id-ID')}`)
      : (item.last_close && item.price_pct ? (pctVal > 0 ? `+${Math.round(item.last_close * (pctVal / 100))}` : `${Math.round(item.last_close * (pctVal / 100))}`) : '');

    if (abs && pctStr) return `${abs}(${pctStr})`;
    if (pctStr) return pctStr;
    return '-';
  }

  // For Value, Volume, Net Buy, Net Sell: show change_abs & pctStr
  const abs = item.change_abs !== undefined 
    ? (item.change_abs > 0 ? `+${Number(item.change_abs).toLocaleString('id-ID')}` : `${Number(item.change_abs).toLocaleString('id-ID')}`)
    : '';

  if (abs && pctStr) return `${abs}(${pctStr})`;
  if (pctStr) return pctStr;
  return '-';
}

function isPositive(item) {
  if (activeTab.value === 'loser' || activeTab.value === 'net_sell') return false;
  if (item.price_pct !== undefined) return item.price_pct >= 0;
  if (item.change_abs !== undefined) return item.change_abs >= 0;
  return true;
}

// Background circle color based on stock symbol for avatars without images
const avatarColors = [
  '#0d9488', '#059669', '#2563eb', '#7c3aed', '#c026d3', '#db2777', '#d97706', '#dc2626'
];
function getAvatarBg(symbol) {
  if (!symbol) return '#222';
  let hash = 0;
  for (let i = 0; i < symbol.length; i++) hash += symbol.charCodeAt(i);
  return avatarColors[hash % avatarColors.length];
}
</script>

<template>
  <div class="card span12 movers-card">
    <!-- Header -->
    <div class="chd">
      <div class="t">
        MOVERS
        <span class="subtext" style="color:var(--faint);font-weight:400;text-transform:none;letter-spacing:0;margin-left:6px">
          (Sectors.app EOD &amp; Foreign Flow)
        </span>
      </div>
      <div class="meta">
        <span class="live"><span class="d"></span>Live EOD</span>
      </div>
    </div>

    <!-- Scrollable Tab Strip -->
    <div class="movers-tabs-wrapper">
      <div class="movers-tabs">
        <button
          v-for="t in tabs"
          :key="t.id"
          class="tab-btn"
          :class="{ active: activeTab === t.id }"
          @click="activeTab = t.id"
        >
          {{ t.name }}
        </button>
      </div>
    </div>

    <!-- Movers List -->
    <div class="movers-list">
      <div
        v-for="(item, idx) in currentItems"
        :key="item.symbol + idx"
        class="mover-row"
      >
        <!-- Left: Logo + Symbol & Badge + Name -->
        <div class="mover-left">
          <div class="avatar-box">
            <img
              v-if="resolveLogoUrl(item) && !failedLogos[item.symbol]"
              :src="resolveLogoUrl(item)"
              :alt="item.symbol"
              class="avatar-img"
              @error="failedLogos[item.symbol] = true"
            />
            <div
              v-else
              class="avatar-fallback"
              :style="{ backgroundColor: getAvatarBg(item.symbol) }"
            >
              {{ item.symbol ? item.symbol.substring(0, 2) : 'ST' }}
            </div>
          </div>

          <div class="mover-info">
            <div class="symbol-row">
              <span class="symbol-text">{{ item.symbol }}</span>
              <span
                v-if="item.badge"
                class="mover-badge"
                :class="item.badge_type || (isPositive(item) ? 'purp' : 'danger')"
              >
                {{ item.badge }}
              </span>
            </div>
            <div class="name-text" :title="item.name || item.symbol">{{ item.name || item.symbol }}</div>
          </div>
        </div>

        <!-- Right: Price / Metric Value & Change -->
        <div class="mover-right">
          <div class="price-text">{{ formatDisplayValue(item) }}</div>
          <div
            class="change-text"
            :class="isPositive(item) ? 'pos' : 'neg'"
          >
            {{ formatDisplaySub(item) }}
          </div>
        </div>
      </div>

      <div v-if="!currentItems.length" class="empty-state">
        Data tidak tersedia.
      </div>
    </div>

    <!-- Footer Action Button -->
    <div class="movers-footer">
      <button class="expand-btn" @click="isExpanded = !isExpanded">
        {{ isExpanded ? 'Lebih Sedikit ∧' : 'Selengkapnya ›' }}
      </button>
    </div>
  </div>
</template>

<style scoped>
.movers-card {
  padding: 16px 20px;
  background: var(--card, #121212);
  border-radius: 12px;
  border: 1px solid var(--line, #222);
}

.chd {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 14px;
}
.chd .t {
  font-size: 13px;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: var(--ink, #fff);
  font-weight: 700;
}
.chd .meta {
  font-size: 10px;
  color: var(--muted, #888);
}
.live {
  display: flex;
  align-items: center;
  gap: 5px;
  color: var(--green, #46C46E);
  font-weight: 600;
}
.live .d {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: var(--green, #46C46E);
  box-shadow: 0 0 6px var(--green, #46C46E);
}

/* Tabs */
.movers-tabs-wrapper {
  border-bottom: 1px solid var(--line, #242424);
  margin-bottom: 12px;
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
  scrollbar-width: none; /* Firefox */
}
.movers-tabs-wrapper::-webkit-scrollbar {
  display: none; /* Chrome/Safari */
}
.movers-tabs {
  display: flex;
  gap: 20px;
  min-width: max-content;
}
.tab-btn {
  background: transparent;
  border: none;
  color: var(--muted, #7C7C76);
  font-size: 13px;
  font-weight: 600;
  padding: 8px 2px 10px 2px;
  cursor: pointer;
  position: relative;
  transition: color 0.15s ease;
  white-space: nowrap;
}
.tab-btn:hover {
  color: var(--ink, #eaeae7);
}
.tab-btn.active {
  color: var(--green, #46C46E);
  font-weight: 700;
}
.tab-btn.active::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  height: 2px;
  background: var(--green, #46C46E);
  border-radius: 2px 2px 0 0;
}

/* List Rows */
.movers-list {
  display: flex;
  flex-direction: column;
}
.mover-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 4px;
  border-bottom: 1px solid var(--line, #1f1f1f);
  transition: background 0.1s ease;
  min-width: 0;
}
.mover-row:last-child {
  border-bottom: none;
}
.mover-row:hover {
  background: rgba(255, 255, 255, 0.02);
}

/* Mover Left */
.mover-left {
  display: flex;
  align-items: center;
  gap: 12px;
  min-width: 0;
  flex: 1;
}
.avatar-box {
  width: 38px;
  height: 38px;
  border-radius: 50%;
  overflow: hidden;
  flex-shrink: 0;
  background: #1a1a1a;
}
.avatar-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.avatar-fallback {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 11px;
  font-weight: 700;
  color: #ffffff;
  text-transform: uppercase;
}

.mover-info {
  display: flex;
  flex-direction: column;
  gap: 2px;
  min-width: 0;
  flex: 1;
}
.symbol-row {
  display: flex;
  align-items: center;
  gap: 6px;
}
.symbol-text {
  font-size: 14px;
  font-weight: 700;
  color: var(--ink, #eaeae7);
}
.name-text {
  font-size: 11px;
  color: var(--muted, #7c7c76);
  width: 100%;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* Badges */
.mover-badge {
  font-size: 9px;
  font-weight: 700;
  padding: 1px 5px;
  border-radius: 4px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  line-height: 1.2;
}
.mover-badge.warn {
  background: #3f2d11;
  color: #f59e0b;
  border: 1px solid #78350f;
}
.mover-badge.purp {
  background: #2e1065;
  color: #c084fc;
  border: 1px solid #581c87;
}
.mover-badge.ok {
  background: #064e3b;
  color: #34d399;
  border: 1px solid #047857;
}
.mover-badge.danger {
  background: #450a0a;
  color: #f87171;
  border: 1px solid #991b1b;
}

/* Mover Right */
.mover-right {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 2px;
  flex-shrink: 0;
  margin-left: 10px;
}
.price-text {
  font-size: 14px;
  font-weight: 700;
  color: var(--ink, #eaeae7);
}
.change-text {
  font-size: 11px;
  font-weight: 600;
  white-space: nowrap;
}
.change-text.pos {
  color: var(--green, #46C46E);
}
.change-text.neg {
  color: var(--red, #E2705C);
}

.empty-state {
  padding: 16px;
  text-align: center;
  font-size: 12px;
  color: var(--muted, #7C7C76);
}

/* Footer Action */
.movers-footer {
  margin-top: 10px;
  padding-top: 8px;
}
.expand-btn {
  background: transparent;
  border: none;
  color: var(--green, #46C46E);
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  padding: 4px 0;
  display: flex;
  align-items: center;
  gap: 4px;
  transition: opacity 0.15s ease;
}
.expand-btn:hover {
  opacity: 0.8;
}

@media (max-width: 640px) {
  .movers-card {
    padding: 14px 14px;
  }
  .subtext {
    display: none;
  }
  .mover-left {
    gap: 8px;
  }
  .avatar-box {
    width: 34px;
    height: 34px;
  }
  .symbol-text {
    font-size: 13px;
  }
  .price-text {
    font-size: 13px;
  }
  .change-text {
    font-size: 10.5px;
  }
}
</style>
