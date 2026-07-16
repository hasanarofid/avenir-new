<template>
  <div class="panelBody" id="govBody"></div>
</template>
<script setup>
import { onMounted, watch } from 'vue';
import { activeTab } from '../../../Composables/useOwnershipLogic';

onMounted(() => {
    if (window.renderGov) window.renderGov();
});
watch(activeTab, (val) => {
    if (val === 'govPane' && window.renderGov) {
        setTimeout(() => window.renderGov(), 50);
    }
});
</script>
