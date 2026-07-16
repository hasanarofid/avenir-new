<template>
  <div class="panelBody" id="groupBody"></div>
</template>
<script setup>
import { onMounted, watch } from 'vue';
import { activeTab } from '../../../Composables/useOwnershipLogic';
onMounted(() => { if (window.renderGroup) window.renderGroup(); });
watch(activeTab, (val) => { if (val === 'groupPane' && window.renderGroup) setTimeout(() => window.renderGroup(), 50); });
</script>
