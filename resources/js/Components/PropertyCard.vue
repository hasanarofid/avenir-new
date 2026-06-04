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
      <span class="sector">{{ data.sector }}</span>
      <span class="ticker" v-if="data.ticker">{{ data.ticker }}</span>
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
      <Link :href="`/property/${data.id}`" class="cta" style="width: 100%; justify-content: center;">
        Lihat Detail
      </Link>
    </div>
  </div>
</template>
