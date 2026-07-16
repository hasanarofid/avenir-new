<template>
  <div class="panelBody" id="changesBody"></div>
</template>
<script setup>
import { onMounted, watch } from 'vue';
import { activeTab } from '../../../Composables/useOwnershipLogic';
onMounted(() => { if (window.renderChanges) window.renderChanges(); });
watch(activeTab, (val) => { if (val === 'changesPane' && window.renderChanges) setTimeout(() => window.renderChanges(), 50); });
</script>
