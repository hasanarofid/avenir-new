<script setup>
import { exportChanges, setSelected } from '../../../Composables/useOwnershipLogic';
</script>

<template>
  <section class="header">
    <div>
      <div class="eyebrow">Issuer-level audit + connected investor graph</div>
      <div class="title">Ownership Intelligence</div>
      <div class="sub">Pemetaan kepemilikan lintas emiten: siapa memiliki siapa, emiten apa saja yang dimiliki investor, perubahan saham naik/turun, proxy UBO, dan audit konsentrasi per issuer.</div>
    </div>
    <div class="actions">
      <button class="btn" @click="exportChanges">Export Changes</button>
      <button class="btn primary" @click="setSelected('E:BRPT')">Reset BRPT</button>
    </div>
  </section>
</template>
