<template>
  <div class="panelBody" id="instBody"></div>
</template>
<script setup>
import { onMounted, watch } from 'vue';
import { activeTab } from '../../../Composables/useOwnershipLogic';
onMounted(() => { if (window.renderInst) window.renderInst(); });
watch(activeTab, (val) => { if (val === 'instPane' && window.renderInst) setTimeout(() => window.renderInst(), 50); });
</script>
