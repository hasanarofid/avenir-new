<template>
  <div class="panelBody" id="klasifikasiBody"></div>
</template>
<script setup>
import { onMounted, watch } from 'vue';
import { activeTab } from '../../../Composables/useOwnershipLogic';
onMounted(() => { if (window.renderKlasifikasi) window.renderKlasifikasi(); });
watch(activeTab, (val) => { if (val === 'klasifikasiPane' && window.renderKlasifikasi) setTimeout(() => window.renderKlasifikasi(), 50); });
</script>
