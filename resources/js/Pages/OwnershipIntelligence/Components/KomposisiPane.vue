<template>
  <div class="panelBody" id="komposisiBody"></div>
</template>
<script setup>
import { onMounted, watch } from 'vue';
import { activeTab } from '../../../Composables/useOwnershipLogic';
onMounted(() => { if (window.renderKomposisi) window.renderKomposisi(); });
watch(activeTab, (val) => { if (val === 'komposisiPane' && window.renderKomposisi) setTimeout(() => window.renderKomposisi(), 50); });
</script>
