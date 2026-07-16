<template>
  <div class="panelBody" id="auditBody"></div>
</template>
<script setup>
import { onMounted, watch } from 'vue';
import { activeTab } from '../../../Composables/useOwnershipLogic';
onMounted(() => { if (window.renderAudit) window.renderAudit(); });
watch(activeTab, (val) => { if (val === 'auditPane' && window.renderAudit) setTimeout(() => window.renderAudit(), 50); });
</script>
