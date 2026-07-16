<template>
  <div class="panelBody" id="adminBody"></div>
</template>
<script setup>
import { onMounted, watch } from 'vue';
import { activeTab } from '../../../Composables/useOwnershipLogic';
onMounted(() => { if (window.renderAdmin) window.renderAdmin(); });
watch(activeTab, (val) => { if (val === 'adminPane' && window.renderAdmin) setTimeout(() => window.renderAdmin(), 50); });
</script>
