<template>
  <div class="panelBody" id="tipeBody"></div>
</template>
<script setup>
import { onMounted, watch } from 'vue';
import { activeTab } from '../../../Composables/useOwnershipLogic';
onMounted(() => { if (window.renderTipe) window.renderTipe(); });
watch(activeTab, (val) => { if (val === 'tipePane' && window.renderTipe) setTimeout(() => window.renderTipe(), 50); });
</script>
