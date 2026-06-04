<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import PropertyCard from '@/Components/PropertyCard.vue';

defineProps({
    researches: Array
});
</script>

<template>
  <AppLayout>
    <!-- HERO SECTION -->
    <div class="idx-hero">
      <div class="idx-tag"><span class="dot"></span>AVENIR RESEARCH · EQUITY ANALYSIS</div>
      <h1>Riset &amp; <span class="hl">Publikasi</span></h1>
      <p>Laporan riset ekuitas mendalam berbasis Annual Report audited. Data terverifikasi, valuasi FCFE tiga skenario.</p>
      <div class="idx-stats">
        <div><div class="is-v" data-auto-count="research">{{ researches.length }}</div><div class="is-l">Riset Tersedia</div></div>
        <div><div class="is-v">4 Tahun</div><div class="is-l">Data Historis</div></div>
        <div><div class="is-v">FCFE</div><div class="is-l">Model Valuasi</div></div>
        <div><div class="is-v">100%</div><div class="is-l">AR Audited</div></div>
      </div>
    </div>

    <!-- FILTER BAR -->
    <div class="filter-bar">
      <span class="filter-lbl">Filter:</span>
      <button class="fbtn active">Semua</button>
      <button class="fbtn fbtn-mine" id="filter-mine">⭐ Riset Saya <span id="filter-mine-count" style="opacity:.7;margin-left:4px"></span></button>
      <button class="fbtn">Mining &amp; EPC</button>
      <button class="fbtn">Consumer</button>
      <button class="fbtn">AI &amp; Tech</button>
      <button class="fbtn">Perbankan</button>
      <button class="fbtn">Telco &amp; Infra</button>
      <button class="fbtn">Manufacturing</button>
      <button class="fbtn">Agribusiness</button>
      <button class="fbtn">Power &amp; Utilities</button>
      <button class="fbtn">Property</button>
      <button class="fbtn">Healthcare</button>
    </div>

    <!-- MAIN GRID -->
    <div class="grid">
      <PropertyCard 
        v-for="research in researches" 
        :key="research.id" 
        :data="research" 
      />
    </div>
  </AppLayout>
</template>
