<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    data: Object
});
</script>

<template>
  <div class="card">
    <div class="card-hd">
      <div class="badges">
        <span class="badge bnew" v-if="data.status === 'available'">AVAILABLE</span>
      </div>
      <div class="card-meta">
        <span class="sector" v-if="data.sector" v-html="data.sector"></span>
        <span class="ticker" v-if="data.ticker">{{ data.ticker }}</span>
      </div>
      <h2>{{ data.title }}</h2>
      <p class="card-sub" v-html="data.subtitle"></p>
    </div>
    <div class="cdiv"></div>
    <div class="card-m">
      <div v-if="data.revenue">
        <div class="cmv">{{ data.revenue }}</div>
        <div class="cml">Revenue</div>
      </div>
      <div v-if="data.patmi">
        <div class="cmv">{{ data.patmi }}</div>
        <div class="cml">PATMI</div>
      </div>
      <div v-if="data.sales">
        <div class="cmv">{{ data.sales }}</div>
        <div class="cml">Sales</div>
      </div>
      <div v-if="!data.revenue && data.price">
        <div class="cmv">{{ data.price ? `Rp ${data.price}` : 'TBA' }}</div>
        <div class="cml">Harga</div>
      </div>
    </div>
    <div class="cdiv"></div>
    <div class="card-ft">
      <Link :href="`/property/${data.id}`" class="cta">
        Lihat Detail
      </Link>
    </div>
  </div>
</template>

<style scoped>
.card {
  position: relative;
  background-color: #111413;
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 16px;
  padding: 24px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
  overflow: hidden;
  z-index: 1;
}

.card::after {
  content: '';
  position: absolute;
  inset: 0;
  background: radial-gradient(circle at 50% 50%, rgba(34, 197, 94, 0.05) 0%, transparent 70%);
  opacity: 0;
  transition: opacity 0.4s ease;
  pointer-events: none;
  z-index: -1;
}

.card:hover {
  border-color: rgba(34, 197, 94, 0.25);
  transform: translateY(-6px);
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.6), 0 0 30px rgba(34, 197, 94, 0.03);
}

.card:hover::after {
  opacity: 1;
}

.card-hd {
  flex-grow: 1;
}

.badges {
  display: flex;
  gap: 6px;
  margin-bottom: 12px;
}

.badge {
  font-size: 10px;
  font-weight: 700;
  letter-spacing: 0.05em;
  padding: 3px 8px;
  border-radius: 4px;
  text-transform: uppercase;
}

.bnew {
  background: rgba(34, 197, 94, 0.1);
  color: #22c55e;
  border: 1px solid rgba(34, 197, 94, 0.2);
}

.card-meta {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 8px;
  margin-bottom: 12px;
}

.sector {
  font-size: 11px;
  color: #9ca3af;
  text-transform: uppercase;
  font-weight: 600;
  letter-spacing: 0.02em;
}

.ticker {
  font-family: 'JetBrains Mono', 'Fira Code', monospace;
  font-size: 11px;
  color: #22c55e;
  background: rgba(34, 197, 94, 0.06);
  border: 1px solid rgba(34, 197, 94, 0.18);
  padding: 2px 6px;
  border-radius: 4px;
  font-weight: 600;
}

h2 {
  font-family: 'Space Grotesk', sans-serif;
  font-size: 19px;
  font-weight: 700;
  color: #ffffff;
  margin: 0 0 10px 0;
  line-height: 1.4;
}

.card-sub {
  font-size: 13px;
  color: #9ca3af;
  line-height: 1.6;
  margin: 0;
}

/* Deep rendering styling for sub field span formatting */
.card-sub :deep(span) {
  border-radius: 4px;
  padding: 1px 4px;
  font-size: 11.5px;
}

.cdiv {
  border-top: 1px solid rgba(255, 255, 255, 0.06);
  margin: 16px 0;
}

.card-m {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 8px;
  text-align: center;
}

.cmv {
  font-family: 'JetBrains Mono', monospace;
  font-size: 13.5px;
  font-weight: 700;
  color: #ffffff;
  overflow-wrap: anywhere;
}

.cml {
  font-size: 10.5px;
  font-weight: 600;
  color: #6b7280;
  text-transform: uppercase;
  margin-top: 4px;
  letter-spacing: 0.02em;
}

.card-ft {
  margin-top: auto;
}

.cta {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  padding: 10px 16px;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 600;
  color: #d1d5db;
  background: transparent;
  border: 1px solid rgba(255, 255, 255, 0.1);
  text-decoration: none;
  transition: all 0.2s ease;
}

.card:hover .cta {
  border-color: #22c55e;
  background: #22c55e;
  color: #ffffff;
  box-shadow: 0 0 15px rgba(34, 197, 94, 0.35);
}
</style>
