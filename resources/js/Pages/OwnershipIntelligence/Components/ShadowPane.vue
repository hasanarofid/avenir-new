<template>
  <div class="panelBody" id="shadowBody"></div>
</template>
<script setup>
import { onMounted, watch } from 'vue';
import { activeTab } from '../../../Composables/useOwnershipLogic';
onMounted(() => { if (window.renderShadow) window.renderShadow(); });
watch(activeTab, (val) => { if (val === 'shadowPane' && window.renderShadow) setTimeout(() => window.renderShadow(), 50); });
</script>
