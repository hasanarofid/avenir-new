<script setup>
import { exportChanges, setSelected } from '../../../Composables/useOwnershipLogic';
</script>

<template>
  <div class="topbar">
    <div class="tabs"><span>Beranda</span><span class="on">Research</span><span>Market</span><span>Langganan</span></div>
    <div class="search">
      <input id="globalSearch" list="entityList" placeholder="Cari emiten, investor, atau grup..."/>
      <datalist id="entityList"></datalist>
      <button id="goSearch">Cari</button>
    </div>
  </div>
  <section class="header">
    <div>
      <div class="eyebrow">Issuer-level audit + connected investor graph</div>
      <div class="title">Ownership Intelligence</div>
      <div class="sub">Pemetaan kepemilikan lintas emiten: siapa memiliki siapa, emiten apa saja yang dimiliki investor, perubahan saham naik/turun, proxy UBO, dan audit konsentrasi per issuer.</div>
    </div>
    <div class="actions">
      <button class="btn" id="exportCsv" @click="exportChanges">Export Changes</button>
      <button class="btn primary" id="resetBtn" @click="setSelected('E:BRPT')">Reset</button>
    </div>
  </section>
</template>
