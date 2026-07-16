<template>
  <div class="panelBody" id="insiderBody"></div>
</template>
<script setup>
import { onMounted, watch } from 'vue';
import { activeTab } from '../../../Composables/useOwnershipLogic';
onMounted(() => { if (window.renderInsider) window.renderInsider(); });
watch(activeTab, (val) => { if (val === 'insiderPane' && window.renderInsider) setTimeout(() => window.renderInsider(), 50); });
</script>
